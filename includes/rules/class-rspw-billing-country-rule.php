<?php


class RSPW_Billing_Country_Rule implements RSPW_Rule {


	/**
	 * @param array $rule
	 * @param array $package
	 *
	 * @return boolean
	 */
	public function validate( $rule, $package ) {
		$rule_billing_country    = $rule['value_shipping_country'];
		$package_billing_country = WC()->customer->get_country();

		$operator = RSPW_Operators_Factory::make( $rule['operator'] );
		return ! $operator->match( $package_billing_country, $rule_billing_country );
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
				'name'             => __( 'Billing Countries', 'restricted-shipping-and-payment-for-woocommerce' ),
				'desc'             => __( 'Select billing country/ies', 'restricted-shipping-and-payment-for-woocommerce' ),
				'id'               => 'value_billing_country',
				'type'             => 'pw_multiselect',
				'show_option_none' => false,
				'options'          => $this->get_billing_countries(),
				'classes'          => 'value_field billing_country',
			),
		);
	}

	private function get_billing_countries() {
		if ( function_exists( 'WC' ) ) {
			return WC()->countries->get_allowed_countries();
		}
		return array();
	}
}
