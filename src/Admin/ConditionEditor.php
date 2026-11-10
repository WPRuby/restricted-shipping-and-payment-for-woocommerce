<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Admin;

use WPRuby\RestrictedShippingPaymentLite\Condition;
use WPRuby\RestrictedShippingPaymentLite\Plugin;
use WPRuby\RestrictedShippingPaymentLite\PostTypes;
use WPRuby\RestrictedShippingPaymentLite\RestApi;
use WPRuby\RestrictedShippingPaymentLite\Rules\RuleFactory;
use WPRuby\RestrictedShippingPaymentLite\Targets;

/**
 * Renders the React rule editor (client/condition-editor) in a meta box. The editor writes its state
 * as JSON to a hidden field, which is sanitized and saved with the post.
 */
final class ConditionEditor {

	public const FIELD = 'rspw_lite_condition';
	public const NONCE = 'rspw_lite_condition_nonce';

	public function register(): void {
		foreach ( Condition::POST_TYPES as $post_type ) {
			add_action( 'add_meta_boxes_' . $post_type, array( $this, 'add_meta_box' ) );
			add_action( 'save_post_' . $post_type, array( $this, 'save' ), 10, 2 );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'enter_title_here', array( $this, 'title_placeholder' ), 10, 2 );
	}

	/**
	 * @param \WP_Post $post
	 */
	public function add_meta_box( \WP_Post $post ): void {
		add_meta_box( 'rspw-lite-condition-editor', __( 'Condition', 'restricted-shipping-and-payment-for-woocommerce' ), array( $this, 'render' ), $post->post_type, 'normal', 'high' );
	}

	/**
	 * @param \WP_Post $post
	 */
	public function render( \WP_Post $post ): void {
		wp_nonce_field( 'rspw_lite_save_condition', self::NONCE );
		printf( '<input type="hidden" name="%s" id="%s" value="" />', esc_attr( self::FIELD ), esc_attr( self::FIELD ) );
		echo '<div id="rspw-lite-condition-editor-root"></div>';
		printf( '<noscript><p>%s</p></noscript>', esc_html__( 'The condition editor needs JavaScript.', 'restricted-shipping-and-payment-for-woocommerce' ) );
	}

	/**
	 * @param string   $placeholder
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	public function title_placeholder( $placeholder, $post ) {
		if ( in_array( $post->post_type, Condition::POST_TYPES, true ) ) {
			return __( 'Condition name', 'restricted-shipping-and-payment-for-woocommerce' );
		}
		return $placeholder;
	}

	/**
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public function save( $post_id, $post ): void {
		if ( ! isset( $_POST[ self::NONCE ], $_POST[ self::FIELD ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), 'rspw_lite_save_condition' ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Sanitized per field in Condition::save().
		$data = json_decode( wp_unslash( $_POST[ self::FIELD ] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_array( $data ) || ! $post instanceof \WP_Post ) {
			return;
		}

		( new Condition( $post ) )->save( $data );
	}

	/**
	 * @param string $hook
	 */
	public function enqueue_scripts( $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! PostTypes::is_condition_screen() ) {
			return;
		}

		$condition = Condition::get( get_post() );
		if ( ! $condition ) {
			return;
		}

		$asset_file = Plugin::instance()->path( 'build/condition-editor.asset.php' );
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset = include $asset_file;

		wp_enqueue_script( 'rspw-lite-condition-editor', Plugin::instance()->url( 'build/condition-editor.js' ), $asset['dependencies'], $asset['version'], true );
		wp_enqueue_style( 'rspw-lite-condition-editor', Plugin::instance()->url( 'build/condition-editor.css' ), array( 'wp-components' ), $asset['version'] );
		wp_style_add_data( 'rspw-lite-condition-editor', 'rtl', 'replace' );
		wp_set_script_translations( 'rspw-lite-condition-editor', 'restricted-shipping-and-payment-for-woocommerce', Plugin::instance()->path( 'languages' ) );
		wp_add_inline_script( 'rspw-lite-condition-editor', 'window.rspwLiteConditionEditor = ' . wp_json_encode( $this->get_editor_data( $condition ) ) . ';', 'before' );
	}

	/**
	 * @param Condition $condition
	 *
	 * @return array
	 */
	private function get_editor_data( Condition $condition ): array {
		$data       = $condition->to_editor_data();
		$rule_types = array();
		$labels     = array();

		foreach ( RuleFactory::available_rules() as $type => $definition ) {
			$rule = RuleFactory::make( $type );
			if ( ! $rule ) {
				continue;
			}

			$operators = array();
			foreach ( $rule->get_operators_labels() as $value => $label ) {
				$operators[] = array(
					'value' => $value,
					'label' => $label,
				);
			}

			$fields = array();
			foreach ( $rule->get_value_schema() as $field ) {
				$async    = ! empty( $field['async'] );
				$fields[] = array(
					'key'     => $field['key'],
					'type'    => $field['type'],
					'label'   => $field['label'],
					'async'   => $async,
					'unit'    => isset( $field['unit'] ) ? $field['unit'] : '',
					'help'    => isset( $field['help'] ) ? $field['help'] : '',
					'options' => $async ? array() : RestApi::to_list( isset( $field['options'] ) ? $field['options'] : array() ),
				);
			}

			$rule_types[] = array(
				'type'      => $type,
				'label'     => $definition['label'],
				'operators' => $operators,
				'fields'    => $fields,
			);
		}

		// Labels of the values already chosen in searchable fields, so the editor can show them before any search.
		foreach ( $data['rules'] as $rule_data ) {
			$rule = isset( $rule_data['rule_type'] ) ? RuleFactory::make( (string) $rule_data['rule_type'] ) : null;
			if ( ! $rule ) {
				continue;
			}
			foreach ( $rule->get_value_schema() as $field ) {
				if ( empty( $field['async'] ) || empty( $rule_data[ $field['key'] ] ) ) {
					continue;
				}
				foreach ( RestApi::to_list( $rule->get_options( $field['key'], '', (array) $rule_data[ $field['key'] ] ) ) as $option ) {
					$labels[ $rule_data['rule_type'] ][ $field['key'] ][ $option['value'] ] = $option['label'];
				}
			}
		}

		return array(
			'condition'      => $data,
			'ruleTypes'      => $rule_types,
			'targets'        => RestApi::to_list( Targets::for_context( $condition->get_context() ) ),
			'selectedLabels' => (object) $labels,
			'currency'       => html_entity_decode( get_woocommerce_currency_symbol() ),
			'fieldName'      => self::FIELD,
			'restNamespace'  => RestApi::NAMESPACE_V1,
		);
	}
}
