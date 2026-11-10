<?php
/**
 * Base test case with a real WooCommerce cart, customer and session.
 *
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

use WPRuby\RestrictedShippingPaymentLite\Condition;
use WPRuby\RestrictedShippingPaymentLite\Plugin;
use WPRuby\RestrictedShippingPaymentLite\Targets;

abstract class RSPW_Checkout_Test_Case extends WP_UnitTestCase {

	/**
	 * Gateways passed to the payment filter; the values don't matter.
	 */
	protected const GATEWAYS = array(
		'bacs'   => 'Direct bank transfer',
		'cheque' => 'Check payments',
		'cod'    => 'Cash on delivery',
	);

	public function set_up() {
		parent::set_up();

		wc_load_cart();
		WC()->cart->empty_cart();
		WC()->customer->set_shipping_location( 'US', 'CA', '90001', 'Los Angeles' );
		WC()->customer->set_billing_location( 'US', 'CA', '90001', 'Los Angeles' );

		Plugin::instance()->shipping_filter()->flush_conditions();
		Plugin::instance()->payment_filter()->flush_conditions();
		Targets::reset();
	}

	public function tear_down() {
		remove_all_filters( 'woocommerce_is_checkout' );
		WC()->cart->empty_cart();
		parent::tear_down();
	}

	/**
	 * @param float $price
	 * @param int   $quantity
	 */
	protected function add_to_cart( $price, $quantity = 1 ) {
		$product = RSPW_Tests_Helper::add_product( array( 'price' => $price ) );
		$product->set_regular_price( $price );
		$product->save();

		WC()->cart->add_to_cart( $product->get_id(), $quantity );
		WC()->cart->calculate_totals();
	}

	/**
	 * A shipping zone for the cart's address (US) with the given methods.
	 *
	 * @param string[] $method_ids e.g. flat_rate, free_shipping.
	 *
	 * @return string[] Rate IDs, e.g. flat_rate:3.
	 */
	protected function make_zone( array $method_ids ): array {
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'United States' );
		$zone->add_location( 'US', 'country' );
		$zone->save();

		$rate_ids = array();
		foreach ( $method_ids as $method_id ) {
			$rate_ids[] = $method_id . ':' . $zone->add_shipping_method( $method_id );
		}

		Targets::reset();
		WC_Cache_Helper::get_transient_version( 'shipping', true );
		return $rate_ids;
	}

	/**
	 * Enable WooCommerce gateways, so they are available at checkout.
	 *
	 * @param string[] $gateway_ids
	 */
	protected function enable_gateways( array $gateway_ids ) {
		foreach ( $gateway_ids as $gateway_id ) {
			update_option( "woocommerce_{$gateway_id}_settings", array( 'enabled' => 'yes' ) );
		}
		WC()->payment_gateways()->init();
		Targets::reset();
	}

	/**
	 * Create an enabled condition saved through Condition::save(), as the editor does.
	 *
	 * @param string $post_type Condition::POST_TYPE_SHIPPING or Condition::POST_TYPE_PAYMENT.
	 * @param array  $targets   Rate IDs or gateway IDs to hide.
	 * @param array  $rules     Rules; any of them hides the targets.
	 */
	protected function make_condition( $post_type, array $targets, array $rules ): Condition {
		$id = self::factory()->post->create(
			array(
				'post_type'   => $post_type,
				'post_status' => 'publish',
			)
		);

		$condition = Condition::get( $id );
		$condition->save(
			array(
				'rules'   => $rules,
				'targets' => $targets,
			)
		);

		Plugin::instance()->shipping_filter()->flush_conditions();
		Plugin::instance()->payment_filter()->flush_conditions();

		return Condition::get( $id );
	}

	/**
	 * Gateway IDs left by the payment filter on the checkout page.
	 *
	 * @return string[]
	 */
	protected function available_gateways(): array {
		add_filter( 'woocommerce_is_checkout', '__return_true' );
		return array_keys( Plugin::instance()->payment_filter()->exclude_payment_gateways( self::GATEWAYS ) );
	}

	/**
	 * @param string $type
	 * @param string $operator
	 * @param array  $values value_… fields.
	 */
	protected function rule( $type, $operator, array $values ): array {
		return array_merge(
			array(
				'rule_type' => $type,
				'operator'  => $operator,
			),
			$values
		);
	}
}
