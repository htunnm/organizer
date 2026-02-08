<?php
/**
 * Registration Form View.
 *
 * @package Organizer\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available variables:
 *
 * @var int $event_id Event ID.
 * @var int $session_id Session ID.
 */

$price         = (float) get_post_meta( $event_id, '_organizer_event_price', true );
$custom_fields = get_post_meta( $event_id, '_organizer_custom_fields', true );
$options       = get_option( 'organizer_options' );
$site_key      = isset( $options['organizer_recaptcha_site_key'] ) ? $options['organizer_recaptcha_site_key'] : '';
?>
<div class="organizer-registration-form">
	<?php
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['organizer_registration'] ) ) {
		if ( 'success' === $_GET['organizer_registration'] ) {
			echo '<div class="organizer-message success">' . esc_html__( 'Registration successful!', 'organizer' ) . '</div>';
		} elseif ( 'waitlist' === $_GET['organizer_registration'] ) {
			echo '<div class="organizer-message warning">' . esc_html__( 'Event is full. You have been added to the waitlist.', 'organizer' ) . '</div>';
		} else {
			echo '<div class="organizer-message error">' . esc_html__( 'Registration failed. Please try again.', 'organizer' ) . '</div>';
		}
	}
	// phpcs:enable
	?>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<div class="organizer-progress-bar">
			<div class="organizer-progress-step active">1</div>
			<?php if ( ! empty( $custom_fields ) ) : ?>
				<div class="organizer-progress-step">2</div>
				<div class="organizer-progress-step">3</div>
			<?php else : ?>
				<div class="organizer-progress-step">2</div>
			<?php endif; ?>
		</div>

		<input type="hidden" name="action" value="organizer_register">
		<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
		<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id ); ?>">
		<?php wp_nonce_field( 'organizer_register_nonce', 'organizer_nonce' ); ?>

		<!-- Step 1: Attendee Info -->
		<div class="organizer-step active" data-step="1">
			<h3><?php esc_html_e( 'Attendee Information', 'organizer' ); ?></h3>
			<p><label><?php esc_html_e( 'Name', 'organizer' ); ?> <input type="text" name="organizer_name" required></label></p>
			<p><label><?php esc_html_e( 'Email', 'organizer' ); ?> <input type="email" name="organizer_email" required></label></p>
			<div class="organizer-form-actions">
				<button type="button" class="button organizer-next-step"><?php esc_html_e( 'Next', 'organizer' ); ?></button>
			</div>
		</div>

		<?php if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) : ?>
			<!-- Step 2: Custom Fields -->
			<div class="organizer-step" data-step="2">
				<h3><?php esc_html_e( 'Additional Details', 'organizer' ); ?></h3>
				<?php
				foreach ( $custom_fields as $field ) {
					$label      = isset( $field['label'] ) ? $field['label'] : '';
					$field_type = isset( $field['type'] ) ? $field['type'] : 'text';
					$required   = isset( $field['required'] ) && 'yes' === $field['required'];
					$req_attr   = $required ? 'required' : '';
					echo '<p><label>' . esc_html( $label );
					if ( 'text' === $field_type ) {
						echo ' <input type="text" name="organizer_meta[' . esc_attr( $label ) . ']" ' . esc_attr( $req_attr ) . '>';
					} elseif ( 'checkbox' === $field_type ) {
						echo ' <input type="checkbox" name="organizer_meta[' . esc_attr( $label ) . ']" value="yes" ' . esc_attr( $req_attr ) . '>';
					}
					echo '</label></p>';
				}
				?>
				<div class="organizer-form-actions">
					<button type="button" class="button organizer-prev-step"><?php esc_html_e( 'Back', 'organizer' ); ?></button>
					<button type="button" class="button organizer-next-step"><?php esc_html_e( 'Next', 'organizer' ); ?></button>
				</div>
			</div>
		<?php endif; ?>

		<!-- Step 3 (or 2): Review & Payment -->
		<div class="organizer-step" data-step="<?php echo ! empty( $custom_fields ) ? '3' : '2'; ?>">
			<h3><?php esc_html_e( 'Review & Payment', 'organizer' ); ?></h3>
			<?php if ( $price > 0 ) : ?>
				<p><?php esc_html_e( 'Price:', 'organizer' ); ?> <span id="organizer_event_price_display" data-price="<?php echo esc_attr( $price ); ?>"><?php echo esc_html( number_format( $price, 2 ) ); ?></span></p>
				<div class="organizer-discount-section">
					<p>
						<label><?php esc_html_e( 'Discount Code', 'organizer' ); ?> <input type="text" id="organizer_discount_code" name="discount_code"></label>
						<button type="button" id="organizer_apply_discount" class="button"><?php esc_html_e( 'Apply', 'organizer' ); ?></button>
					</p>
					<p id="organizer_discount_message"></p>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'This event is free.', 'organizer' ); ?></p>
			<?php endif; ?>
			<div class="organizer-form-actions">
				<button type="button" class="button organizer-prev-step"><?php esc_html_e( 'Back', 'organizer' ); ?></button>
				<?php if ( ! empty( $site_key ) ) : ?>
					<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
				<?php endif; ?>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Register', 'organizer' ); ?></button>
			</div>
		</div>
	</form>
</div>