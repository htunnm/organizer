<?php
/**
 * Feedback Model.
 *
 * @package Organizer\Model
 */

namespace Organizer\Model;

/**
 * Class Feedback
 */
class Feedback {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'organizer_feedback';
	}

	/**
	 * Submit feedback.
	 *
	 * @param array $data Feedback data.
	 * @return int|false Inserted ID or false.
	 */
	public static function submit( $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$insert_data = array(
			'event_id'        => $data['event_id'],
			'registration_id' => isset( $data['registration_id'] ) ? $data['registration_id'] : 0,
			'rating'          => intval( $data['rating'] ),
			'comment'         => sanitize_textarea_field( $data['comment'] ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert( $table_name, $insert_data );

		if ( $result ) {
			return $wpdb->insert_id;
		}
		return false;
	}

	/**
	 * Get average rating for an event.
	 *
	 * @param int $event_id Event ID.
	 * @return float Average rating.
	 */
	public static function get_average_rating( $event_id ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (float) $wpdb->get_var( $wpdb->prepare( "SELECT AVG(rating) FROM $table_name WHERE event_id = %d", $event_id ) );
	}

	/**
	 * Get feedback for an event.
	 *
	 * @param int $event_id Event ID.
	 * @param int $limit    Limit.
	 * @return array List of feedback.
	 */
	public static function get_by_event( $event_id, $limit = 5 ) {
		global $wpdb;
		$table_name = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE event_id = %d ORDER BY created_at DESC LIMIT %d", $event_id, $limit ), ARRAY_A );
	}

	/**
	 * Create the feedback table.
	 */
	public static function create_table() {
		global $wpdb;
		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_id bigint(20) unsigned NOT NULL,
			registration_id bigint(20) unsigned DEFAULT 0,
			rating int(1) NOT NULL,
			comment text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY event_id (event_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
