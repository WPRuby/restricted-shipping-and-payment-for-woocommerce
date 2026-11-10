<?php
/**
 * Conditions in the block Cart and Checkout, through the Store API routes they call.
 *
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

use WPRuby\RestrictedShippingPaymentLite\Condition;

class WP_Block_Checkout_Test extends RSPW_Checkout_Test_Case {

	/**
	 * @var string[] Rate IDs of the test zone: flat rate, free shipping.
	 */
	private array $rates;

	private ?string $request_uri = null;

	public function set_up() {
		parent::set_up();

		$this->enable_gateways( array( 'bacs', 'cheque', 'cod' ) );
		$this->rates = $this->make_zone( array( 'flat_rate', 'free_shipping' ) );

		// The Store API recognises its requests by URL; rest_do_request() doesn't set one.
		$this->request_uri      = $_SERVER['REQUEST_URI'] ?? null;
		$_SERVER['REQUEST_URI'] = '/wp-json/wc/store/v1/cart';
		add_filter( 'woocommerce_store_api_disable_nonce_check', '__return_true' );
	}

	public function tear_down() {
		if ( is_null( $this->request_uri ) ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $this->request_uri;
		}
		remove_filter( 'woocommerce_store_api_disable_nonce_check', '__return_true' );
		parent::tear_down();
	}

	public function test_store_api_hides_payment_gateways() {
		$this->make_condition( Condition::POST_TYPE_PAYMENT, array( 'cheque' ), array( $this->rule( 'cart_total', 'gt', array( 'value_cart_total' => '100' ) ) ) );

		$this->add_to_cart( 50 );
		$this->assertSame( array( 'bacs', 'cheque', 'cod' ), $this->get_cart()['payment_methods'] );

		$this->add_to_cart( 100 );
		$this->assertSame( array( 'bacs', 'cod' ), $this->get_cart()['payment_methods'] );
	}

	public function test_store_api_hides_shipping_rates() {
		list( $flat_rate, $free_shipping ) = $this->rates;
		$this->make_condition( Condition::POST_TYPE_SHIPPING, array( $flat_rate ), array( $this->rule( 'cart_total', 'gt', array( 'value_cart_total' => '100' ) ) ) );

		$this->add_to_cart( 50 );
		$this->assertSame( array( $flat_rate, $free_shipping ), $this->rate_ids( $this->get_cart() ) );

		$this->add_to_cart( 100 );
		$this->assertSame( array( $free_shipping ), $this->rate_ids( $this->get_cart() ) );
	}

	/**
	 * The billing country isn't part of the shipping package, but WooCommerce caches rates by package.
	 */
	public function test_billing_country_change_refreshes_cached_shipping_rates() {
		list( $flat_rate, $free_shipping ) = $this->rates;
		$this->make_condition( Condition::POST_TYPE_SHIPPING, array( $free_shipping ), array( $this->rule( 'billing_country', 'is', array( 'value_billing_country' => array( 'CA' ) ) ) ) );
		$this->add_to_cart( 10 );

		$this->assertSame( array( $flat_rate, $free_shipping ), $this->rate_ids( $this->get_cart() ) );

		WC()->customer->set_billing_country( 'CA' );
		$this->assertSame( array( $flat_rate ), $this->rate_ids( $this->get_cart() ) );
	}

	/**
	 * Sessions keep calculated rates; publishing, unpublishing or trashing a condition must still apply to them.
	 */
	public function test_condition_changes_apply_to_cached_rates() {
		list( $flat_rate, $free_shipping ) = $this->rates;
		$this->add_to_cart( 10 );
		$this->assertSame( array( $flat_rate, $free_shipping ), $this->rate_ids( $this->get_cart() ) );

		$condition = $this->make_condition( Condition::POST_TYPE_SHIPPING, array( $flat_rate ), array( $this->rule( 'cart_total', 'gt', array( 'value_cart_total' => '0' ) ) ) );
		$this->assertSame( array( $free_shipping ), $this->rate_ids( $this->get_cart() ) );

		wp_update_post(
			array(
				'ID'          => $condition->get_id(),
				'post_status' => 'draft',
			)
		);
		$this->assertSame( array( $flat_rate, $free_shipping ), $this->rate_ids( $this->get_cart() ) );

		wp_publish_post( $condition->get_id() );
		$this->assertSame( array( $free_shipping ), $this->rate_ids( $this->get_cart() ) );

		wp_trash_post( $condition->get_id() );
		$this->assertSame( array( $flat_rate, $free_shipping ), $this->rate_ids( $this->get_cart() ) );
	}

	private function get_cart(): array {
		$response = rest_do_request( new WP_REST_Request( 'GET', '/wc/store/v1/cart' ) );
		$this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );
		return $response->get_data();
	}

	/**
	 * @return string[]
	 */
	private function rate_ids( array $cart ): array {
		return wp_list_pluck( $cart['shipping_rates'][0]['shipping_rates'], 'rate_id' );
	}
}
