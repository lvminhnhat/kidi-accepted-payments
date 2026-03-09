<?php
/**
 * WooCommerce payment gateway auto-detection.
 *
 * Maps active WooCommerce payment gateways to the payment method
 * icon keys used by this plugin. Keeps a single source of truth
 * so icons stay in sync whenever gateways are enabled / disabled.
 *
 * @package Kidi_Accepted_Payments
 * @since   1.1.0
 */

defined( 'ABSPATH' ) || exit;

class Kidi_AP_Woo_Sync {

	/**
	 * Gateway ID → payment method icon keys mapping.
	 *
	 * Covers the most common WooCommerce gateways. Each entry maps
	 * a gateway `id` (as returned by WC) to one or more icon slugs
	 * that match keys in Kidi_AP_Icons::labels().
	 *
	 * @return array<string, string[]>
	 */
	private static function gateway_map() {
		return array(
            // ── Stripe / WooCommerce Payments ────────────────────
            'woocommerce_payments'              => array( 'visa', 'mastercard', 'amex', 'applepay', 'googlepay' ),
            'woocommerce_payments_apple_pay'    => array( 'applepay' ),
            'woocommerce_payments_google_pay'   => array( 'googlepay' ),
            'stripe'                            => array( 'visa', 'mastercard', 'amex', 'applepay', 'googlepay' ),
			'stripe_cc'                         => array( 'visa', 'mastercard', 'amex' ),
			'stripe_applepay'                   => array( 'applepay' ),
			'stripe_googlepay'                  => array( 'googlepay' ),

            // ── PayPal Commerce Platform ───────────────────────────────
            'ppcp-gateway'                      => array( 'paypal' ),
            'ppcp-card-button-gateway'          => array( 'visa', 'mastercard', 'amex', 'paypal' ),
            'ppcp-credit-card-gateway'          => array( 'visa', 'mastercard', 'amex' ),
            'ppcp-applepay'                     => array( 'applepay' ),
            'ppcp-googlepay'                    => array( 'googlepay' ),
            'paypal'                            => array( 'paypal' ),

			// ── Square ───────────────────────────────────────────
			'square_credit_card'                => array( 'visa', 'mastercard', 'amex' ),

			// ── Braintree ────────────────────────────────────────
			'braintree_credit_card'             => array( 'visa', 'mastercard', 'amex' ),
			'braintree_paypal'                  => array( 'paypal' ),

			// ── Amazon Pay ───────────────────────────────────────
			'amazon_payments_advanced'          => array( 'visa', 'mastercard', 'amex' ),

			// ── Authorize.Net ────────────────────────────────────
			'authorize_net_cim_credit_card'     => array( 'visa', 'mastercard', 'amex' ),

			// ── Mollie ───────────────────────────────────────────
			'mollie_wc_gateway_creditcard'      => array( 'visa', 'mastercard', 'amex' ),
			'mollie_wc_gateway_applepay'        => array( 'applepay' ),
			'mollie_wc_gateway_paypal'          => array( 'paypal' ),

			// ── Klarna (via Stripe or standalone) ────────────────
			// No direct icon — but covers credit-card sub-methods.
		);
	}

	/**
	 * Check if WooCommerce is active and ready.
	 *
	 * @return bool
	 */
	public static function is_woo_active() {
		return class_exists( 'WooCommerce' ) && function_exists( 'WC' );
	}

	/**
	 * Detect payment method icon keys from active WooCommerce gateways.
	 *
	 * Returns a deduplicated array of icon keys in the canonical order
	 * defined by Kidi_AP_Icons::labels() (Visa → MC → Amex → … ).
	 *
	 * @return string[] e.g. [ 'visa', 'mastercard', 'paypal' ]
	 */
	public static function detect() {
		if ( ! self::is_woo_active() ) {
			return array();
		}

		$gateways = WC()->payment_gateways()->get_available_payment_gateways();
		$map      = self::gateway_map();
		$methods  = array();

		foreach ( $gateways as $gateway_id => $gateway ) {
			if ( isset( $map[ $gateway_id ] ) ) {
				$methods = array_merge( $methods, $map[ $gateway_id ] );
			}
		}

		// Deduplicate and preserve canonical icon order.
		$all_keys = array_keys( Kidi_AP_Icons::labels() );
		$detected = array_unique( $methods );

		return array_values( array_intersect( $all_keys, $detected ) );
	}

	/**
	 * Human-readable names of active & mapped gateways (for admin UI).
	 *
	 * @return string[]
	 */
	public static function active_gateway_names() {
		if ( ! self::is_woo_active() ) {
			return array();
		}

		$gateways = WC()->payment_gateways()->get_available_payment_gateways();
		$map      = self::gateway_map();
		$names    = array();

		foreach ( $gateways as $gateway_id => $gateway ) {
			if ( isset( $map[ $gateway_id ] ) ) {
				$names[] = $gateway->get_title();
			}
		}

		return array_unique( $names );
	}
}
