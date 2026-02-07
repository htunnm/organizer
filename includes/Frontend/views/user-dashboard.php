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
 * @var array $registrations List of registrations (all).
 * @var array $upcoming List of upcoming registrations.
 * @var array $past List of past registrations.
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

	<div class="organizer-dashboard-tabs">
		<button class="organizer-tab-link active" onclick="openTab(event, 'upcoming')"><?php esc_html_e( 'Upcoming Events', 'organizer' ); ?></button>
		<button class="organizer-tab-link" onclick="openTab(event, 'past')"><?php esc_html_e( 'Past Events', 'organizer' ); ?></button>
	</div>

	<div id="upcoming" class="organizer-tab-content" style="display: block;">
		<?php if ( empty( $upcoming ) ) : ?>
			<p><?php esc_html_e( 'No upcoming events found.', 'organizer' ); ?></p>
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
					<?php foreach ( $upcoming as $registration ) : ?>
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

	<div id="past" class="organizer-tab-content" style="display: none;">
		<?php if ( empty( $past ) ) : ?>
			<p><?php esc_html_e( 'No past events found.', 'organizer' ); ?></p>
		<?php else : ?>
			<table class="organizer-dashboard-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Event', 'organizer' ); ?></th>
						<th><?php esc_html_e( 'Date', 'organizer' ); ?></th>
						<th><?php esc_html_e( 'Status', 'organizer' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $past as $registration ) : ?>
						<tr>
							<td><?php echo esc_html( get_the_title( $registration['event_id'] ) ); ?></td>
							<td><?php echo esc_html( $registration['created_at'] ); ?></td>
							<td><?php echo esc_html( ucfirst( $registration['status'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
