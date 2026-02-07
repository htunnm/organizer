<?php
/**
 * Class EventSearchWidgetTest
 *
 * @package Organizer
 */

use Organizer\Admin\EventSearchWidget;

/**
 * Test the EventSearchWidget class.
 */
class EventSearchWidgetTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Reset mocks before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		// Ensure the class is loaded if autoloader fails.
		if ( ! class_exists( 'Organizer\Admin\EventSearchWidget' ) ) {
			require_once dirname( __DIR__ ) . '/includes/Admin/EventSearchWidget.php';
		}
		if ( class_exists( 'WPMocks' ) ) {
			WPMocks::reset();
		}
	}

	/**
	 * Test widget renders form.
	 */
	public function test_widget_renders_form() {
		$widget   = new EventSearchWidget();
		$args     = array(
			'before_widget' => '<div>',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		);
		$instance = array(
			'title'      => 'Search',
			'target_url' => 'http://example.com/events',
		);

		ob_start();
		$widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'action="http://example.com/events"', $output );
		$this->assertStringContainsString( 'name="organizer_search"', $output );
		$this->assertStringContainsString( 'name="organizer_start_date"', $output );
	}
}
