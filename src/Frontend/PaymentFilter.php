<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Frontend;

use WPRuby\RestrictedShippingPaymentLite\Condition;
use WPRuby\RestrictedShippingPaymentLite\Context;

/**
 * Removes the payment gateways hidden by payment conditions.
 */
final class PaymentFilter extends ConditionFilter {

	public function register(): void {
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'exclude_payment_gateways' ) );
	}

	protected function get_post_type(): string {
		return Condition::POST_TYPE_PAYMENT;
	}

	/**
	 * @param array $gateways gateway ID => gateway.
	 *
	 * @return array
	 */
	public function exclude_payment_gateways( $gateways ) {
		if ( ! is_array( $gateways ) || ! WC()->cart || ! $this->is_checkout_request() ) {
			return $gateways;
		}

		$context = Context::from_cart();

		foreach ( $this->get_conditions() as $condition ) {
			$condition_gateways = array_values( array_intersect( $condition->get_targets(), array_map( 'strval', array_keys( $gateways ) ) ) );
			if ( empty( $condition_gateways ) || ! $condition->matches( $context ) ) {
				continue;
			}

			foreach ( $condition_gateways as $gateway_id ) {
				unset( $gateways[ $gateway_id ] );
			}
		}

		return $gateways;
	}

	/**
	 * Gateways are filtered on the classic checkout page, its AJAX requests and Store API requests (block checkout).
	 */
	private function is_checkout_request(): bool {
		return is_checkout() || defined( 'WOOCOMMERCE_CHECKOUT' ) || $this->is_store_api_request();
	}
}
