<?php
/**
 * Tests for Condition: matching, saving and conditions saved by 1.x.
 *
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

use WPRuby\RestrictedShippingPaymentLite\Condition;
use WPRuby\RestrictedShippingPaymentLite\Context;
use WPRuby\RestrictedShippingPaymentLite\RuleSummary;

class WP_Condition_Test extends WP_UnitTestCase {

	public function test_any_rule_matches() {
		$condition = $this->make_condition( array( $this->country_rule( 'US' ), $this->total_rule( 100 ) ) );

		$this->assertTrue( $condition->matches( $this->get_context( 'US', 50 ) ) );
		$this->assertTrue( $condition->matches( $this->get_context( 'GB', 150 ) ) );
		$this->assertFalse( $condition->matches( $this->get_context( 'GB', 50 ) ) );
	}

	public function test_condition_without_rules_never_matches() {
		$this->assertFalse( $this->make_condition( array() )->matches( $this->get_context( 'US', 50 ) ) );
	}

	public function test_unknown_rules_never_match() {
		$condition = $this->make_condition(
			array(
				array(
					'rule_type' => 'postcode',
					'operator'  => 'is',
				),
				array( 'operator' => 'is' ),
			)
		);

		$this->assertFalse( $condition->matches( $this->get_context( 'US', 50 ) ) );
	}

	/**
	 * 1.x saved the fields of every rule type with each rule (CMB2 group fields).
	 */
	public function test_reads_rules_saved_by_1x() {
		$id = self::factory()->post->create( array( 'post_type' => Condition::POST_TYPE_SHIPPING ) );
		update_post_meta(
			$id,
			'shipping_condition_rules',
			array(
				array(
					'rule_type'              => 'cart_total',
					'operator'               => 'gt',
					'value_shipping_class'   => array(),
					'value_cart_total'       => '100',
					'value_package_weight'   => '',
					'value_shipping_country' => array(),
					'value_customer'         => '',
				),
			)
		);
		update_post_meta( $id, 'excluded_shipping_methods', array( 'flat_rate:1' ) );

		$condition = Condition::get( $id );

		$this->assertSame( array( 'flat_rate:1' ), $condition->get_targets() );
		$this->assertTrue( $condition->matches( $this->get_context( 'US', 150 ) ) );
		$this->assertFalse( $condition->matches( $this->get_context( 'US', 50 ) ) );
		$this->assertSame( array( 'Cart Total · more than · $100' ), RuleSummary::condition( $condition ) );
	}

	public function test_save_keeps_the_1x_format() {
		$id        = self::factory()->post->create( array( 'post_type' => Condition::POST_TYPE_PAYMENT ) );
		$condition = Condition::get( $id );

		$condition->save(
			array(
				'targets' => array( 'cod', 'cod', '' ),
				'rules'   => array(
					array(
						'rule_type'             => 'billing_country',
						'operator'              => 'nope',
						'value_billing_country' => array( 'US', 'XX' ),
						'value_cart_total'      => '5',
					),
					array(
						'rule_type' => 'postcode',
						'operator'  => 'is',
					),
					array(
						'rule_type'        => 'cart_total',
						'operator'         => 'lt',
						'value_cart_total' => '-3',
					),
					'not a rule',
				),
			)
		);

		$this->assertSame( array( 'cod' ), get_post_meta( $id, 'excluded_payment_gateways', true ) );
		$this->assertSame(
			array(
				array(
					'rule_type'             => 'billing_country',
					'operator'              => 'is',
					'value_billing_country' => array( 'US' ),
				),
				array(
					'rule_type'        => 'cart_total',
					'operator'         => 'lt',
					'value_cart_total' => '0',
				),
			),
			get_post_meta( $id, 'payment_condition_rules', true )
		);
		$this->assertSame( get_post_meta( $id, 'payment_condition_rules', true ), $condition->get_rules() );
	}

	public function test_get_returns_null_for_other_posts() {
		$this->assertNull( Condition::get( self::factory()->post->create() ) );
		$this->assertNull( Condition::get( 0 ) );
	}

	public function test_conditions_are_admin_only() {
		$post_type = get_post_type_object( Condition::POST_TYPE_SHIPPING );

		$this->assertFalse( $post_type->public );
		$this->assertFalse( $post_type->show_in_rest );
		$this->assertSame( 'manage_woocommerce', $post_type->cap->edit_posts );
	}

	private function make_condition( array $rules ): Condition {
		$id = self::factory()->post->create( array( 'post_type' => Condition::POST_TYPE_SHIPPING ) );
		update_post_meta( $id, 'shipping_condition_rules', $rules );
		return Condition::get( $id );
	}

	private function get_context( string $country, float $subtotal ): Context {
		$product = RSPW_Tests_Helper::add_product();
		return Context::from_package(
			array(
				'contents'      => array(
					array(
						'data'     => $product,
						'quantity' => 1,
					),
				),
				'destination'   => array( 'country' => $country ),
				'cart_subtotal' => $subtotal,
			)
		);
	}

	private function country_rule( string $country ): array {
		return array(
			'rule_type'              => 'shipping_country',
			'operator'               => 'is',
			'value_shipping_country' => array( $country ),
		);
	}

	private function total_rule( float $total ): array {
		return array(
			'rule_type'        => 'cart_total',
			'operator'         => 'gt',
			'value_cart_total' => (string) $total,
		);
	}
}
