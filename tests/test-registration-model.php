<?php
/**
 * Class RegistrationModelTest
 *
 * @package Organizer
 */

use Organizer\Model\Registration;

/**
 * Test the Registration model.
 */
class RegistrationModelTest extends \PHPUnit\Framework\TestCase {

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
	 * Test that create_table generates the correct SQL.
	 */
	public function test_create_table_generates_sql() {
		Registration::create_table();

		$this->assertCount( 1, WPMocks::$db_delta );
		$sql = WPMocks::$db_delta[0];

		$this->assertStringContainsString( 'CREATE TABLE wp_organizer_registrations', $sql );
		$this->assertStringContainsString( 'id bigint(20) unsigned NOT NULL AUTO_INCREMENT', $sql );
		$this->assertStringContainsString( 'event_id bigint(20) unsigned NOT NULL', $sql );
		$this->assertStringContainsString( 'name varchar(255) NOT NULL', $sql );
		$this->assertStringContainsString( 'email varchar(255) NOT NULL', $sql );
		$this->assertStringContainsString( "status varchar(50) NOT NULL DEFAULT 'pending'", $sql );
		$this->assertStringContainsString( 'created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL', $sql );
		$this->assertStringContainsString( 'PRIMARY KEY  (id)', $sql );
		$this->assertStringContainsString( 'KEY event_id (event_id)', $sql );
		$this->assertStringContainsString( 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $sql );
	}

	/**
	 * Test get_table_name returns correct name with prefix.
	 */
	public function test_get_table_name() {
		$this->assertEquals( 'wp_organizer_registrations', Registration::get_table_name() );
	}
}
