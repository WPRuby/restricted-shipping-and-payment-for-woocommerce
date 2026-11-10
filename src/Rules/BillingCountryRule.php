<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Rules;

use WPRuby\RestrictedShippingPaymentLite\Context;
use WPRuby\RestrictedShippingPaymentLite\Operators\OperatorFactory;

/**
 * The billing country is one of the countries.
 */
final class BillingCountryRule extends AbstractRule {

	public function validate( array $rule, Context $context ): bool {
		if ( empty( $rule['value_billing_country'] ) ) {
			return false;
		}
		return $this->operator( $rule )->match( $context->get_billing_country(), array_map( 'strval', (array) $rule['value_billing_country'] ) );
	}

	public function get_operators_labels(): array {
		return array(
			OperatorFactory::OPERATOR_IS => __( 'is', 'restricted-shipping-and-payment-for-woocommerce' ),
			OperatorFactory::OPERATOR_IS_NOT => __( 'is not', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	public function get_value_schema(): array {
		return array(
			array(
				'key'     => 'value_billing_country',
				'type'    => 'multiselect',
				'label'   => __( 'Billing countries', 'restricted-shipping-and-payment-for-woocommerce' ),
				'options' => WC()->countries->get_allowed_countries(),
			),
		);
	}
}
