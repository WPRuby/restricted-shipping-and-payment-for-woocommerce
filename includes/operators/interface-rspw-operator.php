<?php


interface RSPW_Operator {

	/**
	 * @param $needle
	 * @param $haystack
	 *
	 * @return boolean
	 */
	public function match( $needle, $haystack);
}
