<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Frontend;

use WPRuby\RestrictedShippingPaymentLite\Condition;
use WPRuby\RestrictedShippingPaymentLite\Context;
use WPRuby\RestrictedShippingPaymentLite\Rules\RuleFactory;

/**
 * Removes the shipping rates of the methods hidden by shipping conditions.
 */
final class ShippingFilter extends ConditionFilter {

	public function register(): void {
		add_filter( 'woocommerce_package_rates', array( $this, 'exclude_package_shipping_methods' ), 10, 2 );
		add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'add_rate_cache_inputs' ) );
	}

	protected function get_post_type(): string {
		return Condition::POST_TYPE_SHIPPING;
	}

	/**
	 * WooCommerce caches rates per package hash and only runs woocommerce_package_rates when the hash changes.
	 * Add the inputs of rules that do not come from the package, so a change re-runs the conditions.
	 *
	 * @param array $packages
	 *
	 * @return array
	 */
	public function add_rate_cache_inputs( $packages ) {
		if ( ! is_array( $packages ) ) {
			return $packages;
		}

		$inputs = array();

		// Guests are identified by their billing email, which isn't part of the package.
		if ( WC()->customer && $this->conditions_use_rule( RuleFactory::RULE_CUSTOMER ) ) {
			$inputs['email'] = WC()->customer->get_billing_email();
		}

		if ( WC()->customer && $this->conditions_use_rule( RuleFactory::RULE_BILLING_COUNTRY ) ) {
			$inputs['billing_country'] = WC()->customer->get_billing_country();
		}

		if ( empty( $inputs ) ) {
			return $packages;
		}

		foreach ( $packages as $index => $package ) {
			$packages[ $index ]['rspw'] = $inputs;
		}

		return $packages;
	}

	/**
	 * @param \WC_Shipping_Rate[] $rates
	 * @param array               $package
	 *
	 * @return array
	 */
	public function exclude_package_shipping_methods( $rates, $package ) {
		if ( ! is_array( $rates ) || ! is_array( $package ) ) {
			return $rates;
		}

		$context = Context::from_package( $package );

		foreach ( $this->get_conditions() as $condition ) {
			$rate_keys = $this->get_condition_rate_keys( $rates, $condition );
			if ( empty( $rate_keys ) || ! $condition->matches( $context ) ) {
				continue;
			}

			foreach ( $rate_keys as $key ) {
				unset( $rates[ $key ] );
			}
		}

		return $rates;
	}

	/**
	 * Keys of the rates that belong to the methods a condition hides.
	 *
	 * @param \WC_Shipping_Rate[] $rates
	 *
	 * @return string[]
	 */
	private function get_condition_rate_keys( array $rates, Condition $condition ): array {
		$targets = $condition->get_targets();
		$keys    = array();

		foreach ( $rates as $key => $rate ) {
			if ( $rate instanceof \WC_Shipping_Rate && in_array( $rate->get_method_id() . ':' . $rate->get_instance_id(), $targets, true ) ) {
				$keys[] = (string) $key;
			}
		}

		return $keys;
	}
}
