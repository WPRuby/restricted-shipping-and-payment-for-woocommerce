<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Admin;

use WPRuby\RestrictedShippingPaymentLite\Condition;
use WPRuby\RestrictedShippingPaymentLite\RuleSummary;

/**
 * Adds the Hides and When columns to the conditions list.
 */
final class ConditionsList {

	public function register(): void {
		foreach ( Condition::POST_TYPES as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'render_column' ), 10, 2 );
		}
	}

	/**
	 * @param array $columns
	 *
	 * @return array
	 */
	public function columns( $columns ) {
		return array(
			'cb'           => isset( $columns['cb'] ) ? $columns['cb'] : '',
			'title'        => __( 'Name', 'restricted-shipping-and-payment-for-woocommerce' ),
			'rspw_targets' => __( 'Hides', 'restricted-shipping-and-payment-for-woocommerce' ),
			'rspw_rules'   => __( 'When', 'restricted-shipping-and-payment-for-woocommerce' ),
			'date'         => isset( $columns['date'] ) ? $columns['date'] : __( 'Date', 'restricted-shipping-and-payment-for-woocommerce' ),
		);
	}

	/**
	 * @param string $column
	 * @param int    $post_id
	 */
	public function render_column( $column, $post_id ): void {
		$condition = Condition::get( $post_id );
		if ( ! $condition ) {
			return;
		}

		switch ( $column ) {
			case 'rspw_targets':
				$targets = RuleSummary::targets( $condition );
				echo $targets ? esc_html( RuleSummary::shorten_list( $targets ) ) : '<span aria-hidden="true">—</span>';
				break;

			case 'rspw_rules':
				$lines = RuleSummary::condition( $condition );
				if ( empty( $lines ) ) {
					printf( '<em>%s</em>', esc_html__( 'No rules', 'restricted-shipping-and-payment-for-woocommerce' ) );
					break;
				}
				$joiner = sprintf( '<br><strong class="rspw-joiner">%s</strong><br>', esc_html__( 'OR', 'restricted-shipping-and-payment-for-woocommerce' ) );
				echo implode( $joiner, array_map( 'esc_html', $lines ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				break;
		}
	}
}
