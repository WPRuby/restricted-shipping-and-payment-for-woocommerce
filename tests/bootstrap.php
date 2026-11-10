<?php
/**
 * PHPUnit bootstrap: loads the WordPress test library, WooCommerce and the plugin.
 *
 * Install them with bin/install-wp-tests.sh; WP_TESTS_DIR and WP_CORE_DIR point to them.
 * Set WC_HPOS=1 to store orders in WooCommerce's order tables (HPOS) instead of posts.
 *
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

$_plugin_dir = dirname( __DIR__ );

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_plugin_dir . '/vendor/yoast/phpunit-polyfills' );
require_once $_plugin_dir . '/vendor/autoload.php';
require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () use ( $_plugin_dir ) {
		$core_dir = getenv( 'WP_CORE_DIR' );
		$wc_file  = $core_dir ? rtrim( $core_dir, '/' ) . '/wp-content/plugins/woocommerce/woocommerce.php' : dirname( $_plugin_dir ) . '/woocommerce/woocommerce.php';

		require $wc_file;
		require $_plugin_dir . '/restricted-shipping-payment-for-woocommerce.php';
	}
);

// Create WooCommerce's tables, options and roles, as WooCommerce's own test suite does.
tests_add_filter(
	'setup_theme',
	static function () {
		WC_Install::install();
		$GLOBALS['wp_roles'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_roles();

		if ( '1' === getenv( 'WC_HPOS' ) ) {
			wc_get_container()->get( Automattic\WooCommerce\Internal\DataStores\Orders\DataSynchronizer::class )->create_database_tables();
		}
	}
);

$_hpos = '1' === getenv( 'WC_HPOS' ) ? 'yes' : 'no';
tests_add_filter(
	'pre_option_woocommerce_custom_orders_table_enabled',
	static function () use ( $_hpos ) {
		return $_hpos;
	}
);

require $_tests_dir . '/includes/bootstrap.php';
