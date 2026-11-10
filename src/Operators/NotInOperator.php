<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Operators;

/**
 * None of the needle values is in the haystack.
 */
final class NotInOperator implements Operator {

	/**
	 * @inheritDoc
	 */
	public function match( $needle, $haystack ): bool {
		return empty( array_intersect( (array) $needle, (array) $haystack ) );
	}
}
