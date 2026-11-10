<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Rules;

use WPRuby\RestrictedShippingPaymentLite\Operators\Operator;
use WPRuby\RestrictedShippingPaymentLite\Operators\OperatorFactory;
use WPRuby\RestrictedShippingPaymentLite\RuleSummary;

/**
 * Base class for rules. A rule describes its value fields with get_value_schema(); options,
 * sanitizing and summaries are derived from that schema.
 *
 * Field types: multiselect, text, email, number, money.
 * A field has: key (the rule array key, e.g. value_cart_total), type, label, and optionally
 * options (value => label), async (options are searched through the REST API), unit, help.
 */
abstract class AbstractRule implements Rule {

	/**
	 * The operator chosen for the rule.
	 */
	protected function operator( array $rule ): Operator {
		return OperatorFactory::make( (string) ( $rule['operator'] ?? '' ) );
	}

	/**
	 * Options for a field: all of them, the ones matching $search, or only the $include values.
	 * Searchable (async) fields override get_async_options().
	 *
	 * @return array<int|string, string> value => label (numeric values become int keys)
	 */
	public function get_options( string $field_key, string $search = '', array $include = array() ): array {
		$field = $this->get_field( $field_key );
		if ( ! $field ) {
			return array();
		}

		$async   = ! empty( $field['async'] );
		$options = $async ? $this->get_async_options( $field_key, $search, $include ) : ( $field['options'] ?? array() );

		if ( ! empty( $include ) ) {
			$options = array_intersect_key( $options, array_flip( array_map( 'strval', $include ) ) );
		}

		if ( '' !== $search && ! $async ) {
			$options = array_filter(
				$options,
				static function ( $label ) use ( $search ): bool {
					return false !== stripos( (string) $label, $search );
				}
			);
		}

		return $options;
	}

	/**
	 * @return array<int|string, string> value => label
	 */
	protected function get_async_options( string $field_key, string $search, array $include ): array {
		return array();
	}

	/**
	 * Clean a rule submitted by the editor. Unknown keys are dropped, unknown operators replaced.
	 */
	public function sanitize( array $rule ): array {
		$operators = array_keys( $this->get_operators_labels() );
		$operator  = (string) ( $rule['operator'] ?? '' );

		$clean = array(
			'rule_type' => sanitize_key( (string) ( $rule['rule_type'] ?? '' ) ),
			'operator'  => in_array( $operator, $operators, true ) ? $operator : (string) reset( $operators ),
		);

		foreach ( $this->get_value_schema() as $field ) {
			$clean[ $field['key'] ] = $this->sanitize_field( $field, $rule[ $field['key'] ] ?? null );
		}

		return $clean;
	}

	/**
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function sanitize_field( array $field, $value ) {
		switch ( $field['type'] ) {
			case 'multiselect':
				$values = array_map( 'sanitize_text_field', array_map( 'strval', (array) $value ) );
				$values = array_values( RuleSummary::without_empty( array_unique( $values ) ) );
				if ( empty( $field['async'] ) && isset( $field['options'] ) ) {
					$values = array_values( array_intersect( $values, array_map( 'strval', array_keys( $field['options'] ) ) ) );
				}
				return $values;

			case 'email':
				return sanitize_email( (string) $value );

			case 'number':
			case 'money':
				$value = wc_format_decimal( (string) $value );
				return '' === $value ? '' : (string) max( 0, (float) $value );

			default:
				return sanitize_text_field( (string) $value );
		}
	}

	public function get_value_summary( array $rule ): string {
		$parts = array();

		foreach ( $this->get_value_schema() as $field ) {
			$value = $rule[ $field['key'] ] ?? '';

			switch ( $field['type'] ) {
				case 'multiselect':
					$values  = array_map( 'strval', (array) $value );
					$labels  = $this->get_options( $field['key'], '', $values );
					$parts[] = RuleSummary::shorten_list(
						array_map(
							static function ( string $v ) use ( $labels ): string {
								return (string) ( $labels[ $v ] ?? $v );
							},
							$values
						)
					);
					break;

				case 'money':
					$parts[] = '' === $value ? '' : html_entity_decode( get_woocommerce_currency_symbol() ) . $value;
					break;

				default:
					$unit    = isset( $field['unit'] ) && '' !== $value ? ' ' . $field['unit'] : '';
					$parts[] = is_scalar( $value ) ? trim( $value . $unit ) : '';
			}
		}

		return implode( ' = ', RuleSummary::without_empty( $parts ) );
	}

	protected function get_field( string $field_key ): ?array {
		foreach ( $this->get_value_schema() as $field ) {
			if ( $field['key'] === $field_key ) {
				return $field;
			}
		}
		return null;
	}
}
