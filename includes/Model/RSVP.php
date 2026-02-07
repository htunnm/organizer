<?php
/**
 * RSVP Model.
 *
 * @package Organizer\Model
 */

namespace Organizer\Model;

/**
 * Class RSVP
 */
class RSVP {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'organizer_rsvps';
	}

	/**
	 * Submit an RSVP response.
	 *
	 * @param array $data RSVP data.
	 * @return int|false Inserted ID or false on failure.
	 */
	public static function submit( $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$insert_data = array(
			'registration_id' => $data['registration_id'],
			'response'        => $data['response'],
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert( $table_name, $insert_data );

		if ( $result ) {
			return $wpdb->insert_id;
		}
		return false;
	}

	/**
	 * Create the RSVP table.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			registration_id bigint(20) unsigned NOT NULL,
			response varchar(20) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY registration_id (registration_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
