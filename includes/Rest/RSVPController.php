<?php
/**
 * RSVP REST Controller.
 *
 * @package Organizer\Rest
 */

namespace Organizer\Rest;

use WP_REST_Controller;
use WP_Error;
use Organizer\Model\RSVP;
use Organizer\Services\RateLimiter;

/**
 * Class RSVPController
 */
class RSVPController extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'organizer/v1';
		$this->rest_base = 'rsvps';
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
	 * Create an RSVP.
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

		if ( empty( $params['registration_id'] ) || empty( $params['response'] ) ) {
			return new WP_Error( 'missing_params', __( 'Missing required parameters', 'organizer' ), array( 'status' => 400 ) );
		}

		$data = array(
			'registration_id' => absint( $params['registration_id'] ),
			'response'        => sanitize_text_field( $params['response'] ),
		);

		$id = RSVP::submit( $data );

		if ( ! $id ) {
			return new WP_Error( 'db_error', __( 'Could not submit RSVP', 'organizer' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'id'      => $id,
				'message' => __( 'RSVP submitted successfully', 'organizer' ),
			)
		);
	}
}
