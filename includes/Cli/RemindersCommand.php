<?php
/**
 * Reminders CLI Command.
 *
 * @package Organizer\Cli
 */

namespace Organizer\Cli;

use WP_CLI;
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

		$email_service    = new GmailAdapter();
		$reminder_service = new ReminderService( $email_service );

		$count = $reminder_service->process_upcoming_reminders( $hours );
		WP_CLI::success( "Sent $count reminders." );
	}
}
