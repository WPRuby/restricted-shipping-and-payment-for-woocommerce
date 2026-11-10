<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Rules;

/**
 * The available rule types.
 */
final class RuleFactory {

	public const RULE_SHIPPING_CLASS   = 'shipping_class';
	public const RULE_COUPON_CODE      = 'coupon_code';
	public const RULE_CART_TOTAL       = 'cart_total';
	public const RULE_PACKAGE_WEIGHT   = 'package_weight';
	public const RULE_SHIPPING_COUNTRY = 'shipping_country';
	public const RULE_BILLING_COUNTRY  = 'billing_country';
	public const RULE_CUSTOMER         = 'customer';

	/**
	 * @var array<string, Rule|null> Rule instances by type, for this request.
	 */
	private static array $instances = array();

	public static function make( string $rule ): ?Rule {
		if ( ! array_key_exists( $rule, self::$instances ) ) {
			$rules = self::available_rules();
			$class = $rules[ $rule ]['class'] ?? '';

			self::$instances[ $rule ] = '' !== $class ? new $class() : null;
		}

		return self::$instances[ $rule ];
	}

	public static function get_label( string $rule ): string {
		$rules = self::available_rules();
		return (string) ( $rules[ $rule ]['label'] ?? $rule );
	}

	/**
	 * @return array<string, array{class: class-string<Rule>, label: string}> rule type => class and label
	 */
	public static function available_rules(): array {
		return array(
			self::RULE_SHIPPING_CLASS   => array(
				'class' => ShippingClassRule::class,
				'label' => __( 'Shipping Class', 'restricted-shipping-and-payment-for-woocommerce' ),
			),
			self::RULE_COUPON_CODE      => array(
				'class' => CouponCodeRule::class,
				'label' => __( 'Coupon Code', 'restricted-shipping-and-payment-for-woocommerce' ),
			),
			self::RULE_CART_TOTAL       => array(
				'class' => CartTotalRule::class,
				'label' => __( 'Cart Total', 'restricted-shipping-and-payment-for-woocommerce' ),
			),
			self::RULE_PACKAGE_WEIGHT   => array(
				'class' => PackageWeightRule::class,
				'label' => __( 'Package Weight', 'restricted-shipping-and-payment-for-woocommerce' ),
			),
			self::RULE_SHIPPING_COUNTRY => array(
				'class' => ShippingCountryRule::class,
				'label' => __( 'Shipping Country', 'restricted-shipping-and-payment-for-woocommerce' ),
			),
			self::RULE_BILLING_COUNTRY  => array(
				'class' => BillingCountryRule::class,
				'label' => __( 'Billing Country', 'restricted-shipping-and-payment-for-woocommerce' ),
			),
			self::RULE_CUSTOMER         => array(
				'class' => CustomerRule::class,
				'label' => __( 'Customer', 'restricted-shipping-and-payment-for-woocommerce' ),
			),
		);
	}
}
