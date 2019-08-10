<?php
/**
 * Class SampleTest
 *
 * @package Woocommerce_Restricted_Shipping_And_Payment_Pro
 */

/**
 * Sample test case.
 */
class WP_Shipping_Rules_Test extends WP_UnitTestCase {

	public function test_shipping_class_rule() {
		$rule = RSPW_Tests_Helper::prepare_rule(
			RSPW_Rules_Factory::RULE_SHIPPING_CLASS,
			RSPW_Operators_Factory::OPERATOR_IN,
			array( 'free' )
		);

		$package    = $this->get_package( array( 'shipping_classes' => array( 'free' ) ) );
		$rule_class = new RSPW_Shipping_Class_Rule();
		$this->assertTrue( $rule_class->validate( $rule, $package ) );
	}

	public function test_cart_total_rule() {
		$rule    = RSPW_Tests_Helper::prepare_rule(
			RSPW_Rules_Factory::RULE_CART_TOTAL,
			RSPW_Operators_Factory::OPERATOR_GT,
			50
		);
		$package = $this->get_package( array( 'price' => 50 ) );

		$rule_class = new RSPW_Cart_Total_Rule();

		$this->assertFalse( $rule_class->validate( $rule, $package ) );

		$package = $this->get_package( array( 'price' => 49 ) );
		$this->assertTrue( $rule_class->validate( $rule, $package ) );
	}

	public function test_coupon_code_rule() {
		$rule_class = new RSPW_Coupon_Code_Rule();
		$rule       = RSPW_Tests_Helper::prepare_rule(
			RSPW_Rules_Factory::RULE_COUPON_CODE,
			RSPW_Operators_Factory::OPERATOR_IN,
			array( 'free-shipping' )
		);

		$package = $this->get_package( array(), array( 'applied_coupons' => array( 'free-shipping' ) ) );
		$this->assertTrue( $rule_class->validate( $rule, $package ) );

		$package = $this->get_package( array(), array( 'applied_coupons' => array( 'not-free-shipping' ) ) );
		$this->assertFalse( $rule_class->validate( $rule, $package ) );
	}

	public function test_customer_rule() {
		$rule_class = new RSPW_Customer_Rule();
		$rule       = RSPW_Tests_Helper::prepare_rule(
			RSPW_Rules_Factory::RULE_CUSTOMER,
			RSPW_Operators_Factory::OPERATOR_IS,
			'admin@example.org'
		);

		$package = $this->get_package( array(), array( 'customer_id' => 1 ) );
		$this->assertTrue( $rule_class->validate( $rule, $package ) );
	}

	public function test_package_weight_rule() {
		$rule_class = new RSPW_Package_Weight_Rule();
		$rule_input = RSPW_Tests_Helper::prepare_rule(
			RSPW_Rules_Factory::RULE_PACKAGE_WEIGHT,
			RSPW_Operators_Factory::OPERATOR_GT,
			2
		);

		$package = $this->get_package( array( 'weight' => 2.1 ) );
		$this->assertTrue( $rule_class->validate( $rule_input, $package ) );

		$package = $this->get_package( array( 'weight' => 1.9 ) );
		$this->assertFalse( $rule_class->validate( $rule_input, $package ) );
	}

	public function test_shipping_country_rule() {
		$rule_class = new RSPW_Shipping_Country_Rule();
		$rule_input = RSPW_Tests_Helper::prepare_rule(
			RSPW_Rules_Factory::RULE_SHIPPING_COUNTRY,
			RSPW_Operators_Factory::OPERATOR_IS,
			'US'
		);

		$package = $this->get_package( array(), array( 'country' => 'US' ) );
		$this->assertTrue( $rule_class->validate( $rule_input, $package ) );

		$package = $this->get_package( array(), array( 'country' => 'UK' ) );
		$this->assertFalse( $rule_class->validate( $rule_input, $package ) );
	}



	private function get_package( $product_args = array(), $package_args = array() ) {
		$product = RSPW_Tests_Helper::add_product( $product_args );
		$package = RSPW_Tests_Helper::prepare_package( $product, $package_args );
		return $package;
	}

}
