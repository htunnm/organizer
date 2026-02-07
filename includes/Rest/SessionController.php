<?php
/**
 * Session REST Controller.
 *
 * @package Organizer\Rest
 */

namespace Organizer\Rest;

use WP_REST_Controller;
use WP_Error;
use Organizer\Model\Session;
use Organizer\Model\Registration;
use Organizer\Services\Logger;
use Organizer\Services\Email\GmailAdapter;
use Organizer\Services\Email\TemplateService;

/**
 * Class SessionController
 */
class SessionController extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'organizer/v1';
		$this->rest_base = 'sessions';
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)/cancel',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'cancel_item' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => '__return_true',
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Check permissions.
	 *
	 * @return bool
	 */
	public function permissions_check() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Cancel a session.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|WP_Error Response object.
	 */
	public function cancel_item( $request ) {
		$id      = (int) $request->get_param( 'id' );
		$session = Session::get( $id );

		if ( ! $session ) {
			return new WP_Error( 'not_found', __( 'Session not found', 'organizer' ), array( 'status' => 404 ) );
		}

		if ( 'cancelled' === $session->status ) {
			return new WP_Error( 'already_cancelled', __( 'Session is already cancelled', 'organizer' ), array( 'status' => 400 ) );
		}

		$updated = Session::cancel( $id );

		if ( false === $updated ) {
			return new WP_Error( 'db_error', __( 'Could not cancel session', 'organizer' ), array( 'status' => 500 ) );
		}

		Logger::log( 'session_cancelled', "Session $id cancelled", $session->event_id, $id );

		// Notify attendees.
		$attendees        = Registration::get_attendees( $session->event_id, $id );
		$email_service    = new GmailAdapter();
		$template_service = new TemplateService();
		$template         = $template_service->get_template( 'session_cancelled' );
		$placeholders     = array(
			'event_title' => get_the_title( $session->event_id ),
			'start_date'  => $session->start_datetime,
		);
		$subject          = $template_service->render( $template['subject'], $placeholders );
		$message          = $template_service->render( $template['message'], $placeholders );

		foreach ( $attendees as $attendee ) {
			$email_service->send( $attendee->email, $subject, nl2br( $message ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Session cancelled and attendees notified', 'organizer' ),
			)
		);
	}

	/**
	 * Get sessions.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public function get_items( $request ) {
		$limit  = $request->get_param( 'limit' ) ? (int) $request->get_param( 'limit' ) : 20;
		$offset = $request->get_param( 'offset' ) ? (int) $request->get_param( 'offset' ) : 0;

		$filters = array();
		if ( $request->get_param( 'keyword' ) ) {
			$filters['keyword'] = sanitize_text_field( $request->get_param( 'keyword' ) );
		}
		if ( $request->get_param( 'start_date' ) ) {
			$filters['start_date'] = sanitize_text_field( $request->get_param( 'start_date' ) );
		}
		if ( $request->get_param( 'end_date' ) ) {
			$filters['end_date'] = sanitize_text_field( $request->get_param( 'end_date' ) );
		}

		$sessions = Session::get_all( $limit, $offset, 'start_datetime', 'ASC', '', $filters );
		$data     = array();

		foreach ( $sessions as $session ) {
			$data[] = $this->prepare_session_for_response( $session );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get a single session.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response object.
	 */
	public function get_item( $request ) {
		$id      = (int) $request->get_param( 'id' );
		$session = Session::get( $id );

		if ( ! $session ) {
			return new WP_Error( 'not_found', __( 'Session not found', 'organizer' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $this->prepare_session_for_response( $session ) );
	}

	/**
	 * Prepare session for response.
	 *
	 * @param object|array $session Session data.
	 * @return array Prepared data.
	 */
	private function prepare_session_for_response( $session ) {
		$session  = (object) $session;
		$event_id = $session->event_id;
		$venue    = get_post_meta( $event_id, '_organizer_event_venue', true );

		return array(
			'id'             => $session->id,
			'event_id'       => $session->event_id,
			'event_title'    => get_the_title( $event_id ),
			'start_datetime' => $session->start_datetime,
			'end_datetime'   => $session->end_datetime,
			'status'         => $session->status,
			'capacity'       => $session->capacity,
			'venue'          => $venue ? $venue : null,
		);
	}
}
