<?php
/**
 * Class CronServiceTest
 *
 * @package Organizer
 */

use Organizer\Services\CronService;

/**
 * Test the CronService class.
 */
class CronServiceTest extends \PHPUnit\Framework\TestCase {

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
	 * Test handle_daily_reminders runs without error.
	 */
	public function test_handle_daily_reminders() {
		global $wpdb;
		// Mock sessions found.
		$wpdb->get_results_return = array(
			(object) array(
				'id'             => 1,
				'event_id'       => 10,
				'start_datetime' => '2023-10-01 10:00:00',
				'email'          => 'test@example.com',
			),
		);

		CronService::handle_daily_reminders();

		// Verify email sent (indirectly via ReminderService).
		$this->assertCount( 1, WPMocks::$sent_emails );
	}
}
