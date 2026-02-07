<?php
/**
 * Class RSVPControllerTest
 *
 * @package Organizer
 */

use Organizer\Rest\RSVPController;

/**
 * Test the RSVPController class.
 */
class RSVPControllerTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->insert_return_value = false;
			$wpdb->insert_id           = 0;
		}
	}

	/**
	 * Test create_item returns error when parameters are missing.
	 */
	public function test_create_item_missing_params() {
		$controller = new RSVPController();
		$request    = new WP_REST_Request();
		$request->set_params( array() );

		$response = $controller->create_item( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'missing_params', $response->get_error_code() );
	}

	/**
	 * Test create_item submits RSVP and returns success.
	 */
	public function test_create_item_success() {
		$controller = new RSVPController();
		$request    = new WP_REST_Request();
		$request->set_params(
			array(
				'registration_id' => 123,
				'response'        => 'attending',
			)
		);

		global $wpdb;
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 456;

		$response = $controller->create_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 456, $response->data['id'] );
	}
}
