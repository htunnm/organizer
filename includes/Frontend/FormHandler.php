<?php
/**
 * Form Handler.
 *
 * @package Organizer\Frontend
 */

namespace Organizer\Frontend;

use Organizer\Model\Event;
use Organizer\Model\Registration;
use Organizer\Model\Waitlist;
use Organizer\Services\Email\GmailAdapter;
use Organizer\Services\IcsGenerator;
use Organizer\Model\Session;
use Organizer\Services\Email\TemplateService;
use Organizer\Model\RegistrationMeta;
use Organizer\Services\Payment\StripeService;
use Organizer\Services\QrCodeService;
use Organizer\Model\DiscountCode;
use Organizer\Model\Feedback;

/**
 * Class FormHandler
 */
class FormHandler {

	/**
	 * Initialize the handler.
	 */
	public static function init() {
		add_action( 'admin_post_organizer_register', array( __CLASS__, 'handle_registration' ) );
		add_action( 'admin_post_nopriv_organizer_register', array( __CLASS__, 'handle_registration' ) );
		add_action( 'admin_post_organizer_payment_return', array( __CLASS__, 'handle_payment_return' ) );
		add_action( 'admin_post_organizer_cancel_registration', array( __CLASS__, 'handle_cancellation' ) );
		add_action( 'admin_post_organizer_update_profile', array( __CLASS__, 'handle_profile_update' ) );
		add_action( 'admin_post_organizer_submit_feedback', array( __CLASS__, 'handle_feedback_submission' ) );
	}

