<?php

class RSPW_Coupon_Code_Rule implements RSPW_Rule {


	/**
	 * @param array $rule
	 * @param array $package
	 *
	 * @return boolean
	 */
	public function validate( $rule, $package ) {
		$applied_coupons = null;

		if ( isset( $package['applied_coupons'] ) && ! empty( $package['applied_coupons'] ) ) {
			$applied_coupons = $package['applied_coupons'];
		}

		if ( is_null( $applied_coupons ) ) {
			return false;
		}

		$rule_coupon_codes = array_map( 'strtolower', $rule['value_coupon_code'] );
		$operator          = RSPW_Operators_Factory::make( $rule['operator'] );
		return $operator->match( $rule_coupon_codes, $applied_coupons );
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

	/**
	 * @return array
	 */
	public function get_meta_box_fields() {
		return array(
			array(
				'name'             => __( 'Coupon Codes', 'restricted-shipping-and-payment-for-woocommerce' ),
				'desc'             => __( 'Select coupon code/s', 'restricted-shipping-and-payment-for-woocommerce' ),
				'id'               => 'value_coupon_code',
				'type'             => 'pw_multiselect',
				'show_option_none' => false,
				'options'          => $this->get_available_coupon_codes(),
				'classes'          => 'value_field coupon_code',
			),
		);
	}


	/**
	 * @return array
	 */
	private function get_available_coupon_codes() {
		$args = array(
			'posts_per_page' => - 1,
			'orderby'        => 'title',
			'order'          => 'asc',
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
		);

		$coupons      = get_posts( $args );
		$coupon_names = array();
		foreach ( $coupons as $coupon ) {
			// Get the name for each coupon post.
			$coupon_name                  = $coupon->post_title;
			$coupon_names[ $coupon_name ] = $coupon_name;
		}
		return $coupon_names;
	}
}
