<?php


class RSPW_Package_Weight_Rule implements RSPW_Rule {


	/**
	 * @param array $rule
	 * @param array $package
	 *
	 * @return boolean
	 */
	public function validate( $rule, $package ) {
		$rule_weight    = wc_get_weight( $rule['value_package_weight'], 'kg', 'kg' );
		$package_weight = wc_get_weight( $this->get_package_weight( $package ), 'kg' );
		$operator       = RSPW_Operators_Factory::make( $rule['operator'] );
		return $operator->match( $package_weight, $rule_weight );
	}

	/**
	 * @return array
	 */
	public function get_operators_labels() {
		return array(
			RSPW_Operators_Factory::OPERATOR_LT    => __( 'less than', 'restricted-shipping-and-payment-for-woocommerce' ),
			RSPW_Operators_Factory::OPERATOR_EQUAL => __( 'equal to', 'restricted-shipping-and-payment-for-woocommerce' ),
			RSPW_Operators_Factory::OPERATOR_GT    => __( 'more than', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	/**
	 * @param $package
	 *
	 * @return float|int
	 */
	private function get_package_weight( $package ) {
		$total_weight = 0;
		foreach ( $package['contents'] as $content ) {
			/** @var WC_Product_Simple $product */
			$product       = $content['data'];
			$total_weight += ($content['quantity'] * $product->get_weight());
		}
		return $total_weight;
	}


	/**
	 * @return array
	 */
	public function get_meta_box_fields() {
		return array(
			array(
				'name'    => __( 'Package Weight', 'restricted-shipping-and-payment-for-woocommerce' ),
				'id'      => 'value_package_weight',
				'type'    => 'text_small',
				'classes' => 'value_field package_weight',
			),
		);
	}
}
