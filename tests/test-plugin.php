<?php
/**
 * Tests for the plugin bootstrap.
 *
 * @package Restricted_Shipping_And_Payment_For_Woocommerce
 */

use WPRuby\RestrictedShippingPaymentLite\Admin\Branding;
use WPRuby\RestrictedShippingPaymentLite\Plugin;

class WP_Plugin_Test extends WP_UnitTestCase {

	/**
	 * Plugin::VERSION is the version used in code; the plugin header and readme must say the same.
	 */
	public function test_version_is_the_same_everywhere() {
		$plugin_file = Plugin::instance()->file();
		$header      = get_file_data( $plugin_file, array( 'version' => 'Version' ) );
		$readme      = get_file_data( dirname( $plugin_file ) . '/readme.txt', array( 'stable_tag' => 'Stable tag' ) );

		$this->assertSame( Plugin::VERSION, $header['version'] );
		$this->assertSame( Plugin::VERSION, $readme['stable_tag'] );
	}

	public function test_hooks_are_registered_once() {
		$this->assertSame( 10, has_filter( 'woocommerce_package_rates', array( Plugin::instance()->shipping_filter(), 'exclude_package_shipping_methods' ) ) );
		$this->assertSame( 10, has_filter( 'woocommerce_available_payment_gateways', array( Plugin::instance()->payment_filter(), 'exclude_payment_gateways' ) ) );
	}

	public function test_plugins_screen_links() {
		$branding = new Branding();
		$basename = plugin_basename( Plugin::instance()->file() );

		$actions = $branding->action_links( array( 'deactivate' => '<a>Deactivate</a>' ) );
		$this->assertSame( array( 'shipping_conditions', 'payment_conditions', 'deactivate' ), array_keys( $actions ) );
		$this->assertStringContainsString( 'edit.php?post_type=shipping_condition', $actions['shipping_conditions'] );

		$meta = $branding->row_meta( array( 'By WPRuby' ), $basename );
		$this->assertStringContainsString( Branding::DOCUMENTATION_URL, $meta['docs'] );
		$this->assertStringContainsString( Branding::SUPPORT_URL, $meta['support'] );

		$this->assertSame( array( 'By someone' ), $branding->row_meta( array( 'By someone' ), 'hello.php' ), 'Other plugins are left alone.' );
	}

	public function test_logo_is_bundled() {
		$this->assertFileExists( Plugin::instance()->path( 'assets/images/wpruby-logo.svg' ) );
		$this->assertStringEndsWith( 'assets/images/wpruby-logo.svg', Branding::logo_url() );
	}

	public function test_options_endpoint_is_for_store_managers_only() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$request = new WP_REST_Request( 'GET', '/rspw-lite/v1/options/coupon_code' );
		$request->set_param( 'field', 'value_coupon_code' );
		$this->assertSame( 403, rest_do_request( $request )->get_status() );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );
		self::factory()->post->create(
			array(
				'post_type'   => 'shop_coupon',
				'post_title'  => 'summer',
				'post_status' => 'publish',
			)
		);
		$response = rest_do_request( $request );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame(
			array(
				array(
					'value' => 'summer',
					'label' => 'summer',
				),
			),
			$response->get_data()
		);
	}
}
