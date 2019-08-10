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
3. A new admin menu item `Shipping Conditions` will be added.


## Screenshots 
### 1. Shipping Conditions
[missing image]



## Changelog 
### 2.0.0
* ADDED: New Rule: Postcode.
* ADDED: New Rule: City.
* ADDED: New Rule: State.
* ADDED: New Rule: Product Category.
* ADDED: New Rule: Product Tag.
* ADDED: New Rule: Product Attribute.
* ADDED: New Rule: Cart has downloadable products.
* ADDED: New Rule: Cart has virtual products.
* ADDED: New Rule: Total Cart Quantity.
* ADDED: Complete code refactoring.
* FIXED: Adding new payment rule for condition does not refresh the operators.

### 1.2.0 
* Fixed: The exclusion logic of payment gateways.

### 1.1.1
* Fixed: remove unnecessary CSS and JS files loading.

### 1.1.0
* Add the ability to exclude shipping methods by coupon code

### 1.0.0 
* Initial release


## Upgrade Notice 


### 1.0.0 
Initial release

