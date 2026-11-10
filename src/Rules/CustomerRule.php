<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Rules;

use WPRuby\RestrictedShippingPaymentLite\Context;
use WPRuby\RestrictedShippingPaymentLite\Operators\OperatorFactory;

/**
 * Compares the customer's email.
 */
final class CustomerRule extends AbstractRule {

	public function validate( array $rule, Context $context ): bool {
		$email = isset( $rule['value_customer'] ) && is_string( $rule['value_customer'] ) ? trim( $rule['value_customer'] ) : '';
		if ( '' === $email ) {
			return false;
		}
		return $this->operator( $rule )->match( $email, $context->get_customer_email() );
	}

	public function get_operators_labels(): array {
		return array(
			OperatorFactory::OPERATOR_IS => __( 'email is', 'restricted-shipping-and-payment-for-woocommerce' ),
			OperatorFactory::OPERATOR_IS_NOT => __( 'email is not', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	public function get_value_schema(): array {
		return array(
			array(
				'key'   => 'value_customer',
				'type'  => 'email',
				'label' => __( 'Customer email', 'restricted-shipping-and-payment-for-woocommerce' ),
			),
		);
	}
}
