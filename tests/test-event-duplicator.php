<?php
/**
 * Class EventDuplicatorTest
 *
 * @package Organizer
 */

use Organizer\Admin\EventDuplicator;

/**
 * Test the EventDuplicator class.
 */
class EventDuplicatorTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Reset mocks before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		if ( class_exists( 'WPMocks' ) ) {
			WPMocks::reset();
		}
		$_GET = array();
	}

	/**
	 * Test add_duplicate_link adds link for organizer_event.
	 */
	public function test_add_duplicate_link() {
		$post = (object) array(
			'ID'        => 1,
			'post_type' => 'organizer_event',
		);

		$actions = EventDuplicator::add_duplicate_link( array(), $post );
		$this->assertArrayHasKey( 'duplicate', $actions );
		$this->assertStringContainsString( 'action=organizer_duplicate_event', $actions['duplicate'] );
	}

	/**
	 * Test handle_duplication creates new draft and copies meta.
	 */
	public function test_handle_duplication_success() {
		$_GET['post_id']  = 1;
		$_GET['_wpnonce'] = 'valid';

		WPMocks::$post_meta[1]['_organizer_event_price'] = '10.00';

		EventDuplicator::handle_duplication();

		$this->assertEquals( '10.00', WPMocks::$post_meta[101]['_organizer_event_price'] );
	}
}
