<?php
/**
 * Class SettingsTest
 *
 * @package Organizer
 */

use Organizer\Admin\Settings;

/**
 * Test the Settings class.
 */
class SettingsTest extends \PHPUnit\Framework\TestCase {

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
	 * Test that init registers the admin_menu and admin_init actions.
	 */
	public function test_init_registers_hooks() {
		Settings::init();

		$this->assertCount( 2, WPMocks::$actions );
		$this->assertEquals( 'admin_menu', WPMocks::$actions[0]['tag'] );
		$this->assertEquals( array( Settings::class, 'add_admin_menu' ), WPMocks::$actions[0]['callback'] );
		$this->assertEquals( 'admin_init', WPMocks::$actions[1]['tag'] );
	}

	/**
	 * Test that add_admin_menu registers the options page.
	 */
	public function test_add_admin_menu_registers_page() {
		Settings::add_admin_menu();

		$this->assertCount( 1, WPMocks::$options_pages );
		$this->assertEquals( 'Organizer Settings', WPMocks::$options_pages[0]['page_title'] );
		$this->assertEquals( 'organizer-settings', WPMocks::$options_pages[0]['menu_slug'] );
	}
}
