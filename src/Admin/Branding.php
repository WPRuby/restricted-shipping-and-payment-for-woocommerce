<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite\Admin;

use WPRuby\RestrictedShippingPaymentLite\Condition;
use WPRuby\RestrictedShippingPaymentLite\Plugin;
use WPRuby\RestrictedShippingPaymentLite\PostTypes;

/**
 * WPRuby branding: a header on the condition screens, the Upgrade to Pro box in the condition editor
 * and links on the Plugins screen.
 */
final class Branding {

	public const SUPPORT_URL       = 'https://wordpress.org/support/plugin/restricted-shipping-and-payment-for-woocommerce/';
	public const DOCUMENTATION_URL = 'https://wpruby.com/knowledgebase_category/woocommerce-restricted-shipping-and-payment-pro/';
	public const PRO_URL           = 'https://wpruby.com/plugin/woocommerce-restricted-shipping-payment-pro/?utm_source=restricted-lite&utm_medium=widget&utm_campaign=freetopro';

	public function register(): void {
		add_action( 'in_admin_header', array( $this, 'render_header' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		foreach ( Condition::POST_TYPES as $post_type ) {
			add_action( 'add_meta_boxes_' . $post_type, array( $this, 'add_pro_box' ) );
		}
		add_filter( 'plugin_action_links_' . plugin_basename( Plugin::instance()->file() ), array( $this, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );
	}

	/**
	 * The WPRuby ruby, from wpruby.com (238.5 × 169.5).
	 */
	public static function logo_url(): string {
		return Plugin::instance()->url( 'assets/images/wpruby-logo.svg' );
	}

	public function enqueue_styles(): void {
		if ( PostTypes::is_condition_screen() ) {
			wp_enqueue_style( 'rspw-lite-admin', Plugin::instance()->url( 'assets/css/rspw-admin.css' ), array(), Plugin::VERSION );
		}
	}

	public function render_header(): void {
		if ( ! PostTypes::is_condition_screen() ) {
			return;
		}
		?>
		<div class="rspw-brand-header">
			<a class="rspw-brand-header__logo" href="https://wpruby.com" target="_blank" rel="noopener noreferrer">
				<img src="<?php echo esc_url( self::logo_url() ); ?>" alt="WPRuby" width="45" height="32" />
			</a>
			<div class="rspw-brand-header__title">
				<p class="rspw-brand-header__name"><?php echo esc_html( Plugin::NAME ); ?></p>
				<p class="rspw-brand-header__vendor">
					<?php
					/* translators: %s: plugin version. */
					echo esc_html( sprintf( __( 'by WPRuby · v%s', 'restricted-shipping-and-payment-for-woocommerce' ), Plugin::VERSION ) );
					?>
				</p>
			</div>
			<nav class="rspw-brand-header__links" aria-label="<?php esc_attr_e( 'Plugin help', 'restricted-shipping-and-payment-for-woocommerce' ); ?>">
				<?php $this->render_link( self::DOCUMENTATION_URL, 'dashicons-book', __( 'Documentation', 'restricted-shipping-and-payment-for-woocommerce' ) ); ?>
				<?php $this->render_link( self::SUPPORT_URL, 'dashicons-sos', __( 'Support', 'restricted-shipping-and-payment-for-woocommerce' ) ); ?>
			</nav>
		</div>
		<?php
	}

	public function add_pro_box(): void {
		$screen = get_current_screen();
		if ( $screen ) {
			add_meta_box( 'rspw_pro_promotion_box_id', __( 'Upgrade To Pro', 'restricted-shipping-and-payment-for-woocommerce' ), array( $this, 'render_pro_box' ), $screen->post_type, 'side', 'low' );
		}
	}

	public function render_pro_box(): void {
		// The Pro version's rules that this plugin doesn't have, by topic. The rule names show as each topic's tooltip.
		$groups = array(
			array(
				'label' => __( 'Location', 'restricted-shipping-and-payment-for-woocommerce' ),
				'icon'  => 'dashicons-location',
				'rules' => array(
					__( 'Postcode', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'City', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'State', 'restricted-shipping-and-payment-for-woocommerce' ),
				),
			),
			array(
				'label' => __( 'Products', 'restricted-shipping-and-payment-for-woocommerce' ),
				'icon'  => 'dashicons-products',
				'rules' => array(
					__( 'Product', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Product Category', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Product Tag', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Product Attribute', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Downloadable', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Virtual', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Total Cart Quantity', 'restricted-shipping-and-payment-for-woocommerce' ),
				),
			),
			array(
				'label' => __( 'Customer', 'restricted-shipping-and-payment-for-woocommerce' ),
				'icon'  => 'dashicons-admin-users',
				'rules' => array(
					__( 'Customer Role', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Logged In or Guest', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Previous Orders', 'restricted-shipping-and-payment-for-woocommerce' ),
				),
			),
			array(
				'label' => __( 'Checkout', 'restricted-shipping-and-payment-for-woocommerce' ),
				'icon'  => 'dashicons-cart',
				'rules' => array(
					__( 'Payment Gateway', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Shipping Method', 'restricted-shipping-and-payment-for-woocommerce' ),
				),
			),
			array(
				'label' => __( 'Date and time', 'restricted-shipping-and-payment-for-woocommerce' ),
				'icon'  => 'dashicons-calendar-alt',
				'rules' => array(
					__( 'Time of Day', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Day of Week', 'restricted-shipping-and-payment-for-woocommerce' ),
					__( 'Date', 'restricted-shipping-and-payment-for-woocommerce' ),
				),
			),
		);

		$features = array(
			__( 'AND / OR rule groups', 'restricted-shipping-and-payment-for-woocommerce' ),
			__( 'Show-only conditions', 'restricted-shipping-and-payment-for-woocommerce' ),
			__( 'Messages at checkout', 'restricted-shipping-and-payment-for-woocommerce' ),
			__( 'Priorities and debugger', 'restricted-shipping-and-payment-for-woocommerce' ),
		);

		$count = array_sum(
			array_map(
				static function ( array $group ): int {
					return count( $group['rules'] );
				},
				$groups
			)
		);
		?>
		<div class="rspw-support rspw-pro">
			<p class="rspw-pro__headline">
				<?php
				/* translators: %d: number of rules. */
				echo esc_html( sprintf( _n( '%d more rule', '%d more rules', $count, 'restricted-shipping-and-payment-for-woocommerce' ), $count ) );
				?>
			</p>
			<ul class="rspw-pro__groups">
				<?php foreach ( $groups as $group ) : ?>
					<?php $rules = wp_sprintf( '%l', $group['rules'] ); ?>
					<li title="<?php echo esc_attr( $rules ); ?>">
						<span class="dashicons <?php echo esc_attr( $group['icon'] ); ?>" aria-hidden="true"></span><?php echo esc_html( $group['label'] ); ?>
						<span class="screen-reader-text">: <?php echo esc_html( $rules ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<ul class="rspw-pro__features">
				<?php foreach ( $features as $feature ) : ?>
					<li><span class="dashicons dashicons-yes" aria-hidden="true"></span><?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
			<a href="<?php echo esc_url( self::PRO_URL ); ?>" class="button button-primary rspw-pro-button" target="_blank" rel="noopener noreferrer">
				<span class="dashicons dashicons-star-filled" aria-hidden="true"></span><?php esc_html_e( 'Upgrade Now', 'restricted-shipping-and-payment-for-woocommerce' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Links to the condition lists, before Deactivate.
	 *
	 * @param string[] $links
	 *
	 * @return string[]
	 */
	public function action_links( $links ): array {
		$own = array(
			'shipping_conditions' => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=' . Condition::POST_TYPE_SHIPPING ) ), esc_html__( 'Shipping conditions', 'restricted-shipping-and-payment-for-woocommerce' ) ),
			'payment_conditions'  => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=' . Condition::POST_TYPE_PAYMENT ) ), esc_html__( 'Payment conditions', 'restricted-shipping-and-payment-for-woocommerce' ) ),
		);
		return array_merge( $own, is_array( $links ) ? $links : array() );
	}

	/**
	 * Documentation and Support links after "By WPRuby | Visit plugin site".
	 *
	 * @param string[] $meta
	 * @param string   $file
	 *
	 * @return string[]
	 */
	public function row_meta( $meta, $file ): array {
		$meta = is_array( $meta ) ? $meta : array();
		if ( plugin_basename( Plugin::instance()->file() ) !== $file ) {
			return $meta;
		}

		$meta['docs']    = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( self::DOCUMENTATION_URL ), esc_html__( 'Documentation', 'restricted-shipping-and-payment-for-woocommerce' ) );
		$meta['support'] = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( self::SUPPORT_URL ), esc_html__( 'Support', 'restricted-shipping-and-payment-for-woocommerce' ) );

		return $meta;
	}

	private function render_link( string $url, string $icon, string $label ): void {
		printf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer"><span class="dashicons %2$s" aria-hidden="true"></span>%3$s<span class="screen-reader-text"> %4$s</span></a>',
			esc_url( $url ),
			esc_attr( $icon ),
			esc_html( $label ),
			esc_html__( '(opens in a new tab)', 'restricted-shipping-and-payment-for-woocommerce' )
		);
	}
}
