<?php
/**
 * Registration Model.
 *
 * @package Organizer\Model
 */

namespace Organizer\Model;

/**
 * Class Registration
 */
class Registration {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'organizer_registrations';
	}

	/**
	 * Create a new registration.
	 *
	 * @param array $data Registration data.
	 * @return int|false Inserted ID or false on failure.
	 */
	public static function create( $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		// Ensure defaults.
		if ( ! isset( $data['status'] ) ) {
			$data['status'] = 'pending';
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert( $table_name, $data );

		if ( $result ) {
			return $wpdb->insert_id;
		}
		return false;
	}

	/**
	 * Count registrations for an event.
	 *
	 * @param int $event_id Event ID.
	 * @return int Count.
	 */
	public static function count_by_event( $event_id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE event_id = %d AND status != 'cancelled'", $event_id ) );
	}

	/**
	 * Get attendees for an event.
	 *
	 * @param int $event_id Event ID.
	 * @param int $session_id Session ID (optional).
	 * @return array List of attendees.
	 */
	public static function get_attendees( $event_id, $session_id = 0 ) {
		global $wpdb;
		$table_name = self::get_table_name();
		if ( $session_id > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE event_id = %d AND session_id = %d AND status != 'cancelled'", $event_id, $session_id ) );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE event_id = %d AND status != 'cancelled'", $event_id ) );
	}

	/**
	 * Get all registrations with pagination.
	 *
	 * @param int    $limit   Number of items.
	 * @param int    $offset  Offset.
	 * @param string $orderby Column to sort by.
	 * @param string $order   Sort order.
	 * @return array List of registrations.
	 */
	public static function get_all( $limit = 20, $offset = 0, $orderby = 'created_at', $order = 'DESC' ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$allowed_orderby = array( 'name', 'email', 'status', 'created_at' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'created_at';
		}
		$order = ( 'ASC' === strtoupper( $order ) ) ? 'ASC' : 'DESC';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $limit, $offset ), ARRAY_A );
	}

	/**
	 * Count all registrations.
	 *
	 * @return int Count.
	 */
	public static function count_all() {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
	}

	/**
	 * Create the registrations table.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned NOT NULL,
			session_id bigint(20) unsigned DEFAULT 0,
			name varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			status varchar(50) NOT NULL DEFAULT 'pending',
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY session_id (session_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
