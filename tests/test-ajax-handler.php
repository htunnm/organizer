<?php
/**
 * Class AjaxHandlerTest
 *
 * @package Organizer
 */

use Organizer\Frontend\AjaxHandler;

/**
 * Test the AjaxHandler class.
 */
class AjaxHandlerTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->get_var_return     = 0;
		}
		$_GET = array();
	}

	/**
	 * Test load_calendar returns HTML.
	 */
	public function test_load_calendar_returns_html() {
		$_GET['offset'] = 10;

		// Mock output buffering for wp_send_json_success.
		ob_start();
		AjaxHandler::load_calendar();
		$output = ob_get_clean();

		$json = json_decode( $output, true );

		$this->assertTrue( $json['success'] );
		$this->assertArrayHasKey( 'html', $json['data'] );
		$this->assertStringContainsString( 'organizer-calendar', $json['data']['html'] );
	}
}
