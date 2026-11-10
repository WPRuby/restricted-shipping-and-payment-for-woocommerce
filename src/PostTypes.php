<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite;

/**
 * The shipping and payment condition post types.
 */
final class PostTypes {

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	public function register_post_types(): void {
		register_post_type(
			Condition::POST_TYPE_SHIPPING,
			$this->get_args(
				array(
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
				)
			)
		);

		register_post_type(
			Condition::POST_TYPE_PAYMENT,
			$this->get_args(
				array(
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
				)
			)
		);
	}

	/**
	 * Is the current admin screen one of the condition screens?
	 */
	public static function is_condition_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && in_array( $screen->post_type, Condition::POST_TYPES, true );
	}

	/**
	 * Conditions are admin-only data: never public, and only store managers can edit them.
	 */
	private function get_args( array $labels ): array {
		$capability = 'manage_woocommerce';

		return array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'woocommerce',
			'show_in_nav_menus'   => false,
			'show_in_rest'        => false,
			'query_var'           => false,
			'rewrite'             => false,
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'menu_icon'           => 'dashicons-filter',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'capabilities'        => array(
				'create_posts'           => $capability,
				'edit_posts'             => $capability,
				'edit_others_posts'      => $capability,
				'edit_published_posts'   => $capability,
				'edit_private_posts'     => $capability,
				'publish_posts'          => $capability,
				'read_private_posts'     => $capability,
				'delete_posts'           => $capability,
				'delete_others_posts'    => $capability,
				'delete_published_posts' => $capability,
				'delete_private_posts'   => $capability,
			),
		);
	}
}
