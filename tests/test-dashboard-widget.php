<?php
/**
 * Class DashboardWidgetTest
 *
 * @package Organizer
 */

use Organizer\Admin\DashboardWidget;

/**
 * Test the DashboardWidget class.
 */
class DashboardWidgetTest extends \PHPUnit\Framework\TestCase {

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
	 * Test init registers wp_dashboard_setup action.
	 */
	public function test_init_registers_action() {
		DashboardWidget::init();

		$this->assertCount( 2, WPMocks::$actions );
		$this->assertEquals( 'wp_dashboard_setup', WPMocks::$actions[0]['tag'] );
		$this->assertEquals( array( DashboardWidget::class, 'add_widget' ), WPMocks::$actions[0]['callback'] );
		$this->assertEquals( 'admin_enqueue_scripts', WPMocks::$actions[1]['tag'] );
	}

	/**
	 * Test add_widget registers the dashboard widget.
	 */
	public function test_add_widget_registers_widget() {
		DashboardWidget::add_widget();

		$this->assertCount( 1, WPMocks::$dashboard_widgets );
		$this->assertEquals( 'organizer_dashboard_widget', WPMocks::$dashboard_widgets[0]['widget_id'] );
		$this->assertEquals( 'Upcoming Events', WPMocks::$dashboard_widgets[0]['widget_name'] );
		$this->assertEquals( array( DashboardWidget::class, 'render_widget' ), WPMocks::$dashboard_widgets[0]['callback'] );
	}
}
