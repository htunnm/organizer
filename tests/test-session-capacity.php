<?php
/**
 * Class SessionCapacityTest
 *
 * @package Organizer
 */

use Organizer\Model\Session;

/**
 * Test Session capacity logic.
 */
class SessionCapacityTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->get_row_return = null;
			$wpdb->get_var_return = 0;
		}
	}

	/**
	 * Test is_full returns true when capacity reached.
	 */
	public function test_is_full_returns_true() {
		global $wpdb;
		$wpdb->get_row_return = (object) array(
			'id'       => 1,
			'capacity' => 10,
		);
		$wpdb->get_var_return = 10; // 10 registrations.

		$this->assertTrue( Session::is_full( 1 ) );
	}

	/**
	 * Test get_remaining_spots returns correct number.
	 */
	public function test_get_remaining_spots() {
		global $wpdb;
		$wpdb->get_row_return = (object) array(
			'id'       => 1,
			'capacity' => 10,
		);
		$wpdb->get_var_return = 4; // 4 registrations.

		$this->assertEquals( 6, Session::get_remaining_spots( 1 ) );
	}
}
