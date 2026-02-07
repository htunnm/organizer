<?php
/**
 * Waitlist Model.
 *
 * @package Organizer\Model
 */

namespace Organizer\Model;

/**
 * Class Waitlist
 */
class Waitlist {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'organizer_waitlist';
	}

	/**
	 * Add to waitlist.
	 *
	 * @param array $data Waitlist data.
	 * @return int|false Inserted ID or false on failure.
	 */
	public static function add( $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$insert_data = array(
			'event_id'   => $data['event_id'],
			'session_id' => isset( $data['session_id'] ) ? $data['session_id'] : 0,
			'name'       => $data['name'],
			'email'      => $data['email'],
			'priority'   => isset( $data['priority'] ) ? (int) $data['priority'] : 0,
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert( $table_name, $insert_data );

		if ( $result ) {
			return $wpdb->insert_id;
		}
		return false;
	}

	/**
	 * Create the waitlist table.
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
			priority int(11) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY session_id (session_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Get the next person on the waitlist.
	 *
	 * @param int $event_id Event ID.
	 * @param int $session_id Session ID (optional).
	 * @return object|null Row object or null.
	 */
	public static function get_next_in_line( $event_id, $session_id = 0 ) {
		global $wpdb;
		$table_name = self::get_table_name();
		if ( $session_id > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE event_id = %d AND session_id = %d ORDER BY priority DESC, created_at ASC LIMIT 1", $event_id, $session_id ) );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE event_id = %d ORDER BY priority DESC, created_at ASC LIMIT 1", $event_id ) );
	}

	/**
	 * Remove from waitlist.
	 *
	 * @param int $id Waitlist ID.
	 * @return bool|int False on failure, number of rows affected on success.
	 */
	public static function remove( $id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete( $table_name, array( 'id' => $id ) );
	}
}
