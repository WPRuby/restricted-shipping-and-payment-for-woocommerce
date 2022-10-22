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
		add_action('add_meta_boxes', array($this, 'add_pro_promotion_meta_box'));
		$this->prefix = '_rsapfw_';
	}

	abstract public function rules_metabox();


	public function add_pro_promotion_meta_box()
	{
		$screens = [RSPW_SHIPPING_CONDITION, RSPW_PAYMENT_CONDITION];
		foreach ($screens as $screen) {
			add_meta_box(
				'rspw_pro_promotion_box_id',
				'Upgrade To Pro',
				array($this, 'add_pro_promotion_meta_box_html'),
				$screen,
                'side'
			);
		}
	}

	public function add_pro_promotion_meta_box_html(){
		?>
        <p>If you would like to have more rules: </p>
		<div class="support-widget">
			<ul>
				<li>» Postcode</li>
				<li>» City</li>
				<li>» State</li>
				<li>» Product Category</li>
				<li>» Product Tag</li>
				<li>» Product Attribute</li>
				<li>» Downloadable</li>
				<li>» Virtual</li>
				<li>» Total Cart Quantity</li>

			</ul>
			<a href="https://wpruby.com/plugin/woocommerce-restricted-shipping-payment-pro/?utm_source=restricted-lite&utm_medium=widget&utm_campaign=freetopro" class="button wpruby_button" target="_blank"><span class="dashicons dashicons-star-filled"></span> Upgrade Now</a>
		</div>
		<?php
	}
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
		if ( ! is_null( $rule ) ) {
			return $rule->get_operators_labels();
		}

		return array();
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
