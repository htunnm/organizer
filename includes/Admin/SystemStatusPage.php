<?php
/**
 * System Status Page.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Model\Registration;
use Organizer\Model\Session;
use Organizer\Model\Waitlist;
use Organizer\Model\Log;
use Organizer\Model\RegistrationMeta;
use Organizer\Model\DiscountCode;
use Organizer\Model\Feedback;
use Organizer\Model\RSVP;

/**
 * Class SystemStatusPage
 */
class SystemStatusPage {

	/**
	 * Initialize the page.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
	}

	/**
	 * Add the admin menu.
	 */
	public static function add_menu() {
		add_submenu_page(
			'edit.php?post_type=organizer_event',
			__( 'System Status', 'organizer' ),
			__( 'System Status', 'organizer' ),
			'manage_options',
			'organizer-system-status',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the page.
	 */
	public static function render_page() {
		global $wpdb;
		$tables = array(
			Registration::get_table_name(),
			Session::get_table_name(),
			Waitlist::get_table_name(),
			Log::get_table_name(),
			RegistrationMeta::get_table_name(),
			DiscountCode::get_table_name(),
			Feedback::get_table_name(),
			RSVP::get_table_name(), // Assuming RSVP model has get_table_name.
		);

		$classes = array(
			'Organizer\Admin\Settings',
			'Organizer\Admin\MetaBox', // Handles Event Metaboxes and Columns.
			'Organizer\Frontend\Shortcodes',
			'Organizer\Frontend\FormHandler',
			'Organizer\Frontend\AjaxHandler',
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'System Status', 'organizer' ); ?></h1>
			
			<h2><?php esc_html_e( 'Active Classes', 'organizer' ); ?></h2>
			<ul>
				<?php foreach ( $classes as $class ) : ?>
					<li><?php echo esc_html( $class ); ?>: <?php echo class_exists( $class ) ? '<span style="color:green">Loaded</span>' : '<span style="color:red">Not Loaded</span>'; ?></li>
				<?php endforeach; ?>
			</ul>

			<h2><?php esc_html_e( 'Database Tables', 'organizer' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th>Table Name</th><th>Status</th></tr></thead>
				<tbody>
					<?php foreach ( $tables as $table ) : ?>
						<?php
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
						?>
						<tr>
							<td><?php echo esc_html( $table ); ?></td>
							<td><?php echo $exists ? '<span style="color:green">Exists</span>' : '<span style="color:red">Missing</span>'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
