<?php
/**
 * Class ReminderServiceTest
 *
 * @package Organizer
 */

use Organizer\Services\Email\ReminderService;
use Organizer\Services\Email\EmailServiceInterface;

/**
 * Test the ReminderService class.
 */
class ReminderServiceTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Reset mocks before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		if ( class_exists( 'WPMocks' ) ) {
			WPMocks::reset();
		}
	}

	/**
	 * Test send_reminders sends emails to all attendees.
	 */
	public function test_send_reminders_sends_emails() {
		// Mock EmailService.
		$email_mock = $this->createMock( EmailServiceInterface::class );
		$email_mock->expects( $this->exactly( 2 ) )
			->method( 'send' )
			->willReturn( true );

		// Mock DB results.
		global $wpdb;
		$wpdb->get_results_return = array(
			(object) array( 'email' => 'user1@example.com' ),
			(object) array( 'email' => 'user2@example.com' ),
		);

		$service = new ReminderService( $email_mock );
		$count   = $service->send_reminders( 1, 0, 'Reminder', 'Don\'t forget!' );

		$this->assertEquals( 2, $count );
	}
}
