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
			'status'     => 'pending',
		);

		// Check capacity.
		if ( Event::is_full( $event_id ) ) {
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

		// Send confirmation email with ICS.
		$email_service = new GmailAdapter();
		$subject       = __( 'Registration Confirmation', 'organizer' );
		$message       = sprintf(
			/* translators: %s: Attendee Name */
			__( 'Hi %s,<br><br>Thank you for registering for the event.', 'organizer' ),
			esc_html( $name )
		);

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

		$email_service->send( $email, $subject, $message, array(), $attachments );

		wp_safe_redirect( add_query_arg( 'organizer_registration', 'success', wp_get_referer() ) );
		exit;
	}
}
