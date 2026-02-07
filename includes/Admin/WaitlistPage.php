<?php
/**
 * Waitlist Page.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Model\Waitlist;
use Organizer\Services\WaitlistService;
use Organizer\Services\Email\GmailAdapter;

/**
 * Class WaitlistPage
 */
class WaitlistPage {

	/**
	 * Initialize the page.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_post_organizer_promote_waitlist', array( __CLASS__, 'handle_promote' ) );
		add_action( 'admin_post_organizer_remove_waitlist', array( __CLASS__, 'handle_remove' ) );
	}

	/**
	 * Add the admin menu.
	 */
	public static function add_menu() {
		add_submenu_page(
			'edit.php?post_type=organizer_event',
			__( 'Waitlist', 'organizer' ),
			__( 'Waitlist', 'organizer' ),
			'manage_options',
			'organizer-waitlist',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the page.
	 */
	public static function render_page() {
		$table = new WaitlistListTable();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Waitlist Management', 'organizer' ); ?></h1>
			
			<!-- Filter Form -->
			<form method="get" style="margin-top: 10px;">
				<input type="hidden" name="post_type" value="organizer_event">
				<input type="hidden" name="page" value="organizer-waitlist">
				<label for="filter-event-id"><?php esc_html_e( 'Filter by Event ID:', 'organizer' ); ?></label>
				<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<input type="number" name="event_id" id="filter-event-id" value="<?php echo isset( $_GET['event_id'] ) ? esc_attr( absint( $_GET['event_id'] ) ) : ''; ?>">
				<button type="submit" class="button"><?php esc_html_e( 'Filter', 'organizer' ); ?></button>
				<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<?php if ( ! empty( $_GET['event_id'] ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=organizer_event&page=organizer-waitlist' ) ); ?>" class="button"><?php esc_html_e( 'Clear', 'organizer' ); ?></a>
				<?php endif; ?>
			</form>

			<form method="post">
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle promote action.
	 */
	public static function handle_promote() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'organizer' ) );
		}

		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'organizer_promote_waitlist_' . $id );

		$email_service = new GmailAdapter();
		$service       = new WaitlistService( $email_service );

		if ( $service->promote_user( $id ) ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=organizer_event&page=organizer-waitlist&promoted=1' ) );
		} else {
			wp_safe_redirect( admin_url( 'edit.php?post_type=organizer_event&page=organizer-waitlist&error=1' ) );
		}
		exit;
	}

	/**
	 * Handle remove action.
	 */
	public static function handle_remove() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'organizer' ) );
		}

		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'organizer_remove_waitlist_' . $id );

		if ( Waitlist::remove( $id ) ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=organizer_event&page=organizer-waitlist&removed=1' ) );
		} else {
			wp_safe_redirect( admin_url( 'edit.php?post_type=organizer_event&page=organizer-waitlist&error=1' ) );
		}
		exit;
	}
}
