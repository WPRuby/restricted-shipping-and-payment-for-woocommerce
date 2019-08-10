<?php


class RSPW_Customer_Rule implements RSPW_Rule {


	/**
	 * @param array $rule
	 * @param array $package
	 *
	 * @return boolean
	 */
	public function validate( $rule, $package ) {
		$rule_customer_email    = $rule['value_customer'];
		$package_customer_email = $this->get_package_user_email( $package );
		$operator               = RSPW_Operators_Factory::make( $rule['operator'] );
		return $operator->match( $rule_customer_email, $package_customer_email );
	}

	/**
	 * @param $package
	 *
	 * @return string
	 */
	private function get_package_user_email( $package ) {
		$user_id   = intval( $package['user']['ID'] );
		$user_info = get_userdata( $user_id );
		/** @var $user_info WP_User */
		return $user_info->user_email;
	}

	/**
	 * @return array
	 */
	public function get_operators_labels() {
		return array(
			RSPW_Operators_Factory::OPERATOR_IS     => __( 'email is', 'restricted-shipping-and-payment-for-woocommerce' ),
			RSPW_Operators_Factory::OPERATOR_IS_NOT => __( 'email is not', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	/**
	 * @return array
	 */
	public function get_meta_box_fields() {
		return array(
			array(
				'name'    => __( 'Customer Email', 'restricted-shipping-and-payment-for-woocommerce' ),
				'id'      => 'value_customer',
				'type'    => 'text_email',
				'classes' => 'value_field customer',
			),
		);
	}
}
