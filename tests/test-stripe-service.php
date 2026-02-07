<?php
/**
 * Class StripeServiceTest
 *
 * @package Organizer
 */

use Organizer\Services\Payment\StripeService;

/**
 * Test the StripeService class.
 */
class StripeServiceTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Test is_zero_decimal.
	 */
	public function test_is_zero_decimal() {
		$service = new StripeService();
		$this->assertTrue( $service->is_zero_decimal( 'JPY' ) );
		$this->assertFalse( $service->is_zero_decimal( 'USD' ) );
	}

	/**
	 * Test get_amount_in_cents.
	 */
	public function test_get_amount_in_cents() {
		$service = new StripeService();

		// USD: 10.50 -> 1050
		$this->assertEquals( 1050, $service->get_amount_in_cents( 10.50, 'USD' ) );

		// JPY: 1000 -> 1000
		$this->assertEquals( 1000, $service->get_amount_in_cents( 1000, 'JPY' ) );
	}
}
