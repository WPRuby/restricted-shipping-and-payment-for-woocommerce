<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Rules;

use WPRuby\RestrictedShippingPaymentLite\Context;
use WPRuby\RestrictedShippingPaymentLite\Operators\OperatorFactory;

/**
 * A product in the cart has one of the shipping classes.
 */
final class ShippingClassRule extends AbstractRule {

	public function validate( array $rule, Context $context ): bool {
		if ( empty( $rule['value_shipping_class'] ) || ! is_array( $rule['value_shipping_class'] ) ) {
			return false;
		}

		$classes = array();
		foreach ( $context->get_products() as $product ) {
			$classes[] = $this->get_shipping_class( $product );
		}

		return $this->operator( $rule )->match( $rule['value_shipping_class'], $classes );
	}

	public function get_operators_labels(): array {
		return array(
			OperatorFactory::OPERATOR_IN => __( 'included in the order', 'restricted-shipping-and-payment-for-woocommerce' ),
			OperatorFactory::OPERATOR_NOT_IN => __( 'not included in the order', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	public function get_value_schema(): array {
		return array(
			array(
				'key'     => 'value_shipping_class',
				'type'    => 'multiselect',
				'label'   => __( 'Shipping classes', 'restricted-shipping-and-payment-for-woocommerce' ),
				'options' => $this->get_shipping_classes(),
			),
		);
	}

	/**
	 * Variations without their own shipping class use their parent's.
	 */
	private function get_shipping_class( \WC_Product $product ): string {
		$shipping_class = $product->get_shipping_class();

		if ( '' === $shipping_class && $product->get_parent_id() > 0 ) {
			$parent = wc_get_product( $product->get_parent_id() );
			if ( $parent instanceof \WC_Product ) {
				$shipping_class = $parent->get_shipping_class();
			}
		}

		return $shipping_class;
	}

	private function get_shipping_classes(): array {
		$classes = array();
		foreach ( WC()->shipping()->get_shipping_classes() as $shipping_class ) {
			$classes[ $shipping_class->slug ] = $shipping_class->name;
		}
		return $classes;
	}
}
