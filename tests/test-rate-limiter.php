<?php
/**
 * Class RateLimiterTest
 *
 * @package Organizer
 */

use Organizer\Services\RateLimiter;

/**
 * Test the RateLimiter service.
 */
class RateLimiterTest extends \PHPUnit\Framework\TestCase {

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
	 * Test check allows requests within limit.
	 */
	public function test_check_allows_requests() {
		$ip = '127.0.0.1';
		$this->assertTrue( RateLimiter::check( $ip, 2 ) );
		$this->assertTrue( RateLimiter::check( $ip, 2 ) );
	}

	/**
	 * Test check blocks requests exceeding limit.
	 */
	public function test_check_blocks_requests() {
		$ip = '127.0.0.1';
		// First request (count 1).
		RateLimiter::check( $ip, 1 );
		// Second request (count 2, limit 1) -> should fail.
		$this->assertFalse( RateLimiter::check( $ip, 1 ) );
	}
}
