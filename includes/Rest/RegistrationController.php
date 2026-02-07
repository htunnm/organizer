<?php
/**
 * Registration REST Controller.
 *
 * @package Organizer\Rest
 */

namespace Organizer\Rest;

use WP_REST_Controller;
use WP_Error;
use Organizer\Model\Registration;
use Organizer\Model\Waitlist;
use Organizer\Model\Event;
use Organizer\Services\Email\GmailAdapter;
use Organizer\Services\IcsGenerator;
use Organizer\Model\Session;
use Organizer\Services\RateLimiter;
use Organizer\Services\Email\TemplateService;

/**
 * Class RegistrationController
 */
class RegistrationController extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'organizer/v1';
		$this->rest_base = 'registrations';
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Create a registration.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|WP_Error Response object.
	 */
	public function create_item( $request ) {
		// Rate limiting.
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '127.0.0.1';
		if ( ! RateLimiter::check( $ip ) ) {
			return new WP_Error( 'rate_limit_exceeded', __( 'Too many requests. Please try again later.', 'organizer' ), array( 'status' => 429 ) );
		}

		$params = $request->get_params();

		if ( empty( $params['event_id'] ) || empty( $params['name'] ) || empty( $params['email'] ) ) {
			return new WP_Error( 'missing_params', __( 'Missing required parameters', 'organizer' ), array( 'status' => 400 ) );
		}

		if ( ! is_email( $params['email'] ) ) {
			return new WP_Error( 'invalid_email', __( 'Invalid email address', 'organizer' ), array( 'status' => 400 ) );
		}

		$data = array(
			'event_id' => absint( $params['event_id'] ),
			'name'     => sanitize_text_field( $params['name'] ),
			'email'    => sanitize_email( $params['email'] ),
			'status'   => 'pending',
		);

		// Check capacity.
		if ( Event::is_full( $data['event_id'] ) ) {
			$id = Waitlist::add( $data );

			if ( ! $id ) {
				return new WP_Error( 'db_error', __( 'Could not add to waitlist', 'organizer' ), array( 'status' => 500 ) );
			}

			// Send waitlist email.
			$email_service    = new GmailAdapter();
			$template_service = new TemplateService();
			$template         = $template_service->get_template( 'waitlist_confirmation' );
			$placeholders     = array(
				'attendee_name' => esc_html( $data['name'] ),
				'event_title'   => get_the_title( $data['event_id'] ),
			);
			$subject          = $template_service->render( $template['subject'], $placeholders );
			$message          = $template_service->render( $template['message'], $placeholders );
			$email_service->send( $data['email'], $subject, nl2br( $message ) );

			return rest_ensure_response(
				array(
					'id'      => $id,
					'message' => __( 'Event is full. You have been added to the waitlist.', 'organizer' ),
					'status'  => 'waitlist',
				)
			);
		}

		$id = Registration::create( $data );

		if ( ! $id ) {
			return new WP_Error( 'db_error', __( 'Could not create registration', 'organizer' ), array( 'status' => 500 ) );
		}

		// Send confirmation email.
		$email_service    = new GmailAdapter();
		$template_service = new TemplateService();
		$template         = $template_service->get_template( 'registration_confirmation' );
		$placeholders     = array(
			'attendee_name' => esc_html( $data['name'] ),
			'event_title'   => get_the_title( $data['event_id'] ),
		);
		$subject          = $template_service->render( $template['subject'], $placeholders );
		$message          = $template_service->render( $template['message'], $placeholders );

		// Generate ICS.
		$attachments = array();
		if ( ! empty( $params['session_id'] ) ) {
			global $wpdb;
			$session_table = Session::get_table_name();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$session = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $session_table WHERE id = %d", $params['session_id'] ) );

			if ( $session ) {
				$ics_generator = new IcsGenerator();
				$event_title   = get_the_title( $data['event_id'] );
				$ics_content   = $ics_generator->generate_session_ics( $session, $event_title );
				$upload_dir    = wp_upload_dir();
				$file_path     = $upload_dir['basedir'] . '/invite.ics';
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				file_put_contents( $file_path, $ics_content );
				$attachments[] = $file_path;
			}
		}

		$email_service->send( $data['email'], $subject, nl2br( $message ), array(), $attachments );

		return rest_ensure_response(
			array(
				'id'      => $id,
				'message' => __( 'Registration successful', 'organizer' ),
			)
		);
	}
}
