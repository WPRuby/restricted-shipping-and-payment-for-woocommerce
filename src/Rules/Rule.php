<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Rules;

use WPRuby\RestrictedShippingPaymentLite\Context;

/**
 * A rule type, e.g. Cart Total. Rule types extend AbstractRule and are listed in RuleFactory.
 */
interface Rule {

	/**
	 * @param array $rule rule_type, operator and the value_… fields of get_value_schema().
	 */
	public function validate( array $rule, Context $context ): bool;

	/**
	 * @return array<string, string> operator (OperatorFactory::OPERATOR_…) => label
	 */
	public function get_operators_labels(): array;

	/**
	 * Value fields shown in the condition editor. See AbstractRule.
	 */
	public function get_value_schema(): array;

	/**
	 * @return array<int|string, string> value => label
	 */
	public function get_options( string $field_key, string $search = '', array $include = array() ): array;

	/**
	 * Clean a rule submitted by the condition editor.
	 */
	public function sanitize( array $rule ): array;

	/**
	 * Human readable value, e.g. "France, Germany" or "$100".
	 */
	public function get_value_summary( array $rule ): string;
}
