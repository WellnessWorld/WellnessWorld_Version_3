<?php 
/*
  Plugin Name: Toolset WooCommerce Views
  Plugin URI: https://toolset.com/documentation/views-inside/woocommerce-views/
  Description: Lets you add e-commerce functionality to any site, running any theme.
  Author: OnTheGoSystems
  Author URI: http://www.onthegosystems.com
  Version: 2.7.9
  WC tested up to: 3.5.7
 */

/** {ENCRYPTION PATCH HERE} **/

/**
 * include plugin class
 */
if(defined('WOOCOMMERCE_VIEWS_PLUGIN_PATH')) return;

define('WOOCOMMERCE_VIEWS_PLUGIN_PATH', dirname(__FILE__));

if(defined('WOOCOMMERCE_VIEWS_PATH')) return;

define('WOOCOMMERCE_VIEWS_PATH', dirname(__FILE__) . '/Class_WooCommerce_Views.php');

define('WC_VIEWS_VERSION', '2.7.9');

if (!defined('WPVDEMO_TOOLSET_DOMAIN')) {
	define('WPVDEMO_TOOLSET_DOMAIN', 'toolset.com');
}

if(!class_exists('Class_WooCommerce_Views'))
{
	require_once('Class_WooCommerce_Views.php');
}

/**
 *  instantiate new plugin object
 */
if(!isset($Class_WooCommerce_Views))
{

	$Class_WooCommerce_Views = new Class_WooCommerce_Views;
}

//WooCommerce Views Alias Functions compatible for [wpv-if]
require WOOCOMMERCE_VIEWS_PLUGIN_PATH . '/inc/wcviews-alias-functions.php';

// Offer to recalculate prices on plugin activation:
// This is as simple as to add an admin notice if the woocommerce_last_run_update option is missing
// or empty, no need to run this here...
register_activation_hook( __FILE__, array( $Class_WooCommerce_Views, 'maybe_start_processing_products_fields' ) );

//Reset custom fields updating when deactivated
register_deactivation_hook(__FILE__,array($Class_WooCommerce_Views,'wcviews_request_to_reset_field_option'));

//Clear Functions inside conditional evaluations when deactivated
register_deactivation_hook(__FILE__,array($Class_WooCommerce_Views,'wcviews_clear_all_func_conditional_eval'));

//Shortcodes GUI
require WOOCOMMERCE_VIEWS_PLUGIN_PATH . '/inc/wcviews-shortcodes-gui.php';
$WCViews_shortcodes_gui = new WCViews_shortcodes_gui();
$WCViews_shortcodes_gui->initialize();

//WooCommerce Views Constants for messages
require WOOCOMMERCE_VIEWS_PLUGIN_PATH . '/inc/wcviews-messaging-constants.php';

//WooCommerce Views Tooltip messages
require WOOCOMMERCE_VIEWS_PLUGIN_PATH . '/inc/wcviews-admin-messages.php';

//WooCommerce Views Core Compatibility
require WOOCOMMERCE_VIEWS_PLUGIN_PATH . '/inc/wcviews-core-compatibility.php';
?>