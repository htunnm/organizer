<?php
/**
 * Ajax Handler.
 *
 * @package Organizer\Frontend
 */

namespace Organizer\Frontend;

use Organizer\Model\DiscountCode;

/**
 * Class AjaxHandler
 */
class AjaxHandler {

	/**
	 * Initialize.
	 */
	public static function init() {
		add_action( 'wp_ajax_organizer_validate_discount', array( __CLASS__, 'validate_discount' ) );
		add_action( 'wp_ajax_nopriv_organizer_validate_discount', array( __CLASS__, 'validate_discount' ) );
	}

	/**
	 * Validate discount code.
	 */
	public static function validate_discount() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$price = isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : 0;

		if ( empty( $code ) ) {
			wp_send_json_error( array( 'message' => __( 'Code is required.', 'organizer' ) ) );
		}

		$discount = DiscountCode::get_by_code( $code );

		if ( ! $discount ) {
			wp_send_json_error( array( 'message' => __( 'Invalid code.', 'organizer' ) ) );
		}

		// Check expiration.
		if ( ! empty( $discount->expires_at ) && strtotime( $discount->expires_at ) < time() ) {
			wp_send_json_error( array( 'message' => __( 'Code expired.', 'organizer' ) ) );
		}

		// Check usage limit.
		if ( $discount->usage_limit > 0 && $discount->usage_count >= $discount->usage_limit ) {
			wp_send_json_error( array( 'message' => __( 'Usage limit reached.', 'organizer' ) ) );
		}

		$new_price = $price;
		if ( 'percent' === $discount->type ) {
			$new_price = $price - ( $price * ( $discount->amount / 100 ) );
		} else {
			$new_price = $price - $discount->amount;
		}

		$new_price = max( 0, $new_price );

		wp_send_json_success(
			array(
				'new_price' => number_format( $new_price, 2 ),
				'message'   => __( 'Code applied!', 'organizer' ),
			)
		);
	}
}
