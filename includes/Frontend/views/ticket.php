<?php
/**
 * Ticket View.
 *
 * @package Organizer\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available variables:
 *
 * @var object $registration Registration object.
 * @var string $fileurl QR Code image URL.
 */
?>
<div class="organizer-ticket" style="border: 2px dashed #333; padding: 20px; max-width: 400px; margin: 0 auto; text-align: center;">
	<h2><?php echo esc_html( get_the_title( $registration->event_id ) ); ?></h2>
	<p><strong><?php esc_html_e( 'Attendee:', 'organizer' ); ?></strong> <?php echo esc_html( $registration->name ); ?></p>
	<p><strong><?php esc_html_e( 'Status:', 'organizer' ); ?></strong> <?php echo esc_html( ucfirst( $registration->status ) ); ?></p>
	<div class="organizer-qr-code">
		<img src="<?php echo esc_url( $fileurl ); ?>" alt="Ticket QR Code" style="max-width: 100%;">
	</div>
	<p><small><?php esc_html_e( 'Scan this code at the entrance.', 'organizer' ); ?></small></p>
</div>
