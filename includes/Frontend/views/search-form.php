<?php
/**
 * Search Form View.
 *
 * @package Organizer\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$keyword = isset( $_GET['organizer_search'] ) ? sanitize_text_field( wp_unslash( $_GET['organizer_search'] ) ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$start_date = isset( $_GET['organizer_start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['organizer_start_date'] ) ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$end_date = isset( $_GET['organizer_end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['organizer_end_date'] ) ) : '';
?>
<div class="organizer-search-form">
	<form method="get" action="">
		<?php
		// Preserve existing query parameters.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		foreach ( $_GET as $key => $value ) {
			if ( in_array( $key, array( 'organizer_search', 'organizer_start_date', 'organizer_end_date' ), true ) ) {
				continue;
			}
			// Simple handling for scalar values. Arrays would need recursion.
			if ( is_scalar( $value ) ) {
				echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
			}
		}
		?>
		<div class="organizer-search-row">
			<input type="text" name="organizer_search" placeholder="<?php esc_attr_e( 'Search events...', 'organizer' ); ?>" value="<?php echo esc_attr( $keyword ); ?>">
			<input type="date" name="organizer_start_date" placeholder="<?php esc_attr_e( 'Start Date', 'organizer' ); ?>" value="<?php echo esc_attr( $start_date ); ?>">
			<input type="date" name="organizer_end_date" placeholder="<?php esc_attr_e( 'End Date', 'organizer' ); ?>" value="<?php echo esc_attr( $end_date ); ?>">
			<button type="submit"><?php esc_html_e( 'Search', 'organizer' ); ?></button>
		</div>
	</form>
</div>
