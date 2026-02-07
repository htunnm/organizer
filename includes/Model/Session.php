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
	 * @param array  $filters       Additional filters (keyword, start_date, end_date).
	 * @return array List of sessions.
	 */
	public static function get_all( $limit = 20, $offset = 0, $orderby = 'start_datetime', $order = 'ASC', $category_slug = '', $filters = array() ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$allowed_orderby = array( 'start_datetime', 'end_datetime', 'status', 'created_at' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'start_datetime';
		}
		$order = ( 'DESC' === strtoupper( $order ) ) ? 'DESC' : 'ASC';

		$where = array( '1=1' );
		$join  = array();
		$args  = array();

		if ( ! empty( $category_slug ) ) {
			$term = get_term_by( 'slug', $category_slug, 'organizer_category' );
			if ( $term ) {
				$join[]  = "INNER JOIN {$wpdb->prefix}term_relationships tr ON s.event_id = tr.object_id";
				$join[]  = "INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
				$where[] = 'tt.term_id = %d';
				$args[]  = $term->term_id;
			}
		}

		if ( ! empty( $filters['keyword'] ) ) {
			$join[]  = "INNER JOIN {$wpdb->prefix}posts p ON s.event_id = p.ID";
			$where[] = 'p.post_title LIKE %s';
			$args[]  = '%' . $wpdb->esc_like( $filters['keyword'] ) . '%';
		}

		if ( ! empty( $filters['start_date'] ) ) {
			$where[] = 's.start_datetime >= %s';
			$args[]  = $filters['start_date'] . ' 00:00:00';
		}

		if ( ! empty( $filters['end_date'] ) ) {
			$where[] = 's.end_datetime <= %s';
			$args[]  = $filters['end_date'] . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );
		$join_sql  = implode( ' ', array_unique( $join ) );
		$sql       = "SELECT s.* FROM $table_name s $join_sql WHERE $where_sql ORDER BY s.$orderby $order LIMIT %d OFFSET %d";
		$args[]    = $limit;
		$args[]    = $offset;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A );
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

	/**
	 * Check if session is full.
	 *
	 * @param int $session_id Session ID.
	 * @return bool True if full.
	 */
	public static function is_full( $session_id ) {
		$session = self::get( $session_id );
		if ( ! $session || $session->capacity < 0 ) {
			return false; // No limit or not found.
		}

		$count = Registration::count_by_session( $session_id );
		return $count >= $session->capacity;
	}

	/**
	 * Get remaining spots for a session.
	 *
	 * @param int $session_id Session ID.
	 * @return int|string Number of spots or 'Unlimited'.
	 */
	public static function get_remaining_spots( $session_id ) {
		$session = self::get( $session_id );
		if ( ! $session || $session->capacity < 0 ) {
			return 'Unlimited';
		}

		$count = Registration::count_by_session( $session_id );
		return max( 0, $session->capacity - $count );
	}
}
