<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Operators;

/**
 * The opposite of IsOperator: the needle is none of the haystack values, or differs from the haystack ignoring case.
 */
final class IsNotOperator implements Operator {

	/**
	 * @inheritDoc
	 */
	public function match( $needle, $haystack ): bool {
		return ! ( new IsOperator() )->match( $needle, $haystack );
	}
}
