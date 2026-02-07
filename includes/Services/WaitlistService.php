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
use Organizer\Services\Email\TemplateService;

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

		$options = get_option( 'organizer_options' );
		$hours   = isset( $options['organizer_waitlist_expiration'] ) ? (int) $options['organizer_waitlist_expiration'] : 24;

		$data = array(
			'event_id'   => $event_id,
			'session_id' => $session_id,
			'name'       => $next_user->name,
			'email'      => $next_user->email,
			'status'     => 'pending',
			'expires_at' => gmdate( 'Y-m-d H:i:s', time() + ( $hours * 3600 ) ),
		);

		$registration_id = Registration::create( $data );

		if ( ! $registration_id ) {
			return false;
		}

		Waitlist::remove( $next_user->id );

		$template_service = new TemplateService();
		$template         = $template_service->get_template( 'waitlist_promotion' );
		$placeholders     = array(
			'attendee_name' => esc_html( $next_user->name ),
			'event_title'   => get_the_title( $event_id ),
		);
		$subject          = $template_service->render( $template['subject'], $placeholders );
		$message          = $template_service->render( $template['message'], $placeholders );

		$this->email_service->send( $next_user->email, $subject, nl2br( $message ) );

		return true;
	}
}
