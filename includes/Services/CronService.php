<?php
/**
 * Cron Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use Organizer\Services\Email\GmailAdapter;
use Organizer\Services\Email\ReminderService;

/**
 * Class CronService
 */
class CronService {

	/**
	 * Handle daily reminders.
	 */
	public static function handle_daily_reminders() {
		$options = get_option( 'organizer_options' );
		$hours   = isset( $options['organizer_reminder_hours'] ) ? (int) $options['organizer_reminder_hours'] : 24;

		$email_service    = new GmailAdapter();
		$reminder_service = new ReminderService( $email_service );

		// We run this hourly via cron, checking for sessions starting in X hours.
		// The window in process_upcoming_reminders is 1 hour wide, so running hourly covers all.
		$count = $reminder_service->process_upcoming_reminders( $hours );

		// Ideally log this action.
	}
}
