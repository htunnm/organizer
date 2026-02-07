<?php
/**
 * Settings Class.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

/**
 * Class Settings
 */
class Settings {

	/**
	 * Initialize the settings.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Add the admin menu.
	 */
	public static function add_admin_menu() {
		add_options_page(
			__( 'Organizer Settings', 'organizer' ),
			__( 'Organizer', 'organizer' ),
			'manage_options',
			'organizer-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public static function register_settings() {
		register_setting( 'organizer_options', 'organizer_options' );

		add_settings_section(
			'organizer_general_section',
			__( 'General Settings', 'organizer' ),
			null,
			'organizer-settings'
		);

		add_settings_field(
			'organizer_events_per_page',
			__( 'Events Per Page', 'organizer' ),
			array( __CLASS__, 'render_events_per_page_field' ),
			'organizer-settings',
			'organizer_general_section'
		);
	}

	/**
	 * Render the events per page field.
	 */
	public static function render_events_per_page_field() {
		$options = get_option( 'organizer_options' );
		$value   = isset( $options['events_per_page'] ) ? $options['events_per_page'] : 10;
		?>
		<input type="number" name="organizer_options[events_per_page]" value="<?php echo esc_attr( $value ); ?>" class="small-text">
		<p class="description"><?php esc_html_e( 'Number of events to show per page in the admin list.', 'organizer' ); ?></p>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Ensure the view file exists before requiring it.
		$view_file = ORGANIZER_PATH . 'admin/views/settings.php';
		if ( file_exists( $view_file ) ) {
			require_once $view_file;
		} else {
			echo '<div class="error"><p>' . esc_html__( 'Settings view file not found.', 'organizer' ) . '</p></div>';
		}
	}
}