	/**
	 * Handle registration form submission.
	 */
	public static function handle_registration() {
		if ( ! isset( $_POST['organizer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['organizer_nonce'] ) ), 'organizer_register_nonce' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'organizer' ) );
		}

		$event_id   = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
		$session_id = isset( $_POST['session_id'] ) ? absint( $_POST['session_id'] ) : 0;
		$name       = isset( $_POST['organizer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['organizer_name'] ) ) : '';
		$email      = isset( $_POST['organizer_email'] ) ? sanitize_email( wp_unslash( $_POST['organizer_email'] ) ) : '';

		if ( empty( $event_id ) || empty( $name ) || empty( $email ) ) {
			wp_safe_redirect( add_query_arg( 'organizer_registration', 'error', wp_get_referer() ) );
			exit;
		}

		$data = array(
			'event_id'   => $event_id,
			'session_id' => $session_id,
			'name'       => $name,
			'email'      => $email,
			'status'     => 'pending', // Default.
		);

		$price = (float) get_post_meta( $event_id, '_organizer_event_price', true );
		if ( $price > 0 ) {
			// Handle Discount.
			$discount_code = isset( $_POST['discount_code'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_code'] ) ) : '';
			if ( ! empty( $discount_code ) ) {
				$discount = DiscountCode::get_by_code( $discount_code );
				if ( $discount ) {
					// Validate expiration and limit.
					$valid = true;
					if ( ! empty( $discount->expires_at ) && strtotime( $discount->expires_at ) < time() ) {
						$valid = false;
					}
					if ( $discount->usage_limit > 0 && $discount->usage_count >= $discount->usage_limit ) {
						$valid = false;
					}

					if ( $valid ) {
						$original_price = $price;
						if ( 'percent' === $discount->type ) {
							$price = $price - ( $price * ( $discount->amount / 100 ) );
						} else {
							$price = $price - $discount->amount;
						}
						$price                   = max( 0, $price );
						$data['discount_code']   = $discount->code;
						$data['discount_amount'] = (int) ( ( $original_price - $price ) * 100 ); // Store in cents.
						DiscountCode::increment_usage( $discount->id );
					}
				}
			}

			$data['status'] = 'pending_payment';
		}

		// Check capacity.
		$is_full = false;
		if ( $session_id > 0 ) {
			$is_full = Session::is_full( $session_id );
		} elseif ( Event::is_full( $event_id ) ) {
			$is_full = true;
		}

		if ( $is_full ) {
			Waitlist::add( $data );
			// Send waitlist email (simplified for brevity, similar to API).
			wp_safe_redirect( add_query_arg( 'organizer_registration', 'waitlist', wp_get_referer() ) );
			exit;
		}

		$id = Registration::create( $data );

		if ( ! $id ) {
			wp_safe_redirect( add_query_arg( 'organizer_registration', 'error', wp_get_referer() ) );
			exit;
		}

		// Save custom meta.
		if ( isset( $_POST['organizer_meta'] ) && is_array( $_POST['organizer_meta'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$organizer_meta = wp_unslash( $_POST['organizer_meta'] );
			foreach ( $organizer_meta as $key => $value ) {
				$clean_key   = sanitize_text_field( $key );
				$clean_value = sanitize_text_field( $value );
				RegistrationMeta::add( $id, $clean_key, $clean_value );
			}
		}

		// Handle Payment.
		if ( 0.0 === $price && isset( $data['discount_code'] ) ) {
			// 100% discount, skip payment.
			$data['status'] = 'confirmed';
			// Update status in DB since we created it as pending_payment (or update logic flow).
			// Actually, we created it above. Let's update it.
			global $wpdb;
			$table_name = Registration::get_table_name();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update( $table_name, array( 'status' => 'confirmed' ), array( 'id' => $id ) );
		} elseif ( $price > 0 ) {
			$stripe_service = new StripeService();
			$options        = get_option( 'organizer_options' );
			$currency       = isset( $options['organizer_currency'] ) ? $options['organizer_currency'] : 'USD';
			$event_title    = get_the_title( $event_id );

			$success_url = admin_url( 'admin-post.php?action=organizer_payment_return&reg_id=' . $id );
			$cancel_url  = add_query_arg( 'organizer_registration', 'cancelled', wp_get_referer() );

			$session = $stripe_service->create_checkout_session( $id, $event_title, $price, $currency, $success_url, $cancel_url );

			if ( $session && isset( $session->url ) ) {
				// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				wp_redirect( $session->url );
				exit;
			}
			// Fallback if Stripe fails.
			wp_safe_redirect( add_query_arg( 'organizer_registration', 'error', wp_get_referer() ) );
			exit;
		}

		// Fetch created registration to get token.
		global $wpdb;
		$table_name = Registration::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$registration = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );
		$token        = $registration ? $registration->checkin_token : '';

		// Generate QR Code for attachment.
		$qr_service  = new QrCodeService();
		$checkin_url = admin_url( 'admin-post.php?action=organizer_checkin&token=' . $token );
		$upload_dir  = wp_upload_dir();
		$qr_path     = $upload_dir['basedir'] . '/qr-' . $token . '.png';
		$qr_service->generate_file( $checkin_url, $qr_path );

		// Send confirmation email with ICS.
		$email_service    = new GmailAdapter();
		$template_service = new TemplateService();
		$template         = $template_service->get_template( 'registration_confirmation', $event_id );
		$placeholders     = array(
			'ticket_link'   => home_url( '?organizer_ticket=1&token=' . $token ),
			'attendee_name' => esc_html( $name ),
			'event_title'   => get_the_title( $event_id ),
		);
		$subject          = $template_service->render( $template['subject'], $placeholders );
		$message          = $template_service->render( $template['message'], $placeholders );

		$attachments = array();
		if ( $session_id ) {
			global $wpdb;
			$session_table = Session::get_table_name();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$session = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $session_table WHERE id = %d", $session_id ) );

			if ( $session ) {
				$ics_generator = new IcsGenerator();
				$event_title   = get_the_title( $event_id );
				$ics_content   = $ics_generator->generate_session_ics( $session, $event_title );
				$upload_dir    = wp_upload_dir();
				$file_path     = $upload_dir['basedir'] . '/invite.ics';
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				file_put_contents( $file_path, $ics_content );
				$attachments[] = $file_path;
			}
		}

		if ( file_exists( $qr_path ) ) {
			$attachments[] = $qr_path;
		}

		$email_service->send( $email, $subject, nl2br( $message ), array(), $attachments );

		wp_safe_redirect( add_query_arg( 'organizer_registration', 'success', wp_get_referer() ) );
		exit;
	}

	/**
	 * Handle payment return from Stripe.
	 */
	public static function handle_payment_return() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$reg_id = isset( $_GET['reg_id'] ) ? absint( $_GET['reg_id'] ) : 0;

		if ( empty( $reg_id ) ) {
			wp_die( esc_html__( 'Invalid registration.', 'organizer' ) );
		}

		// Update status.
		global $wpdb;
		$table_name = Registration::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update( $table_name, array( 'status' => 'confirmed' ), array( 'id' => $reg_id ) );

		// Fetch registration details for email.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$registration = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $reg_id ) );

		if ( $registration ) {
			$email_service    = new GmailAdapter();
			$template_service = new TemplateService();
			$template         = $template_service->get_template( 'registration_confirmation', $registration->event_id );
			$placeholders     = array(
				'attendee_name' => esc_html( $registration->name ),
				'event_title'   => get_the_title( $registration->event_id ),
			);
			$subject          = $template_service->render( $template['subject'], $placeholders );
			$message          = $template_service->render( $template['message'], $placeholders );

			$attachments = array();
			if ( $registration->session_id ) {
				$session_table = Session::get_table_name();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$session = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $session_table WHERE id = %d", $registration->session_id ) );

				if ( $session ) {
					$ics_generator = new IcsGenerator();
					$event_title   = get_the_title( $registration->event_id );
					$ics_content   = $ics_generator->generate_session_ics( $session, $event_title );
					$upload_dir    = wp_upload_dir();
					$file_path     = $upload_dir['basedir'] . '/invite.ics';
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
					file_put_contents( $file_path, $ics_content );
					$attachments[] = $file_path;
				}
			}

			$email_service->send( $registration->email, $subject, nl2br( $message ), array(), $attachments );
		}

		wp_safe_redirect( add_query_arg( 'organizer_registration', 'success', home_url() ) );
		exit;
	}

	/**
	 * Handle registration cancellation.
	 */
	public static function handle_cancellation() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You must be logged in to cancel a registration.', 'organizer' ) );
		}

		if ( ! isset( $_POST['organizer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['organizer_nonce'] ) ), 'organizer_cancel_nonce' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'organizer' ) );
		}

		$registration_id = isset( $_POST['registration_id'] ) ? absint( $_POST['registration_id'] ) : 0;

		if ( empty( $registration_id ) ) {
			wp_safe_redirect( add_query_arg( 'organizer_cancellation', 'error', wp_get_referer() ) );
			exit;
		}

		// Verify ownership.
		global $wpdb;
		$table_name = Registration::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$registration = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $registration_id ) );

		$current_user = wp_get_current_user();

		if ( ! $registration || $registration->email !== $current_user->user_email ) {
			wp_die( esc_html__( 'You are not authorized to cancel this registration.', 'organizer' ) );
		}

		// Update status to cancelled.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->update( $table_name, array( 'status' => 'cancelled' ), array( 'id' => $registration_id ) );

		if ( false === $result ) {
			wp_safe_redirect( add_query_arg( 'organizer_cancellation', 'error', wp_get_referer() ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( 'organizer_cancellation', 'success', wp_get_referer() ) );
		exit;
	}

	/**
	 * Handle profile update.
	 */
	public static function handle_profile_update() {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You must be logged in to update your profile.', 'organizer' ) );
		}

		if ( ! isset( $_POST['organizer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['organizer_nonce'] ) ), 'organizer_profile_nonce' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'organizer' ) );
		}

		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;
		$first_name   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name    = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		// Password fields.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$password_confirm = isset( $_POST['password_confirm'] ) ? wp_unslash( $_POST['password_confirm'] ) : '';

		if ( ! empty( $password ) && $password !== $password_confirm ) {
			wp_die( esc_html__( 'Passwords do not match.', 'organizer' ) );
		}

		if ( ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'organizer_profile_update', 'error', wp_get_referer() ) );
			exit;
		}

		$user_data = array(
			'ID'         => $user_id,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'user_email' => $email,
		);

		if ( ! empty( $password ) ) {
			$user_data['user_pass'] = $password;
		}

		$updated_user_id = wp_update_user( $user_data );

		if ( is_wp_error( $updated_user_id ) ) {
			wp_safe_redirect( add_query_arg( 'organizer_profile_update', 'error', wp_get_referer() ) );
			exit;
		}

		// Update past registrations if email changed.
		if ( $email !== $current_user->user_email ) {
			Registration::update_email( $current_user->user_email, $email );
		}

		// Handle Avatar Upload.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! empty( $_FILES['organizer_avatar']['name'] ) ) {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$uploadedfile     = $_FILES['organizer_avatar'];
			$upload_overrides = array( 'test_form' => false );

			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

			if ( $movefile && ! isset( $movefile['error'] ) ) {
				$attachment = array(
					'post_mime_type' => $movefile['type'],
					'post_title'     => sanitize_file_name( $uploadedfile['name'] ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);
				$attach_id  = wp_insert_attachment( $attachment, $movefile['file'] );
				// Ideally generate metadata here too.
				update_user_meta( $user_id, 'organizer_avatar', $attach_id );
			}
		}

		wp_safe_redirect( add_query_arg( 'organizer_profile_update', 'success', wp_get_referer() ) );
		exit;
	}

	/**
	 * Handle feedback submission.
	 */
	public static function handle_feedback_submission() {
		if ( ! isset( $_POST['organizer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['organizer_nonce'] ) ), 'organizer_feedback_nonce' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'organizer' ) );
		}

		$event_id        = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
		$registration_id = isset( $_POST['registration_id'] ) ? absint( $_POST['registration_id'] ) : 0;
		$rating          = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;
		$comment         = isset( $_POST['comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comment'] ) ) : '';

		if ( empty( $event_id ) || empty( $rating ) ) {
			wp_die( esc_html__( 'Missing required fields.', 'organizer' ) );
		}

		Feedback::submit(
			array(
				'event_id'        => $event_id,
				'registration_id' => $registration_id,
				'rating'          => $rating,
				'comment'         => $comment,
			)
		);

		wp_safe_redirect( add_query_arg( 'organizer_feedback', 'success', wp_get_referer() ) );
		exit;
	}
}
