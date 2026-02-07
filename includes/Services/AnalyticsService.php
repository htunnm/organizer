<?php
/**
 * Analytics Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use Organizer\Model\Registration;
use Organizer\Model\Waitlist;
use Organizer\Model\Log;

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

	/**
	 * Get waitlist metrics.
	 *
	 * @return array Metrics.
	 */
	public function get_waitlist_metrics() {
		global $wpdb;
		$waitlist_table = Waitlist::get_table_name();
		$log_table      = Log::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$current_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $waitlist_table" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$promoted_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $log_table WHERE action = 'waitlist_promotion'" );

		// Calculate average wait time (in hours) for current waitlist.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$avg_wait_seconds = (int) $wpdb->get_var( "SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, NOW())) FROM $waitlist_table" );
		$avg_wait_hours   = round( $avg_wait_seconds / 3600, 1 );

		return array(
			'current'  => $current_count,
			'promoted' => $promoted_count,
			'avg_wait' => $avg_wait_hours,
		);
	}

	/**
	 * Get waitlist growth over last 7 days.
	 *
	 * @return array Daily counts.
	 */
	public function get_waitlist_growth() {
		global $wpdb;
		$waitlist_table = Waitlist::get_table_name();
		$days           = 7;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT DATE(created_at) as date, COUNT(*) as count FROM $waitlist_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) GROUP BY DATE(created_at) ORDER BY date ASC", $days ), ARRAY_A );

		$data = array();
		foreach ( $results as $row ) {
			$data[ $row['date'] ] = (int) $row['count'];
		}
		return $data;
	}

	/**
	 * Get revenue statistics.
	 *
	 * @return array Revenue stats.
	 */
	public function get_revenue_stats() {
		global $wpdb;
		$reg_table = Registration::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_revenue = (float) $wpdb->get_var( "SELECT SUM(amount) FROM $reg_table WHERE status = 'confirmed'" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_attendees = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $reg_table WHERE status = 'confirmed'" );

		$avg_revenue = $total_attendees > 0 ? $total_revenue / $total_attendees : 0;

		return array(
			'total_revenue' => $total_revenue,
			'avg_revenue'   => $avg_revenue,
		);
	}

	/**
	 * Get revenue by event.
	 *
	 * @return array List of events with revenue.
	 */
	public function get_revenue_by_event() {
		global $wpdb;
		$reg_table   = Registration::get_table_name();
		$posts_table = $wpdb->prefix . 'posts';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT r.event_id, p.post_title as event_title, COUNT(r.id) as count, SUM(r.amount) as revenue FROM %i r INNER JOIN %i p ON r.event_id = p.ID WHERE r.status = %s GROUP BY r.event_id ORDER BY revenue DESC',
				$reg_table,
				$posts_table,
				'confirmed'
			),
			ARRAY_A
		);
	}
}
