<?php
/**
 * Template Service.
 *
 * @package Organizer\Services\Email
 */

namespace Organizer\Services\Email;

/**
 * Class TemplateService
 */
class TemplateService {

	/**
	 * Get email template.
	 *
	 * @param string $type Template type key.
	 * @return array Array with 'subject' and 'message'.
	 */
	public function get_template( $type ) {
		$options = get_option( 'organizer_email_templates', array() );
		$subject = isset( $options[ $type . '_subject' ] ) ? $options[ $type . '_subject' ] : '';
		$message = isset( $options[ $type . '_message' ] ) ? $options[ $type . '_message' ] : '';

		if ( empty( $subject ) || empty( $message ) ) {
			$defaults = $this->get_defaults( $type );
			if ( empty( $subject ) ) {
				$subject = $defaults['subject'];
			}
			if ( empty( $message ) ) {
				$message = $defaults['message'];
			}
		}

		return array(
			'subject' => $subject,
			'message' => $message,
		);
	}

	/**
	 * Render template.
	 *
	 * @param string $content Content with placeholders.
	 * @param array  $data    Data to replace placeholders.
	 * @return string Rendered content.
	 */
	public function render( $content, $data ) {
		foreach ( $data as $key => $value ) {
			$content = str_replace( '{' . $key . '}', $value, $content );
		}
		return $content;
	}

	/**
	 * Get default templates.
	 *
	 * @param string $type Template type.
	 * @return array Default subject and message.
	 */
	private function get_defaults( $type ) {
		$defaults = array(
			'registration_confirmation' => array(
				'subject' => __( 'Registration Confirmation', 'organizer' ),
				'message' => __( "Hi {attendee_name},\n\nThank you for registering for {event_title}. Your registration is pending approval.\n\nView your ticket here: {ticket_link}\n\nRegards,\nOrganizer Team", 'organizer' ),
			),
			'waitlist_confirmation'     => array(
				'subject' => __( 'Added to Waitlist', 'organizer' ),
				'message' => __( "Hi {attendee_name},\n\nThe event {event_title} is full. You have been added to the waitlist.\n\nRegards,\nOrganizer Team", 'organizer' ),
			),
			'waitlist_promotion'        => array(
				'subject' => __( 'You have been promoted from the waitlist!', 'organizer' ),
				'message' => __( "Hi {attendee_name},\n\nGood news! A spot has opened up for {event_title} and you have been registered.\n\nRegards,\nOrganizer Team", 'organizer' ),
			),
			'event_reminder'            => array(
				'subject' => __( 'Reminder: {event_title} is coming up!', 'organizer' ),
				'message' => __( "Hi there,\n\nThis is a reminder that {event_title} is starting on {start_date}.\n\nSee you there!", 'organizer' ),
			),
			'session_cancelled'         => array(
				'subject' => __( 'Session Cancelled', 'organizer' ),
				'message' => __( "Hi,\n\nThe session for {event_title} scheduled for {start_date} has been cancelled.\n\nRegards,\nOrganizer Team", 'organizer' ),
			),
		);

		return isset( $defaults[ $type ] ) ? $defaults[ $type ] : array(
			'subject' => '',
			'message' => '',
		);
	}
}
