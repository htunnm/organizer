<?php
/**
 * Class LoggerTest
 *
 * @package Organizer
 */

use Organizer\Services\Logger;

/**
 * Test the Logger service.
 */
class LoggerTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->insert_return_value = 1;
			$wpdb->insert_id           = 100;
		}
	}

	/**
	 * Test log creates a log entry.
	 */
	public function test_log_creates_entry() {
		$id = Logger::log( 'test_action', 'Test message', 1, 2 );

		$this->assertEquals( 100, $id );
		// Verify user_id was mocked correctly in bootstrap.
		// Since we can't inspect the arguments passed to $wpdb->insert in the current mock implementation
		// without extending the mock, we rely on the return value for now.
		// A more advanced mock would capture arguments.
	}
}
