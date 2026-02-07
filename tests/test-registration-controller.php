<?php
/**
 * Class RegistrationControllerTest
 *
 * @package Organizer
 */

use Organizer\Rest\RegistrationController;

/**
 * Test the RegistrationController class.
 */
class RegistrationControllerTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->get_var_return      = 0;
			$wpdb->get_row_return      = null;
		}
	}

	/**
	 * Test create_item returns error when parameters are missing.
	 */
	public function test_create_item_missing_params() {
		$controller = new RegistrationController();
		$request    = new WP_REST_Request();
		$request->set_params( array() );

		$response = $controller->create_item( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'missing_params', $response->get_error_code() );
	}

	/**
	 * Test create_item returns error when email is invalid.
	 */
	public function test_create_item_invalid_email() {
		$controller = new RegistrationController();
		$request    = new WP_REST_Request();
		$request->set_params(
			array(
				'event_id' => 1,
				'name'     => 'Test User',
				'email'    => 'invalid-email',
			)
		);

		$response = $controller->create_item( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'invalid_email', $response->get_error_code() );
	}

	/**
	 * Test create_item creates registration and returns success.
	 */
	public function test_create_item_success() {
		$controller = new RegistrationController();
		$request    = new WP_REST_Request();
		$request->set_params(
			array(
				'event_id' => 1,
				'name'     => 'Test User',
				'email'    => 'test@example.com',
			)
		);

		global $wpdb;
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 123;

		$response = $controller->create_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 123, $response->data['id'] );

		// Verify email was sent.
		$this->assertCount( 1, WPMocks::$sent_emails );
		$this->assertEquals( 'test@example.com', WPMocks::$sent_emails[0]['to'] );
		$this->assertStringContainsString( 'Registration Confirmation', WPMocks::$sent_emails[0]['subject'] );
	}

	/**
	 * Test create_item adds to waitlist when event is full.
	 */
	public function test_create_item_adds_to_waitlist_when_full() {
		$controller = new RegistrationController();
		$request    = new WP_REST_Request();
		$request->set_params(
			array(
				'event_id' => 1,
				'name'     => 'Waitlist User',
				'email'    => 'waitlist@example.com',
			)
		);

		// Mock capacity.
		WPMocks::$post_meta[1]['_organizer_event_capacity'] = 10;

		global $wpdb;
		// Mock current registration count to match capacity.
		$wpdb->get_var_return = 10;
		// Mock insert success.
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 789;

		$response = $controller->create_item( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 789, $response->data['id'] );
		$this->assertEquals( 'waitlist', $response->data['status'] );

		// Verify waitlist email was sent.
		$this->assertCount( 1, WPMocks::$sent_emails );
		$this->assertEquals( 'waitlist@example.com', WPMocks::$sent_emails[0]['to'] );
		$this->assertStringContainsString( 'Added to Waitlist', WPMocks::$sent_emails[0]['subject'] );
	}

	/**
	 * Test create_item with session generates ICS.
	 */
	public function test_create_item_with_session_sends_ics() {
		$controller = new RegistrationController();
		$request    = new WP_REST_Request();
		$request->set_params(
			array(
				'event_id'   => 1,
				'session_id' => 10,
				'name'       => 'Session User',
				'email'      => 'session@example.com',
			)
		);

		global $wpdb;
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 555;
		$wpdb->get_row_return      = (object) array(
			'id'             => 10,
			'start_datetime' => '2023-12-25 10:00:00',
			'end_datetime'   => '2023-12-25 11:00:00',
		);

		$controller->create_item( $request );

		$this->assertCount( 1, WPMocks::$sent_emails );
		$this->assertNotEmpty( WPMocks::$sent_emails[0]['attachments'] );
		$this->assertStringContainsString( 'invite.ics', WPMocks::$sent_emails[0]['attachments'][0] );
	}
}
