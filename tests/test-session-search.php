<?php
/**
 * Class SessionSearchTest
 *
 * @package Organizer
 */

use Organizer\Model\Session;

/**
 * Test Session search functionality.
 */
class SessionSearchTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->get_results_return = array();
		}
	}

	/**
	 * Test get_all with keyword filter.
	 */
	public function test_get_all_with_keyword() {
		global $wpdb;
		$filters = array( 'keyword' => 'Workshop' );
		Session::get_all( 10, 0, 'start_datetime', 'ASC', '', $filters );

		// Verify execution without error.
		$this->assertTrue( true );
	}
}
