<?php
/**
 * Class SeriesGeneratorTest
 *
 * @package Organizer
 */

use Organizer\Services\SeriesGenerator;

/**
 * Test the SeriesGenerator class.
 */
class SeriesGeneratorTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->insert_return_value = 1;
			$wpdb->insert_id           = 1;
		}
	}

	/**
	 * Test generate_sessions creates single session.
	 */
	public function test_generate_single_session() {
		$generator = new SeriesGenerator();
		$rules     = array(
			'type'       => 'single',
			'start_date' => '2023-10-01',
			'start_time' => '10:00:00',
		);

		WPMocks::$post_meta[1]['_organizer_event_capacity'] = 20;

		$ids = $generator->generate_sessions( 1, $rules );
		$this->assertCount( 1, $ids );
	}

	/**
	 * Test generate_sessions creates weekly sessions.
	 */
	public function test_generate_weekly_sessions() {
		$generator = new SeriesGenerator();
		$rules     = array(
			'type'       => 'weekly',
			'interval'   => 1,
			'end_type'   => 'count',
			'end_count'  => 3,
			'start_date' => '2023-10-01',
			'start_time' => '10:00:00',
		);

		$ids = $generator->generate_sessions( 1, $rules );
		$this->assertCount( 3, $ids );
	}
}
