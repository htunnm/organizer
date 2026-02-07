<?php
/**
 * Class RSVPModelTest
 *
 * @package Organizer
 */

use Organizer\Model\RSVP;

/**
 * Test the RSVP model.
 */
class RSVPModelTest extends \PHPUnit\Framework\TestCase {

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
		RSVP::create_table();

		$this->assertCount( 1, WPMocks::$db_delta );
		$sql = WPMocks::$db_delta[0];

		$this->assertStringContainsString( 'CREATE TABLE wp_organizer_rsvps', $sql );
		$this->assertStringContainsString( 'id bigint(20) unsigned NOT NULL AUTO_INCREMENT', $sql );
		$this->assertStringContainsString( 'registration_id bigint(20) unsigned NOT NULL', $sql );
		$this->assertStringContainsString( 'response varchar(20) NOT NULL', $sql );
		$this->assertStringContainsString( 'created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL', $sql );
		$this->assertStringContainsString( 'PRIMARY KEY  (id)', $sql );
		$this->assertStringContainsString( 'KEY registration_id (registration_id)', $sql );
	}

	/**
	 * Test submit inserts data into the database.
	 */
	public function test_submit_inserts_data() {
		global $wpdb;
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 202;

		$data = array(
			'registration_id' => 50,
			'response'        => 'attending',
		);
		$id   = RSVP::submit( $data );

		$this->assertEquals( 202, $id );
	}
}
