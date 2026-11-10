<?php
/**
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

namespace WPRuby\RestrictedShippingPaymentLite;

use WPRuby\RestrictedShippingPaymentLite\Admin\Branding;
use WPRuby\RestrictedShippingPaymentLite\Admin\ConditionEditor;
use WPRuby\RestrictedShippingPaymentLite\Admin\ConditionsList;
use WPRuby\RestrictedShippingPaymentLite\Frontend\PaymentFilter;
use WPRuby\RestrictedShippingPaymentLite\Frontend\ShippingFilter;

/**
 * Creates the plugin's services and registers their hooks. Classes don't hook themselves
 * into WordPress when they are loaded; everything is wired here.
 */
final class Plugin {

	/**
	 * The plugin version. Keep it equal to "Version" in the plugin header and "Stable tag" in readme.txt
	 * (tests/test-plugin.php checks this).
	 */
	public const VERSION = '2.0.0';

	public const TEXT_DOMAIN = 'restricted-shipping-and-payment-for-woocommerce';
	public const NAME        = 'Conditional Payments and Shipping for WooCommerce';

	private static ?Plugin $instance = null;

	private string $file;

	private ShippingFilter $shipping_filter;

	private PaymentFilter $payment_filter;

	private function __construct( string $file ) {
		$this->file = $file;
	}

	/**
	 * @param string $file Main plugin file.
	 */
	public static function boot( string $file ): Plugin {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $file );
			self::$instance->register();
		}
		return self::$instance;
	}

	public static function instance(): Plugin {
		if ( is_null( self::$instance ) ) {
			throw new \LogicException( 'The plugin has not been booted.' );
		}
		return self::$instance;
	}

	private function register(): void {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );

		( new PostTypes() )->register();
		( new RestApi() )->register();

		$this->shipping_filter = new ShippingFilter();
		$this->payment_filter  = new PaymentFilter();
		$this->shipping_filter->register();
		$this->payment_filter->register();

		if ( is_admin() ) {
			( new ConditionEditor() )->register();
			( new ConditionsList() )->register();
			( new Branding() )->register();
		}
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( self::TEXT_DOMAIN, false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	public function declare_compatibility(): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->file, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->file, true );
		}
	}

	public function file(): string {
		return $this->file;
	}

	/**
	 * @param string $path Relative to the plugin directory.
	 */
	public function path( string $path = '' ): string {
		return plugin_dir_path( $this->file ) . ltrim( $path, '/' );
	}

	/**
	 * @param string $path Relative to the plugin directory.
	 */
	public function url( string $path = '' ): string {
		return plugins_url( ltrim( $path, '/' ), $this->file );
	}

	public function shipping_filter(): ShippingFilter {
		return $this->shipping_filter;
	}

	public function payment_filter(): PaymentFilter {
		return $this->payment_filter;
	}
}
