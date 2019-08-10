<?php
/**
 * Class Is_Operator
 */
class RSPW_Is_Operator implements RSPW_Operator {

	/**
	 * @param $needle
	 * @param $haystack
	 *
	 * @return boolean
	 */
	public function match( $needle, $haystack ) {
		if ( is_array( $haystack ) ) {
			return in_array( $needle, $haystack, true );
		}
		return $needle === $haystack;
	}
}
