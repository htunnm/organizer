<?php
/**
 * User Profile View.
 *
 * @package Organizer\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available variables:
 *
 * @var WP_User $current_user Current user object.
 */

$avatar_id = get_user_meta( $current_user->ID, 'organizer_avatar', true );
?>
<div class="organizer-user-profile">
	<h2><?php esc_html_e( 'Edit Profile', 'organizer' ); ?></h2>
	<?php
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['organizer_profile_update'] ) ) {
		if ( 'success' === $_GET['organizer_profile_update'] ) {
			echo '<div class="organizer-message success">' . esc_html__( 'Profile updated successfully.', 'organizer' ) . '</div>';
		} else {
			echo '<div class="organizer-message error">' . esc_html__( 'Profile update failed.', 'organizer' ) . '</div>';
		}
	}
	// phpcs:enable
	?>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="action" value="organizer_update_profile">
		<?php wp_nonce_field( 'organizer_profile_nonce', 'organizer_nonce' ); ?>
		<p>
			<label for="organizer_first_name"><?php esc_html_e( 'First Name', 'organizer' ); ?></label>
			<input type="text" name="first_name" id="organizer_first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>">
		</p>
		<p>
			<label for="organizer_last_name"><?php esc_html_e( 'Last Name', 'organizer' ); ?></label>
			<input type="text" name="last_name" id="organizer_last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>">
		</p>
		<p>
			<label for="organizer_email"><?php esc_html_e( 'Email', 'organizer' ); ?></label>
			<input type="email" name="email" id="organizer_email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
		</p>

		<h3><?php esc_html_e( 'Change Password (Optional)', 'organizer' ); ?></h3>
		<p>
			<label for="organizer_password"><?php esc_html_e( 'New Password', 'organizer' ); ?></label>
			<input type="password" name="password" id="organizer_password">
		</p>
		<p>
			<label for="organizer_password_confirm"><?php esc_html_e( 'Confirm New Password', 'organizer' ); ?></label>
			<input type="password" name="password_confirm" id="organizer_password_confirm">
		</p>

		<h3><?php esc_html_e( 'Profile Picture', 'organizer' ); ?></h3>
		<?php if ( $avatar_id ) : ?>
			<div class="organizer-avatar-preview">
				<?php echo wp_get_attachment_image( $avatar_id, 'thumbnail' ); ?>
			</div>
		<?php endif; ?>
		<p><input type="file" name="organizer_avatar" id="organizer_avatar" accept="image/*"></p>

		<p><button type="submit" class="button"><?php esc_html_e( 'Update Profile', 'organizer' ); ?></button></p>
	</form>
</div>
