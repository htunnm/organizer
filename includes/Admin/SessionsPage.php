<?php
/**
 * Sessions Page.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

/**
 * Class SessionsPage
 */
class SessionsPage {

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
			__( 'Sessions', 'organizer' ),
			__( 'Sessions', 'organizer' ),
			'manage_options',
			'organizer-sessions',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the page.
	 */
	public static function render_page() {
		$table = new SessionsListTable();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Sessions', 'organizer' ); ?></h1>
			<form method="post">
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}
}
