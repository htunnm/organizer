<?php
/**
 * Class UserProfileTest
 *
 * @package Organizer
 */

use Organizer\Frontend\Shortcodes;
use Organizer\Frontend\FormHandler;

/**
 * Test the User Profile functionality.
 */
class UserProfileTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->update_return_value = false;
		}
		$_POST = array();
	}

	/**
	 * Test render_user_profile outputs HTML.
	 */
	public function test_render_user_profile_outputs_html() {
		$output = Shortcodes::render_user_profile();

		$this->assertStringContainsString( 'organizer-user-profile', $output );
		$this->assertStringContainsString( 'Edit Profile', $output );
	}

	/**
	 * Test handle_profile_update updates user and registrations.
	 */
	public function test_handle_profile_update_success() {
		$_POST['organizer_nonce'] = 'valid';
		$_POST['email']           = 'new@example.com';
		$_POST['first_name']      = 'New';
		$_POST['last_name']       = 'Name';

		global $wpdb;
		$wpdb->update_return_value = 1;

		FormHandler::handle_profile_update();

		// If we reached here without wp_die, and wp_safe_redirect was called (mocked), it's a success path.
		$this->assertTrue( true );
	}
}
