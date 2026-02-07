<?php
/**
 * Discount Codes Page.
 *
 * @package Organizer\Admin
 */

namespace Organizer\Admin;

use Organizer\Model\DiscountCode;

/**
 * Class DiscountCodesPage
 */
class DiscountCodesPage {

	/**
	 * Initialize the page.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_post_organizer_add_discount', array( __CLASS__, 'handle_add_discount' ) );
		add_action( 'admin_post_organizer_delete_discount', array( __CLASS__, 'handle_delete_discount' ) );
	}

	/**
	 * Add the admin menu.
	 */
	public static function add_menu() {
		add_submenu_page(
			'edit.php?post_type=organizer_event',
			__( 'Discounts', 'organizer' ),
			__( 'Discounts', 'organizer' ),
			'manage_options',
			'organizer-discounts',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the page.
	 */
	public static function render_page() {
		$codes = DiscountCode::get_all();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Discount Codes', 'organizer' ); ?></h1>
			
			<h2><?php esc_html_e( 'Add New Code', 'organizer' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="organizer_add_discount">
				<?php wp_nonce_field( 'organizer_add_discount_nonce', 'organizer_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="code"><?php esc_html_e( 'Code', 'organizer' ); ?></label></th>
						<td><input type="text" name="code" id="code" required class="regular-text"></td>
					</tr>
					<tr>
						<th><label for="type"><?php esc_html_e( 'Type', 'organizer' ); ?></label></th>
						<td>
							<select name="type" id="type">
								<option value="fixed"><?php esc_html_e( 'Fixed Amount', 'organizer' ); ?></option>
								<option value="percent"><?php esc_html_e( 'Percentage', 'organizer' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="amount"><?php esc_html_e( 'Amount', 'organizer' ); ?></label></th>
						<td><input type="number" step="0.01" name="amount" id="amount" required class="small-text"></td>
					</tr>
					<tr>
						<th><label for="expires_at"><?php esc_html_e( 'Expires At', 'organizer' ); ?></label></th>
						<td><input type="datetime-local" name="expires_at" id="expires_at"></td>
					</tr>
					<tr>
						<th><label for="usage_limit"><?php esc_html_e( 'Usage Limit', 'organizer' ); ?></label></th>
						<td><input type="number" name="usage_limit" id="usage_limit" class="small-text" placeholder="0 for unlimited"></td>
					</tr>
				</table>
				<?php submit_button( __( 'Add Discount Code', 'organizer' ) ); ?>
			</form>

			<h2><?php esc_html_e( 'Existing Codes', 'organizer' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Code', 'organizer' ); ?></th>
						<th><?php esc_html_e( 'Type', 'organizer' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'organizer' ); ?></th>
						<th><?php esc_html_e( 'Expires', 'organizer' ); ?></th>
						<th><?php esc_html_e( 'Usage', 'organizer' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'organizer' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $codes as $code ) : ?>
						<tr>
							<td><?php echo esc_html( $code['code'] ); ?></td>
							<td><?php echo esc_html( ucfirst( $code['type'] ) ); ?></td>
							<td><?php echo esc_html( $code['amount'] ); ?></td>
							<td><?php echo esc_html( $code['expires_at'] ); ?></td>
							<td><?php echo esc_html( $code['usage_count'] . ' / ' . ( $code['usage_limit'] > 0 ? $code['usage_limit'] : '∞' ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=organizer_delete_discount&id=' . $code['id'] ), 'organizer_delete_discount_' . $code['id'] ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'organizer' ); ?>');" style="color: #a00;"><?php esc_html_e( 'Delete', 'organizer' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Handle add discount.
	 */
	public static function handle_add_discount() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'organizer' ) );
		}
		check_admin_referer( 'organizer_add_discount_nonce', 'organizer_nonce' );

		$code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		if ( empty( $code ) ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=organizer_event&page=organizer-discounts&error=missing_code' ) );
			exit;
		}

		$data = array(
			'code'        => $code,
			'type'        => isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'fixed',
			'amount'      => isset( $_POST['amount'] ) ? floatval( $_POST['amount'] ) : 0,
			'expires_at'  => ! empty( $_POST['expires_at'] ) ? sanitize_text_field( wp_unslash( $_POST['expires_at'] ) ) : null,
			'usage_limit' => isset( $_POST['usage_limit'] ) ? intval( $_POST['usage_limit'] ) : 0,
		);

		DiscountCode::create( $data );
		wp_safe_redirect( admin_url( 'edit.php?post_type=organizer_event&page=organizer-discounts&success=1' ) );
		exit;
	}

	/**
	 * Handle delete discount.
	 */
	public static function handle_delete_discount() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'organizer' ) );
		}
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'organizer_delete_discount_' . $id );

		DiscountCode::delete( $id );
		wp_safe_redirect( admin_url( 'edit.php?post_type=organizer_event&page=organizer-discounts&deleted=1' ) );
		exit;
	}
}
