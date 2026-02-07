<?php
/**
 * Analytics Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use Organizer\Model\Registration;
use Organizer\Model\Waitlist;

/**
 * Class AnalyticsService
 */
class AnalyticsService {

	/**
	 * Get registration statistics.
	 *
	 * @return array Stats.
	 */
	public function get_registration_stats() {
		global $wpdb;
		$reg_table      = Registration::get_table_name();
		$waitlist_table = Waitlist::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reg_table" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$pending = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reg_table WHERE status = 'pending'" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$waitlist = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $waitlist_table" );

		return array(
			'total'    => $total,
			'pending'  => $pending,
			'waitlist' => $waitlist,
		);
	}

	/**
	 * Get daily registrations for the last 7 days.
	 *
	 * @return array Daily counts (date => count).
	 */
	public function get_daily_registrations() {
		global $wpdb;
		$table_name = Registration::get_table_name();
		$days       = 7;

		$sql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) GROUP BY DATE(created_at) ORDER BY date ASC";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prepare( $sql, $days ),
			ARRAY_A
		);

		$data = array();
		foreach ( $results as $row ) {
			$data[ $row['date'] ] = (int) $row['count'];
		}
		return $data;
	}
}
