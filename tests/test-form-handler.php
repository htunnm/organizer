<?php
/**
 * Class FormHandlerTest
 *
 * @package Organizer
 */

use Organizer\Frontend\FormHandler;

/**
 * Test the FormHandler class.
 */
class FormHandlerTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->insert_return_value = false;
			$wpdb->insert_id           = 0;
			$wpdb->get_row_return      = null;
		}
		$_POST = array();
	}

	/**
	 * Test handle_registration creates registration and sends email.
	 */
	public function test_handle_registration_success() {
		$_POST['organizer_nonce'] = 'valid';
		$_POST['event_id']        = 1;
		$_POST['organizer_name']  = 'Form User';
		$_POST['organizer_email'] = 'form@example.com';

		global $wpdb;
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 999;

		// We expect wp_redirect to be called, but since it's mocked to do nothing,
		// we verify the side effects (DB insert and email).
		FormHandler::handle_registration();

		// Verify email sent.
		$this->assertCount( 1, WPMocks::$sent_emails );
		$this->assertEquals( 'form@example.com', WPMocks::$sent_emails[0]['to'] );
		$this->assertStringContainsString( 'Registration Confirmation', WPMocks::$sent_emails[0]['subject'] );

		// Verify DB insert (indirectly via insert_id return, but in a real mock we'd check calls).
		// Since our mock is simple, we rely on the fact that if insert failed, we'd redirect with error.
		// The email sending confirms success path was taken.
	}
}
