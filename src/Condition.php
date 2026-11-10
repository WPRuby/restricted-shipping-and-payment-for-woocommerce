<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite;

use WPRuby\RestrictedShippingPaymentLite\Rules\RuleFactory;

/**
 * A shipping or payment condition: when any of its rules matches, its shipping methods or payment
 * gateways are hidden.
 *
 * Rules are stored in the {context}_condition_rules meta as a list of
 * array( 'rule_type' => …, 'operator' => …, 'value_…' => … ), as in 1.x, so conditions saved by
 * older versions keep working and the Pro version can read them.
 */
final class Condition {

	public const POST_TYPE_SHIPPING = 'shipping_condition';
	public const POST_TYPE_PAYMENT  = 'payment_condition';
	public const POST_TYPES         = array( self::POST_TYPE_SHIPPING, self::POST_TYPE_PAYMENT );

	public const CONTEXT_SHIPPING = 'shipping';
	public const CONTEXT_PAYMENT  = 'payment';

	private \WP_Post $post;

	private ?array $rules = null;

	public function __construct( \WP_Post $post ) {
		$this->post = $post;
	}

	/**
	 * @param int|\WP_Post $post
	 */
	public static function get( $post ): ?Condition {
		$post = get_post( $post );
		if ( ! $post || ! in_array( $post->post_type, self::POST_TYPES, true ) ) {
			return null;
		}
		return new self( $post );
	}

	public function get_post(): \WP_Post {
		return $this->post;
	}

	public function get_id(): int {
		return (int) $this->post->ID;
	}

	public function get_title(): string {
		return '' !== $this->post->post_title ? $this->post->post_title : __( '(no title)', 'restricted-shipping-and-payment-for-woocommerce' );
	}

	/**
	 * @return string CONTEXT_SHIPPING or CONTEXT_PAYMENT
	 */
	public function get_context(): string {
		return self::POST_TYPE_PAYMENT === $this->post->post_type ? self::CONTEXT_PAYMENT : self::CONTEXT_SHIPPING;
	}

	/**
	 * Meta key holding the methods or gateways this condition hides.
	 */
	public function get_targets_key(): string {
		return self::CONTEXT_PAYMENT === $this->get_context() ? 'excluded_payment_gateways' : 'excluded_shipping_methods';
	}

	/**
	 * Meta key holding the rules.
	 */
	public function get_rules_key(): string {
		return $this->get_context() . '_condition_rules';
	}

	/**
	 * @return string[] Rate IDs (method_id:instance_id) or gateway IDs.
	 */
	public function get_targets(): array {
		$targets = get_post_meta( $this->get_id(), $this->get_targets_key(), true );
		return is_array( $targets ) ? array_values( array_map( 'strval', $targets ) ) : array();
	}

	public function is_enabled(): bool {
		return 'publish' === $this->post->post_status;
	}

	/**
	 * @return array[] Rules with rule_type, operator and value_… fields.
	 */
	public function get_rules(): array {
		if ( is_null( $this->rules ) ) {
			$rules       = get_post_meta( $this->get_id(), $this->get_rules_key(), true );
			$this->rules = is_array( $rules ) ? array_values( array_filter( $rules, 'is_array' ) ) : array();
		}
		return $this->rules;
	}

	/**
	 * Does any rule match? A condition without rules never matches. Unknown or broken rules never match.
	 */
	public function matches( Context $context ): bool {
		foreach ( $this->get_rules() as $rule ) {
			$validator = isset( $rule['rule_type'] ) ? RuleFactory::make( (string) $rule['rule_type'] ) : null;
			if ( $validator && $validator->validate( $rule, $context ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Save the editor's data. Rules are sanitized by their rule class; unknown rule types are dropped.
	 *
	 * @param array $data rules and targets.
	 */
	public function save( array $data ): void {
		$rules = array();
		foreach ( isset( $data['rules'] ) && is_array( $data['rules'] ) ? $data['rules'] : array() as $rule ) {
			$validator = is_array( $rule ) && isset( $rule['rule_type'] ) ? RuleFactory::make( (string) $rule['rule_type'] ) : null;
			if ( $validator ) {
				$rules[] = $validator->sanitize( $rule );
			}
		}

		update_post_meta( $this->get_id(), $this->get_rules_key(), $rules );

		$targets = isset( $data['targets'] ) ? array_map( 'sanitize_text_field', array_map( 'strval', (array) $data['targets'] ) ) : array();
		update_post_meta( $this->get_id(), $this->get_targets_key(), array_values( array_unique( RuleSummary::without_empty( $targets ) ) ) );

		$this->rules = null;
	}

	/**
	 * Data for the condition editor.
	 */
	public function to_editor_data(): array {
		return array(
			'id'      => $this->get_id(),
			'context' => $this->get_context(),
			'rules'   => $this->get_rules(),
			'targets' => $this->get_targets(),
		);
	}
}
