<?php


class RSPW_Shipping_Class_Rule implements RSPW_Rule {

	/**
	 * @param array $rule
	 * @param array $package
	 *
	 * @return boolean
	 */
	public function validate( $rule, $package ) {
		$rule_shipping_class = $rule['value_shipping_class'];
		$operator            = RSPW_Operators_Factory::make( $rule['operator'] );

		$cart_shipping_classes = [];
		foreach ( $package['contents'] as $item_id => $content ) {

            if (!isset($content['data'])) continue;
            /** @var WC_Product $product */
            $product    = $content['data'];
            $product_id = $product->get_id();
            $parent_id  = $product->get_parent_id();

            if ( $parent_id > 0 ) {
                $product_id = $parent_id;
            }

            $product = wc_get_product($product_id);
            $cart_shipping_classes[] = $product->get_shipping_class();

		}

		return $operator->match( $rule_shipping_class, $cart_shipping_classes );
	}

	/**
	 * @return array
	 */
	public function get_operators_labels() {
		return array(
			RSPW_Operators_Factory::OPERATOR_IN     => __( 'included in the order', 'restricted-shipping-and-payment-for-woocommerce' ),
			RSPW_Operators_Factory::OPERATOR_NOT_IN => __( 'not included in the order', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	public function get_meta_box_fields() {
		return array(
			array(
				'name'             => __( 'Shipping Classes', 'restricted-shipping-and-payment-for-woocommerce' ),
				'desc'             => __( 'Select shipping class/es', 'restricted-shipping-and-payment-for-woocommerce' ),
				'id'               => 'value_shipping_class',
				'type'             => 'pw_multiselect',
				'show_option_none' => false,
				'options'          => $this->get_shipping_classes(),
				'classes'          => 'value_field shipping_class',
			),
		);
	}

	/**
	 * @return array
	 */
	private function get_shipping_classes() {
		if ( ! function_exists( 'WC' ) ) {
			return array();
		}
		$classes          = array();
		$shipping_classes = WC()->shipping()->get_shipping_classes();
		foreach ( $shipping_classes as $shipping_class ) {
			$classes[ $shipping_class->slug ] = $shipping_class->name;
		}
		return $classes;
	}
}
