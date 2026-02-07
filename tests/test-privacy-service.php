<?php
/**
 * Class PrivacyServiceTest
 *
 * @package Organizer
 */

use Organizer\Services\PrivacyService;

/**
 * Test the PrivacyService class.
 */
class PrivacyServiceTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->delete_return_value = false;
		}
	}

	/**
	 * Test export_personal_data returns data.
	 */
	public function test_export_personal_data() {
		global $wpdb;
		$wpdb->get_results_return = array(
			array(
				'id'         => 1,
				'event_id'   => 10,
				'created_at' => '2023-01-01',
				'status'     => 'confirmed',
			),
		);

		$result = PrivacyService::export_personal_data( 'test@example.com' );

		$this->assertTrue( $result['done'] );
		$this->assertCount( 1, $result['data'] );
		$this->assertEquals( 'organizer-registrations', $result['data'][0]['group_id'] );
	}

	/**
	 * Test erase_personal_data anonymizes data.
	 */
	public function test_erase_personal_data() {
		global $wpdb;
		$wpdb->get_results_return  = array(
			array( 'id' => 1 ),
		);
		$wpdb->update_return_value = 1;
		$wpdb->delete_return_value = 1;

		$result = PrivacyService::erase_personal_data( 'test@example.com' );

		$this->assertTrue( $result['done'] );
		$this->assertTrue( $result['items_removed'] );
	}
}
