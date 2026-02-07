<?php
/**
 * Email Service Interface.
 *
 * @package Organizer\Services\Email
 */

namespace Organizer\Services\Email;

/**
 * Interface EmailServiceInterface
 */
interface EmailServiceInterface {

	/**
	 * Send an email.
	 *
	 * @param string $to      Recipient email address.
	 * @param string $subject Email subject.
	 * @param string $message Email body (HTML).
	 * @param array  $headers Optional headers.
	 * @param array  $attachments Optional attachments.
	 * @return bool True on success, false on failure.
	 */
	public function send( string $to, string $subject, string $message, array $headers = array(), array $attachments = array() ): bool;
}
