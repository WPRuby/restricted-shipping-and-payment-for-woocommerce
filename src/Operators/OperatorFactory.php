<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Operators;

final class OperatorFactory {

	public const OPERATOR_IN        = 'in';
	public const OPERATOR_NOT_IN    = 'not_in';
	public const OPERATOR_IS        = 'is';
	public const OPERATOR_IS_NOT    = 'is_not';
	public const OPERATOR_GT        = 'gt';
	public const OPERATOR_LT        = 'lt';
	public const OPERATOR_EQUAL     = 'eq';
	public const OPERATOR_NOT_EQUAL = 'neq';

	private const OPERATORS = array(
		self::OPERATOR_IN        => InOperator::class,
		self::OPERATOR_NOT_IN    => NotInOperator::class,
		self::OPERATOR_IS        => IsOperator::class,
		self::OPERATOR_IS_NOT    => IsNotOperator::class,
		self::OPERATOR_GT        => GreaterThanOperator::class,
		self::OPERATOR_LT        => LessThanOperator::class,
		self::OPERATOR_EQUAL     => EqualOperator::class,
		self::OPERATOR_NOT_EQUAL => NotEqualOperator::class,
	);

	/**
	 * @return Operator An operator that never matches when $operator is unknown.
	 */
	public static function make( string $operator ): Operator {
		$class = self::OPERATORS[ $operator ] ?? NeverOperator::class;
		return new $class();
	}
}
