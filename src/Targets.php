<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite;

/**
 * The shipping method instances and payment gateways a condition can hide (or a rule can check).
 */
final class Targets {

	/**
	 * @var array<string, array<string, string>> context => id => label
	 */
	private static array $cache = array();

	/**
	 * Forget the lists loaded during this request, e.g. after shipping zones changed.
	 */
	public static function reset(): void {
		self::$cache = array();
	}

	/**
	 * @param string $context Condition::CONTEXT_SHIPPING or Condition::CONTEXT_PAYMENT.
	 *
	 * @return array<string, string> id => label
	 */
	public static function for_context( string $context ): array {
		return Condition::CONTEXT_PAYMENT === $context ? self::payment_gateways() : self::shipping_methods();
	}

	/**
	 * Shipping method instances of every zone, e.g. "flat_rate:3" => "Europe – "Flat rate" (Instance ID: 3)".
	 *
	 * @return array<string, string> rate ID => label
	 */
	public static function shipping_methods(): array {
		if ( isset( self::$cache['shipping'] ) ) {
			return self::$cache['shipping'];
		}

		$methods = array();
		$zones   = \WC_Shipping_Zones::get_zones();

		if ( ! isset( $zones[0] ) ) {
			$rest_of_world                = \WC_Shipping_Zones::get_zone_by();
			$zones[0]                     = $rest_of_world->get_data();
			$zones[0]['shipping_methods'] = $rest_of_world->get_shipping_methods();
		}

		foreach ( $zones as $zone ) {
			if ( empty( $zone['shipping_methods'] ) ) {
				continue;
			}

			foreach ( $zone['shipping_methods'] as $instance_id => $method ) {
				/* translators: 1: shipping method title, 2: instance ID. */
				$method_title = sprintf( __( '"%1$s" (Instance ID: %2$s)', 'restricted-shipping-and-payment-for-woocommerce' ), $method->get_title(), $instance_id );
				/* translators: 1: zone name, 2: method title. */
				$methods[ $method->get_rate_id() ] = sprintf( __( '%1$s – %2$s', 'restricted-shipping-and-payment-for-woocommerce' ), $zone['zone_name'], $method_title );
			}
		}

		self::$cache['shipping'] = $methods;
		return $methods;
	}

	/**
	 * @return array<string, string> gateway ID => title
	 */
	public static function payment_gateways(): array {
		if ( isset( self::$cache['payment'] ) ) {
			return self::$cache['payment'];
		}

		$gateways = array();
		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			$gateways[ $gateway->id ] = wp_strip_all_tags( $gateway->get_title() );
		}
		unset( $gateways['pre_install_woocommerce_payments_promotion'] );

		self::$cache['payment'] = $gateways;
		return $gateways;
	}
}
