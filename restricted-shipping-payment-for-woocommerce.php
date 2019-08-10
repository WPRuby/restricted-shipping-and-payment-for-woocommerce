<?php
/**
 *
 * @link              https://wpruby.com
 * @since             1.0.0
 * @package           Restricted_Shipping_And_Payment_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Restricted_Shipping_And_Payment_For_Woocommerce
 * Plugin URI:        https://wpruby.com
 * Description:       Add conditions and rules to enable/disable your WooCommerce shipping methods and Payment gateways.
 * Version:           1.0.0
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

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-rspw.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-rspw-deactivator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-rspw-activator.php';


define( 'RSPW_SHIPPING_CONDITION', 'shipping_condition' );
define( 'RSPW_PAYMENT_CONDITION', 'payment_condition' );

class RSPW_Bootstrap {

	/**
	 * The single instance of the class.
	 *
	 * @var RSPW_Bootstrap
	 * @since 2.1.1
	 */
	protected static $instance = null;

	/**
	 * @return RSPW_Bootstrap
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * RSPW_Bootstrap constructor.
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'autoloader' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-rspw-deactivator.php
	 */
	public function deactivate() {
		RSPW_Deactivator::deactivate();
	}

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-restricted-shipping-and-payment-for-woocommerce-activator.php
	 */
	public function activate() {
		RSPW_Activator::activate();
	}

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		( new RSPW() )->run();
	}

	/**
	 * @param $class_name
	 */
	public function autoloader( $class_name ) {
		$class_name  = str_replace( '_', '-', strtolower( $class_name ) );
		$directories = array( 'rules', 'operators' );

		foreach ( $directories as $directory ) {
			$class_path     = plugin_dir_path( __FILE__ ) . 'includes/' . $directory . '/class-' . $class_name . '.php';
			$interface_path = plugin_dir_path( __FILE__ ) . 'includes/' . $directory . '/interface-' . $class_name . '.php';
			if ( file_exists( $class_path ) ) {
				require_once $class_path;
			}
			if ( file_exists( $interface_path ) ) {
				require_once $interface_path;
			}
		}
	}
}

/** initiate the plugin */
RSPW_Bootstrap::get_instance()->run();