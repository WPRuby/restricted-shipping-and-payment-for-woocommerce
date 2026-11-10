=== Conditional Payments and Shipping for WooCommerce ===
Contributors: waseem_senjer, wprubyplugins
Donate link: https://wpruby.com
Tags: conditional shipping, conditional payments, woocommerce, payment gateways, shipping method
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
WC requires at least: 8.9
WC tested up to: 11.0
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simplistic plugin for excluding shipping methods based on multiple rules such as shipping class, package weight and cart totals.



## Excluding Payment Gateways
You can take full control of your store payment gateways by excluding certain gateways if certain rules were met in the checkout process. For example, you can exclude Check Payments if the cart total is less than 100$. You can add an unlimited number of rules to control your payment methods availability.

## Excluding Shipping Methods
Moreover, you can have a high level of control over your store’s shipping methods, You can apply as many rules as you need in order to manage your shipping methods availability. For example, you may exclude some shipping methods if the order weight exceeds a certain weight, or exclude shipping method/s if the destination was a certain country.

* [Upgrade to Pro Now](https://wpruby.com/plugin/woocommerce-restricted-shipping-payment-pro/?utm_source=restricted-lite&utm_medium=readme&utm_campaign=freetopro "Upgrade to Pro NOW")
* [Documentation](https://wpruby.com/knowledgebase_category/woocommerce-restricted-shipping-and-payment-pro/ "Documentation ")


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

= 2.0.0 =
* Changed: New condition editor. Choose the shipping methods or payment gateways to hide, then add rules; searchable lists replace the old select boxes and the condition is checked before publishing.
* Changed: The conditions lists show what each condition hides and its rules.
* Changed: Conditions can only be viewed and edited by store managers and administrators.
* Added: Support for the WooCommerce Cart and Checkout blocks.
* Fixed: "more than" rules also matched equal values.
* Fixed: Customer rule failed for guests; it now uses the billing email they entered.
* Fixed: Shipping rates were not refreshed when only the billing country or email changed.
* Fixed: Rules without values could match.
* Dev: Requires WordPress 6.6, PHP 7.4 and WooCommerce 8.9 or newer.
* Dev: Rewritten as namespaced classes; the CMB2 library is no longer bundled. Existing conditions keep working unchanged.

= 1.0.17 =
* Added: WooCommerce 11.0 compatibility.
* Added: WordPress 7.1 compatibility.

= 1.0.16 =
* Fixed: Missing authorization check in the get_rule_type_operators AJAX handler that could allow contributors to read shipping and payment condition configuration from posts they do not own.

= 1.0.15 =
* Added: WordPress 6.9 compatibility.

= 1.0.14 =
* Added: WooCommerce 9.4 compatibility.
* Added: WordPress 6.7 compatibility.

= 1.0.13 =
* Fixed: PHP deprecated warnings.
* Added: WooCommerce 9.3 compatibility.
* Added: WordPress 6.6 compatibility.
* Updated: select2 to 4.0.7

= 1.0.12 =
* Fixed: Shipping classes issue for variable products.
* Added: WooCommerce 8.5 compatibility.

= 1.0.11 =
* Added: WordPress 6.4 compatibility.
* Added: WooCommerce 8.2 compatibility.
* Added: Declaring WooCommerce HPOS compatibility.
* Fixed: Fix billing country rule.

= 1.0.10 =
* Added: WordPress 6.3 compatibility.
* Added: WooCommerce 8.0 compatibility.

= 1.0.9 =
* Added: WordPress 6.0 compatibility.
* Added: WooCommerce 7.0 compatibility.
* Fixed: Updated Select2.js to 4.0.6

= 1.0.8 =
* Added: WordPress 5.9 compatibility.

= 1.0.7
* FIXED: fix shipping classes issue with product variations

= 1.0.6 =
* FIXED: product weight for variations.

= 1.0.5 =
* FIXED: Not In operator was not working as expected.
* FIXED: Shipping classes rule was not calculated correctly.

= 1.0.4 =
* FIXED: Weight rule unit conversion was not considered.
* FIXED: Weight rule product quantity was not considered.

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

= 2.0.0 =
New condition editor and Cart and Checkout blocks support. Requires WordPress 6.6, PHP 7.4 and WooCommerce 8.9. Existing conditions keep working.

= 1.0.16 =
Security update. Fixes a missing authorization check in an admin AJAX handler.

= 1.0.0 =
Initial release


