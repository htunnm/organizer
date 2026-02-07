<?php
/**
 * Checkin Handler.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Model\Registration;

/**
 * Class CheckinHandler
 */
class CheckinHandler {

	/**
	 * Initialize the handler.
	 */
	public static function init() {
		add_action( 'admin_post_organizer_checkin', array( __CLASS__, 'handle_checkin' ) );
	}

	/**
	 * Handle check-in request.
	 */
	public static function handle_checkin() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform check-ins.', 'organizer' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

		if ( empty( $token ) ) {
			wp_die( esc_html__( 'Invalid token.', 'organizer' ) );
		}

		$registration = Registration::get_by_token( $token );

		if ( ! $registration ) {
			wp_die( esc_html__( 'Registration not found.', 'organizer' ) );
		}

		if ( ! empty( $registration->checked_in_at ) ) {
			/* translators: %s: Check-in time */
			wp_die( sprintf( esc_html__( 'Attendee already checked in at %s', 'organizer' ), esc_html( $registration->checked_in_at ) ) );
		}

		$result = Registration::checkin( $registration->id );

		if ( $result ) {
			wp_die( esc_html__( 'Check-in successful for: ', 'organizer' ) . esc_html( $registration->name ), esc_html__( 'Success', 'organizer' ) );
		} else {
			wp_die( esc_html__( 'Check-in failed.', 'organizer' ) );
		}
	}
}
