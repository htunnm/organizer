<?php
/**
 * ICS Generator Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use DateTime;
use DateTimeZone;

/**
 * Class IcsGenerator
 */
class IcsGenerator {

	/**
	 * Generate ICS content for a single session.
	 *
	 * @param object $session Session object.
	 * @param string $title   Event title.
	 * @param string $desc    Event description.
	 * @return string ICS content.
	 */
	public function generate_session_ics( $session, $title, $desc = '' ) {
		$start = new DateTime( $session->start_datetime );
		$end   = new DateTime( $session->end_datetime );
		$now   = new DateTime();

		$content  = "BEGIN:VCALENDAR\r\n";
		$content .= "VERSION:2.0\r\n";
		$content .= "PRODID:-//Organizer Plugin//NONSGML v1.0//EN\r\n";
		$content .= "CALSCALE:GREGORIAN\r\n";
		$content .= "METHOD:PUBLISH\r\n";
		$content .= "BEGIN:VEVENT\r\n";
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$content .= 'UID:organizer-session-' . $session->id . '@' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . "\r\n";
		$content .= 'DTSTAMP:' . $now->format( 'Ymd\THis\Z' ) . "\r\n";
		$content .= 'DTSTART:' . $start->format( 'Ymd\THis' ) . "\r\n";
		$content .= 'DTEND:' . $end->format( 'Ymd\THis' ) . "\r\n";
		$content .= 'SUMMARY:' . $this->escape_string( $title ) . "\r\n";
		$content .= 'DESCRIPTION:' . $this->escape_string( $desc ) . "\r\n";
		$content .= "STATUS:CONFIRMED\r\n";
		$content .= "END:VEVENT\r\n";
		$content .= 'END:VCALENDAR';

		return $content;
	}

	/**
	 * Generate ICS content for a series (RRULE).
	 *
	 * @param array  $rules Recurrence rules.
	 * @param string $title Event title.
	 * @param string $desc  Event description.
	 * @return string ICS content.
	 */
	public function generate_series_ics( $rules, $title, $desc = '' ) {
		// Basic implementation for weekly series.
		// In a real scenario, this would need to handle all RRULE complexities.
		$start = new DateTime( $rules['start_date'] . ' ' . $rules['start_time'] );
		$now   = new DateTime();

		// Calculate end time based on duration.
		$duration = isset( $rules['duration_minutes'] ) ? (int) $rules['duration_minutes'] : 60;
		$end      = clone $start;
		$end->modify( "+{$duration} minutes" );

		$rrule = '';
		if ( 'weekly' === $rules['type'] ) {
			$interval = isset( $rules['interval'] ) ? (int) $rules['interval'] : 1;
			$rrule    = "RRULE:FREQ=WEEKLY;INTERVAL=$interval";

			if ( 'count' === $rules['end_type'] && ! empty( $rules['end_count'] ) ) {
				$rrule .= ';COUNT=' . (int) $rules['end_count'];
			} elseif ( 'date' === $rules['end_type'] && ! empty( $rules['end_date'] ) ) {
				$until  = new DateTime( $rules['end_date'] . ' 23:59:59' );
				$rrule .= ';UNTIL=' . $until->format( 'Ymd\THis\Z' );
			}
		}

		$content  = "BEGIN:VCALENDAR\r\n";
		$content .= "VERSION:2.0\r\n";
		$content .= "PRODID:-//Organizer Plugin//NONSGML v1.0//EN\r\n";
		$content .= "BEGIN:VEVENT\r\n";
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$content .= 'UID:organizer-series-' . uniqid() . '@' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . "\r\n";
		$content .= 'DTSTAMP:' . $now->format( 'Ymd\THis\Z' ) . "\r\n";
		$content .= 'DTSTART:' . $start->format( 'Ymd\THis' ) . "\r\n";
		$content .= 'DTEND:' . $end->format( 'Ymd\THis' ) . "\r\n";
		if ( $rrule ) {
			$content .= $rrule . "\r\n";
		}
		$content .= 'SUMMARY:' . $this->escape_string( $title ) . "\r\n";
		$content .= 'DESCRIPTION:' . $this->escape_string( $desc ) . "\r\n";
		$content .= "END:VEVENT\r\n";
		$content .= 'END:VCALENDAR';

		return $content;
	}

	/**
	 * Escape string for ICS.
	 *
	 * @param string $text Text to escape.
	 * @return string Escaped text.
	 */
	private function escape_string( $text ) {
		return preg_replace( '/([\,;])/', '\\\$1', $text );
	}
}
