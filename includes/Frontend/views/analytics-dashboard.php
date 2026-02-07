<?php
/**
 * Analytics Dashboard View.
 *
 * @package Organizer\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available variables:
 *
 * @var array $stats Registration stats.
 * @var array $daily Daily registration counts.
 * @var array $wl_stats Waitlist metrics.
 * @var array $wl_growth Waitlist growth data.
 * @var array $upcoming_events List of upcoming events.
 */
?>
<div class="organizer-analytics-dashboard">
	<h2><?php esc_html_e( 'Event Analytics', 'organizer' ); ?></h2>

	<div class="organizer-stats-grid">
		<div class="organizer-stat-card"><span class="organizer-stat-value"><?php echo esc_html( $stats['total'] ); ?></span><span class="organizer-stat-label"><?php esc_html_e( 'Total', 'organizer' ); ?></span></div>
		<div class="organizer-stat-card"><span class="organizer-stat-value"><?php echo esc_html( $stats['pending'] ); ?></span><span class="organizer-stat-label"><?php esc_html_e( 'Pending', 'organizer' ); ?></span></div>
		<div class="organizer-stat-card"><span class="organizer-stat-value"><?php echo esc_html( $stats['waitlist'] ); ?></span><span class="organizer-stat-label"><?php esc_html_e( 'Waitlist', 'organizer' ); ?></span></div>
		<div class="organizer-stat-card"><span class="organizer-stat-value"><?php echo esc_html( $wl_stats['promoted'] ); ?></span><span class="organizer-stat-label"><?php esc_html_e( 'Promoted', 'organizer' ); ?></span></div>
	</div>

	<?php if ( ! empty( $daily ) ) : ?>
		<h4><?php esc_html_e( 'Registrations (Last 7 Days)', 'organizer' ); ?></h4>
		<div class="organizer-chart-container">
			<?php
			$max = max( $daily ) > 0 ? max( $daily ) : 1;
			for ( $i = 6; $i >= 0; $i-- ) {
				$date      = gmdate( 'Y-m-d', strtotime( "-$i days" ) );
				$count     = isset( $daily[ $date ] ) ? $daily[ $date ] : 0;
				$h         = ( $count / $max ) * 100;
				$bar_title = gmdate( 'M j', strtotime( $date ) ) . ': ' . $count;
				echo '<div class="organizer-chart-bar" style="height: ' . esc_attr( $h ) . '%;" title="' . esc_attr( $bar_title ) . '"></div>';
			}
			?>
		</div>
	<?php endif; ?>

	<h3><?php esc_html_e( 'My Upcoming Events', 'organizer' ); ?></h3>
	<?php if ( ! empty( $upcoming_events ) ) : ?>
		<table class="organizer-dashboard-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Event', 'organizer' ); ?></th>
					<th><?php esc_html_e( 'Registrations', 'organizer' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $upcoming_events as $event ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( $event['link'] ); ?>"><?php echo esc_html( $event['title'] ); ?></a></td>
						<td><?php echo esc_html( $event['count'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<p><?php esc_html_e( 'No upcoming events found.', 'organizer' ); ?></p>
	<?php endif; ?>
</div>
