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

		if ( ! isset( $data['checkin_token'] ) ) {
			$data['checkin_token'] = wp_generate_password( 32, false );
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
	 * Count registrations for a session.
	 *
	 * @param int $session_id Session ID.
	 * @return int Count.
	 */
	public static function count_by_session( $session_id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE session_id = %d AND status != 'cancelled'", $session_id ) );
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
	 * Get registrations by user email.
	 *
	 * @param string $email User email.
	 * @return array List of registrations.
	 */
	public static function get_by_user_email( $email ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE email = %s ORDER BY created_at DESC", $email ), ARRAY_A );
	}

	/**
	 * Update email for registrations.
	 *
	 * @param string $old_email Old email.
	 * @param string $new_email New email.
	 * @return int|false Number of rows updated or false on error.
	 */
	public static function update_email( $old_email, $new_email ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update( $table_name, array( 'email' => $new_email ), array( 'email' => $old_email ) );
	}

	/**
	 * Get registration by checkin token.
	 *
	 * @param string $token Checkin token.
	 * @return object|null Registration object or null.
	 */
	public static function get_by_token( $token ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE checkin_token = %s", $token ) );
	}

	/**
	 * Check in a registration.
	 *
	 * @param int $id Registration ID.
	 * @return int|false Number of rows updated or false on error.
	 */
	public static function checkin( $id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update( $table_name, array( 'checked_in_at' => current_time( 'mysql' ) ), array( 'id' => $id ) );
	}

	/**
	 * Get registrations for export.
	 *
	 * @return array List of registrations with RSVP status.
	 */
	public static function get_for_export() {
		global $wpdb;
		$table_name = self::get_table_name();
		$rsvp_table = $wpdb->prefix . 'organizer_rsvps';

		$sql = "SELECT r.*, rv.response as rsvp_status 
			FROM $table_name r 
			LEFT JOIN $rsvp_table rv ON r.id = rv.registration_id 
			ORDER BY r.created_at DESC";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql, ARRAY_A );
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
			transaction_id varchar(255) DEFAULT '',
			amount int(11) DEFAULT 0,
			discount_code varchar(50) DEFAULT '',
			discount_amount int(11) DEFAULT 0,
			checkin_token varchar(64) DEFAULT '',
			checked_in_at datetime DEFAULT NULL,
			expires_at datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY session_id (session_id),
			KEY checkin_token (checkin_token)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
