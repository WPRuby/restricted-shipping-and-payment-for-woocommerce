<?php
/**
 * Payment conditions applied to the gateways of a real cart.
 *
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

use WPRuby\RestrictedShippingPaymentLite\Condition;
use WPRuby\RestrictedShippingPaymentLite\Plugin;

class WP_Payment_Conditions_Test extends RSPW_Checkout_Test_Case {

	public function test_cart_total_rule_hides_gateway() {
		$this->make_condition( Condition::POST_TYPE_PAYMENT, array( 'cheque' ), array( $this->rule( 'cart_total', 'gt', array( 'value_cart_total' => '100' ) ) ) );

		$this->add_to_cart( 50 );
		$this->assertSame( array( 'bacs', 'cheque', 'cod' ), $this->available_gateways() );

		$this->add_to_cart( 60 );
		$this->assertSame( array( 'bacs', 'cod' ), $this->available_gateways() );
	}

	public function test_any_rule_hides_gateway() {
		$this->make_condition(
			Condition::POST_TYPE_PAYMENT,
			array( 'bacs' ),
			array(
				$this->rule( 'cart_total', 'gt', array( 'value_cart_total' => '100' ) ),
				$this->rule( 'shipping_country', 'is', array( 'value_shipping_country' => array( 'GB' ) ) ),
			)
		);
		$this->add_to_cart( 10 );

		$this->assertContains( 'bacs', $this->available_gateways() );

		WC()->customer->set_shipping_country( 'GB' );
		$this->assertNotContains( 'bacs', $this->available_gateways() );
	}

	public function test_billing_country_rule() {
		$this->make_condition( Condition::POST_TYPE_PAYMENT, array( 'cod' ), array( $this->rule( 'billing_country', 'is', array( 'value_billing_country' => array( 'US' ) ) ) ) );
		$this->add_to_cart( 10 );

		$this->assertNotContains( 'cod', $this->available_gateways() );

		WC()->customer->set_billing_country( 'CA' );
		$this->assertContains( 'cod', $this->available_gateways() );
	}

	public function test_customer_rule_does_not_fail_for_guests() {
		wp_set_current_user( 0 );
		WC()->customer->set_billing_email( 'guest@example.org' );
		$this->make_condition( Condition::POST_TYPE_PAYMENT, array( 'cod' ), array( $this->rule( 'customer', 'is', array( 'value_customer' => 'guest@example.org' ) ) ) );
		$this->add_to_cart( 10 );

		$this->assertNotContains( 'cod', $this->available_gateways() );
	}

	public function test_condition_without_gateways_does_not_stop_later_conditions() {
		$this->make_condition( Condition::POST_TYPE_PAYMENT, array( 'cheque' ), array( $this->rule( 'cart_total', 'gt', array( 'value_cart_total' => '0' ) ) ) );
		$this->make_condition( Condition::POST_TYPE_PAYMENT, array(), array( $this->rule( 'cart_total', 'gt', array( 'value_cart_total' => '0' ) ) ) );
		$this->add_to_cart( 10 );

		$this->assertSame( array( 'bacs', 'cod' ), $this->available_gateways() );
	}

	public function test_draft_condition_is_ignored() {
		$condition = $this->make_condition( Condition::POST_TYPE_PAYMENT, array( 'bacs' ), array( $this->rule( 'cart_total', 'gt', array( 'value_cart_total' => '0' ) ) ) );
		wp_update_post(
			array(
				'ID'          => $condition->get_id(),
				'post_status' => 'draft',
			)
		);
		Plugin::instance()->payment_filter()->flush_conditions();
		$this->add_to_cart( 10 );

		$this->assertContains( 'bacs', $this->available_gateways() );
	}

	public function test_gateways_are_not_filtered_outside_checkout() {
		$this->make_condition( Condition::POST_TYPE_PAYMENT, array( 'bacs' ), array( $this->rule( 'cart_total', 'gt', array( 'value_cart_total' => '0' ) ) ) );
		$this->add_to_cart( 10 );

		$this->assertSame( self::GATEWAYS, Plugin::instance()->payment_filter()->exclude_payment_gateways( self::GATEWAYS ) );
	}
}
