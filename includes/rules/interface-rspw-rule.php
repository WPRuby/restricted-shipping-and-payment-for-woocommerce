<?php


interface RSPW_Rule {
	/**
	 * @param array $rule
	 * @param array $package
	 *
	 * @return boolean
	 */
	public function validate( $rule, $package);

	/**
	 * @return array
	 */
	public function get_operators_labels();

	/**
	 * @return array
	 */
	public function get_meta_box_fields();
}
