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

use Organizer\Admin\CheckinHandler;
use Organizer\Admin\DashboardWidget;
use Organizer\Blocks\CalendarBlock;
use Organizer\Admin\EventDuplicator;
use Organizer\Admin\DiscountCodesPage;
use Organizer\Admin\ExportHandler;
use Organizer\Admin\MetaBox;
use Organizer\Admin\RegistrationsPage;
use Organizer\Admin\SessionsPage;
use Organizer\Admin\Settings;
use Organizer\Cli\RemindersCommand;
use Organizer\Cli\ExpirationCommand;
use Organizer\Frontend\AjaxHandler;
use Organizer\Frontend\FormHandler;
use Organizer\Frontend\Shortcodes;
use Organizer\Model\Event;
use Organizer\Services\CronService;
use Organizer\Model\Log;
use Organizer\Model\Registration;
use Organizer\Model\RegistrationMeta;
use Organizer\Model\RSVP;
use Organizer\Model\Session;
use Organizer\Model\Waitlist;
use Organizer\Model\DiscountCode;
use Organizer\Rest\RegistrationController;
use Organizer\Rest\RSVPController;
use Organizer\Rest\SessionController;
use Organizer\Rest\CheckinController;
use Organizer\Rest\WaitlistController;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ORGANIZER_VERSION', '1.0.0' );
define( 'ORGANIZER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ORGANIZER_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( ORGANIZER_PATH . 'vendor/autoload.php' ) ) {
	require ORGANIZER_PATH . 'vendor/autoload.php';
}

if ( file_exists( ORGANIZER_PATH . 'includes/Blocks/CalendarBlock.php' ) ) {
	require_once ORGANIZER_PATH . 'includes/Blocks/CalendarBlock.php';
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
		add_action( 'init', array( Event::class, 'register' ) );
		add_action(
			'rest_api_init',
			function () {
				$controller = new RegistrationController();
				$controller->register_routes();
				$rsvp_controller = new RSVPController();
				$rsvp_controller->register_routes();
				$waitlist_controller = new WaitlistController();
				$waitlist_controller->register_routes();
				$session_controller = new SessionController();
				$session_controller->register_routes();
				$checkin_controller = new CheckinController();
				$checkin_controller->register_routes();
			}
		);
		Settings::init();
		RegistrationsPage::init();
		SessionsPage::init();
		DashboardWidget::init();
		MetaBox::init();
		Shortcodes::init();
		ExportHandler::init();
		FormHandler::init();
		CheckinHandler::init();
		DiscountCodesPage::init();
		AjaxHandler::init();
		EventDuplicator::init();
		CalendarBlock::init();

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'organizer', RemindersCommand::class );
			\WP_CLI::add_command( 'organizer process-expirations', array( ExpirationCommand::class, 'process_expirations' ) );
		}

		add_action( 'organizer_daily_reminders', array( CronService::class, 'handle_daily_reminders' ) );
	}

	/**
	 * Activation hook.
	 */
	public static function activate() {
		// Flush rewrite rules, create tables, etc.
		Event::register();
		Registration::create_table();
		RSVP::create_table();
		Waitlist::create_table();
		Session::create_table();
		Log::create_table();
		RegistrationMeta::create_table();
		DiscountCode::create_table();
		flush_rewrite_rules();

		if ( ! wp_next_scheduled( 'organizer_daily_reminders' ) ) {
			wp_schedule_event( time(), 'hourly', 'organizer_daily_reminders' );
		}
	}

	/**
	 * Deactivation hook.
	 */
	public static function deactivate() {
		// Cleanup tasks.
		wp_clear_scheduled_hook( 'organizer_daily_reminders' );
	}
}

// Hook into WordPress.
add_action( 'plugins_loaded', array( Plugin::class, 'init' ) );
register_activation_hook( __FILE__, array( Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Plugin::class, 'deactivate' ) );
