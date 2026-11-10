<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Rules;

use WPRuby\RestrictedShippingPaymentLite\Context;
use WPRuby\RestrictedShippingPaymentLite\Operators\OperatorFactory;

/**
 * One of the coupons is applied.
 */
final class CouponCodeRule extends AbstractRule {

	public function validate( array $rule, Context $context ): bool {
		$applied = $context->get_applied_coupons();
		if ( empty( $applied ) || empty( $rule['value_coupon_code'] ) || ! is_array( $rule['value_coupon_code'] ) ) {
			return false;
		}

		return $this->operator( $rule )->match( array_map( 'strtolower', $rule['value_coupon_code'] ), $applied );
	}

	public function get_operators_labels(): array {
		return array(
			OperatorFactory::OPERATOR_IN => __( 'included in the order', 'restricted-shipping-and-payment-for-woocommerce' ),
			OperatorFactory::OPERATOR_NOT_IN => __( 'not included in the order', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	public function get_value_schema(): array {
		return array(
			array(
				'key'   => 'value_coupon_code',
				'type'  => 'multiselect',
				'label' => __( 'Coupon codes', 'restricted-shipping-and-payment-for-woocommerce' ),
				'async' => true,
			),
		);
	}

	/**
	 * Coupon codes are their own labels.
	 *
	 * @inheritDoc
	 */
	protected function get_async_options( string $field_key, string $search, array $include ): array {
		if ( ! empty( $include ) ) {
			$include = array_map( 'strval', $include );
			return array_combine( $include, $include );
		}

		$codes   = array();
		$coupons = get_posts(
			array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'title',
				'order'          => 'asc',
				's'              => $search,
			)
		);
		foreach ( $coupons as $coupon ) {
			$codes[ $coupon->post_title ] = $coupon->post_title;
		}
		return $codes;
	}
}
