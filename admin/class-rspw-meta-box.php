<?php


abstract class RSPW_Meta_Box {

	/** @var string $prefix */
	protected $prefix;

	/**
	 * Initialize the metaboxes
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'cmb2_admin_init', array( $this, 'rules_metabox' ) );
		$this->prefix = '_rsapfw_';
	}

	abstract public function rules_metabox();

	/**
	 * @return array
	 */
	protected function get_rules_types() {
		$rules           = RSPW_Rules_Factory::available_rules();
		$available_rules = array();
		foreach ( $rules as $key => $rule ) {
			$available_rules[ $key ] = $rule['label'];
		}
		return $available_rules;
	}

	/**
	 * @param $rule_type
	 *
	 * @return array
	 */
	public static function get_operators( $rule_type ) {
		$rule = RSPW_Rules_Factory::make( $rule_type );
		return $rule->get_operators_labels();
	}

	/**
	 * @param CMB2 $cmb
	 * @param $group_field_id
	 */
	protected function add_rules( $cmb, $group_field_id ) {
		$available_rules = RSPW_Rules_Factory::available_rules();
		foreach ( $available_rules as $rule ) {
			/** @var RSPW_Rule $rule_instance */
			$rule_instance = new $rule['class']();
			foreach ( $rule_instance->get_meta_box_fields() as $meta_box_field ) {
				$cmb->add_group_field( $group_field_id, $meta_box_field );
			}
		}
	}
}
