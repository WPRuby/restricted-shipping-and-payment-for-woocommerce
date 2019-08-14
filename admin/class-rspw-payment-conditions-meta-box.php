<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpruby.com
 * @since      1.0.0
 *
 * @package    Restricted_payment_And_Payment_For_Woocommerce
 * @subpackage Restricted_payment_And_Payment_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Restricted_payment_And_Payment_For_Woocommerce
 * @subpackage Restricted_payment_And_Payment_For_Woocommerce/admin
 * @author     WPRuby <info@wpruby.com>
 */
class RSPW_Payment_Conditions_Meta_Box extends RSPW_Meta_Box {

	public function rules_metabox() {
		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box(
			array(
				'id'           => 'payment_conditions_metabox',
				'title'        => __( 'Condition Properties', 'wc-restricted-shipping-and-payment' ),
				'object_types' => array( RSPW_PAYMENT_CONDITION ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true,
			)
		);

		$cmb->add_field(
			array(
				'name'             => __( 'Excluded Payment Gateways', 'wc-restricted-shipping-and-payment' ),
				'desc'             => __( 'Select Payment gateway/s', 'wc-restricted-shipping-and-payment' ),
				'id'               => 'excluded_payment_gateways',
				'type'             => 'pw_multiselect',
				'show_option_none' => false,
				'options'          => $this->get_payment_gateways(),
			)
		);

		$group_field_id = $cmb->add_field(
			array(
				'id'          => 'payment_condition_rules',
				'type'        => 'group',
				'description' => __( 'Add the condition rules, any rule would match cart contents the payment methods will be excluded', 'wc-restricted-shipping-and-payment' ),
				'options'     => array(
					'group_title'   => __( 'Rule {#}', 'wc-restricted-shipping-and-payment' ),
					'add_button'    => __( 'Add Another Rule', 'wc-restricted-shipping-and-payment' ),
					'remove_button' => __( 'Remove Rule', 'wc-restricted-shipping-and-payment' ),
					'sortable'      => true,
					'closed'        => true,
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
				'options_cb' => array( $this, 'get_payment_operators' ),
			)
		);

		$this->add_rules( $cmb, $group_field_id );
		wp_nonce_field( 'get_rule_type_operators', 'operators_field_nonce' );
	}

	/**
	 * @return array
	 */
	private function get_payment_gateways() {
		if ( ! function_exists( 'WC' ) ) {
			return array();
		}

		$available_gateways = array();
		$gateways           = WC()->payment_gateways()->payment_gateways();
		foreach ( $gateways as $key => $gateway ) {
			$available_gateways[ $key ] = $gateway->title;
		}
		return $available_gateways;
	}

	/**
	 * @param $field
	 *
	 * @return array
	 */
	public function get_payment_operators( $field ) {
		/** @var $field CMB2_Field */
		$index     = $field->group->index;
		$rule      = get_post_meta( $field->object_id, 'payment_condition_rules', true );
		$rule_type = ( $rule ) ? $rule[ $index ]['rule_type'] : 'shipping_class';
		return RSPW_Meta_Box::get_operators( $rule_type );
	}
}

new RSPW_Payment_Conditions_Meta_Box();
