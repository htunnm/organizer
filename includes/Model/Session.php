<?php
/**
 * Session Model.
 *
 * @package Organizer\Model
 */

namespace Organizer\Model;

/**
 * Class Session
 */
class Session {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'organizer_sessions';
	}

	/**
	 * Create a session.
	 *
	 * @param array $data Session data.
	 * @return int|false Inserted ID or false on failure.
	 */
	public static function create( $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert( $table_name, $data );

		if ( $result ) {
			return $wpdb->insert_id;
		}
		return false;
	}

	/**
	 * Create the sessions table.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned NOT NULL,
			start_datetime datetime NOT NULL,
			end_datetime datetime NOT NULL,
			capacity int(11) DEFAULT -1,
			status varchar(20) DEFAULT 'scheduled',
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY start_datetime (start_datetime)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Get all sessions with pagination.
	 *
	 * @param int    $limit   Number of items.
	 * @param int    $offset  Offset.
	 * @param string $orderby Column to sort by.
	 * @param string $order   Sort order.
	 * @param string $category_slug Category slug to filter by.
	 * @return array List of sessions.
	 */
	public static function get_all( $limit = 20, $offset = 0, $orderby = 'start_datetime', $order = 'ASC', $category_slug = '' ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$allowed_orderby = array( 'start_datetime', 'end_datetime', 'status', 'created_at' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'start_datetime';
		}
		$order = ( 'DESC' === strtoupper( $order ) ) ? 'DESC' : 'ASC';

		if ( ! empty( $category_slug ) ) {
			$term = get_term_by( 'slug', $category_slug, 'organizer_category' );
			if ( $term ) {
				// Join with WP term tables.
				$sql = "SELECT s.* FROM $table_name s
					INNER JOIN {$wpdb->prefix}term_relationships tr ON s.event_id = tr.object_id
					INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
					WHERE tt.term_id = %d
					ORDER BY s.$orderby $order LIMIT %d OFFSET %d";

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				return $wpdb->get_results( $wpdb->prepare( $sql, $term->term_id, $limit, $offset ), ARRAY_A );
			}
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $limit, $offset ), ARRAY_A );
	}

	/**
	 * Count all sessions.
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
	 * Delete sessions by event ID.
	 *
	 * @param int $event_id Event ID.
	 * @return int|false Number of rows deleted or false on error.
	 */
	public static function delete_by_event( $event_id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete( $table_name, array( 'event_id' => $event_id ) );
	}

	/**
	 * Get a session by ID.
	 *
	 * @param int $id Session ID.
	 * @return object|null Session object or null.
	 */
	public static function get( $id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );
	}

	/**
	 * Cancel a session.
	 *
	 * @param int $id Session ID.
	 * @return int|false Number of rows updated or false on error.
	 */
	public static function cancel( $id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update( $table_name, array( 'status' => 'cancelled' ), array( 'id' => $id ) );
	}
}
