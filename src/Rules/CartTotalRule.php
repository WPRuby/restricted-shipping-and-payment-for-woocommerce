<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Rules;

use WPRuby\RestrictedShippingPaymentLite\Context;
use WPRuby\RestrictedShippingPaymentLite\Operators\OperatorFactory;

/**
 * Compares the cart subtotal.
 */
final class CartTotalRule extends AbstractRule {

	public function validate( array $rule, Context $context ): bool {
		return $this->compare_numbers( $rule, 'value_cart_total', $context->get_subtotal(), wc_get_price_decimals() );
	}

	public function get_operators_labels(): array {
		return array(
			OperatorFactory::OPERATOR_LT => __( 'less than', 'restricted-shipping-and-payment-for-woocommerce' ),
			OperatorFactory::OPERATOR_EQUAL => __( 'equal to', 'restricted-shipping-and-payment-for-woocommerce' ),
			OperatorFactory::OPERATOR_NOT_EQUAL => __( 'not equal to', 'restricted-shipping-and-payment-for-woocommerce' ),
			OperatorFactory::OPERATOR_GT => __( 'more than', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	public function get_value_schema(): array {
		return array(
			array(
				'key'   => 'value_cart_total',
				'type'  => 'money',
				'label' => __( 'Cart total', 'restricted-shipping-and-payment-for-woocommerce' ),
			),
		);
	}
}
