<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Rules;

use WPRuby\RestrictedShippingPaymentLite\Context;
use WPRuby\RestrictedShippingPaymentLite\Operators\OperatorFactory;

/**
 * Compares the total weight of the cart in kg.
 */
final class PackageWeightRule extends AbstractRule {

	public function validate( array $rule, Context $context ): bool {
		return $this->compare_numbers( $rule, 'value_package_weight', $context->get_total_weight(), 6 );
	}

	public function get_operators_labels(): array {
		return array(
			OperatorFactory::OPERATOR_LT => __( 'less than', 'restricted-shipping-and-payment-for-woocommerce' ),
			OperatorFactory::OPERATOR_EQUAL => __( 'equal to', 'restricted-shipping-and-payment-for-woocommerce' ),
			OperatorFactory::OPERATOR_GT => __( 'more than', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	public function get_value_schema(): array {
		return array(
			array(
				'key'   => 'value_package_weight',
				'type'  => 'number',
				'label' => __( 'Weight', 'restricted-shipping-and-payment-for-woocommerce' ),
				'unit'  => 'kg',
			),
		);
	}
}
