<?php
/**
 * Class CheckinControllerTest
 *
 * @package Organizer
 */

use Organizer\Rest\CheckinController;

/**
 * Test the CheckinController class.
 */
class CheckinControllerTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Reset mocks before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		if ( class_exists( 'WPMocks' ) ) {
			WPMocks::reset();
		}
		global $wpdb;
		if ( isset( $wpdb ) ) {
			$wpdb->get_row_return      = null;
			$wpdb->update_return_value = false;
		}
	}

	/**
	 * Test create_item performs checkin.
	 */
	public function test_create_item_success() {
		$controller = new CheckinController();
		$request    = new WP_REST_Request();
		$request->set_param( 'token', 'valid_token' );

		global $wpdb;
		$wpdb->get_row_return      = (object) array(
			'id'            => 1,
			'name'          => 'Attendee',
			'checked_in_at' => null,
		);
		$wpdb->update_return_value = 1;

		$response = $controller->create_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertTrue( $response->data['success'] );
	}

	/**
	 * Test permissions_check returns true.
	 */
	public function test_permissions_check() {
		$controller = new CheckinController();
		$this->assertTrue( $controller->permissions_check() );
	}
}
