<?php


class RSPW_Operators_Factory {

	const OPERATOR_IN     = 'in';
	const OPERATOR_NOT_IN = 'not_in';
	const OPERATOR_IS     = 'is';
	const OPERATOR_IS_NOT = 'is_not';
	const OPERATOR_GT     = 'gt';
	const OPERATOR_LT     = 'lt';
	const OPERATOR_EQUAL  = 'eq';
	const OPERATOR_NOT_EQUAL  = 'neq';

	/**
	 * @param string $operator
	 *
	 * @return null|RSPW_Operator
	 */
	public static function make( $operator ) {
		$available_operators = self::available_operators();

		if ( isset( $available_operators[ $operator ] ) ) {
			return new $available_operators[ $operator ]();
		}

		return null;
	}

	public static function available_operators() {
		return array(
			self::OPERATOR_IN     => RSPW_In_Operator::class,
			self::OPERATOR_NOT_IN => RSPW_Not_In_Operator::class,
			self::OPERATOR_IS     => RSPW_Is_Operator::class,
			self::OPERATOR_IS_NOT => RSPW_Is_Not_Operator::class,
			self::OPERATOR_GT     => RSPW_Greater_Than_Operator::class,
			self::OPERATOR_LT     => RSPW_Less_Than_Operator::class,
			self::OPERATOR_EQUAL  => RSPW_Equal_Operator::class,
			self::OPERATOR_NOT_EQUAL  => RSPW_Not_Equal_Operator::class,
		);
	}
}
