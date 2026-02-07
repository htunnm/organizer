<?php
/**
 * Class SessionControllerTest
 *
 * @package Organizer
 */

use Organizer\Rest\SessionController;

/**
 * Test the SessionController class.
 */
class SessionControllerTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->insert_return_value = 1; // For Logger.
			$wpdb->get_results_return  = array(); // For attendees.
		}
	}

	/**
	 * Test cancel_item returns error if session not found.
	 */
	public function test_cancel_item_not_found() {
		$controller = new SessionController();
		$request    = new WP_REST_Request();
		$request->set_param( 'id', 999 );

		$response = $controller->cancel_item( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'not_found', $response->get_error_code() );
	}

	/**
	 * Test cancel_item cancels session and sends emails.
	 */
	public function test_cancel_item_success() {
		$controller = new SessionController();
		$request    = new WP_REST_Request();
		$request->set_param( 'id', 1 );

		global $wpdb;
		// Mock session.
		$wpdb->get_row_return = (object) array(
			'id'             => 1,
			'event_id'       => 10,
			'status'         => 'scheduled',
			'start_datetime' => '2023-10-01 10:00:00',
		);
		// Mock update success.
		$wpdb->update_return_value = 1;
		// Mock attendees.
		$wpdb->get_results_return = array(
			(object) array( 'email' => 'attendee@example.com' ),
		);

		$response = $controller->cancel_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertTrue( $response->data['success'] );

		// Verify email sent.
		$this->assertCount( 1, WPMocks::$sent_emails );
		$this->assertEquals( 'attendee@example.com', WPMocks::$sent_emails[0]['to'] );
		$this->assertStringContainsString( 'Session Cancelled', WPMocks::$sent_emails[0]['subject'] );
	}

	/**
	 * Test get_item returns session with venue.
	 */
	public function test_get_item_returns_session() {
		$controller = new SessionController();
		$request    = new WP_REST_Request();
		$request->set_param( 'id', 1 );

		global $wpdb;
		$wpdb->get_row_return = (object) array(
			'id'             => 1,
			'event_id'       => 10,
			'status'         => 'scheduled',
			'start_datetime' => '2023-10-01 10:00:00',
		);

		$response = $controller->get_item( $request );
		$this->assertEquals( 1, $response->data['id'] );
	}

	/**
	 * Test get_items returns list of sessions.
	 */
	public function test_get_items_returns_list() {
		$controller = new SessionController();
		$request    = new WP_REST_Request();

		global $wpdb;
		$wpdb->get_results_return = array(
			array(
				'id'             => 1,
				'event_id'       => 10,
				'status'         => 'scheduled',
				'start_datetime' => '2023-10-01 10:00:00',
			),
			array(
				'id'             => 2,
				'event_id'       => 10,
				'status'         => 'scheduled',
				'start_datetime' => '2023-10-02 10:00:00',
			),
		);

		$response = $controller->get_items( $request );
		$this->assertCount( 2, $response->data );
		$this->assertEquals( 1, $response->data[0]['id'] );
	}
}
