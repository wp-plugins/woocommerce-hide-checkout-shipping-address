=== WooCommerce Hide Checkout Shipping Address ===
Contributors: ronald_edelschaap
Tags: woocommerce,hide address,hide shipping address,checkout,shipping,shipping method
Requires at least: 3.8
Tested up to: 4.1
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Hide the shipping address form fields for specific shipping methods during checkout

== Description ==
Some shipping methods in WooCommerce just don\'t need a shipping address. It can even be confusing for your customers to fill in a shipping address when they select a method like the local pick up method. Why would they be asked for that information if it doesn\'t make sense?

This plugin hides the shipping address form fields at the checkout page when your customers select a shipping method that doesn\'t require that information. You can turn this feature on and off for each shipping method individually at their settings pages. As a bonus, on the main shipping settings page you can select the effect of hiding and showing the form fields.

This plugin should work with any WooCommerce installation, since it hooks up to the core functionality of WooCommerce. Note that at least version 2.1.0 of WooCommerce is required. If the the shipping address fields are not hiding/showing, please check your theme files first to see if they are overruling the standard WooCommerce checkout pages. If that is the case, please contact the creator of that theme first.

The plugin language is English. Dutch language files are included. Feel free to send your translation to me, so I can include it in the plugin.

== Installation ==
1. Download the archive and unzip it in /wp-content/plugins, upload the archive at Plugins > Add New > Upload Plugin or install via Plugins > Add New > Search for the plugin name in the search bar.
2. Activate the plugin through the Plugins menu in WordPress
3. Update the plugin settings in your WooCommerce settings at WooCommerce > Settings > Shipping and the individual shipping method settings pages.

== Frequently Asked Questions ==
= Will this plugin work with my theme? =
As long as your theme doesn\'t overrule the default checkout page parts OR your theme has the same div classes and input field name attributes (which most themes have), this plugin will definitely work with your theme.

= The shipping address fields at the checkout page are not hiding/showing when I change the shipping method =
This plugin responds to the WooCommerce builtin AJAX calls that are made when you change the shipping method. It also relies on the original input field name attributes and the div that\'s wrapped around the shipping address fields (the one with class woocommerce-shipping-fields). As long as you see that div along with the input name attributes, this plugin should work fine. If that is the case, please contact me so we can work it out.

== Changelog ==
= 1.0 =
First release