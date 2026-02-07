<?php
/**
 * Stripe Service.
 *
 * @package Organizer\Services\Payment
 */

namespace Organizer\Services\Payment;

use Stripe\Stripe;
use Stripe\Checkout\Session;

/**
 * Class StripeService
 */
class StripeService {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$options = get_option( 'organizer_options' );
		$api_key = isset( $options['organizer_stripe_secret_key'] ) ? $options['organizer_stripe_secret_key'] : '';
		if ( ! empty( $api_key ) && class_exists( 'Stripe\Stripe' ) ) {
			Stripe::setApiKey( $api_key );
		}
	}

	/**
	 * Create a checkout session.
	 *
	 * @param int    $registration_id Registration ID.
	 * @param string $event_title     Event title.
	 * @param float  $price           Price.
	 * @param string $currency        Currency code.
	 * @param string $success_url     Success URL.
	 * @param string $cancel_url      Cancel URL.
	 * @return Session|false Stripe Session object or false.
	 */
	public function create_checkout_session( $registration_id, $event_title, $price, $currency, $success_url, $cancel_url ) {
		if ( ! class_exists( 'Stripe\Checkout\Session' ) ) {
			return false;
		}

		$amount = $this->get_amount_in_cents( $price, $currency );

		try {
			$session = Session::create(
				array(
					'payment_method_types' => array( 'card' ),
					'line_items'           => array(
						array(
							'price_data' => array(
								'currency'     => $currency,
								'product_data' => array(
									'name' => $event_title,
								),
								'unit_amount'  => $amount,
							),
							'quantity'   => 1,
						),
					),
					'mode'                 => 'payment',
					'success_url'          => $success_url,
					'cancel_url'           => $cancel_url,
					'client_reference_id'  => $registration_id,
				)
			);
			return $session;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if currency is zero decimal.
	 *
	 * @param string $currency Currency code.
	 * @return bool
	 */
	public function is_zero_decimal( $currency ) {
		$zero_decimal = array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' );
		return in_array( strtoupper( $currency ), $zero_decimal, true );
	}

	/**
	 * Get amount in smallest currency unit.
	 *
	 * @param float  $price    Price.
	 * @param string $currency Currency.
	 * @return int Amount.
	 */
	public function get_amount_in_cents( $price, $currency ) {
		if ( $this->is_zero_decimal( $currency ) ) {
			return (int) $price;
		}
		return (int) ( $price * 100 );
	}
}
