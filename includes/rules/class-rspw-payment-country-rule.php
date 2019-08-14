<?php


class RSPW_Payment_Country_Rule implements RSPW_Rule {


	/**
	 * @param array $rule
	 * @param array $package
	 *
	 * @return boolean
	 */
	public function validate( $rule, $package ) {
		$rule_shipping_country    = $rule['value_payment_country'];
		$package_shipping_country = $this->get_payment_country( $package );
		$operator                 = RSPW_Operators_Factory::make( $rule['operator'] );
		return ! $operator->match( $package_shipping_country, $rule_shipping_country );
	}

	/**
	 * @param $package
	 *
	 * @return string
	 */
	private function get_payment_country( $package ) {
		return '';
	}

	/**
	 * @return array
	 */
	public function get_operators_labels() {
		return array(
			RSPW_Operators_Factory::OPERATOR_IS     => __( 'is', 'wc-restricted-shipping-and-payment' ),
			RSPW_Operators_Factory::OPERATOR_IS_NOT => __( 'is not', 'wc-restricted-shipping-and-payment' ),
		);
	}

	/**
	 * @return array
	 */
	public function get_meta_box_fields() {
		return array(
			array(
				'name'             => __( 'payment Countries', 'wc-restricted-shipping-and-payment' ),
				'desc'             => __( 'Select payment countries', 'wc-restricted-shipping-and-payment' ),
				'id'               => 'value_payment_country',
				'type'             => 'pw_multiselect',
				'show_option_none' => false,
				'options'          => WC()->countries->get_shipping_countries(),
				'classes'          => 'value_field shipping_country',
			),
		);
	}
}
