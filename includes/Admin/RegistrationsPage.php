<?php
/**
 * Registrations Page.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

/**
 * Class RegistrationsPage
 */
class RegistrationsPage {

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
			__( 'Registrations', 'organizer' ),
			__( 'Registrations', 'organizer' ),
			'manage_options',
			'organizer-registrations',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the page.
	 */
	public static function render_page() {
		$table = new RegistrationsListTable();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Registrations', 'organizer' ); ?></h1>
			<form method="post">
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}
}
