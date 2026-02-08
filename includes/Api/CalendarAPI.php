<?php
/**
 * Calendar API.
 *
 * @package Organizer\Api
 */

namespace Organizer\Api;

use Organizer\Model\Session;

/**
 * Class CalendarAPI
 *
 * Provides REST API endpoint for FullCalendar month view.
 */
class CalendarAPI {

	/**
	 * Register the REST API routes.
	 */
	public static function register_routes() {
		register_rest_route(
			'organizer/v1',
			'/events',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_events' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'start' => array(
						'description'       => 'Start date in ISO 8601 format',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'end'   => array(
						'description'       => 'End date in ISO 8601 format',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Get events for the given date range.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 * @return \WP_REST_Response
	 */
	public static function get_events( $request ) {
		$start_date = $request->get_param( 'start' );
		$end_date   = $request->get_param( 'end' );

		if ( ! $start_date || ! $end_date ) {
			return new \WP_REST_Response(
				array(
					'error' => 'Start and end dates are required',
				),
				400
			);
		}

		// Convert ISO 8601 dates to WordPress format.
		$start_timestamp = strtotime( $start_date );
		$end_timestamp   = strtotime( $end_date );

		if ( ! $start_timestamp || ! $end_timestamp ) {
			return new \WP_REST_Response(
				array(
					'error' => 'Invalid date format',
				),
				400
			);
		}

		// Query events for this date range.
		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->prepare(
			'SELECT DISTINCT
				se.id,
				se.event_id,
				p.post_title,
				se.start_date,
				se.end_date,
				pm_reg_url.meta_value as registration_url
			FROM ' . $wpdb->prefix . 'organizer_events se
			LEFT JOIN ' . $wpdb->posts . ' p ON se.event_id = p.ID
			LEFT JOIN ' . $wpdb->postmeta . ' pm_reg_url ON p.ID = pm_reg_url.post_id AND pm_reg_url.meta_key = %s
			WHERE se.start_date >= %s
			AND se.start_date < %s
			AND p.post_status IN (%s, %s)
			ORDER BY se.start_date ASC',
			'_organizer_registration_url',
			$start_date . ' 00:00:00',
			$end_date . ' 23:59:59',
			'publish',
			'future'
		);
		// phpcs:enable

		// Debug logging.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Calendar API Query: ' . $query );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$results = $wpdb->get_results( $query );

		$events = array();

		if ( $results ) {
			foreach ( $results as $row ) {
				$event_id         = (int) $row->event_id;
				$registration_url = $row->registration_url ? $row->registration_url : site_url( '/register/?event_id=' . $event_id );

				// Convert datetime to ISO 8601 format.
				$start = self::format_datetime( $row->start_date );
				$end   = self::format_datetime( $row->end_date );

				$events[] = array(
					'id'              => (int) $row->id,
					'title'           => $row->post_title ? $row->post_title : 'Untitled Event',
					'start'           => $start,
					'end'             => $end,
					'url'             => $registration_url,
					'backgroundColor' => '#8b0000',
					'borderColor'     => '#8b0000',
					'textColor'       => '#ffffff',
				);
			}
		}

		return new \WP_REST_Response( $events, 200 );
	}

	/**
	 * Format datetime to ISO 8601 format.
	 *
	 * @param string $datetime The datetime string from database.
	 * @return string ISO 8601 formatted datetime.
	 */
	private static function format_datetime( $datetime ) {
		if ( ! $datetime ) {
			return '';
		}

		// Parse the datetime and format as ISO 8601.
		$timestamp = strtotime( $datetime );
		if ( ! $timestamp ) {
			return '';
		}

		return gmdate( 'Y-m-d\TH:i:s', $timestamp );
	}
}
