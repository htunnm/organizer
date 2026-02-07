<?php
/**
 * Class AnalyticsServiceTest
 *
 * @package Organizer
 */

use Organizer\Services\AnalyticsService;

/**
 * Test the AnalyticsService class.
 */
class AnalyticsServiceTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->get_var_return     = 0;
			$wpdb->get_results_return = array();
		}
	}

	/**
	 * Test get_registration_stats returns correct structure.
	 */
	public function test_get_registration_stats() {
		global $wpdb;
		$wpdb->get_var_return = 5; // Mock count.

		$service = new AnalyticsService();
		$stats   = $service->get_registration_stats();

		$this->assertEquals( 5, $stats['total'] );
		$this->assertEquals( 5, $stats['pending'] );
		$this->assertEquals( 5, $stats['waitlist'] );
	}

	/**
	 * Test get_daily_registrations returns array.
	 */
	public function test_get_daily_registrations() {
		global $wpdb;
		$wpdb->get_results_return = array(
			array(
				'date'  => '2023-10-01',
				'count' => 2,
			),
		);

		$service = new AnalyticsService();
		$daily   = $service->get_daily_registrations();

		$this->assertEquals( 2, $daily['2023-10-01'] );
	}

	/**
	 * Test get_waitlist_metrics returns correct structure.
	 */
	public function test_get_waitlist_metrics() {
		global $wpdb;
		$wpdb->get_var_return = 10; // Mock count.

		$service = new AnalyticsService();
		$metrics = $service->get_waitlist_metrics();

		$this->assertEquals( 10, $metrics['current'] );
		$this->assertEquals( 10, $metrics['promoted'] );
		// avg_wait calculation depends on get_var return, which is 10 (seconds).
		// 10 / 3600 = 0.0027 -> round to 0.0.
		$this->assertEquals( 0.0, $metrics['avg_wait'] );
	}

	/**
	 * Test get_waitlist_growth returns array.
	 */
	public function test_get_waitlist_growth() {
		$service = new AnalyticsService();
		$this->assertIsArray( $service->get_waitlist_growth() );
	}
}
