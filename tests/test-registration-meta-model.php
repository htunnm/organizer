<?php
/**
 * Class RegistrationMetaModelTest
 *
 * @package Organizer
 */

use Organizer\Model\RegistrationMeta;

/**
 * Test the RegistrationMeta model.
 */
class RegistrationMetaModelTest extends \PHPUnit\Framework\TestCase {

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
		}
	}

	/**
	 * Test that create_table generates the correct SQL.
	 */
	public function test_create_table_generates_sql() {
		RegistrationMeta::create_table();

		$this->assertCount( 1, WPMocks::$db_delta );
		$sql = WPMocks::$db_delta[0];

		$this->assertStringContainsString( 'CREATE TABLE wp_organizer_registration_meta', $sql );
		$this->assertStringContainsString( 'registration_id bigint(20) unsigned NOT NULL', $sql );
		$this->assertStringContainsString( 'meta_key varchar(255) NOT NULL', $sql );
		$this->assertStringContainsString( 'meta_value longtext', $sql );
	}

	/**
	 * Test add inserts data into the database.
	 */
	public function test_add_inserts_data() {
		global $wpdb;
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 101;

		$id = RegistrationMeta::add( 50, 'Dietary', 'Vegan' );

		$this->assertEquals( 101, $id );
	}
}
