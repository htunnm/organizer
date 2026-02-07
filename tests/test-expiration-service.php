<?php
/**
 * Class ExpirationServiceTest
 *
 * @package Organizer
 */

use Organizer\Services\ExpirationService;

/**
 * Test the ExpirationService class.
 */
class ExpirationServiceTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->get_results_return  = array();
			$wpdb->update_return_value = false;
			$wpdb->insert_return_value = 1; // For Logger/Waitlist.
			$wpdb->get_row_return      = null; // For Waitlist check.
		}
	}

	/**
	 * Test process_expired_registrations handles expired items.
	 */
	public function test_process_expired_registrations() {
		global $wpdb;
		$wpdb->get_results_return  = array(
			(object) array(
				'id'         => 1,
				'event_id'   => 10,
				'session_id' => 0,
				'email'      => 'expired@example.com',
			),
		);
		$wpdb->update_return_value = 1;

		// Mock waitlist user for promotion.
		$wpdb->get_row_return = (object) array(
			'id'    => 5,
			'name'  => 'Next User',
			'email' => 'next@example.com',
		);

		$service = new ExpirationService();
		$count   = $service->process_expired_registrations();

		$this->assertEquals( 1, $count );
	}
}
