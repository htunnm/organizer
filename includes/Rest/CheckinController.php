<?php
/**
 * Checkin REST Controller.
 *
 * @package Organizer\Rest
 */

namespace Organizer\Rest;

use WP_REST_Controller;
use WP_Error;
use Organizer\Model\Registration;

/**
 * Class CheckinController
 */
class CheckinController extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'organizer/v1';
		$this->rest_base = 'checkin';
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
					'permission_callback' => array( $this, 'permissions_check' ),
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
	 * Perform check-in.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|WP_Error Response object.
	 */
	public function create_item( $request ) {
		$token = $request->get_param( 'token' );

		if ( empty( $token ) ) {
			return new WP_Error( 'missing_token', __( 'Missing token', 'organizer' ), array( 'status' => 400 ) );
		}

		$registration = Registration::get_by_token( $token );

		if ( ! $registration ) {
			return new WP_Error( 'invalid_token', __( 'Invalid token', 'organizer' ), array( 'status' => 404 ) );
		}

		if ( ! empty( $registration->checked_in_at ) ) {
			return new WP_Error( 'already_checked_in', __( 'Already checked in', 'organizer' ), array( 'status' => 400 ) );
		}

		Registration::checkin( $registration->id );

		return rest_ensure_response(
			array(
				'success'      => true,
				'message'      => __( 'Check-in successful', 'organizer' ),
				'registration' => $registration,
			)
		);
	}
}
