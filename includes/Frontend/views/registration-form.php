<?php
/**
 * Registration Form View.
 *
 * @package Organizer\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available variables:
 *
 * @var int $event_id Event ID.
 * @var int $session_id Session ID.
 */
?>
<div class="organizer-registration-form">
	<?php
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['organizer_registration'] ) ) {
		if ( 'success' === $_GET['organizer_registration'] ) {
			echo '<div class="organizer-message success">' . esc_html__( 'Registration successful!', 'organizer' ) . '</div>';
		} elseif ( 'waitlist' === $_GET['organizer_registration'] ) {
			echo '<div class="organizer-message warning">' . esc_html__( 'Event is full. You have been added to the waitlist.', 'organizer' ) . '</div>';
		} else {
			echo '<div class="organizer-message error">' . esc_html__( 'Registration failed. Please try again.', 'organizer' ) . '</div>';
		}
	}
	// phpcs:enable
	?>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="organizer_register">
		<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id ); ?>">
		<?php wp_nonce_field( 'organizer_register_nonce', 'organizer_nonce' ); ?>
		<p><label><?php esc_html_e( 'Name', 'organizer' ); ?> <input type="text" name="organizer_name" required></label></p>
		<p><label><?php esc_html_e( 'Email', 'organizer' ); ?> <input type="email" name="organizer_email" required></label></p>
		<p><button type="submit"><?php esc_html_e( 'Register', 'organizer' ); ?></button></p>
	</form>
</div>