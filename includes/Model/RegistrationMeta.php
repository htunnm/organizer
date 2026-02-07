<?php
/**
 * Registration Meta Model.
 *
 * @package Organizer\Model
 */

namespace Organizer\Model;

/**
 * Class RegistrationMeta
 */
class RegistrationMeta {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'organizer_registration_meta';
	}

	/**
	 * Add meta data.
	 *
	 * @param int    $registration_id Registration ID.
	 * @param string $meta_key        Meta key.
	 * @param string $meta_value      Meta value.
	 * @return int|false Inserted ID or false on failure.
	 */
	public static function add( $registration_id, $meta_key, $meta_value ) {
		global $wpdb;
		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$table_name,
			array(
				'registration_id' => $registration_id,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_key'        => $meta_key,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_value'      => $meta_value,
			)
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}
		return false;
	}

	/**
	 * Get meta data by registration ID.
	 *
	 * @param int $registration_id Registration ID.
	 * @return array List of meta objects.
	 */
	public static function get_by_registration_id( $registration_id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE registration_id = %d", $registration_id ) );
	}

	/**
	 * Create the meta table.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			registration_id bigint(20) unsigned NOT NULL,
			meta_key varchar(255) NOT NULL,
			meta_value longtext,
			PRIMARY KEY  (id),
			KEY registration_id (registration_id),
			KEY meta_key (meta_key)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
