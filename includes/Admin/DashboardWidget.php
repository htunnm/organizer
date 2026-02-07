<?php
/**
 * Dashboard Widget.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Model\Registration;
use Organizer\Services\AnalyticsService;

/**
 * Class DashboardWidget
 */
class DashboardWidget {

	/**
	 * Initialize the widget.
	 */
	public static function init() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'add_widget' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue assets.
	 */
	public static function enqueue_assets() {
		wp_register_style( 'organizer-admin-dashboard', ORGANIZER_URL . 'assets/css/admin-dashboard.css', array(), ORGANIZER_VERSION );
	}

	/**
	 * Add the dashboard widget.
	 */
	public static function add_widget() {
		wp_add_dashboard_widget(
			'organizer_dashboard_widget',
			__( 'Upcoming Events', 'organizer' ),
			array( __CLASS__, 'render_widget' )
		);
	}

	/**
	 * Render the widget.
	 */
	public static function render_widget() {
		wp_enqueue_style( 'organizer-admin-dashboard' );

		$analytics = new AnalyticsService();
		$stats     = $analytics->get_registration_stats();
		$daily     = $analytics->get_daily_registrations();

		// Stats Grid.
		echo '<div class="organizer-stats-grid">';
		echo '<div class="organizer-stat-card"><span class="organizer-stat-value">' . esc_html( $stats['total'] ) . '</span><span class="organizer-stat-label">' . esc_html__( 'Total', 'organizer' ) . '</span></div>';
		echo '<div class="organizer-stat-card"><span class="organizer-stat-value">' . esc_html( $stats['pending'] ) . '</span><span class="organizer-stat-label">' . esc_html__( 'Pending', 'organizer' ) . '</span></div>';
		echo '<div class="organizer-stat-card"><span class="organizer-stat-value">' . esc_html( $stats['waitlist'] ) . '</span><span class="organizer-stat-label">' . esc_html__( 'Waitlist', 'organizer' ) . '</span></div>';
		echo '</div>';

		// Simple CSS Bar Chart.
		if ( ! empty( $daily ) ) {
			echo '<h4>' . esc_html__( 'Last 7 Days', 'organizer' ) . '</h4>';
			echo '<div class="organizer-chart-container">';
			$max = max( $daily ) > 0 ? max( $daily ) : 1;
			// Fill in last 7 days if missing.
			for ( $i = 6; $i >= 0; $i-- ) {
				$date  = gmdate( 'Y-m-d', strtotime( "-$i days" ) );
				$count = isset( $daily[ $date ] ) ? $daily[ $date ] : 0;
				$h     = ( $count / $max ) * 100;
				$title = gmdate( 'M j', strtotime( $date ) ) . ': ' . $count;
				echo '<div class="organizer-chart-bar" style="height: ' . esc_attr( $h ) . '%;" title="' . esc_attr( $title ) . '"></div>';
			}
			echo '</div>';
		}

		self::render_upcoming_events();
	}

	/**
	 * Render upcoming events list.
	 */
	private static function render_upcoming_events() {
		$args = array(
			'post_type'      => 'organizer_event',
			'posts_per_page' => 5,
			'post_status'    => 'publish',
		);

		$events = new \WP_Query( $args );

		if ( $events->have_posts() ) {
			echo '<table class="widefat striped">';
			echo '<thead><tr><th>' . esc_html__( 'Event', 'organizer' ) . '</th><th>' . esc_html__( 'Registrations', 'organizer' ) . '</th></tr></thead>';
			echo '<tbody>';
			while ( $events->have_posts() ) {
				$events->the_post();
				$count = Registration::count_by_event( get_the_ID() );
				echo '<tr>';
				echo '<td><a href="' . esc_url( get_edit_post_link() ) . '">' . esc_html( get_the_title() ) . '</a></td>';
				echo '<td>' . esc_html( $count ) . '</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>';
			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__( 'No upcoming events found.', 'organizer' ) . '</p>';
		}
	}
}
