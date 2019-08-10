<?php


class RSPW_Shipping_Country_Rule implements RSPW_Rule {


	/**
	 * @param array $rule
	 * @param array $package
	 *
	 * @return boolean
	 */
	public function validate( $rule, $package ) {
		$rule_shipping_country    = $rule['value_shipping_country'];
		$package_shipping_country = $this->get_package_shipping_country( $package );
		$operator                 = RSPW_Operators_Factory::make( $rule['operator'] );
		return $operator->match( $package_shipping_country, $rule_shipping_country );
	}

	/**
	 * @param $package
	 *
	 * @return string
	 */
	private function get_package_shipping_country( $package ) {
		if ( isset( $package['destination']['country'] ) ) {
			return $package['destination']['country'];
		}
		return '';
	}

	/**
	 * @return array
	 */
	public function get_operators_labels() {
		return array(
			RSPW_Operators_Factory::OPERATOR_IS     => __( 'is', 'restricted-shipping-and-payment-for-woocommerce' ),
			RSPW_Operators_Factory::OPERATOR_IS_NOT => __( 'is not', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	/**
	 * @return array
	 */
	public function get_meta_box_fields() {
		return array(
			array(
				'name'             => __( 'Shipping Countries', 'restricted-shipping-and-payment-for-woocommerce' ),
				'desc'             => __( 'Select shipping countries', 'restricted-shipping-and-payment-for-woocommerce' ),
				'id'               => 'value_shipping_country',
				'type'             => 'pw_multiselect',
				'show_option_none' => false,
				'options'          => WC()->countries->get_shipping_countries(),
				'classes'          => 'value_field shipping_country',
			),
		);
	}
}
