<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite;

/**
 * Everything rules can check about the current checkout, the same for shipping and payment conditions.
 *
 * Cart data (items, destination, customer, subtotal, coupons) is given when the context is created.
 * Customer data (billing country, email) is read from WooCommerce when asked for, unless it was given
 * in $values, which tests use.
 */
final class Context {

	/**
	 * Cart items: arrays with 'data' (WC_Product) and 'quantity'.
	 */
	private array $items;

	/**
	 * Shipping destination: country, state, postcode, city, address, address_2.
	 */
	private array $destination;

	private int $customer_id;

	private float $subtotal;

	/**
	 * @var string[]
	 */
	private array $applied_coupons;

	/**
	 * Values that would otherwise come from the customer: billing_country, customer_email.
	 */
	private array $values;

	public function __construct( array $items, array $destination = array(), int $customer_id = 0, float $subtotal = 0.0, array $applied_coupons = array(), array $values = array() ) {
		$this->items           = array_filter(
			$items,
			static function ( $item ): bool {
				return is_array( $item ) && isset( $item['data'] ) && $item['data'] instanceof \WC_Product;
			}
		);
		$this->destination     = $destination;
		$this->customer_id     = $customer_id;
		$this->subtotal        = $subtotal;
		$this->applied_coupons = array_map( 'strval', $applied_coupons );
		$this->values          = $values;
	}

	/**
	 * From a WooCommerce shipping package.
	 *
	 * @param array $package contents, destination, user, cart_subtotal, applied_coupons.
	 * @param array $values  See $values.
	 */
	public static function from_package( array $package, array $values = array() ): Context {
		return new self(
			isset( $package['contents'] ) && is_array( $package['contents'] ) ? $package['contents'] : array(),
			isset( $package['destination'] ) && is_array( $package['destination'] ) ? $package['destination'] : array(),
			isset( $package['user']['ID'] ) ? (int) $package['user']['ID'] : 0,
			isset( $package['cart_subtotal'] ) ? (float) $package['cart_subtotal'] : 0.0,
			isset( $package['applied_coupons'] ) && is_array( $package['applied_coupons'] ) ? $package['applied_coupons'] : array(),
			$values
		);
	}

	/**
	 * For the current cart and customer (payment conditions).
	 */
	public static function from_cart(): Context {
		$cart     = WC()->cart;
		$customer = WC()->customer;

		return new self(
			$cart ? $cart->get_cart_contents() : array(),
			array(
				'country'   => $customer ? $customer->get_shipping_country() : '',
				'state'     => $customer ? $customer->get_shipping_state() : '',
				'postcode'  => $customer ? $customer->get_shipping_postcode() : '',
				'city'      => $customer ? $customer->get_shipping_city() : '',
				'address'   => $customer ? $customer->get_shipping_address() : '',
				'address_2' => $customer ? $customer->get_shipping_address_2() : '',
			),
			get_current_user_id(),
			$cart ? (float) $cart->get_displayed_subtotal() : 0.0,
			$cart ? $cart->get_applied_coupons() : array()
		);
	}

	/**
	 * @return array Cart items with 'data' (WC_Product) and 'quantity'.
	 */
	public function get_items(): array {
		return $this->items;
	}

	/**
	 * @return \WC_Product[]
	 */
	public function get_products(): array {
		return array_values( wp_list_pluck( $this->items, 'data' ) );
	}

	/**
	 * @return float Total weight in kg.
	 */
	public function get_total_weight(): float {
		$weight = 0.0;
		foreach ( $this->items as $item ) {
			$weight += (float) $item['data']->get_weight() * (float) $item['quantity'];
		}
		return (float) wc_get_weight( $weight, 'kg' );
	}

	/**
	 * @param string $field country, state, postcode, city, address or address_2.
	 */
	public function get_destination( string $field ): string {
		return isset( $this->destination[ $field ] ) ? (string) $this->destination[ $field ] : '';
	}

	public function get_customer_id(): int {
		return $this->customer_id;
	}

	public function get_subtotal(): float {
		return $this->subtotal;
	}

	/**
	 * @return string[]
	 */
	public function get_applied_coupons(): array {
		return $this->applied_coupons;
	}

	/**
	 * The account email, or for guests the billing email entered at checkout.
	 */
	public function get_customer_email(): string {
		if ( array_key_exists( 'customer_email', $this->values ) ) {
			return (string) $this->values['customer_email'];
		}

		$user = $this->customer_id > 0 ? get_userdata( $this->customer_id ) : false;
		if ( $user ) {
			return (string) $user->user_email;
		}

		return WC()->customer ? (string) WC()->customer->get_billing_email() : '';
	}

	public function get_billing_country(): string {
		if ( array_key_exists( 'billing_country', $this->values ) ) {
			return (string) $this->values['billing_country'];
		}
		return WC()->customer ? (string) WC()->customer->get_billing_country() : '';
	}
}
