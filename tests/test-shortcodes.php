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

	/**
	 * Test render_calendar shows search form.
	 */
	public function test_render_calendar_shows_search() {
		global $wpdb;
		$wpdb->get_results_return = array();

		$output = Shortcodes::render_calendar( array( 'show_search' => 'yes' ) );

		$this->assertStringContainsString( 'organizer-search-form', $output );
	}

	/**
	 * Test render_registration_form outputs multi-step structure.
	 */
	public function test_render_registration_form_outputs_steps() {
		$output = Shortcodes::render_registration_form( array( 'event_id' => 1 ) );

		$this->assertStringContainsString( 'organizer-registration-form', $output );
		$this->assertStringContainsString( 'organizer-step', $output );
		$this->assertStringContainsString( 'organizer-progress-bar', $output );
		$this->assertStringContainsString( 'data-step="1"', $output );
	}

	/**
	 * Test render_analytics_dashboard outputs dashboard for authorized user.
	 */
	public function test_render_analytics_dashboard_authorized() {
		global $wpdb;
		$wpdb->get_var_return = 5; // Mock stats.

		$output = Shortcodes::render_analytics_dashboard();

		$this->assertStringContainsString( 'organizer-analytics-dashboard', $output );
		$this->assertStringContainsString( 'Event Analytics', $output );
	}

	/**
	 * Test render_checkin_scanner outputs scanner for authorized user.
	 */
	public function test_render_checkin_scanner_authorized() {
		$output = Shortcodes::render_checkin_scanner();
		$this->assertStringContainsString( 'organizer-scanner-wrapper', $output );
		$this->assertStringContainsString( 'organizer-qr-reader', $output );
	}
}
