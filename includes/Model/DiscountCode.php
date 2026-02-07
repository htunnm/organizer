<?php
/**
 * Discount Code Model.
 *
 * @package Organizer\Model
 */

namespace Organizer\Model;

/**
 * Class DiscountCode
 */
class DiscountCode {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'organizer_discount_codes';
	}

	/**
	 * Create a discount code.
	 *
	 * @param array $data Data.
	 * @return int|false Inserted ID or false.
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
	 * Get discount code by code string.
	 *
	 * @param string $code Code.
	 * @return object|null Row object or null.
	 */
	public static function get_by_code( $code ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE code = %s", $code ) );
	}

	/**
	 * Get all discount codes.
	 *
	 * @return array List of codes.
	 */
	public static function get_all() {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A );
	}

	/**
	 * Increment usage count.
	 *
	 * @param int $id ID.
	 * @return int|false
	 */
	public static function increment_usage( $id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET usage_count = usage_count + 1 WHERE id = %d", $id ) );
	}

	/**
	 * Delete a discount code.
	 *
	 * @param int $id ID.
	 * @return int|false
	 */
	public static function delete( $id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete( $table_name, array( 'id' => $id ) );
	}

	/**
	 * Create the table.
	 */
	public static function create_table() {
		global $wpdb;
		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			code varchar(50) NOT NULL,
			type varchar(20) NOT NULL DEFAULT 'fixed',
			amount float NOT NULL DEFAULT 0,
			expires_at datetime DEFAULT NULL,
			usage_limit int(11) DEFAULT 0,
			usage_count int(11) DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
