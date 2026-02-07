<?php
/**
 * Plugin Name: Organizer
 * Description: Event management: registrations, reminders, RSVPs, and waitlists.
 * Version: 1.0.0
 * Author: Organizer Team
 * Text Domain: organizer
 * Requires PHP: 8.0
 *
 * @package Organizer
 */

namespace Organizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ORGANIZER_VERSION', '1.0.0' );
define( 'ORGANIZER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ORGANIZER_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( ORGANIZER_PATH . 'vendor/autoload.php' ) ) {
	require ORGANIZER_PATH . 'vendor/autoload.php';
}

/**
 * Main plugin class.
 */
class Plugin {

	/**
	 * Initialize the plugin.
	 */
	public static function init() {
		// Initialize hooks here.
	}

	/**
	 * Activation hook.
	 */
	public static function activate() {
		// Flush rewrite rules, create tables, etc.
	}

	/**
	 * Deactivation hook.
	 */
	public static function deactivate() {
		// Cleanup tasks.
	}
}

// Hook into WordPress.
add_action( 'plugins_loaded', array( Plugin::class, 'init' ) );
register_activation_hook( __FILE__, array( Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Plugin::class, 'deactivate' ) );
