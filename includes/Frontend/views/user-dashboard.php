<?php
/**
 * User Dashboard View.
 *
 * @package Organizer\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available variables:
 *
 * @var array $registrations List of registrations.
 */
?>
<div class="organizer-user-dashboard">
	<h2><?php esc_html_e( 'My Registrations', 'organizer' ); ?></h2>
	<?php
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['organizer_cancellation'] ) ) {
		if ( 'success' === $_GET['organizer_cancellation'] ) {
			echo '<div class="organizer-message success">' . esc_html__( 'Registration cancelled successfully.', 'organizer' ) . '</div>';
		} else {
			echo '<div class="organizer-message error">' . esc_html__( 'Cancellation failed.', 'organizer' ) . '</div>';
		}
	}
	// phpcs:enable
	?>
	<?php if ( empty( $registrations ) ) : ?>
		<p><?php esc_html_e( 'You have no registrations.', 'organizer' ); ?></p>
	<?php else : ?>
		<table class="organizer-dashboard-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Event', 'organizer' ); ?></th>
					<th><?php esc_html_e( 'Date', 'organizer' ); ?></th>
					<th><?php esc_html_e( 'Status', 'organizer' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'organizer' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $registrations as $registration ) : ?>
					<tr>
						<td><?php echo esc_html( get_the_title( $registration['event_id'] ) ); ?></td>
						<td><?php echo esc_html( $registration['created_at'] ); ?></td>
						<td><?php echo esc_html( ucfirst( $registration['status'] ) ); ?></td>
						<td>
							<?php if ( 'cancelled' !== $registration['status'] ) : ?>
								<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to cancel?', 'organizer' ); ?>');">
									<input type="hidden" name="action" value="organizer_cancel_registration">
									<input type="hidden" name="registration_id" value="<?php echo esc_attr( $registration['id'] ); ?>">
									<?php wp_nonce_field( 'organizer_cancel_nonce', 'organizer_nonce' ); ?>
									<button type="submit" class="button"><?php esc_html_e( 'Cancel', 'organizer' ); ?></button>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
