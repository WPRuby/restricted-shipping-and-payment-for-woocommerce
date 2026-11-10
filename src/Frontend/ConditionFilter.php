<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Frontend;

use WPRuby\RestrictedShippingPaymentLite\Condition;

/**
 * Applies the enabled conditions of one post type (shipping or payment).
 */
abstract class ConditionFilter {

	/**
	 * Enabled conditions, loaded once per request.
	 *
	 * @var Condition[]|null
	 */
	private ?array $conditions = null;

	abstract public function register(): void;

	/**
	 * @return string Condition::POST_TYPE_SHIPPING or Condition::POST_TYPE_PAYMENT.
	 */
	abstract protected function get_post_type(): string;

	/**
	 * Enabled (published) conditions.
	 *
	 * @return Condition[]
	 */
	public function get_conditions(): array {
		if ( is_null( $this->conditions ) ) {
			$posts = get_posts(
				array(
					'post_type'        => $this->get_post_type(),
					'post_status'      => 'publish',
					'posts_per_page'   => -1,
					'suppress_filters' => true,
				)
			);

			$this->conditions = array_map(
				static function ( \WP_Post $post ): Condition {
					return new Condition( $post );
				},
				$posts
			);
		}

		return $this->conditions;
	}

	/**
	 * Forget the loaded conditions, e.g. after one was saved.
	 */
	public function flush_conditions(): void {
		$this->conditions = null;
	}

	/**
	 * Does any enabled condition use the given rule type?
	 */
	public function conditions_use_rule( string $rule_type ): bool {
		foreach ( $this->get_conditions() as $condition ) {
			if ( in_array( $rule_type, wp_list_pluck( $condition->get_rules(), 'rule_type' ), true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Store API requests come from the block Cart and Checkout.
	 */
	protected function is_store_api_request(): bool {
		return method_exists( WC(), 'is_store_api_request' ) && WC()->is_store_api_request();
	}
}
