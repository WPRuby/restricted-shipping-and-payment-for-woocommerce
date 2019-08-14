<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://wpruby.com
 * @since      1.0.0
 *
 * @package    WC_Restricted_Shipping_And_Payment
 * @subpackage WC_Restricted_Shipping_And_Payment/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WC_Restricted_Shipping_And_Payment
 * @subpackage WC_Restricted_Shipping_And_Payment/includes
 * @author     WPRuby <info@wpruby.com>
 */
class RSPW_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'wc-restricted-shipping-and-payment',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
