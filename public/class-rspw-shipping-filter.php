<?php
/**
 * The class to actually restrict the shipping methods
 *
 * @link       https://wpruby.com
 * @since      1.0.0
 *
 * @package    Restricted_Shipping_And_Payment_For_Woocommerce
 * @subpackage Restricted_Shipping_And_Payment_For_Woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Restricted_Shipping_And_Payment_For_Woocommerce
 * @subpackage Restricted_Shipping_And_Payment_For_Woocommerce/public
 * @author     WPRuby <info@wpruby.com>
 */
class RSPW_Shipping_Filter {

	/**
	 * RSPW_Shipping_Filter constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_package_rates', array( $this, 'exclude_package_shipping_methods' ), 10, 2 );
	}

	/**
	 * @param $rates
	 * @param $package
	 *
	 * @return mixed
	 */
	public function exclude_package_shipping_methods( $rates, $package ) {
		$conditions = $this->get_conditions();

		foreach ( $rates as $key => $rate ) {
			foreach ( $conditions as $condition ) {
				if ( ! $this->is_rate_from_condition_method( $rate, $condition ) ) {
					continue;
				}
				if ( $this->is_condition_match( $condition, $package ) ) {
					unset( $rates[ $key ] );
				}
			}
		}
		return $rates;
	}

	/**
	 * @param $rate
	 * @param $condition
	 *
	 * @return bool
	 */
	private function is_rate_from_condition_method( $rate, $condition ) {
		/** @var  WC_Shipping_Rate $rate */
		$method_id                  = $rate->get_method_id();
		$instance_id                = $rate->get_instance_id();
		$rate_id                    = sprintf( '%s:%s', $method_id, $instance_id );
		$condition_shipping_methods = get_post_meta( $condition->ID, 'excluded_shipping_methods', true );
		if ( is_array( $condition_shipping_methods ) ) {
			if ( in_array( $rate_id, $condition_shipping_methods, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function get_conditions() {
		$conditions = get_posts(
			array(
				'post_type'      => RSPW_SHIPPING_CONDITION,
				'posts_per_page' => -1,
			)
		);
		return $conditions;
	}

	/**
	 * @param $condition
	 * @param $package
	 *
	 * @return bool|string
	 */
	private function is_condition_match( $condition, $package ) {
		$rules = $this->get_rules( $condition );
		if ( ! is_array( $rules ) ) {
			return false;
		}

		foreach ( $rules as $rule ) {
			$rule_type      = $rule['rule_type'];
			$rule_validator = RSPW_Rules_Factory::make( $rule_type );
			if ( $rule_validator->validate( $rule, $package ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $condition
	 *
	 * @return mixed
	 */
	private function get_rules( $condition ) {
		/** @var WP_Post $condition */
		$rules = get_post_meta( $condition->ID, 'shipping_condition_rules', true );
		return $rules;
	}
}

new RSPW_Shipping_Filter();
