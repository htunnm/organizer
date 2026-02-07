<?php
/**
 * Class CalendarBlockTest
 *
 * @package Organizer
 */

use Organizer\Blocks\CalendarBlock;

/**
 * Test the CalendarBlock class.
 */
class CalendarBlockTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Reset mocks before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		// Ensure the class is loaded if autoloader fails.
		if ( ! class_exists( 'Organizer\Blocks\CalendarBlock' ) ) {
			$file = dirname( __DIR__ ) . '/includes/Blocks/CalendarBlock.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
		if ( class_exists( 'WPMocks' ) ) {
			WPMocks::reset();
		}
		global $wpdb;
		if ( isset( $wpdb ) ) {
			$wpdb->get_results_return = array();
		}
	}

	/**
	 * Test init registers hooks.
	 */
	public function test_init_registers_hooks() {
		CalendarBlock::init();

		$this->assertCount( 2, WPMocks::$actions );
		$this->assertEquals( 'init', WPMocks::$actions[0]['tag'] );
		$this->assertEquals( 'enqueue_block_editor_assets', WPMocks::$actions[1]['tag'] );
	}

	/**
	 * Test register_block registers the block type.
	 */
	public function test_register_block() {
		CalendarBlock::register_block();
		$this->assertArrayHasKey( 'organizer/calendar', WPMocks::$blocks );
	}

	/**
	 * Test render_callback calls shortcode render.
	 */
	public function test_render_callback() {
		$attributes = array(
			'limit'      => 5,
			'showSearch' => true,
			'category'   => 'test-cat',
		);

		$output = CalendarBlock::render_callback( $attributes );

		$this->assertStringContainsString( 'organizer-calendar', $output );
		$this->assertStringContainsString( 'organizer-search-form', $output );
	}
}
