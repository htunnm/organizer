<?php
/**
 * Calendar View.
 *
 * @package Organizer\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available variables:
 *
 * @var array $sessions List of session objects.
 */
?>
<div class="organizer-calendar">
	<?php if ( empty( $sessions ) ) : ?>
		<p><?php esc_html_e( 'No upcoming events scheduled.', 'organizer' ); ?></p>
	<?php else : ?>
		<ul class="organizer-session-list">
			<?php foreach ( $sessions as $session ) : ?>
				<?php
				$event_title = get_the_title( $session->event_id );
				$start_date  = date_i18n( get_option( 'date_format' ), strtotime( $session->start_datetime ) );
				$start_time  = date_i18n( get_option( 'time_format' ), strtotime( $session->start_datetime ) );
				?>
				<li class="organizer-session-item">
					<h3 class="organizer-session-title"><?php echo esc_html( $event_title ); ?></h3>
					<div class="organizer-session-meta"><?php echo esc_html( $start_date . ' @ ' . $start_time ); ?></div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
