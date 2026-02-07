<?php
/**
 * Class TemplateServiceTest
 *
 * @package Organizer
 */

use Organizer\Services\Email\TemplateService;

/**
 * Test the TemplateService class.
 */
class TemplateServiceTest extends \PHPUnit\Framework\TestCase {

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
