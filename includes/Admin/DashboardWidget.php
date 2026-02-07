<?php
/**
 * Dashboard Widget.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Model\Registration;

/**
 * Class DashboardWidget
 */
class DashboardWidget {

	/**
	 * Initialize the widget.
	 */
	public static function init() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'add_widget' ) );
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
