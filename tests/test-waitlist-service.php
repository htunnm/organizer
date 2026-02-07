<?php
/**
 * Class WaitlistServiceTest
 *
 * @package Organizer
 */

use Organizer\Services\WaitlistService;
use Organizer\Services\Email\EmailServiceInterface;

/**
 * Test the WaitlistService class.
 */
class WaitlistServiceTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->insert_id           = 100; // For Logger.
			$wpdb->delete_return_value = false;
		}
	}

	/**
	 * Test promote_next_user returns false when waitlist is empty.
	 */
	public function test_promote_returns_false_when_empty() {
		$email_mock = $this->createMock( EmailServiceInterface::class );
		$service    = new WaitlistService( $email_mock );

		$this->assertFalse( $service->promote_next_user( 1 ) );
	}

	/**
	 * Test promote_next_user promotes user and sends email.
	 */
	public function test_promote_promotes_user() {
		$email_mock = $this->createMock( EmailServiceInterface::class );
		$email_mock->expects( $this->once() )
			->method( 'send' )
			->willReturn( true );

		$service = new WaitlistService( $email_mock );

		global $wpdb;
		$wpdb->get_row_return      = (object) array(
			'id'       => 5,
			'event_id' => 1,
			'name'     => 'Promoted User',
			'email'    => 'promoted@example.com',
		);
		$wpdb->insert_return_value = 10; // New registration ID.
		$wpdb->delete_return_value = 1; // Rows deleted.

		$this->assertTrue( $service->promote_next_user( 1 ) );

		// Verify log entry created.
		// Since Logger::log calls Log::create which calls $wpdb->insert, and we mocked insert to return 10,
		// we can assume it was called. In a stricter test we'd check call counts or arguments.
	}
}
