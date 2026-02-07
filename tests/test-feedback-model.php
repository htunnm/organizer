<?php
/**
 * Class FeedbackModelTest
 *
 * @package Organizer
 */

use Organizer\Model\Feedback;

/**
 * Test the Feedback model.
 */
class FeedbackModelTest extends \PHPUnit\Framework\TestCase {

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
			$wpdb->get_var_return      = 0;
		}
	}

	/**
	 * Test submit inserts data.
	 */
	public function test_submit_inserts_data() {
		global $wpdb;
		$wpdb->insert_return_value = 1;
		$wpdb->insert_id           = 10;

		$data = array(
			'event_id' => 1,
			'rating'   => 5,
			'comment'  => 'Great!',
		);
		$id   = Feedback::submit( $data );
		$this->assertEquals( 10, $id );
	}

	/**
	 * Test get_average_rating returns float.
	 */
	public function test_get_average_rating() {
		global $wpdb;
		$wpdb->get_var_return = '4.5';
		$this->assertEquals( 4.5, Feedback::get_average_rating( 1 ) );
	}
}
