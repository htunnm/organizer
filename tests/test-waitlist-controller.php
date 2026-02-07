<?php
/**
 * Class WaitlistControllerTest
 *
 * @package Organizer
 */

use Organizer\Rest\WaitlistController;

/**
 * Test the WaitlistController class.
 */
class WaitlistControllerTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Reset mocks before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		if ( class_exists( 'WPMocks' ) ) {
			WPMocks::reset();
		}
		// Reset DB mock.
		global $wpdb;
		if ( isset( $wpdb ) ) {
			$wpdb->get_row_return      = null;
			$wpdb->insert_return_value = false;
			$wpdb->delete_return_value = false;
		}
	}

	/**
	 * Test promote_item returns error when event_id is missing.
	 */
	public function test_promote_item_missing_params() {
		$controller = new WaitlistController();
		$request    = new WP_REST_Request();
		$request->set_params( array() );

		$response = $controller->promote_item( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'missing_params', $response->get_error_code() );
	}

	/**
	 * Test promote_item promotes user and returns success.
	 */
	public function test_promote_item_success() {
		$controller = new WaitlistController();
		$request    = new WP_REST_Request();
		$request->set_params( array( 'event_id' => 1 ) );

		global $wpdb;
		// Mock getting next user.
		$wpdb->get_row_return = (object) array(
			'id'       => 5,
			'event_id' => 1,
			'name'     => 'Promoted User',
			'email'    => 'promoted@example.com',
		);
		// Mock registration creation.
		$wpdb->insert_return_value = 10;
		// Mock waitlist removal.
		$wpdb->delete_return_value = 1;

		$response = $controller->promote_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertTrue( $response->data['success'] );
	}

	/**
	 * Test permissions_check returns true (mocked user has capability).
	 */
	public function test_permissions_check() {
		$controller = new WaitlistController();
		$this->assertTrue( $controller->permissions_check() );
	}
}
