<?php
/**
 * Rules validated against a shipping package.
 *
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

use WPRuby\RestrictedShippingPaymentLite\Context;
use WPRuby\RestrictedShippingPaymentLite\Operators\OperatorFactory;
use WPRuby\RestrictedShippingPaymentLite\Rules\BillingCountryRule;
use WPRuby\RestrictedShippingPaymentLite\Rules\CartTotalRule;
use WPRuby\RestrictedShippingPaymentLite\Rules\CouponCodeRule;
use WPRuby\RestrictedShippingPaymentLite\Rules\CustomerRule;
use WPRuby\RestrictedShippingPaymentLite\Rules\PackageWeightRule;
use WPRuby\RestrictedShippingPaymentLite\Rules\RuleFactory;
use WPRuby\RestrictedShippingPaymentLite\Rules\ShippingClassRule;
use WPRuby\RestrictedShippingPaymentLite\Rules\ShippingCountryRule;

class WP_Shipping_Rules_Test extends WP_UnitTestCase {

	public function test_shipping_class_rule() {
		$rule = RSPW_Tests_Helper::prepare_rule(
			RuleFactory::RULE_SHIPPING_CLASS,
			OperatorFactory::OPERATOR_IN,
			array( 'free' )
		);

		$package    = $this->get_package( array( 'shipping_classes' => array( 'free' ) ) );
		$rule_class = new ShippingClassRule();
		$this->assertTrue( $rule_class->validate( $rule, Context::from_package( $package ) ) );

		$rule['operator'] = OperatorFactory::OPERATOR_NOT_IN;
		$this->assertFalse( $rule_class->validate( $rule, Context::from_package( $package ) ) );
	}

	public function test_shipping_class_rule_uses_the_parent_class_of_variations() {
		$shipping_class = wp_insert_term( 'bulky', 'product_shipping_class' );

		$parent = new WC_Product_Variable();
		$parent->set_shipping_class_id( $shipping_class['term_id'] );
		$parent->save();

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $parent->get_id() );
		$variation->set_price( 10 );
		$variation->save();

		$rule    = RSPW_Tests_Helper::prepare_rule( RuleFactory::RULE_SHIPPING_CLASS, OperatorFactory::OPERATOR_IN, array( 'bulky' ) );
		$package = RSPW_Tests_Helper::prepare_package( wc_get_product( $variation->get_id() ) );

		$this->assertTrue( ( new ShippingClassRule() )->validate( $rule, Context::from_package( $package ) ) );
	}

	public function test_cart_total_rule() {
		$rule    = RSPW_Tests_Helper::prepare_rule(
			RuleFactory::RULE_CART_TOTAL,
			OperatorFactory::OPERATOR_GT,
			50
		);
		$package = $this->get_package( array( 'price' => 50 ) );

		$rule_class = new CartTotalRule();

		$this->assertFalse( $rule_class->validate( $rule, Context::from_package( $package ) ), '"more than" does not match an equal total.' );

		$package = $this->get_package( array( 'price' => 51 ) );
		$this->assertTrue( $rule_class->validate( $rule, Context::from_package( $package ) ) );

		$rule['operator'] = OperatorFactory::OPERATOR_LT;
		$package          = $this->get_package( array( 'price' => 49 ) );
		$this->assertTrue( $rule_class->validate( $rule, Context::from_package( $package ) ) );
	}

	public function test_coupon_code_rule() {
		$rule_class = new CouponCodeRule();
		$rule       = RSPW_Tests_Helper::prepare_rule(
			RuleFactory::RULE_COUPON_CODE,
			OperatorFactory::OPERATOR_IN,
			array( 'Free-Shipping' )
		);

		$package = $this->get_package( array(), array( 'applied_coupons' => array( 'free-shipping' ) ) );
		$this->assertTrue( $rule_class->validate( $rule, Context::from_package( $package ) ) );

		$package = $this->get_package( array(), array( 'applied_coupons' => array( 'not-free-shipping' ) ) );
		$this->assertFalse( $rule_class->validate( $rule, Context::from_package( $package ) ) );
	}

	public function test_customer_rule() {
		$rule_class = new CustomerRule();
		$rule       = RSPW_Tests_Helper::prepare_rule(
			RuleFactory::RULE_CUSTOMER,
			OperatorFactory::OPERATOR_IS,
			'admin@example.org'
		);

		$package = $this->get_package( array(), array( 'customer_id' => 1 ) );
		$this->assertTrue( $rule_class->validate( $rule, Context::from_package( $package ) ) );
	}

	public function test_customer_rule_uses_the_billing_email_of_guests() {
		$rule_class = new CustomerRule();
		$rule       = RSPW_Tests_Helper::prepare_rule(
			RuleFactory::RULE_CUSTOMER,
			OperatorFactory::OPERATOR_IS,
			'guest@example.org'
		);

		$package = $this->get_package( array(), array( 'customer_id' => 0 ) );
		$this->assertTrue( $rule_class->validate( $rule, Context::from_package( $package, array( 'customer_email' => 'guest@example.org' ) ) ) );
		$this->assertFalse( $rule_class->validate( $rule, Context::from_package( $package, array( 'customer_email' => '' ) ) ) );
	}

	public function test_package_weight_rule() {
		// Rule values are in kg; product weights are in the store's unit.
		update_option( 'woocommerce_weight_unit', 'kg' );

		$rule_class = new PackageWeightRule();
		$rule_input = RSPW_Tests_Helper::prepare_rule(
			RuleFactory::RULE_PACKAGE_WEIGHT,
			OperatorFactory::OPERATOR_GT,
			2
		);

		$package = $this->get_package( array( 'weight' => 2.1 ) );
		$this->assertTrue( $rule_class->validate( $rule_input, Context::from_package( $package ) ) );

		$package = $this->get_package( array( 'weight' => 1.9 ) );
		$this->assertFalse( $rule_class->validate( $rule_input, Context::from_package( $package ) ) );
	}

	public function test_shipping_country_rule() {
		$rule_class = new ShippingCountryRule();
		$rule_input = RSPW_Tests_Helper::prepare_rule(
			RuleFactory::RULE_SHIPPING_COUNTRY,
			OperatorFactory::OPERATOR_IS,
			array( 'US' )
		);

		$package = $this->get_package( array(), array( 'country' => 'US' ) );
		$this->assertTrue( $rule_class->validate( $rule_input, Context::from_package( $package ) ) );

		$package = $this->get_package( array(), array( 'country' => 'GB' ) );
		$this->assertFalse( $rule_class->validate( $rule_input, Context::from_package( $package ) ) );
	}

	public function test_billing_country_rule() {
		$rule_class = new BillingCountryRule();
		$rule_input = RSPW_Tests_Helper::prepare_rule(
			RuleFactory::RULE_BILLING_COUNTRY,
			OperatorFactory::OPERATOR_IS_NOT,
			array( 'US' )
		);

		$package = $this->get_package();
		$this->assertFalse( $rule_class->validate( $rule_input, Context::from_package( $package, array( 'billing_country' => 'US' ) ) ) );
		$this->assertTrue( $rule_class->validate( $rule_input, Context::from_package( $package, array( 'billing_country' => 'GB' ) ) ) );
	}

	public function test_rules_without_values_never_match() {
		$package = Context::from_package( $this->get_package() );

		foreach ( array_keys( RuleFactory::available_rules() ) as $type ) {
			$rule = RuleFactory::make( $type );
			foreach ( array_keys( $rule->get_operators_labels() ) as $operator ) {
				if ( in_array( $type, array( RuleFactory::RULE_CART_TOTAL, RuleFactory::RULE_PACKAGE_WEIGHT, RuleFactory::RULE_CUSTOMER ), true ) ) {
					continue; // Compared with 0 or an empty email.
				}
				$this->assertFalse(
					$rule->validate(
						array(
							'rule_type' => $type,
							'operator'  => $operator,
						),
						$package
					),
					"$type $operator"
				);
			}
		}
	}

	private function get_package( $product_args = array(), $package_args = array() ) {
		$product = RSPW_Tests_Helper::add_product( $product_args );
		return RSPW_Tests_Helper::prepare_package( $product, $package_args );
	}
}
