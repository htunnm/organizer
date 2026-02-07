<?php
/**
 * Class DiscountCodeModelTest
 *
 * @package Organizer
 */

use Organizer\Model\DiscountCode;

/**
 * Test the DiscountCode model.
 */
class DiscountCodeModelTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->insert_return_value = false;
			$wpdb->insert_id           = 0;
			$wpdb->get_row_return      = null;
		}
	}

	/**
	 * Test create inserts data.
	 */
	public function test_create_inserts_data() {
		global $wpdb;
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 10;

		$id = DiscountCode::create( array( 'code' => 'TEST' ) );
		$this->assertEquals( 10, $id );
	}

	/**
	 * Test get_by_code returns row.
	 */
	public function test_get_by_code() {
		global $wpdb;
		$wpdb->get_row_return = (object) array(
			'id'   => 1,
			'code' => 'TEST',
		);

		$code = DiscountCode::get_by_code( 'TEST' );
		$this->assertEquals( 'TEST', $code->code );
	}
}
