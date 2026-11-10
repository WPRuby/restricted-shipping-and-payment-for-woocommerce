<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite;

use WPRuby\RestrictedShippingPaymentLite\Rules\RuleFactory;

/**
 * Plain-text summaries of rules and conditions, e.g. "Cart Total · more than · $100".
 */
final class RuleSummary {

	public static function rule( array $rule ): string {
		$type      = isset( $rule['rule_type'] ) ? (string) $rule['rule_type'] : '';
		$validator = RuleFactory::make( $type );

		if ( is_null( $validator ) ) {
			/* translators: %s: rule type. */
			return sprintf( __( 'Unknown rule "%s"', 'restricted-shipping-and-payment-for-woocommerce' ), $type );
		}

		$operators = $validator->get_operators_labels();
		$operator  = isset( $rule['operator'], $operators[ $rule['operator'] ] ) ? $operators[ $rule['operator'] ] : '';

		return implode( ' · ', self::without_empty( array( RuleFactory::get_label( $type ), $operator, $validator->get_value_summary( $rule ) ) ) );
	}

	/**
	 * One line per rule.
	 *
	 * @return string[]
	 */
	public static function condition( Condition $condition ): array {
		return array_map( array( self::class, 'rule' ), $condition->get_rules() );
	}

	/**
	 * Labels of the methods or gateways a condition hides.
	 *
	 * @return string[]
	 */
	public static function targets( Condition $condition ): array {
		$all = Targets::for_context( $condition->get_context() );
		return array_map(
			static function ( string $id ) use ( $all ): string {
				return $all[ $id ] ?? $id;
			},
			$condition->get_targets()
		);
	}

	/**
	 * Drop empty values, e.g. empty summary parts.
	 *
	 * @param array<int|string, mixed> $values
	 *
	 * @return array<int|string, mixed>
	 */
	public static function without_empty( array $values ): array {
		return array_filter(
			$values,
			static function ( $value ): bool {
				return '' !== (string) $value;
			}
		);
	}

	/**
	 * "A, B, C +2 more"
	 *
	 * @param string[] $names
	 */
	public static function shorten_list( array $names ): string {
		$names = array_values( self::without_empty( $names ) );
		if ( count( $names ) > 3 ) {
			/* translators: %d: number of other values. */
			return implode( ', ', array_slice( $names, 0, 3 ) ) . ' ' . sprintf( __( '+%d more', 'restricted-shipping-and-payment-for-woocommerce' ), count( $names ) - 3 );
		}
		return implode( ', ', $names );
	}
}
