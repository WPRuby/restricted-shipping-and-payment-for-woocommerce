<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpruby.com
 * @since      1.0.0
 *
 * @package    WC_Restricted_Shipping_And_Payment
 * @subpackage WC_Restricted_Shipping_And_Payment/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WC_Restricted_Shipping_And_Payment
 * @subpackage WC_Restricted_Shipping_And_Payment/admin
 * @author     WPRuby <info@wpruby.com>
 */
class RSPW_Shipping_Conditions_Meta_Box extends RSPW_Meta_Box {

	public function rules_metabox() {

		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box(
			array(
				'id'           => 'shipping_conditions_metabox',
				'title'        => __( 'Condition Properties', 'wc-restricted-shipping-and-payment' ),
				'object_types' => array( RSPW_SHIPPING_CONDITION ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true,
			)
		);

		$cmb->add_field(
			array(
				'name'             => __( 'Excluded Shipping Methods', 'wc-restricted-shipping-and-payment' ),
				'desc'             => __( 'Select shipping method/s', 'wc-restricted-shipping-and-payment' ),
				'id'               => 'excluded_shipping_methods',
				'type'             => 'pw_multiselect',
				'show_option_none' => false,
				'options'          => $this->get_shipping_methods(),
			)
		);

		$group_field_id = $cmb->add_field(
			array(
				'id'          => 'shipping_condition_rules',
				'type'        => 'group',
				'description' => __( 'Add the condition rules, any rule would match cart contents the shipping methods will be excluded', 'wc-restricted-shipping-and-payment' ),
				'options'     => array(
					'group_title'   => __( 'Rule {#}', 'wc-restricted-shipping-and-payment' ),
					'add_button'    => __( 'Add Another Rule', 'wc-restricted-shipping-and-payment' ),
					'remove_button' => __( 'Remove Rule', 'wc-restricted-shipping-and-payment' ),
					'sortable'      => true,
					'closed'        => true, // true to have the groups closed by default.
				),
			)
		);

		$cmb->add_group_field(
			$group_field_id,
			array(
				'name'             => __( 'Rule Type', 'wc-restricted-shipping-and-payment' ),
				'id'               => 'rule_type',
				'type'             => 'select',
				'classes'          => 'rule_type',
				'show_option_none' => false,
				'options'          => $this->get_rules_types(),
			)
		);

		$cmb->add_group_field(
			$group_field_id,
			array(
				'name'       => __( 'Operator', 'wc-restricted-shipping-and-payment' ),
				'id'         => 'operator',
				'type'       => 'select',
				'options_cb' => array( $this, 'get_shipping_operators' ),
			)
		);

		$this->add_rules( $cmb, $group_field_id );
		wp_nonce_field( 'get_rule_type_operators', 'operators_field_nonce' );
	}

	/**
	 * @return array
	 */
	private function get_shipping_methods() {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return array();
		}

		$shipping_methods = array();
		$shipping_zones   = WC_Shipping_Zones::get_zones();

		if ( ! isset( $shipping_zones[0] ) ) {
			$rest_of_world_zone                    = WC_Shipping_Zones::get_zone_by();
			$shipping_zones[0]                     = $rest_of_world_zone->get_data();
			$shipping_zones[0]['shipping_methods'] = $rest_of_world_zone->get_shipping_methods();
		}

		foreach ( $shipping_zones as $shipping_zone ) {
			if ( empty( $shipping_zone['shipping_methods'] ) ) {
				continue;
			}

			$zone_name = $shipping_zone['zone_name'];
			foreach ( $shipping_zone['shipping_methods'] as $instance_id => $method_instance ) {
				$method_id                      = $method_instance->get_rate_id();
				$method_name                    = sprintf( '[%s - ID: %d] &ndash; [%s]', $method_instance->get_title(), $instance_id, $zone_name );
				$shipping_methods[ $method_id ] = $method_name;
			}
		}

		return $shipping_methods;
	}

	/**
	 * @param $field
	 *
	 * @return array
	 */
	public function get_shipping_operators( $field ) {
		/** @var $field CMB2_Field */
		$index     = $field->group->index;
		$rule      = get_post_meta( $field->object_id, 'shipping_condition_rules', true );
		$rule_type = ( $rule ) ? $rule[ $index ]['rule_type'] : 'shipping_class';
		return RSPW_Meta_Box::get_operators( $rule_type );
	}


}

new RSPW_Shipping_Conditions_Meta_Box();
