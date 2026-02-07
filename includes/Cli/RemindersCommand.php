<?php
/**
 * Reminders CLI Command.
 *
 * @package Organizer\Cli
 */

namespace Organizer\Cli;

use WP_CLI;
use Organizer\Model\Session;
use Organizer\Services\Email\GmailAdapter;
use Organizer\Services\Email\ReminderService;

/**
 * Class RemindersCommand
 */
class RemindersCommand {

	/**
	 * Send reminders for upcoming sessions.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Simulate sending without actually sending emails.
	 *
	 * [--hours=<hours>]
	 * : Number of hours before the session to send reminders. Default is 24.
	 *
	 * ## EXAMPLES
	 *
	 *     wp organizer send-reminders --dry-run
	 *     wp organizer send-reminders --hours=48
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function send_reminders( $args, $assoc_args ) {
		$dry_run = isset( $assoc_args['dry-run'] );
		$hours   = isset( $assoc_args['hours'] ) ? (int) $assoc_args['hours'] : 24;

		WP_CLI::log( "Checking for sessions starting in approx $hours hours..." );

		// Find sessions starting between (now + hours) and (now + hours + 1).
		// This is a simplified logic for the example. In production, you might want a flag on the session.
		$start_window = gmdate( 'Y-m-d H:i:s', time() + ( $hours * 3600 ) );
		$end_window   = gmdate( 'Y-m-d H:i:s', time() + ( $hours * 3600 ) + 3600 );

		global $wpdb;
		$table_name = Session::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sessions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE start_datetime BETWEEN %s AND %s AND status = 'scheduled'", $start_window, $end_window ) );

		if ( empty( $sessions ) ) {
			WP_CLI::success( 'No sessions found requiring reminders.' );
			return;
		}

		$email_service    = new GmailAdapter();
		$reminder_service = new ReminderService( $email_service );

		foreach ( $sessions as $session ) {
			$event_title = get_the_title( $session->event_id );
			WP_CLI::log( "Found session for event '$event_title' at {$session->start_datetime} (ID: {$session->id})" );

			if ( $dry_run ) {
				WP_CLI::log( '  [Dry Run] Would send reminders to attendees.' );
				continue;
			}

			$subject = sprintf(
				/* translators: %s: Event Title */
				__( 'Reminder: %s is coming up!', 'organizer' ),
				$event_title
			);
			$message = sprintf(
				/* translators: 1: Event Title, 2: Start Time */
				__( 'Hi there,<br><br>This is a reminder that <strong>%1$s</strong> is starting on %2$s.<br><br>See you there!', 'organizer' ),
				$event_title,
				$session->start_datetime
			);

			$count = $reminder_service->send_reminders( $session->event_id, $session->id, $subject, $message );
			WP_CLI::success( "  Sent $count reminders." );
		}
	}
}
