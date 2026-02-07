<?php
/**
 * Class RemindersCommandTest
 *
 * @package Organizer
 */

use Organizer\Cli\RemindersCommand;

/**
 * Test the RemindersCommand class.
 */
class RemindersCommandTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Reset mocks before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		if ( class_exists( 'WPMocks' ) ) {
			WPMocks::reset();
		}
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::$logs = array();
		}
		global $wpdb;
		if ( isset( $wpdb ) ) {
			$wpdb->get_results_return = array();
		}
	}

	/**
	 * Test send_reminders with no sessions.
	 */
	public function test_send_reminders_no_sessions() {
		$command = new RemindersCommand();
		$command->send_reminders( array(), array() );

		$this->assertContains( 'SUCCESS: No sessions found requiring reminders.', WP_CLI::$logs );
	}

	/**
	 * Test send_reminders dry run.
	 */
	public function test_send_reminders_dry_run() {
		global $wpdb;
		$wpdb->get_results_return = array(
			(object) array(
				'id'             => 1,
				'event_id'       => 10,
				'start_datetime' => '2023-10-01 10:00:00',
			),
		);

		$command = new RemindersCommand();
		$command->send_reminders( array(), array( 'dry-run' => true ) );

		$this->assertContains( '  [Dry Run] Would send reminders to attendees.', WP_CLI::$logs );
		$this->assertEmpty( WPMocks::$sent_emails );
	}
}
