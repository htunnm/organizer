<?php
/**
 * Privacy Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use Organizer\Model\Registration;
use Organizer\Model\Waitlist;
use Organizer\Model\RSVP;
use Organizer\Model\Feedback;

/**
 * Class PrivacyService
 */
class PrivacyService {

	/**
	 * Register privacy hooks.
	 */
	public static function init() {
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
	}

	/**
	 * Register data exporter.
	 *
	 * @param array $exporters Exporters list.
	 * @return array Modified list.
	 */
	public static function register_exporter( $exporters ) {
		$exporters['organizer-plugin'] = array(
			'exporter_friendly_name' => __( 'Organizer Plugin', 'organizer' ),
			'callback'               => array( __CLASS__, 'export_personal_data' ),
		);
		return $exporters;
	}

	/**
	 * Register data eraser.
	 *
	 * @param array $erasers Erasers list.
	 * @return array Modified list.
	 */
	public static function register_eraser( $erasers ) {
		$erasers['organizer-plugin'] = array(
			'eraser_friendly_name' => __( 'Organizer Plugin', 'organizer' ),
			'callback'             => array( __CLASS__, 'erase_personal_data' ),
		);
		return $erasers;
	}

	/**
	 * Export personal data.
	 *
	 * @param string $email_address Email address.
	 * @return array Export data.
	 */
	public static function export_personal_data( $email_address ) {
		$export_items  = array();
		$registrations = Registration::get_by_user_email( $email_address );

		foreach ( $registrations as $reg ) {
			$data           = array(
				array(
					'name'  => __( 'Event ID', 'organizer' ),
					'value' => $reg['event_id'],
				),
				array(
					'name'  => __( 'Registration Date', 'organizer' ),
					'value' => $reg['created_at'],
				),
				array(
					'name'  => __( 'Status', 'organizer' ),
					'value' => $reg['status'],
				),
			);
			$export_items[] = array(
				'group_id'    => 'organizer-registrations',
				'group_label' => __( 'Organizer Registrations', 'organizer' ),
				'item_id'     => 'organizer-registration-' . $reg['id'],
				'data'        => $data,
			);
		}

		return array(
			'data' => $export_items,
			'done' => true,
		);
	}

	/**
	 * Erase personal data.
	 *
	 * @param string $email_address Email address.
	 * @return array Erasure result.
	 */
	public static function erase_personal_data( $email_address ) {
		$items_removed  = false;
		$items_retained = false;
		$messages       = array();

		// Anonymize Registrations.
		$registrations = Registration::get_by_user_email( $email_address );
		global $wpdb;
		$reg_table = Registration::get_table_name();

		foreach ( $registrations as $reg ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$updated = $wpdb->update(
				$reg_table,
				array(
					'name'  => 'Deleted User',
					'email' => 'deleted@example.com',
				),
				array( 'id' => $reg['id'] )
			);

			if ( $updated ) {
				$items_removed = true;
			}
		}

		// Remove from Waitlist.
		$waitlist_table = Waitlist::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted_wl = $wpdb->delete( $waitlist_table, array( 'email' => $email_address ) );
		if ( $deleted_wl ) {
			$items_removed = true;
		}

		// Anonymize Feedback (optional, but linked to registration usually via ID, here we assume email isn't stored directly in feedback but linked via registration which is now anonymized).
		// However, if we stored email in feedback, we'd clean it here.
		// Since feedback links to registration_id, and we anonymized the registration, the link remains but PII is gone from registration.

		return array(
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => true,
		);
	}
}
