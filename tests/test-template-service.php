<?php
/**
 * Class Test_Template_Service
 *
 * @package Organizer
 */

use Organizer\Services\Email\TemplateService;

/**
 * Test the TemplateService class.
 */
class Test_Template_Service extends \PHPUnit\Framework\TestCase {

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
	 * Test get_template returns defaults.
	 */
	public function test_get_template_returns_defaults() {
		$service  = new TemplateService();
		$template = $service->get_template( 'registration_confirmation' );

		$this->assertNotEmpty( $template['subject'] );
		$this->assertNotEmpty( $template['message'] );
		$this->assertStringContainsString( 'Registration Confirmation', $template['subject'] );
	}

	/**
	 * Test get_template returns event override.
	 */
	public function test_get_template_returns_event_override() {
		WPMocks::$post_meta[1]['_organizer_email_registration_confirmation_subject'] = 'Custom Subject';
		WPMocks::$post_meta[1]['_organizer_email_registration_confirmation_message'] = 'Custom Message';

		$service  = new TemplateService();
		$template = $service->get_template( 'registration_confirmation', 1 );

		$this->assertEquals( 'Custom Subject', $template['subject'] );
		$this->assertEquals( 'Custom Message', $template['message'] );
	}

	/**
	 * Test render replaces placeholders.
	 */
	public function test_render_replaces_placeholders() {
		$service = new TemplateService();
		$content = 'Hello {name}, welcome to {event}!';
		$data    = array(
			'name'  => 'John',
			'event' => 'WordCamp',
		);

		$rendered = $service->render( $content, $data );
		$this->assertEquals( 'Hello John, welcome to WordCamp!', $rendered );
	}
}
