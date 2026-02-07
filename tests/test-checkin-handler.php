<?php
/**
 * Class CheckinHandlerTest
 *
 * @package Organizer
 */

use Organizer\Admin\CheckinHandler;

/**
 * Test the CheckinHandler class.
 */
class CheckinHandlerTest extends \PHPUnit\Framework\TestCase {

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
		$_GET = array();
	}

	/**
	 * Test handle_checkin success.
	 */
	public function test_handle_checkin_success() {
		$_GET['token'] = 'valid_token';

		global $wpdb;
		$wpdb->get_row_return      = (object) array(
			'id'            => 1,
			'name'          => 'Attendee',
			'checked_in_at' => null,
		);
		$wpdb->update_return_value = 1;

		try {
			CheckinHandler::handle_checkin();
		} catch ( Exception $e ) {
			// wp_die throws exception in mock.
			$this->assertStringContainsString( 'Check-in successful', $e->getMessage() );
		}
	}
}
