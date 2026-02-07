<?php
/**
 * Class IcsGeneratorTest
 *
 * @package Organizer
 */

use Organizer\Services\IcsGenerator;

/**
 * Test the IcsGenerator class.
 */
class IcsGeneratorTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Test generate_session_ics returns valid ICS content.
	 */
	public function test_generate_session_ics() {
		$generator = new IcsGenerator();
		$session   = (object) array(
			'id'             => 1,
			'start_datetime' => '2023-10-01 10:00:00',
			'end_datetime'   => '2023-10-01 11:00:00',
		);
		$title     = 'Test Event';

		// Mock $_SERVER['HTTP_HOST'] for the test.
		$_SERVER['HTTP_HOST'] = 'example.com';

		$ics = $generator->generate_session_ics( $session, $title );

		$this->assertStringContainsString( 'BEGIN:VCALENDAR', $ics );
		$this->assertStringContainsString( 'BEGIN:VEVENT', $ics );
		$this->assertStringContainsString( 'SUMMARY:Test Event', $ics );
		$this->assertStringContainsString( 'DTSTART:20231001T100000', $ics );
		$this->assertStringContainsString( 'DTEND:20231001T110000', $ics );
		$this->assertStringContainsString( 'END:VCALENDAR', $ics );
	}
}
