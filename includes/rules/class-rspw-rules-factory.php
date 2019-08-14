<?php


class RSPW_Rules_Factory {

	const RULE_SHIPPING_CLASS   = 'shipping_class';
	const RULE_COUPON_CODE      = 'coupon_code';
	const RULE_CART_TOTAL       = 'cart_total';
	const RULE_PACKAGE_WEIGHT   = 'package_weight';
	const RULE_SHIPPING_COUNTRY = 'shipping_country';
	const RULE_BILLING_COUNTRY  = 'billing_country';
	const RULE_CUSTOMER         = 'customer';

	/**
	 * @param string $rule
	 *
	 * @return RSPW_Rule|null
	 */
	public static function make( $rule ) {
		$available_rules = self::available_rules();

		if ( isset( $available_rules[ $rule ] ) ) {
			return new $available_rules[ $rule ]['class']();
		}

		return null;
	}

	public static function available_rules() {
		return array(
			self::RULE_SHIPPING_CLASS   => array(
				'class' => RSPW_Shipping_Class_Rule::class,
				'label' => __( 'Shipping Class', 'wc-restricted-shipping-and-payment' ),
			),
			self::RULE_COUPON_CODE      => array(
				'class' => RSPW_Coupon_Code_Rule::class,
				'label' => __( 'Coupon Code', 'wc-restricted-shipping-and-payment' ),
			),
			self::RULE_CART_TOTAL       => array(
				'class' => RSPW_Cart_Total_Rule::class,
				'label' => __( 'Cart Total', 'wc-restricted-shipping-and-payment' ),
			),
			self::RULE_PACKAGE_WEIGHT   => array(
				'class' => RSPW_Package_Weight_Rule::class,
				'label' => __( 'Package Weight', 'wc-restricted-shipping-and-payment' ),
			),
			self::RULE_SHIPPING_COUNTRY => array(
				'class' => RSPW_Shipping_Country_Rule::class,
				'label' => __( 'Shipping Country', 'wc-restricted-shipping-and-payment' ),
			),
			self::RULE_BILLING_COUNTRY  => array(
				'class' => RSPW_Billing_Country_Rule::class,
				'label' => __( 'Billing Country', 'wc-restricted-shipping-and-payment' ),
			),
			self::RULE_CUSTOMER         => array(
				'class' => RSPW_Customer_Rule::class,
				'label' => __( 'Customer', 'wc-restricted-shipping-and-payment' ),
			),
		);
	}
}
