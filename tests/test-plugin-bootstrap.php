<?php
/**
 * Class PluginBootstrapTest
 *
 * @package Organizer
 */

use Organizer\Plugin;

class PluginBootstrapTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Test that the plugin constants are defined.
	 *
	 * Note: In a real integration test, we would check if the plugin is active.
	 * For this unit test, we verify the class exists and basic logic.
	 */
	public function test_plugin_class_exists() {
		$this->assertTrue( class_exists( 'Organizer\Plugin' ) );
	}

	/**
	 * Test that the Email Interface exists.
	 */
	public function test_email_interface_exists() {
		$this->assertTrue( interface_exists( 'Organizer\Services\Email\EmailServiceInterface' ) );
	}
}
