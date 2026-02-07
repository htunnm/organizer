<?php
/**
 * Class ShortcodesTest
 *
 * @package Organizer
 */

use Organizer\Frontend\Shortcodes;

/**
 * Test the Shortcodes class.
 */
class ShortcodesTest extends \PHPUnit\Framework\TestCase {

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
	 * Test init registers shortcode and scripts.
	 */
	public function test_init_registers_hooks() {
		Shortcodes::init();

		$this->assertArrayHasKey( 'organizer_calendar', WPMocks::$shortcodes );
		$this->assertEquals( array( Shortcodes::class, 'render_calendar' ), WPMocks::$shortcodes['organizer_calendar'] );

		$this->assertCount( 1, WPMocks::$actions );
		$this->assertEquals( 'wp_enqueue_scripts', WPMocks::$actions[0]['tag'] );
	}

	/**
	 * Test render_calendar outputs HTML.
	 */
	public function test_render_calendar_outputs_html() {
		global $wpdb;
		$wpdb->get_results_return = array(
			(object) array(
				'id'             => 1,
				'event_id'       => 10,
				'start_datetime' => '2023-10-01 10:00:00',
			),
		);

		$output = Shortcodes::render_calendar( array() );

		$this->assertStringContainsString( 'organizer-calendar', $output );
		$this->assertStringContainsString( 'Event Title 10', $output );
	}
}
