<?php


class RSPW_Cart_Total_Rule implements RSPW_Rule {


	/**
	 * @param array $rule
	 * @param array $package
	 *
	 * @return boolean
	 */
	public function validate( $rule, $package ) {
		$rule_price    = floatval( $rule['value_cart_total'] );
		$package_price = floatval( $package['cart_subtotal'] );
		$operator      = RSPW_Operators_Factory::make( $rule['operator'] );
		return ! $operator->match( $package_price, $rule_price );
	}

	/**
	 * @return array
	 */
	public function get_operators_labels() {
		return array(
			RSPW_Operators_Factory::OPERATOR_LT    => __( 'less than', 'wc-restricted-shipping-and-payment' ),
			RSPW_Operators_Factory::OPERATOR_EQUAL => __( 'equal to', 'wc-restricted-shipping-and-payment' ),
			RSPW_Operators_Factory::OPERATOR_GT    => __( 'more than', 'wc-restricted-shipping-and-payment' ),
		);
	}

	/**
	 * @return array
	 */
	public function get_meta_box_fields() {
		return array(
			array(
				'name'         => __( 'Cart Total', 'wc-restricted-shipping-and-payment' ),
				'id'           => 'value_cart_total',
				'type'         => 'text_money',
				'before_field' => $this->get_currency_symbol(),
				'classes'      => 'value_field cart_total',
			),
		);
	}

	private function get_currency_symbol() {
		if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
			return get_woocommerce_currency_symbol();
		}

		return '$';
	}
}
