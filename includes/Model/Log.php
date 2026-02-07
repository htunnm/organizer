<?php
/**
 * Log Model.
 *
 * @package Organizer\Model
 */

namespace Organizer\Model;

/**
 * Class Log
 */
class Log {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'organizer_logs';
	}

	/**
	 * Create a log entry.
	 *
	 * @param array $data Log data.
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
	 * Create the logs table.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned DEFAULT 0,
			session_id bigint(20) unsigned DEFAULT 0,
			action varchar(50) NOT NULL,
			message text NOT NULL,
			user_id bigint(20) unsigned DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY event_id (event_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
