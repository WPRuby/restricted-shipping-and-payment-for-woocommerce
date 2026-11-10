<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite;

use WPRuby\RestrictedShippingPaymentLite\Rules\RuleFactory;

/**
 * REST API used by the condition editor.
 */
final class RestApi {

	public const NAMESPACE_V1 = 'rspw-lite/v1';

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE_V1,
			'/options/(?P<rule_type>[a-z0-9_]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_options' ),
				'permission_callback' => array( $this, 'can_manage' ),
				'args'                => array(
					'field'   => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
					),
					'search'  => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'include' => array(
						'type'    => 'array',
						'items'   => array( 'type' => 'string' ),
						'default' => array(),
					),
				),
			)
		);
	}

	public function can_manage(): bool {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_options( \WP_REST_Request $request ) {
		$rule = RuleFactory::make( (string) $request['rule_type'] );
		if ( is_null( $rule ) ) {
			return new \WP_Error( 'rspw_unknown_rule', __( 'Unknown rule type.', 'restricted-shipping-and-payment-for-woocommerce' ), array( 'status' => 404 ) );
		}

		$include = array_map( 'sanitize_text_field', (array) $request['include'] );
		$options = $rule->get_options( (string) $request['field'], (string) $request['search'], $include );

		return rest_ensure_response( self::to_list( $options ) );
	}

	/**
	 * @param array $options value => label
	 *
	 * @return array List of value and label, as the editor's controls expect.
	 */
	public static function to_list( array $options ): array {
		$list = array();
		foreach ( $options as $value => $label ) {
			$list[] = array(
				'value' => (string) $value,
				'label' => html_entity_decode( wp_strip_all_tags( (string) $label ), ENT_QUOTES ),
			);
		}
		return $list;
	}
}
