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
class RSPW_Payment_Filter {

	/**
	 * RSPW_Payment_Filter constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'exclude_payment_gateways' ) );
	}

	/**
	 * Filter payment gateways when restrictions apply.
	 *
	 * @param  array   $gateways
	 * @param  boolean $bypass
	 * @return array
	 */
	public function exclude_payment_gateways( $gateways, $bypass = false ) {
		if ( ! $bypass && ! is_checkout() && ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			return $gateways;
		}

		if ( is_checkout() ) {
			$package    = ( isset( WC()->cart->get_shipping_packages()[0] ) ) ? WC()->cart->get_shipping_packages()[0] : array();
			$conditions = $this->get_conditions();
			foreach ( $conditions as $condition ) {
				foreach ( $gateways as $gateway_id => $gateway ) {
					if ( in_array( $gateway_id, $this->get_gateways( $condition ), true ) ) {
						if ( $this->is_condition_match( $condition, $package ) ) {
							unset( $gateways[ $gateway_id ] );
						}
					}
				}
			}
		}

		return $gateways;
	}


	/**
	 * @return array
	 */
	public function get_conditions() {
		$conditions = get_posts(
			array(
				'post_type'      => RSPW_PAYMENT_CONDITION,
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

			return $rule_validator->validate( $rule, $package );
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
		$rules = get_post_meta( $condition->ID, 'payment_condition_rules', true );
		return $rules;
	}

	/**
	 * @param $condition
	 *
	 * @return mixed
	 */
	private function get_gateways( $condition ) {
		/** @var WP_Post $condition */
		$gateways = get_post_meta( $condition->ID, 'excluded_payment_gateways', true );
		return $gateways;
	}



	private function get_number_of_completed_orders_by_user( $package ) {
		$current_user        = intval( $package['user']['ID'] );
		$number_of_orders    = wc_get_customer_order_count( $current_user );
		$args                = array(
			'customer_id' => $current_user,
			'post_status' => 'cancelled',
			'post_type'   => 'shop_order',
			'return'      => 'ids',
		);
		$numorders_cancelled = count( wc_get_orders( $args ) );
		$num_not_cancelled   = $number_of_orders - $numorders_cancelled;
		return $num_not_cancelled;
	}
}


new RSPW_Payment_Filter();
