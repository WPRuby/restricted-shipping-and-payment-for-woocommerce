# Restricted Shipping and Payment for WooCommerce 
**Contributors:** waseem_senjer, wprubyplugins  
**Donate link:** https://wpruby.com  
**Tags:** woocommerce, shipping, admin  
**Requires at least:** 4.0
**Tested up to:** 5.2 
**Requires PHP:** 5.6
**Stable tag:** 2.0.0
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

A simplistic plugin for excluding shipping methods based on multiple rules such as shipping class, package weight and cart totals.

## Installation 

1. Upload `restricted-shipping-and-payment-for-woocommerce.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. A new admin menu item `Shipping Conditions` and `Payment Conditions` will be added.


## Frequently Asked Questions
= Which rules are available to restrict payment gateways and shipping methods? =
The following rules are available:
*   Cart Total
*   Coupon Code
*   Customer
*   Package Total Weight
*   Billing Country
*   Shipping Class
*   Shipping Country


## Screenshots 
1. Excluding local pickup shipping method in case the total cart weight is less than 5kg.
2. Excluding Direct Bank Transfer if the Cart Total is less than 400$ or the billing country is the United Kingdom.



## Changelog 
``
### 1.0.1
* FIXED: the plugin security nonce was breaking other plugins.

### 1.0.0 
* Initial release


## Upgrade Notice 


### 1.0.0 
Initial release

