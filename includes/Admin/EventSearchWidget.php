<?php
/**
 * Event Search Widget.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use WP_Widget;

/**
 * Class EventSearchWidget
 */
class EventSearchWidget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'organizer_event_search',
			__( 'Organizer Event Search', 'organizer' ),
			array( 'description' => __( 'A search form for events with date filters.', 'organizer' ) )
		);
	}

	/**
	 * Render widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$target_url = ! empty( $instance['target_url'] ) ? $instance['target_url'] : home_url();
		?>
		<form role="search" method="get" class="organizer-search-widget-form" action="<?php echo esc_url( $target_url ); ?>">
			<p>
				<label for="organizer_search_widget"><?php esc_html_e( 'Keyword:', 'organizer' ); ?></label>
				<input type="text" value="" name="organizer_search" id="organizer_search_widget" class="widefat">
			</p>
			<p>
				<label for="organizer_start_date_widget"><?php esc_html_e( 'Start Date:', 'organizer' ); ?></label>
				<input type="date" value="" name="organizer_start_date" id="organizer_start_date_widget" class="widefat">
			</p>
			<p>
				<label for="organizer_end_date_widget"><?php esc_html_e( 'End Date:', 'organizer' ); ?></label>
				<input type="date" value="" name="organizer_end_date" id="organizer_end_date_widget" class="widefat">
			</p>
			<p>
				<button type="submit" class="button"><?php esc_html_e( 'Search Events', 'organizer' ); ?></button>
			</p>
		</form>
		<?php

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}

	/**
	 * Widget form.
	 *
	 * @param array $instance Widget instance.
	 */
	public function form( $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Search Events', 'organizer' );
		$target_url = ! empty( $instance['target_url'] ) ? $instance['target_url'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'organizer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'target_url' ) ); ?>"><?php esc_html_e( 'Target URL (Calendar Page):', 'organizer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'target_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'target_url' ) ); ?>" type="url" value="<?php echo esc_attr( $target_url ); ?>">
			<br><small><?php esc_html_e( 'URL where the [organizer_calendar] shortcode is located.', 'organizer' ); ?></small>
		</p>
		<?php
	}
}
