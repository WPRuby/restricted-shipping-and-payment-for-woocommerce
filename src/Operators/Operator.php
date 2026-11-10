<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Operators;

/**
 * Compares a value from the checkout with a rule's value.
 */
interface Operator {

	/**
	 * @param mixed $needle
	 * @param mixed $haystack
	 */
	public function match( $needle, $haystack ): bool;
}
