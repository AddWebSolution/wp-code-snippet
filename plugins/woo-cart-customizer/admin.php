<?php
/**
 * Addweb WC_Integration class 
 *
 * @package   Simple Customization of Add to Cart Button
 * @author    Addweb Solution Pvt. Ltd.
 * @license   GPL-2.0+
 * @link      http://www.addwebsolution.com
 * @copyright 2016 AddWeb Solution
 */
/**
* Restricting user to access this file directly (Security Purpose).
**/
if( !defined( 'ABSPATH' ) ) {
    die( "You Don't Have Permission To Access This Page" );
    exit;
  }
  if ( ! class_exists( 'Addweb_Cart_Customizer' ) ) :
	class Addweb_Cart_Customizer {
		/**
		* Plugin version
		*/
		const ADDWEB_CC_VERSION = '2.1';

		/**
		* Construct the plugin.
		*/
		public function __construct() {
            add_action( 'admin_menu', array( $this, 'addweb_wc_addmenu_page' ) );
            add_action( 'admin_init', array( $this, 'addweb_wc_register_settings' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'addweb_wc_admin_style_and_js' ) );
            add_action( 'plugins_loaded', array( $this, 'init' ) );
        
    }

    /**
     * Add Plugin Menu Page
    **/
    public function addweb_wc_addmenu_page() {
        add_submenu_page(
            'woocommerce',
            'Woo Cart Customizer',
            'Woo Cart Customizer',
            'manage_options',
            'woo-cart-customizer',
			array( $this, 'woo_cart_customizer_callback')
        );
    }

    /**
     * Add menu template
    **/
	public function woo_cart_customizer_callback() {
		include ADDWEBWC_PLUGIN_DIR.'includes/plugin-setting-page.php';
	}


    /**
     * Register settings for plugin
    **/
    public function addweb_wc_register_settings() {
        register_setting( ADDWEBWC_TEXT_DOMAIN, ADDWEBWC_TEXT_DOMAIN, array( $this, 'addweb_wc_sanatize_setting' ) );
    }

    /**
     * Sanitizing the submitted text
    **/
    public function addweb_wc_sanatize_setting( $settings ) {
        return $settings;
    }

    /**
     * Adding Script and style file
    **/
    public function addweb_wc_admin_style_and_js() {
		wp_register_style( ADDWEBWC_TEXT_DOMAIN . 'admin-css', ADDWEBWC_PLUGIN_URL . 'assets/css/admin.css', array(), ADDWEBWC_PLUGIN_VERSION );
        wp_register_style( ADDWEBWC_TEXT_DOMAIN . 'jquery-ui-css', ADDWEBWC_PLUGIN_URL . 'assets/css/jquery-ui.min.css', array(), ADDWEBWC_PLUGIN_VERSION );
        wp_register_style( ADDWEBWC_TEXT_DOMAIN . '-style', ADDWEBWC_PLUGIN_URL . 'assets/css/cart-customizer-popup.css', array(), ADDWEBWC_PLUGIN_VERSION );
        
        if( isset( $_GET['page']) && $_GET['page'] == 'woo-cart-customizer' ){
		wp_enqueue_style( ADDWEBWC_TEXT_DOMAIN . 'admin-css');
        wp_enqueue_style( ADDWEBWC_TEXT_DOMAIN . 'jquery-ui-css');
        wp_enqueue_style( ADDWEBWC_TEXT_DOMAIN . '-style');
        }
    }


		/**
		* Initialize the plugin.
		* @since 1.0
 		* @since 2.1 Add admin notice if parent plugin is not activated.
		*/
		public function init() {
			// Checks if WooCommerce is installed.
			if ( class_exists( 'WC_Integration' ) ) {
				// Include our integration class.  
				include ADDWEBWC_PLUGIN_DIR.'woo_cart_customizer_class.php';   

				// Register the integration.
				add_filter( 'woocommerce_integrations', array( $this, 'addweb_add_integration' ) );

				// Set the plugin slug
				define( 'ADDWEB_WOO_CART_CUSTOMIZER_PLUGIN_SLUG', 'wc-settings' );

				// Setting action for plugin
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'addweb_customize_cart_action_links' );
			}else{
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				add_action( 'admin_notices', array( $this, 'addweb_plugin_notice' ) );

			    deactivate_plugins( array(plugin_basename( __FILE__ )) ); 

			    if ( isset( $_GET['activate'] ) ) {
			        unset( $_GET['activate'] );
			    }  
			}
		}

		/**
		* Add a new integration to WooCommerce.
		*/
		public function addweb_add_integration( $integrations ) {
			$integrations[] = 'Add_Woo_Integration_Cart';
			return $integrations;
		}

		/**
		* Display admin notices.
		* @since 2.1 add admin notice.
		*/
		public function addweb_plugin_notice() {
			echo sprintf('<div class="error"><p>%1$s</p></div>', esc_html( __( 'Sorry, but Child Plugin requires the Woocommerce plugin to be installed and active.', 'customized_cart' ) )
			);
		}
	}

	$Addweb_Cart_Customizer = new Addweb_Cart_Customizer();    


	function addweb_customize_cart_action_links( $links ) {
		$links[] = '<a href="'. menu_page_url( ADDWEB_WOO_CART_CUSTOMIZER_PLUGIN_SLUG, false ) .'&tab=integration">Settings</a>';
		return $links;
	}

	/*
	* Returns the array of custom names
	*/
	function addweb_custom_cart_name() {
		$customArr = get_option('woocommerce_cart-customizer_settings');
		return $customArr;
	}

	/*
	* Change cart Button for shop and single page
	*/
	add_filter( 'woocommerce_product_single_add_to_cart_text', 'addweb_custom_cart_button_text' );
	add_filter( 'woocommerce_product_add_to_cart_text', 'addweb_custom_cart_button_text' );
	function addweb_custom_cart_button_text() {
		$customArr = addweb_custom_cart_name();
		$_product = wc_get_product( get_the_ID() );      
		if( $_product->is_type( 'simple' ) ) {
			$finalText = ( empty( $customArr['add_to_cart_text'] )) ? 'Add to Cart' : $customArr['add_to_cart_text'];
			return __( $finalText, 'Addweb-woo-cart-customizer' );
		} 
		if($_product->is_type( 'grouped' ) ) {
			$finalText = ( empty( $customArr['grouped_product_text'] )) ? 'View Products' : $customArr['grouped_product_text'];
			return __( $finalText, 'Addweb-woo-cart-customizer' );
		}
		if($_product->is_type( 'variable' ) ) {
			if(doing_filter('woocommerce_product_single_add_to_cart_text')){
				$finalText = (empty( $customArr['add_to_cart_text'] )) ? 'Add to Cart' : $customArr['add_to_cart_text'];
			} else {
				$finalText = (empty( $customArr['variable_product_text'] )) ? 'Read more' : $customArr['variable_product_text'];
			}				
			return __( $finalText, 'Addweb-woo-cart-customizer' );
		}
		if($_product->is_type( 'external' ) && $customArr['external_product_checkbox'] == "yes") {
			$finalText = (empty( $customArr['external_product_text'] )) ? 'Buy Product' : $customArr['external_product_text'];
			return __( $finalText, 'Addweb-woo-cart-customizer');
		}
		if($_product->is_type( 'external' ) && $customArr['external_product_checkbox'] == "no") {
			$finalExternalText = (empty( $customArr['external_product_text'] )) ? 'Buy Product' : $customArr['external_product_text'];
			$finalText = $_product->button_text ? $_product->button_text : $finalExternalText;
			return __( $finalText, 'Addweb-woo-cart-customizer' );
		}
	}

	/*
	* Change view cart button
	*/
	add_filter( 'woocommerce_add_message'	,	'addweb_custom_message_text', 10, 1 );
	add_filter( 'woocommerce_add_error'	,	'addweb_custom_message_text', 10, 1 );
	add_filter( 'woocommerce_add_notice'	,	'addweb_custom_message_text', 10, 1 );
	function addweb_custom_message_text( $message ) {
		$customArr = addweb_custom_cart_name();
		$finalMessageText = ( empty( $customArr['message_and_notice_text'] )) ? 'Cart' : $customArr['message_and_notice_text'];
		$message = str_replace( 'your cart','your '.$finalMessageText, $message );
		return $message;
	}

	/**
	* change some WooCommerce labels
	* @param string $translation
	* @param string $text
	* @param string $domain
	* @return string
	*/

	add_filter('gettext', 'addweb_custom_update_cart_message', 10, 3);
	function addweb_custom_update_cart_message($translation, $text, $domain) {
		if ($domain == 'woocommerce') {
			$customArr = addweb_custom_cart_name();
			if ($text == 'Cart updated.') {
				$finalText = (empty($customArr['message_and_notice_text'])) ? 'Cart' : $customArr['message_and_notice_text'];
				$translation =  $finalText.' updated.';
			}
			if($text == "View cart") {				
				$finalText = (empty($customArr['view_cart_text'])) ? 'View cart' : $customArr['view_cart_text'];
				$translation = $finalText;
			}
			if($text == "Cart Totals") {
				$finalText = (empty($customArr['message_and_notice_text'])) ? 'Cart' : $customArr['message_and_notice_text'];
				$translation = $finalText . ' Totals';
			}
			if($text == "Your cart is currently empty.") {
				$finalText = (empty($customArr['message_and_notice_text'])) ? 'cart' : $customArr['message_and_notice_text'];
				$translation = 'Your '. $finalText .' is currently empty.';
			}
			if($text == "Update cart") {
				$finalText = (empty($customArr['update_cart_text'])) ? 'Update cart' : $customArr['update_cart_text'];
				$translation = strtoupper($finalText);
			}
		}
		return $translation;
	}
	

endif;