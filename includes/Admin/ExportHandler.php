<?php
/**
 * Export Handler.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Model\Registration;

/**
 * Class ExportHandler
 */
class ExportHandler {

	/**
	 * Initialize the handler.
	 */
	public static function init() {
		add_action( 'admin_post_organizer_export_registrations', array( __CLASS__, 'handle_export' ) );
	}

	/**
	 * Handle CSV export.
	 */
	public static function handle_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to export data.', 'organizer' ) );
		}

		if ( ! isset( $_GET['organizer_export_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['organizer_export_nonce'] ) ), 'organizer_export_registrations' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'organizer' ) );
		}

		$registrations = Registration::get_for_export();
		$filename      = 'registrations-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// Headers.
		fputcsv( $output, array( 'ID', 'Event ID', 'Session ID', 'Name', 'Email', 'Status', 'RSVP', 'Date' ) );

		foreach ( $registrations as $row ) {
			fputcsv(
				$output,
				array(
					$row['id'],
					$row['event_id'],
					$row['session_id'],
					$row['name'],
					$row['email'],
					$row['status'],
					isset( $row['rsvp_status'] ) ? $row['rsvp_status'] : '',
					$row['created_at'],
				)
			);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $output );
		exit;
	}
}
