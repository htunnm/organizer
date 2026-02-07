<?php
/**
 * Gmail Adapter.
 *
 * @package Organizer\Services\Email
 */

namespace Organizer\Services\Email;

/**
 * Class GmailAdapter
 *
 * Adapts the WordPress wp_mail function to send emails, intended to be used
 * with the WP Mail SMTP plugin configured for Gmail/Google Workspace.
 */
class GmailAdapter implements EmailServiceInterface {

	/**
	 * Send an email using wp_mail with HTML content type.
	 *
	 * @param string $to      Recipient email address.
	 * @param string $subject Email subject.
	 * @param string $message Email body.
	 * @param array  $headers Optional headers.
	 * @return bool True on success, false on failure.
	 */
	public function send( string $to, string $subject, string $message, array $headers = array() ): bool {
		// Force HTML content type.
		add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

		// Sanitize headers if necessary, though wp_mail handles much of this.
		// Note: $to, $subject, and $message should be sanitized/escaped by the caller
		// or the specific template renderer before reaching this adapter.

		$result = wp_mail( $to, $subject, $message, $headers );

		// Remove filter to avoid conflicts with other plugins.
		remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

		return $result;
	}

	/**
	 * Set content type to HTML.
	 *
	 * @return string
	 */
	public function set_html_content_type(): string {
		return 'text/html';
	}
}
