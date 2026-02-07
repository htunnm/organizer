<?php
/**
 * Waitlist Service.
 *
 * @package Organizer\Services
 */

namespace Organizer\Services;

use Organizer\Model\Registration;
use Organizer\Model\Waitlist;
use Organizer\Services\Email\EmailServiceInterface;

/**
 * Class WaitlistService
 */
class WaitlistService {

	/**
	 * Email service.
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
	 * Promote the next user from the waitlist.
	 *
	 * @param int $event_id Event ID.
	 * @param int $session_id Session ID (optional).
	 * @return bool True if a user was promoted, false otherwise.
	 */
	public function promote_next_user( $event_id, $session_id = 0 ) {
		$next_user = Waitlist::get_next_in_line( $event_id, $session_id );

		if ( ! $next_user ) {
			return false;
		}

		$data = array(
			'event_id'   => $event_id,
			'session_id' => $session_id,
			'name'       => $next_user->name,
			'email'      => $next_user->email,
			'status'     => 'pending',
		);

		$registration_id = Registration::create( $data );

		if ( ! $registration_id ) {
			return false;
		}

		Waitlist::remove( $next_user->id );

		$subject = __( 'You have been promoted from the waitlist!', 'organizer' );
		$message = sprintf(
			/* translators: %s: Attendee Name */
			__( 'Hi %s,<br><br>Good news! A spot has opened up and you have been registered for the event.<br><br>Regards,<br>Organizer Team', 'organizer' ),
			esc_html( $next_user->name )
		);

		$this->email_service->send( $next_user->email, $subject, $message );

		return true;
	}
}
