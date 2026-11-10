<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Operators;

/**
 * The needle is none of the haystack values, or differs from the haystack.
 */
final class IsNotOperator implements Operator {

	/**
	 * @inheritDoc
	 */
	public function match( $needle, $haystack ): bool {
		if ( is_array( $haystack ) ) {
			return ! in_array( $needle, $haystack, true );
		}
		return $needle !== $haystack;
	}
}
