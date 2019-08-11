<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpruby.com
 * @since      1.0.0
 *
 * @package    Restricted_Shipping_And_Payment_For_Woocommerce
 * @subpackage Restricted_Shipping_And_Payment_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Restricted_Shipping_And_Payment_For_Woocommerce
 * @subpackage Restricted_Shipping_And_Payment_For_Woocommerce/admin
 * @author     WPRuby <info@wpruby.com>
 */
class RSPW_Admin {


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rspw-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rspw-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * The plugin use this method to register the main custom post type of the links.
	 *
	 * @since    1.0.0
	 */
	public function register_post_types() {
		$labels = array(
			'name'               => __( 'Shipping Conditions', 'restricted-shipping-and-payment-for-woocommerce' ),
			'singular_name'      => __( 'Shipping Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'add_new'            => __( 'Add New', 'restricted-shipping-and-payment-for-woocommerce' ),
			'add_new_item'       => __( 'Add New Shipping Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'edit_item'          => __( 'Edit Shipping Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'new_item'           => __( 'New Shipping Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'view_item'          => __( 'View Shipping Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'search_items'       => __( 'Search Shipping Conditions', 'restricted-shipping-and-payment-for-woocommerce' ),
			'not_found'          => __( 'No Shipping Conditions', 'restricted-shipping-and-payment-for-woocommerce' ),
			'not_found_in_trash' => __( 'No Shipping Conditions found in Trash', 'restricted-shipping-and-payment-for-woocommerce' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'woocommerce',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => RSPW_SHIPPING_CONDITION ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'menu_icon'          => 'dashicons-filter',

		);

		register_post_type( RSPW_SHIPPING_CONDITION, $args );

		$labels = array(
			'name'               => __( 'Payment Conditions', 'restricted-shipping-and-payment-for-woocommerce' ),
			'singular_name'      => __( 'Payment Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'add_new'            => __( 'Add New', 'restricted-shipping-and-payment-for-woocommerce' ),
			'add_new_item'       => __( 'Add New Payment Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'edit_item'          => __( 'Edit Payment Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'new_item'           => __( 'New Payment Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'view_item'          => __( 'View Payment Condition', 'restricted-shipping-and-payment-for-woocommerce' ),
			'search_items'       => __( 'Search Payment Conditions', 'restricted-shipping-and-payment-for-woocommerce' ),
			'not_found'          => __( 'No Payment Conditions', 'restricted-shipping-and-payment-for-woocommerce' ),
			'not_found_in_trash' => __( 'No Payment Conditions found in Trash', 'restricted-shipping-and-payment-for-woocommerce' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'woocommerce',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => RSPW_PAYMENT_CONDITION ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'menu_icon'          => 'dashicons-filter',

		);

		register_post_type( RSPW_PAYMENT_CONDITION, $args );
	}

	/**
	 * @return bool
	 */
	public function get_rule_type_operators() {
		$operators_field_nonce = ( isset( $_POST['operators_field_nonce'] ) ) ? sanitize_text_field( wp_unslash( $_POST['operators_field_nonce'] ) ) : null;
		if ( is_null( $operators_field_nonce ) || ! wp_verify_nonce( $operators_field_nonce, 'get_rule_type_operators' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		}

		$rule_type      = isset( $_POST['rule_type'] ) ? sanitize_text_field( wp_unslash( $_POST['rule_type'] ) ) : '';
		$post_id        = isset( $_POST['postID'] ) ? sanitize_text_field( wp_unslash( intval( $_POST['postID'] ) ) ) : '';
		$index          = isset( $_POST['index'] ) ? sanitize_text_field( wp_unslash( intval( $_POST['index'] ) ) ) : '';
		$condition_type = isset( $_POST['condition_type'] ) ? sanitize_text_field( wp_unslash( intval( $_POST['condition_type'] ) ) ) : '';
		$rules          = get_post_meta( $post_id, $condition_type . '_condition_rules', true );

		return $this->print_rules( $rules, $index, $rule_type );
	}

	/**
	 * @param $rules
	 * @param string $index
	 * @param string $rule_type
	 *
	 * @return bool
	 */
	protected function print_rules( $rules, $index, $rule_type ) {
		$selected_rule_type = ( false !== $rules ) ? $rules[ $index ]['operator'] : false;
		$available_rules    = array_keys( RSPW_Rules_Factory::available_rules() );

		if ( ! in_array( $rule_type, $available_rules, true ) ) {
			return false;
		}
		$operators = RSPW_Meta_Box::get_operators( $rule_type );
		foreach ( $operators as $key => $operator ) {
			echo sprintf( '<option %s value="%s">%s</option>', selected( $selected_rule_type, $key, false ), esc_attr( $key ), esc_html( $operator ) );
		}
		exit;
	}
}
