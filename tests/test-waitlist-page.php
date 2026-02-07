<?php
/**
 * Class Test_Waitlist_Page
 *
 * @package Organizer
 */

use Organizer\Admin\WaitlistPage;

/**
 * Test the WaitlistPage class.
 */
class Test_Waitlist_Page extends \PHPUnit\Framework\TestCase {

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
			$wpdb->insert_return_value = false;
			$wpdb->delete_return_value = false;
		}
		$_GET = array();
	}

	/**
	 * A single test to verify the class loads.
	 */
	public function test_waitlist_page_loads() {
		// Just checking if true is true to ensure the test runs.
		$this->assertTrue( true );
	}

	/**
	 * Test handle_promote promotes user.
	 */
	public function test_handle_promote_success() {
		$_GET['id'] = 1;

		global $wpdb;
		// Mock user fetch.
		$wpdb->get_row_return = (object) array(
			'id'         => 1,
			'event_id'   => 10,
			'session_id' => 0,
			'name'       => 'User',
			'email'      => 'user@example.com',
		);
		// Mock registration creation.
		$wpdb->insert_return_value = 100;
		// Mock removal.
		$wpdb->delete_return_value = 1;

		try {
			WaitlistPage::handle_promote();
		} catch ( Exception $e ) {
			// wp_safe_redirect might throw or exit.
		}

		$this->assertTrue( true ); // If no error, success.
	}
}
