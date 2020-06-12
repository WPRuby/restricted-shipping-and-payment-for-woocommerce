=== Shipping and Payment Rules and Conditions for WooCommerce ===
Contributors: waseem_senjer, wprubyplugins
Donate link: https://wpruby.com
Tags: woocommerce, shipping, admin, payment, payment gateways, shipping method
Requires at least: 4.0
Tested up to: 5.2
Requires PHP: 5.6
WC requires at least: 3.0
WC tested up to: 4.2
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simplistic plugin for excluding shipping methods based on multiple rules such as shipping class, package weight and cart totals.
## Excluding Payment Gateways
You can take full control of your store payment gateways by excluding certain gateways if certain rules were met in the checkout process. For example, you can exclude Check Payments if the cart total is less than 100$. You can add an unlimited number of rules to control your payment methods availability.

## Excluding Shipping Methods
Moreover, you can have a high level of control over your store’s shipping methods, You can apply as many rules as you need in order to manage your shipping methods availability. For example, you may exclude some shipping methods if the order weight exceeds a certain weight, or exclude shipping method/s if the destination was a certain country.

== Installation ==

1. Upload `restricted-shipping-and-payment-for-woocommerce.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. A new admin menu items `Shipping Conditions` and `Payment Conditions` will be added to the WooCommerce menu item.

== Frequently Asked Questions ==
= Which rules are available to restrict payment gateways and shipping methods? =
The following rules are available:
*   Cart Total
*   Coupon Code
*   Customer
*   Package Total Weight
*   Billing Country
*   Shipping Class
*   Shipping Country


== Screenshots ==
1. Excluding local pickup shipping method in case the total cart weight is less than 5kg.
2. Excluding Direct Bank Transfer if the Cart Total is less than 400$ or the billing country is the United Kingdom.

== Changelog ==
= 1.0.3 =
* FIXED: Cart total rule was not working.
* FIXED: Payment country rule was not working.

= 1.0.2 =
* FIXED: rule operators miss displayed.

= 1.0.1 =
* FIXED: the plugin security nonce was breaking other plugins.

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release


