<?php
/**
 * Shortcodes Handler.
 *
 * @package Organizer\Frontend
 */

namespace Organizer\Frontend;

use Organizer\Model\Session;

/**
 * Class Shortcodes
 */
class Shortcodes {

	/**
	 * Initialize shortcodes.
	 */
	public static function init() {
		add_shortcode( 'organizer_calendar', array( __CLASS__, 'render_calendar' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public static function enqueue_assets() {
		wp_register_style( 'organizer-calendar', ORGANIZER_URL . 'assets/css/calendar.css', array(), ORGANIZER_VERSION );
	}

	/**
	 * Render the calendar shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render_calendar( $atts ) {
		wp_enqueue_style( 'organizer-calendar' );

		$atts = shortcode_atts(
			array(
				'limit'    => 10,
				'category' => '',
			),
			$atts,
			'organizer_calendar'
		);

		// Fetch upcoming sessions.
		// In a real calendar, we'd fetch by date range. For now, we list upcoming sessions.
		$sessions = Session::get_all( (int) $atts['limit'], 0, 'start_datetime', 'ASC', sanitize_text_field( $atts['category'] ) );

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/calendar.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Calendar view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}
}
