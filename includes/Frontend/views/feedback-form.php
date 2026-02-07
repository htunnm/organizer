<?php
/**
 * Feedback Form View.
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
 * @var int $registration_id Registration ID (0 if anonymous).
 */
?>
<div class="organizer-feedback-form">
	<h3><?php esc_html_e( 'Leave Feedback', 'organizer' ); ?></h3>
	<?php
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['organizer_feedback'] ) && 'success' === $_GET['organizer_feedback'] ) {
		echo '<div class="organizer-message success">' . esc_html__( 'Thank you for your feedback!', 'organizer' ) . '</div>';
	}
	// phpcs:enable
	?>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="organizer_submit_feedback">
		<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
		<input type="hidden" name="registration_id" value="<?php echo esc_attr( $registration_id ); ?>">
		<?php wp_nonce_field( 'organizer_feedback_nonce', 'organizer_nonce' ); ?>
		
		<p>
			<label for="organizer_rating"><?php esc_html_e( 'Rating (1-5)', 'organizer' ); ?></label>
			<input type="number" name="rating" id="organizer_rating" min="1" max="5" required>
		</p>
		<p><label for="organizer_comment"><?php esc_html_e( 'Comment', 'organizer' ); ?></label></p>
		<p><textarea name="comment" id="organizer_comment" rows="4" required></textarea></p>
		<p><button type="submit" class="button"><?php esc_html_e( 'Submit Feedback', 'organizer' ); ?></button></p>
	</form>
</div>
