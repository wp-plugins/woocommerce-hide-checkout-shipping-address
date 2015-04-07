<?php
	/**
	 * Plugin Name: WooCommerce Hide Checkout Shipping Address
	 * Description: Hide the shipping address form fields for specific shipping methods during checkout
	 * Version: 1.0
	 * Author: Web Whales
	 * Author URI: https://webwhales.nl
	 * Contributors: ronald_edelschaap
	 * License: GPLv3
	 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
	 * Text Domain: wc-hcsa
	 * Domain Path: /languages
	 *
	 * Requires at least: 3.8
	 * Tested up to: 4.1
	 *
	 * @author   Web Whales
	 * @package  WooCommerce Hide Checkout Shipping Address
	 * @category WooCommerce
	 * @version  1.0
	 * @requires WooCommerce version 2.1.0
	 */


	if ( !defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}

	if ( !class_exists( 'WC_HCSA' ) ) {

		/**
		 * WooCommerce Hide Checkout Shipping Address Class
		 *
		 * Class WC_HCSA
		 */
		final class WC_HCSA
		{

			const PLUGIN_PREFIX = 'woocommerce_hcsa_', PLUGIN_VERSION = '1.0', TEXT_DOMAIN = 'wc-hcsa';

			public $plugin_name = 'WooCommerce Hide Checkout Shipping Address';

			private $default_settings = array(), $settings = array(), $woocommerce_required_version = '2.1.0';

			/**
			 * @var WC_Shipping_Method[]
			 */
			private $shipping_methods;

			private static $instance;


			/**
			 * Class constructor
			 */
			private function __construct()
			{
				//Check if WooCommerce is active
				if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && function_exists( 'WC' ) && ( defined( 'WC_VERSION' ) && version_compare( $this->woocommerce_required_version, WC_VERSION ) < 0 ) ) {
					$this->init();

					add_action( 'woocommerce_init', array( $this, 'wc_init' ), 99999 );

					do_action( 'wc_hcsa_loaded' );
				} else {
					add_action( 'admin_notices', array( $this, 'wc_error_admin_notice' ) );
				}
			}


			/**
			 * Gets a class instance. Used to prevent this plugin from loading multiple times
			 *
			 * @return WC_HCSA
			 */
			public static function get_instance()
			{
				if ( empty( self::$instance ) ) {
					self::$instance = new self();
				}

				return self::$instance;
			}


			/**
			 * Plugin activator
			 *
			 * @return void
			 */
			public function plugin_activate()
			{
				$this->load_plugin_default_settings();
				$this->plugin_install();
			}


			/**
			 * Plugin uninstaller
			 *
			 * @return void
			 */
			public function plugin_uninstall()
			{
				$this->delete_option( 'current_version' );
				$this->delete_option( 'effect' );
			}


			/**
			 * Echo an error in the admin notices area when WooCommerce was not installed/activated/up-to-date
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			public function wc_error_admin_notice()
			{
				if ( !in_array( 'woocommerce/woocommerce.php', array_keys( get_plugins() ) ) ) {
					$message = sprintf( __( 'The %s plugin depends on the WooCommerce plugin, which is not installed. Please install WooCommerce before using this plugin.', self::TEXT_DOMAIN ), $this->plugin_name );
				} elseif ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
					$message = sprintf( __( 'The %s plugin depends on the WooCommerce plugin, which is not yet activated. Please activate WooCommerce before using this plugin.', self::TEXT_DOMAIN ), $this->plugin_name );
				} elseif ( defined( 'WC_VERSION' ) && version_compare( $this->woocommerce_required_version, WC_VERSION ) > 0 ) {
					$message = sprintf( __( 'The %s requires at least WooCommerce version %s. You are currently using version %s. Please update WooCommerce before using this plugin.', self::TEXT_DOMAIN ), $this->plugin_name, $this->woocommerce_required_version, WC_VERSION );
				} else {
					$message = sprintf( __( 'The %s plugin depends on the WooCommerce plugin, which could not be recognized. Please check your WooCommerce plugin status before using this plugin.', self::TEXT_DOMAIN ), $this->plugin_name );
				}

				echo '<div class="error"><p>' . $message . '</p></div>';
			}


			/**
			 * Add actions to WooCommerce shipping method settings pages and checkout pages
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			public function wc_init()
			{
				//Adjust the WooCommerce shipping methods settings pages
				$this->wc_adjust_method_settings_pages();

				//Adjust the WooCommerce shipping methods main settings page
				$this->wc_adjust_main_settings_page();

				//Get default plugin settings
				$this->load_plugin_default_settings();

				//Adjust the WooCommerce checkout page
				$this->wc_adjust_checkout_page();
			}


			/**
			 * Add a new option
			 *
			 * @see add_option()
			 */
			private function add_option( $option, $value = '', $autoload = 'yes' )
			{
				return add_option( self::PLUGIN_PREFIX . $option, $value, '', $autoload );
			}


			/**
			 * Removes option by name. Prevents removal of protected WordPress options.
			 *
			 * @see delete_option()
			 */
			private function delete_option( $option )
			{
				return delete_option( self::PLUGIN_PREFIX . $option );
			}


			/**
			 * Retrieve option value based on name of option.
			 *
			 * @see get_option()
			 */
			private function get_option( $option, $default = false )
			{
				return get_option( self::PLUGIN_PREFIX . $option, $default );
			}


			/**
			 * Load some general stuff
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			private function init()
			{
				//Load text domain
				load_plugin_textdomain( self::TEXT_DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages/' );

				//Register activation hook
				register_activation_hook( __FILE__, array( $this, 'plugin_activate' ) );

				//Register uninstall hook
				register_uninstall_hook( __FILE__, array( $this, 'plugin_uninstall' ) );
			}


			/**
			 * Load the plugin default settings
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			private function load_plugin_default_settings()
			{
				$default_settings = array(
					'effect'  => 'slide',
					'methods' => 'no',
				);

				$this->default_settings = apply_filters( self::PLUGIN_PREFIX . 'load_plugin_default_settings', $default_settings );
			}


			/**
			 * Load plugin settings saved at the shipping methods setting pages
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			private function load_plugin_settings()
			{
				$this->wc_load_shipping_methods();

				$settings = array(
					'effect'  => $this->get_option( 'effect', $this->default_settings['effect'] ),
					'methods' => array(),
				);

				foreach ( $this->shipping_methods as $key => $shipping_method ) {
					$settings['methods'][$key] = $shipping_method->get_option( 'hcsa', $this->default_settings['methods'] );
				}

				$this->settings = apply_filters( self::PLUGIN_PREFIX . 'load_plugin_settings', $settings );
			}


			/**
			 * Plugin installer
			 *
			 * @return void
			 */
			private function plugin_install()
			{
				$previous_version = $this->get_option( 'current_version', '0' );

				if ( version_compare( $previous_version, self::PLUGIN_VERSION ) === -1 ) {
					switch ( $previous_version ) {
						case '0':
							$this->add_option( 'current_version', '0' );
							$this->add_option( 'effect', $this->default_settings['effect'] );

						case '1.0':
							break;
					}

					$this->update_option( 'current_version', self::PLUGIN_VERSION );
				}
			}


			/**
			 * Update the value of an option that was already added.
			 *
			 * @see update_function()
			 */
			private function update_option( $option, $value )
			{
				return update_option( self::PLUGIN_PREFIX . $option, $value );
			}


			/**
			 * Adjust the checkout page with the necessary scripts
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			private function wc_adjust_checkout_page()
			{
				//Get saved settings from the admin pages
				$this->load_plugin_settings();

				//Load some javascript
				add_action( 'woocommerce_before_checkout_form', function () {
					wp_enqueue_script( 'hide-checkout-shipping-address', plugins_url( '/js/hide-checkout-shipping-address.js', __FILE__ ), array( 'jquery' ), '', true );
					wp_localize_script( 'hide-checkout-shipping-address', 'wc_hcsa_settings', $this->settings );
				} );
			}


			/**
			 * Adjust the order review page if necessary
			 *
			 * @since 1.1
			 *
			 * @return void
			 */
			public function wc_adjust_order_review_page()
			{
				//Get saved settings from the admin pages
				$this->load_plugin_settings();

				add_filter( 'woocommerce_order_hide_shipping_address', function () {
					return array_filter( array_map( function ( $method, $state ) {
						return $state == 'yes' ? $method : '';
					}, array_keys( $this->settings['methods'] ), $this->settings['methods'] ) );
				}, 99 );
			}


			/**
			 * Remove shipping fields information from the order completely if necessary
			 *
			 * @since 1.1
			 *
			 * @return void
			 */
			public function wc_adjust_order_shipping_fields()
			{
				$this->load_plugin_settings();

				$checkout = WC()->checkout();
				$shipping = !empty( $checkout->shipping_methods ) ? $checkout->shipping_methods : array();

				if ( !empty( $shipping ) ) {
					if ( !is_array( $shipping ) ) {
						$shipping = array( $shipping );
					}

					if ( array_key_exists( $shipping[0], $this->settings['methods'] ) && $this->settings['methods'][$shipping[0]] == 'yes' ) {
						$checkout->checkout_fields['shipping'] = array();
					}
				}
			}


			/**
			 * Add settings to the main shipping settings page
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			private function wc_adjust_main_settings_page()
			{
				add_filter( 'woocommerce_shipping_settings', function ( $fields ) {
					array_splice( $fields, 6, 0, array(
						array(
							'title'   => __( 'Hide shipping address effect', self::TEXT_DOMAIN ),
							'id'      => 'woocommerce_hcsa_effect',
							'default' => '',
							'type'    => 'select',
							'class'   => 'chosen_select',
							'options' => array(
								'slide' => __( 'Slide', self::TEXT_DOMAIN ),
								'fade'  => __( 'Fade', self::TEXT_DOMAIN ),
							),
						)
					) );

					return $fields;
				}, 80 );
			}


			/**
			 * Adjust all individual shipping method's setting pages
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			private function wc_adjust_method_settings_pages()
			{
				$this->wc_load_shipping_methods();

				//Add a setting field to all shipping method setting pages
				foreach ( $this->shipping_methods as $key => $shipping_method ) {
					add_filter( 'woocommerce_settings_api_form_fields_' . $key, function ( $fields ) {
						$fields['hcsa'] = array(
							'title'       => __( 'Hide shipping address', self::TEXT_DOMAIN ),
							'type'        => 'checkbox',
							'label'       => __( 'Hide', self::TEXT_DOMAIN ),
							'default'     => 'no',
							'description' => __( 'Hide the shipping address form fields on the checkout page when this shipping method is selected', self::TEXT_DOMAIN ),
							'desc_tip'    => false
						);

						return $fields;
					}, 80 );
				}
			}


			/**
			 * Load all WooCommerce shipping methods into the $shipping_methods array
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			private function wc_load_shipping_methods()
			{
				if ( empty( $this->shipping_methods ) ) {
					$this->shipping_methods = array();
					$all_shipping_methods   = WC()->shipping()->load_shipping_methods();

					foreach ( $all_shipping_methods as $shipping_method ) {
						$this->shipping_methods[$shipping_method->id] = $shipping_method;
					}
				}
			}
		}


		/**
		 * Load this plugin through the static instance
		 */
		add_action( 'plugins_loaded', array( 'WC_HCSA', 'get_instance' ) );



		//Remove shipping address form order creation if necessary
		function wc_hcsa_adjust_order_shipping_fields()
		{
			WC_HCSA::get_instance()->wc_adjust_order_shipping_fields();
		}

		add_action( 'woocommerce_new_order', 'wc_hcsa_adjust_order_shipping_fields', 99 );


		//Adjust the WooCommerce order review page
		function wc_hcsa_adjust_order_review_page()
		{
			WC_HCSA::get_instance()->wc_adjust_order_review_page();
		}

		add_action( 'woocommerce_order_details_after_order_table', 'wc_hcsa_adjust_order_review_page' );
	}