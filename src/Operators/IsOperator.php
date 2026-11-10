<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Operators;

/**
 * The needle is one of the haystack values, or equals the haystack ignoring case.
 */
final class IsOperator implements Operator {

	/**
	 * @inheritDoc
	 */
	public function match( $needle, $haystack ): bool {
		if ( is_array( $haystack ) ) {
			return in_array( $needle, $haystack, true );
		}
		return strtolower( (string) $needle ) === strtolower( (string) $haystack );
	}
}
