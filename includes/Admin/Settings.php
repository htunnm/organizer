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
		register_setting( 'organizer_options', 'organizer_email_templates' );

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

		add_settings_field(
			'organizer_waitlist_expiration',
			__( 'Waitlist Expiration (Hours)', 'organizer' ),
			array( __CLASS__, 'render_number_field' ),
			'organizer-settings',
			'organizer_general_section',
			array(
				'id'      => 'organizer_waitlist_expiration',
				'default' => 24,
			)
		);

		add_settings_section(
			'organizer_payment_section',
			__( 'Payment Settings', 'organizer' ),
			null,
			'organizer-settings'
		);

		add_settings_field(
			'organizer_stripe_publishable_key',
			__( 'Stripe Publishable Key', 'organizer' ),
			array( __CLASS__, 'render_text_field' ),
			'organizer-settings',
			'organizer_payment_section',
			array( 'id' => 'organizer_stripe_publishable_key' )
		);

		add_settings_field(
			'organizer_stripe_secret_key',
			__( 'Stripe Secret Key', 'organizer' ),
			array( __CLASS__, 'render_text_field' ),
			'organizer-settings',
			'organizer_payment_section',
			array( 'id' => 'organizer_stripe_secret_key' )
		);

		add_settings_field(
			'organizer_currency',
			__( 'Currency', 'organizer' ),
			array( __CLASS__, 'render_text_field' ),
			'organizer-settings',
			'organizer_payment_section',
			array(
				'id'      => 'organizer_currency',
				'default' => 'USD',
			)
		);

		add_settings_section(
			'organizer_email_section',
			__( 'Email Templates', 'organizer' ),
			array( __CLASS__, 'render_email_section_description' ),
			'organizer-settings'
		);

		$templates = array(
			'registration_confirmation' => __( 'Registration Confirmation', 'organizer' ),
			'waitlist_confirmation'     => __( 'Waitlist Confirmation', 'organizer' ),
			'waitlist_promotion'        => __( 'Waitlist Promotion', 'organizer' ),
			'event_reminder'            => __( 'Event Reminder', 'organizer' ),
			'session_cancelled'         => __( 'Session Cancelled', 'organizer' ),
		);

		foreach ( $templates as $key => $label ) {
			add_settings_field(
				'organizer_email_' . $key,
				$label,
				function () use ( $key ) {
					self::render_email_template_fields( $key );
				},
				'organizer-settings',
				'organizer_email_section'
			);
		}
	}

	/**
	 * Render email section description.
	 */
	public static function render_email_section_description() {
		echo '<p>' . esc_html__( 'Customize the email templates sent to attendees.', 'organizer' ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Available Placeholders:', 'organizer' ) . '</strong> {attendee_name}, {event_title}, {start_date}</p>';
	}

	/**
	 * Render email template fields.
	 *
	 * @param string $key Template key.
	 */
	public static function render_email_template_fields( $key ) {
		$options = get_option( 'organizer_email_templates', array() );
		$subject = isset( $options[ $key . '_subject' ] ) ? $options[ $key . '_subject' ] : '';
		$message = isset( $options[ $key . '_message' ] ) ? $options[ $key . '_message' ] : '';
		?>
		<p>
			<label for="organizer_email_<?php echo esc_attr( $key ); ?>_subject"><?php esc_html_e( 'Subject:', 'organizer' ); ?></label><br>
			<input type="text" name="organizer_email_templates[<?php echo esc_attr( $key ); ?>_subject]" id="organizer_email_<?php echo esc_attr( $key ); ?>_subject" value="<?php echo esc_attr( $subject ); ?>" class="regular-text">
		</p>
		<p>
			<label for="organizer_email_<?php echo esc_attr( $key ); ?>_message"><?php esc_html_e( 'Message:', 'organizer' ); ?></label><br>
			<textarea name="organizer_email_templates[<?php echo esc_attr( $key ); ?>_message]" id="organizer_email_<?php echo esc_attr( $key ); ?>_message" rows="5" cols="50" class="large-text"><?php echo esc_textarea( $message ); ?></textarea>
		</p>
		<?php
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
	 * Render a generic text field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_text_field( $args ) {
		$options = get_option( 'organizer_options' );
		$id      = $args['id'];
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$value   = isset( $options[ $id ] ) ? $options[ $id ] : $default;
		echo '<input type="text" name="organizer_options[' . esc_attr( $id ) . ']" value="' . esc_attr( $value ) . '" class="regular-text">';
	}

	/**
	 * Render a generic number field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_number_field( $args ) {
		$options = get_option( 'organizer_options' );
		$id      = $args['id'];
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$value   = isset( $options[ $id ] ) ? $options[ $id ] : $default;
		echo '<input type="number" name="organizer_options[' . esc_attr( $id ) . ']" value="' . esc_attr( $value ) . '" class="small-text">';
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
