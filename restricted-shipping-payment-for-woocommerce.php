<?php
/**
 * @link              https://wpruby.com
 * @package           Restricted_Shipping_And_Payment_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Conditional Payments and Shipping for WooCommerce
 * Plugin URI:        https://wpruby.com
 * Description:       Add conditions and rules to enable/disable your WooCommerce shipping methods and Payment gateways.
 * Version:           2.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 8.9
 * WC tested up to:  11.0
 * Author:            WPRuby
 * Author URI:        https://wpruby.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       restricted-shipping-and-payment-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
 * PSR-4 autoloader for the plugin's classes, matching the "autoload" section of composer.json.
 * Release zips don't include vendor/, so the plugin doesn't depend on Composer at runtime.
 */
spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'WPRuby\\RestrictedShippingPaymentLite\\';
		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$file = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

\WPRuby\RestrictedShippingPaymentLite\Plugin::boot( __FILE__ );
