<?php


class RSPW_In_Operator implements RSPW_Operator {


	/**
	 * @param $needle
	 * @param $haystack
	 *
	 * @return boolean
	 */
	public function match( $needle, $haystack ) {
		return ! empty( array_intersect( $needle, $haystack ) );
	}
}
