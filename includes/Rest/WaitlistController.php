<?php
/**
 * Waitlist REST Controller.
 *
 * @package Organizer\Rest
 */

namespace Organizer\Rest;

use WP_REST_Controller;
use WP_Error;
use Organizer\Services\WaitlistService;
use Organizer\Services\Email\GmailAdapter;

/**
 * Class WaitlistController
 */
class WaitlistController extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'organizer/v1';
		$this->rest_base = 'waitlist';
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/promote',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'promote_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Promote next user.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|WP_Error Response object.
	 */
	public function promote_item( $request ) {
		$params = $request->get_params();

		if ( empty( $params['event_id'] ) ) {
			return new WP_Error( 'missing_params', __( 'Missing event_id', 'organizer' ), array( 'status' => 400 ) );
		}

		$event_id = absint( $params['event_id'] );

		$email_service = new GmailAdapter();
		$service       = new WaitlistService( $email_service );

		$promoted = $service->promote_next_user( $event_id );

		if ( ! $promoted ) {
			return new WP_Error( 'promotion_failed', __( 'No users on waitlist or promotion failed', 'organizer' ), array( 'status' => 400 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'User promoted successfully', 'organizer' ),
			)
		);
	}
}
