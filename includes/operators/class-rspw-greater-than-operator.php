<?php


class RSPW_Greater_Than_Operator implements RSPW_Operator {


	/**
	 * @param $needle
	 * @param $haystack
	 *
	 * @return boolean
	 */
	public function match( $needle, $haystack ) {
		return $needle >= $haystack;
	}
}
