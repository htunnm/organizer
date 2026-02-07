<?php
/**
 * Reminder Service.
 *
 * @package Organizer\Services\Email
 */

namespace Organizer\Services\Email;

use Organizer\Model\Registration;

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
}
