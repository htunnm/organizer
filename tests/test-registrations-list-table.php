<?php
/**
 * Class RegistrationsListTableTest
 *
 * @package Organizer
 */

use Organizer\Admin\RegistrationsListTable;

/**
 * Test the RegistrationsListTable class.
 */
class RegistrationsListTableTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->get_var_return     = 0;
			$wpdb->get_results_return = array();
		}
	}

	/**
	 * Test get_columns returns correct columns.
	 */
	public function test_get_columns() {
		$table   = new RegistrationsListTable();
		$columns = $table->get_columns();

		$this->assertArrayHasKey( 'id', $columns );
		$this->assertArrayHasKey( 'name', $columns );
		$this->assertArrayHasKey( 'email', $columns );
		$this->assertArrayHasKey( 'event_id', $columns );
		$this->assertArrayHasKey( 'status', $columns );
		$this->assertArrayHasKey( 'created_at', $columns );
	}

	/**
	 * Test prepare_items fetches data and sets pagination.
	 */
	public function test_prepare_items() {
		global $wpdb;
		$wpdb->get_var_return     = 50; // Total items.
		$wpdb->get_results_return = array(
			array(
				'id'       => 1,
				'name'     => 'Test',
				'event_id' => 10,
			),
			array(
				'id'       => 2,
				'name'     => 'Test 2',
				'event_id' => 10,
			),
		);

		$table = new RegistrationsListTable();
		$table->prepare_items();

		$this->assertCount( 2, $table->items );

		$pagination = $table->get_pagination_args();
		$this->assertEquals( 50, $pagination['total_items'] );
		$this->assertEquals( 3, $pagination['total_pages'] ); // 50 / 20 = 2.5 -> 3.
	}

	/**
	 * Test column_event_id renders title.
	 */
	public function test_column_event_id() {
		$table = new RegistrationsListTable();
		$item  = array( 'event_id' => 123 );
		$this->assertEquals( 'Event Title 123', $table->column_event_id( $item ) );
	}
}
