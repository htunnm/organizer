<?php
/**
 * Reminder Service.
 *
 * @package Organizer\Services\Email
 */

namespace Organizer\Services\Email;

use Organizer\Model\Registration;
use Organizer\Model\Session;
use Organizer\Services\Email\TemplateService;

/**
 * Class ReminderService
 */
class ReminderService {

	/**
	 * Email service instance.
	 *
	 * @var EmailServiceInterface
	 */
	private $email_service;

	/**
	 * Constructor.
	 *
	 * @param EmailServiceInterface $email_service Email service.
	 */
	public function __construct( EmailServiceInterface $email_service ) {
		$this->email_service = $email_service;
	}

	/**
	 * Send reminders for an event.
	 *
	 * @param int    $event_id Event ID.
	 * @param int    $session_id Session ID (optional).
	 * @param string $subject  Email subject.
	 * @param string $message  Email message.
	 * @return int Number of emails sent.
	 */
	public function send_reminders( $event_id, $session_id, $subject, $message ) {
		$attendees = Registration::get_attendees( $event_id, $session_id );
		$count     = 0;

		foreach ( $attendees as $attendee ) {
			if ( $this->email_service->send( $attendee->email, $subject, $message ) ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Process upcoming reminders.
	 *
	 * @param int $hours Hours before session to send.
	 * @return int Number of reminders sent.
	 */
	public function process_upcoming_reminders( $hours = 24 ) {
		// Find sessions starting between (now + hours) and (now + hours + 1).
		$start_window = gmdate( 'Y-m-d H:i:s', time() + ( $hours * 3600 ) );
		$end_window   = gmdate( 'Y-m-d H:i:s', time() + ( $hours * 3600 ) + 3600 );

		global $wpdb;
		$table_name = Session::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sessions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE start_datetime BETWEEN %s AND %s AND status = 'scheduled'", $start_window, $end_window ) );

		if ( empty( $sessions ) ) {
			return 0;
		}

		$total_sent = 0;

		foreach ( $sessions as $session ) {
			$event_title = get_the_title( $session->event_id );

			$template_service = new TemplateService();
			$template         = $template_service->get_template( 'event_reminder' );
			$placeholders     = array(
				'event_title' => $event_title,
				'start_date'  => $session->start_datetime,
			);
			$subject          = $template_service->render( $template['subject'], $placeholders );
			$message          = $template_service->render( $template['message'], $placeholders );

			$count       = $this->send_reminders( $session->event_id, $session->id, $subject, nl2br( $message ) );
			$total_sent += $count;
		}

		return $total_sent;
	}
}
