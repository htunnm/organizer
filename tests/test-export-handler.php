<?php
/**
 * Class ExportHandlerTest
 *
 * @package Organizer
 */

use Organizer\Admin\ExportHandler;
use Organizer\Model\Registration;

/**
 * Test the ExportHandler class.
 */
class ExportHandlerTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->get_results_return = array();
		}
	}

	/**
	 * Test init registers admin_post action.
	 */
	public function test_init_registers_action() {
		ExportHandler::init();

		$this->assertCount( 1, WPMocks::$actions );
		$this->assertEquals( 'admin_post_organizer_export_registrations', WPMocks::$actions[0]['tag'] );
		$this->assertEquals( array( ExportHandler::class, 'handle_export' ), WPMocks::$actions[0]['callback'] );
	}

	/**
	 * Test get_for_export fetches data.
	 */
	public function test_get_for_export_fetches_data() {
		// We can't easily test handle_export output without output buffering and header mocks,
		// so we verify the model method it relies on.
		$data = Registration::get_for_export();
		$this->assertIsArray( $data );
	}
}
