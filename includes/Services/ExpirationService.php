<?php
/**
 * Expiration Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use Organizer\Model\Registration;
use Organizer\Services\WaitlistService;
use Organizer\Services\Email\GmailAdapter;
use Organizer\Services\Logger;

/**
 * Class ExpirationService
 */
class ExpirationService {

	/**
	 * Process expired registrations.
	 *
	 * @return int Number of expired registrations processed.
	 */
	public function process_expired_registrations() {
		global $wpdb;
		$table_name = Registration::get_table_name();
		$now        = gmdate( 'Y-m-d H:i:s' );

		// Find expired pending registrations.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$expired = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE (status = 'pending' OR status = 'pending_payment') AND expires_at IS NOT NULL AND expires_at < %s", $now ) );

		$count = 0;
		if ( empty( $expired ) ) {
			return $count;
		}

		$email_service    = new GmailAdapter();
		$waitlist_service = new WaitlistService( $email_service );

		foreach ( $expired as $registration ) {
			// Cancel registration.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update( $table_name, array( 'status' => 'cancelled' ), array( 'id' => $registration->id ) );

			Logger::log( 'registration_expired', "Registration $registration->id expired", $registration->event_id, $registration->session_id );

			// Promote next user.
			$waitlist_service->promote_next_user( $registration->event_id, $registration->session_id );

			// Notify user (optional, but good practice).
			$subject = __( 'Registration Expired', 'organizer' );
			/* translators: %s: Event ID */
			$message = sprintf( __( 'Your registration for event ID %s has expired due to inactivity.', 'organizer' ), $registration->event_id );
			$email_service->send( $registration->email, $subject, $message );

			++$count;
		}

		return $count;
	}
}
