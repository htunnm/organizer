<?php
/**
 * Class WaitlistModelTest
 *
 * @package Organizer
 */

use Organizer\Model\Waitlist;

/**
 * Test the Waitlist model.
 */
class WaitlistModelTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Reset mocks before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		if ( class_exists( 'WPMocks' ) ) {
			WPMocks::reset();
		}
		// Reset DB mock.
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
		Waitlist::create_table();

		$this->assertCount( 1, WPMocks::$db_delta );
		$sql = WPMocks::$db_delta[0];

		$this->assertStringContainsString( 'CREATE TABLE wp_organizer_waitlist', $sql );
		$this->assertStringContainsString( 'id bigint(20) unsigned NOT NULL AUTO_INCREMENT', $sql );
		$this->assertStringContainsString( 'event_id bigint(20) unsigned NOT NULL', $sql );
		$this->assertStringContainsString( 'name varchar(255) NOT NULL', $sql );
		$this->assertStringContainsString( 'email varchar(255) NOT NULL', $sql );
		$this->assertStringContainsString( 'created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL', $sql );
		$this->assertStringContainsString( 'PRIMARY KEY  (id)', $sql );
		$this->assertStringContainsString( 'KEY event_id (event_id)', $sql );
	}

	/**
	 * Test add inserts data into the database.
	 */
	public function test_add_inserts_data() {
		global $wpdb;
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 456;

		$data = array(
			'event_id' => 10,
			'name'     => 'Waitlist User',
			'email'    => 'waitlist@example.com',
		);
		$id   = Waitlist::add( $data );

		$this->assertEquals( 456, $id );
	}
}
