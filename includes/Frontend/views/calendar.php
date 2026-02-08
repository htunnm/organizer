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
 * @var array $atts Shortcode attributes (limit, offset, etc).
 * @var int $total_items Total number of sessions.
 * @var int $columns Number of columns for the grid.
 */

$options          = get_option( 'organizer_options', array() );
$registration_url = ! empty( $options['organizer_registration_url'] )
	? $options['organizer_registration_url']
	: ( function_exists( 'site_url' ) ? site_url( '/register' ) : get_option( 'siteurl' ) . '/register' );
?>
<div id="organizer-calendar-container" class="organizer-calendar">
	<div class="organizer-loader" style="display:none; text-align:center; padding:20px;">Loading...</div>
	<?php if ( empty( $sessions ) ) : ?>
		<p><?php esc_html_e( 'No upcoming events scheduled.', 'organizer' ); ?></p>
	<?php else : ?>
		<?php
		// Apply filters if set.
		$filtered_sessions = $sessions;
		if ( ! empty( $filters ) ) {
			$filtered_sessions = array_filter(
				$sessions,
				function ( $session ) use ( $filters ) {
					$event_id = $session['event_id'];

					// Filter by city.
					if ( ! empty( $filters['city'] ) ) {
						$event_city = get_post_meta( $event_id, '_event_city', true );
						if ( $event_city !== $filters['city'] ) {
							return false;
						}
					}

					// Filter by duration days.
					if ( ! empty( $filters['duration_days'] ) ) {
						$event_duration = get_post_meta( $event_id, '_event_duration_days', true );
						if ( intval( $event_duration ) !== intval( $filters['duration_days'] ) ) {
							return false;
						}
					}

					return true;
				}
			);
		}
		?>
		<div class="organizer-calendar-grid" style="--org-columns: <?php echo esc_attr( $columns ); ?>;">
			<?php foreach ( $filtered_sessions as $session ) : ?>
				<?php
				$event_id    = $session['event_id'];
				$event_title = get_the_title( $event_id );
				$start_date  = date_i18n( get_option( 'date_format' ), strtotime( $session['start_datetime'] ) );
				$start_time  = date_i18n( get_option( 'time_format' ), strtotime( $session['start_datetime'] ) );

				// Check for manually set icon preference first.
				$icon_class = get_post_meta( $event_id, '_event_icon_type', true );

				if ( empty( $icon_class ) ) {
					// Auto-detect icon based on venue and event type.
					$venue      = get_post_meta( $event_id, '_organizer_event_venue', true );
					$event_type = get_post_meta( $event_id, '_event_type', true );

					// Define online venue keywords.
					$online_keywords = array( 'zoom', 'online', 'teams', 'google meet', 'skype', 'virtual', 'webinar', 'web conference' );
					$venue_lower     = strtolower( $venue );
					$is_online       = empty( $venue ) || preg_match( '/' . implode( '|', $online_keywords ) . '/i', $venue );

					// Determine icon based on venue and type.
					if ( $is_online ) {
						$icon_class = 'dashicons-video-alt'; // Video icon.
					} elseif ( 'workshop' === $event_type || 'seminar' === $event_type ) {
						$icon_class = 'dashicons-groups'; // Group/workshop icon.
					} else {
						$icon_class = 'dashicons-location'; // Location icon (default for physical events).
					}
				}

				$register_link = add_query_arg( 'event_id', $event_id, $registration_url );
				?>
				<div class="organizer-event-card">
					<div class="organizer-card-icon">
						<span class="dashicons <?php echo esc_attr( $icon_class ); ?>"></span>
					</div>
					<h3 class="organizer-card-title"><?php echo esc_html( $event_title ); ?></h3>
					<div class="organizer-card-meta">
						<?php echo esc_html( $start_date . ' @ ' . $start_time ); ?>
					</div>
					<div class="organizer-card-action">
						<a href="<?php echo esc_url( $register_link ); ?>" class="organizer-btn"><?php esc_html_e( 'Register', 'organizer' ); ?></a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
		// Pagination - use filtered count if filters applied.
		$limit            = (int) $atts['limit'];
		$offset           = (int) $atts['offset'];
		$filtered_count   = count( $filtered_sessions );
		$pagination_count = ! empty( $filters ) ? $filtered_count : $total_items;
		?>
		<div class="organizer-calendar-pagination" style="margin-top: 20px; display: flex; justify-content: space-between;">
			<?php if ( $offset > 0 ) : ?>
				<a href="#" class="button organizer-prev-page" data-offset="<?php echo esc_attr( max( 0, $offset - $limit ) ); ?>">&laquo; <?php esc_html_e( 'Previous', 'organizer' ); ?></a>
			<?php endif; ?>
			
			<?php if ( ( $offset + $limit ) < $pagination_count ) : ?>
				<a href="#" class="button organizer-next-page" data-offset="<?php echo esc_attr( $offset + $limit ); ?>"><?php esc_html_e( 'Next', 'organizer' ); ?> &raquo;</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
