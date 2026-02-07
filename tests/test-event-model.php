<?php
/**
 * Class EventModelTest
 *
 * @package Organizer
 */

use Organizer\Model\Event;

/**
 * Test the Event model.
 */
class EventModelTest extends \PHPUnit\Framework\TestCase {

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
	 * Test that register creates the custom post type.
	 */
	public function test_register_creates_post_type() {
		Event::register();

		$this->assertArrayHasKey( 'organizer_event', WPMocks::$post_types );

		$args = WPMocks::$post_types['organizer_event'];
		$this->assertEquals( 'Event', $args['label'] );
		$this->assertTrue( $args['public'] );
		$this->assertTrue( $args['show_ui'] );
		$this->assertTrue( $args['show_in_rest'] );
		$this->assertEquals( 'dashicons-calendar-alt', $args['menu_icon'] );
		$this->assertContains( 'title', $args['supports'] );
		$this->assertContains( 'editor', $args['supports'] );

		$this->assertArrayHasKey( 'organizer_category', WPMocks::$taxonomies );
		$this->assertContains( 'organizer_event', WPMocks::$taxonomies['organizer_category']['object_type'] );
	}
}
