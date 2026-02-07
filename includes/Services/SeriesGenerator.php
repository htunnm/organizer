<?php
/**
 * Series Generator Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use Organizer\Model\Session;
use DateTime;
use DateInterval;
use DatePeriod;

/**
 * Class SeriesGenerator
 */
class SeriesGenerator {

	/**
	 * Generate sessions for an event based on rules.
	 *
	 * @param int   $event_id Event ID.
	 * @param array $rules    Recurrence rules.
	 * @return array List of created session IDs.
	 */
	public function generate_sessions( $event_id, $rules ) {
		$created_ids = array();

		if ( empty( $rules['start_date'] ) || empty( $rules['start_time'] ) ) {
			return $created_ids;
		}

		$start_datetime_str = $rules['start_date'] . ' ' . $rules['start_time'];
		$start_dt           = new DateTime( $start_datetime_str );
		$duration           = isset( $rules['duration_minutes'] ) ? (int) $rules['duration_minutes'] : 60;

		// Single event.
		if ( empty( $rules['type'] ) || 'single' === $rules['type'] ) {
			$id = $this->create_session( $event_id, $start_dt, $duration );
			if ( $id ) {
				$created_ids[] = $id;
			}
			return $created_ids;
		}

		// Recurring event (Weekly).
		if ( 'weekly' === $rules['type'] ) {
			$interval_weeks = isset( $rules['interval'] ) ? max( 1, (int) $rules['interval'] ) : 1;
			$end_type       = isset( $rules['end_type'] ) ? $rules['end_type'] : 'count';
			$end_count      = isset( $rules['end_count'] ) ? (int) $rules['end_count'] : 5;
			$end_date       = isset( $rules['end_date'] ) ? new DateTime( $rules['end_date'] . ' 23:59:59' ) : null;

			// Loop logic.
			$current_dt = clone $start_dt;
			$count      = 0;

			// Safety break.
			$max_iterations = 100;

			while ( $count < $max_iterations ) {
				// Check end conditions.
				if ( 'count' === $end_type && $count >= $end_count ) {
					break;
				}
				if ( 'date' === $end_type && $end_date && $current_dt > $end_date ) {
					break;
				}

				// Create session.
				$id = $this->create_session( $event_id, $current_dt, $duration );
				if ( $id ) {
					$created_ids[] = $id;
				}

				// Advance.
				$current_dt->add( new DateInterval( 'P' . $interval_weeks . 'W' ) );
				++$count;
			}
		}

		return $created_ids;
	}

	/**
	 * Helper to create a single session.
	 *
	 * @param int      $event_id Event ID.
	 * @param DateTime $start_dt Start DateTime.
	 * @param int      $duration Duration in minutes.
	 * @return int|false
	 */
	private function create_session( $event_id, $start_dt, $duration ) {
		$end_dt = clone $start_dt;
		$end_dt->add( new DateInterval( 'PT' . $duration . 'M' ) );

		$data = array(
			'event_id'       => $event_id,
			'start_datetime' => $start_dt->format( 'Y-m-d H:i:s' ),
			'end_datetime'   => $end_dt->format( 'Y-m-d H:i:s' ),
			'status'         => 'scheduled',
		);

		return Session::create( $data );
	}
}
