<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Operators;

/**
 * needle != haystack
 */
final class NotEqualOperator implements Operator {

	/**
	 * @inheritDoc
	 */
	public function match( $needle, $haystack ): bool {
		return $needle != $haystack; // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
	}
}
