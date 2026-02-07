<?php
/**
 * Shortcodes Handler.
 *
 * @package Organizer\Frontend
 */

namespace Organizer\Frontend;

use Organizer\Model\Session;
use Organizer\Model\Registration;

/**
 * Class Shortcodes
 */
class Shortcodes {

	/**
	 * Initialize shortcodes.
	 */
	public static function init() {
		add_shortcode( 'organizer_calendar', array( __CLASS__, 'render_calendar' ) );
		add_shortcode( 'organizer_registration_form', array( __CLASS__, 'render_registration_form' ) );
		add_shortcode( 'organizer_user_dashboard', array( __CLASS__, 'render_user_dashboard' ) );
		add_shortcode( 'organizer_user_profile', array( __CLASS__, 'render_user_profile' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public static function enqueue_assets() {
		wp_register_style( 'organizer-calendar', ORGANIZER_URL . 'assets/css/calendar.css', array(), ORGANIZER_VERSION );
		wp_register_style( 'organizer-dashboard', ORGANIZER_URL . 'assets/css/dashboard.css', array(), ORGANIZER_VERSION );
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

	/**
	 * Render the registration form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render_registration_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'event_id'   => 0,
				'session_id' => 0,
			),
			$atts,
			'organizer_registration_form'
		);

		$event_id   = (int) $atts['event_id'];
		$session_id = (int) $atts['session_id'];

		if ( empty( $event_id ) ) {
			return '<p>' . esc_html__( 'Event ID is required.', 'organizer' ) . '</p>';
		}

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/registration-form.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Registration form view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}

	/**
	 * Render the user dashboard shortcode.
	 *
	 * @return string HTML output.
	 */
	public static function render_user_dashboard() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your dashboard.', 'organizer' ) . '</p>';
		}

		wp_enqueue_style( 'organizer-dashboard' );

		$current_user  = wp_get_current_user();
		$registrations = Registration::get_by_user_email( $current_user->user_email );

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/user-dashboard.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Dashboard view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}

	/**
	 * Render the user profile shortcode.
	 *
	 * @return string HTML output.
	 */
	public static function render_user_profile() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to edit your profile.', 'organizer' ) . '</p>';
		}

		$current_user = wp_get_current_user();

		ob_start();
		$view_file = ORGANIZER_PATH . 'includes/Frontend/views/user-profile.php';
		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			echo '<p>' . esc_html__( 'Profile view not found.', 'organizer' ) . '</p>';
		}
		return ob_get_clean();
	}
}
