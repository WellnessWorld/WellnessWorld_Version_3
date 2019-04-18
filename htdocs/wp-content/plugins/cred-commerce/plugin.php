<?php
/**
Plugin Name: Toolset Forms Commerce
Plugin URI: http://toolset.com/home/cred-commerce/
Description: Integrate 3rd-party E-Commerce payments to Toolset Forms
Version: 1.8.1
Author: OnTheGoSystems
Author URI: http://www.onthegosystems.com/
*/



if ( ! defined( 'CRED_COMMERCE_VERSION' ) ) {
	define( 'CRED_COMMERCE_VERSION', '1.8.1' );
	define( 'CRED_COMMERCE_NAME', 'CRED_COMMERCE' );
	define( 'CRED_COMMERCE_CAPABILITY', 'manage_options' );
	define( 'CRED_COMMERCE_PLUGIN_PATH', realpath( dirname( __FILE__ ) ) );
	define( 'CRED_COMMERCE_PLUGIN_FOLDER', basename( CRED_COMMERCE_PLUGIN_PATH ) );
	define( 'CRED_COMMERCE_PLUGIN_URL', plugins_url() . '/' . CRED_COMMERCE_PLUGIN_FOLDER );
	define( 'CRED_COMMERCE_ASSETS_URL', CRED_COMMERCE_PLUGIN_URL . '/assets' );
	define( 'CRED_COMMERCE_ASSETS_PATH', CRED_COMMERCE_PLUGIN_PATH . '/assets' );
	define( 'CRED_COMMERCE_LOCALE_PATH', CRED_COMMERCE_PLUGIN_FOLDER . '/locale' );
	define( 'CRED_COMMERCE_VIEWS_PATH', CRED_COMMERCE_PLUGIN_PATH . '/views' );
	define( 'CRED_COMMERCE_VIEWS_PATH2', CRED_COMMERCE_PLUGIN_FOLDER . '/views' );
	define( 'CRED_COMMERCE_TEMPLATES_PATH', CRED_COMMERCE_PLUGIN_PATH . '/views/templates' );
	define( 'CRED_COMMERCE_TABLES_PATH', CRED_COMMERCE_PLUGIN_PATH . '/views/tables' );
	define( 'CRED_COMMERCE_CLASSES_PATH', CRED_COMMERCE_PLUGIN_PATH . '/classes' );
	define( 'CRED_COMMERCE_CONTROLLERS_PATH', CRED_COMMERCE_PLUGIN_PATH . '/controllers' );
	define( 'CRED_COMMERCE_MODELS_PATH', CRED_COMMERCE_PLUGIN_PATH . '/models' );
	define( 'CRED_COMMERCE_LOGS_PATH', CRED_COMMERCE_PLUGIN_PATH . '/logs' );
	define( 'CRED_COMMERCE_PLUGINS_PATH', CRED_COMMERCE_PLUGIN_PATH . '/plugins' );

	// define plugin name (path)
	define( 'CRED_COMMERCE_PLUGIN_NAME', CRED_COMMERCE_PLUGIN_FOLDER . '/' . basename( __FILE__ ) );
	define( 'CRED_COMMERCE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

	// load on the go resources
	require_once CRED_COMMERCE_PLUGIN_PATH . '/onthego-resources/loader.php';
	onthego_initialize( CRED_COMMERCE_PLUGIN_PATH . '/onthego-resources', CRED_COMMERCE_PLUGIN_URL . '/onthego-resources/' );

	function toolset_cred_commerce_plugin_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		$this_plugin = basename( CRED_COMMERCE_PLUGIN_PATH ) . '/plugin.php';
		if ( $plugin_file == $this_plugin ) {
			$url_adapted_version = str_replace( '.', '-', CRED_COMMERCE_VERSION );
			$plugin_meta[] = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				'https://toolset.com/version/cred-commerce-' . $url_adapted_version . '/?utm_source=credcommerceplugin&utm_campaign=credcommerce&utm_medium=release-notes-plugin-row&utm_term=Toolset Forms Commerce ' . CRED_COMMERCE_VERSION . ' release notes',
				__( 'Toolset Forms Commerce ' . CRED_COMMERCE_VERSION . ' release notes', 'wpv-views' )
			);
		}

		return $plugin_meta;
	}

	add_filter( 'plugin_row_meta', 'toolset_cred_commerce_plugin_plugin_row_meta', 10, 4 );

	function cred_commerce_activated() {
		add_option( 'cred_commerce_activated', '1' );
	}

	register_activation_hook( __FILE__, 'cred_commerce_activated' );
}

/**
 * if CRED_COMMERCE_BUNDLED is defined means that we must use the new Commerce included in Toolset Forms
 */
function cred_commerce_load_or_deactivate() {
	if ( defined( 'CRED_COMMERCE_BUNDLED' )
		&& CRED_COMMERCE_BUNDLED ) {
		add_action( 'admin_init', 'cred_commerce_deactivate' );
		add_action( 'admin_notices', 'cred_commerce_deactivate_notice' );
	}
}

function cred_commerce_deactivate_notice() {
	?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Toolset Forms Commerce is now part of Toolset Forms plugin. We deactivated Toolset Forms Commerce for you. You can safely remove it from your site.', 'wp-cred' ); ?></p>
    </div>
	<?php
}

function cred_commerce_deactivate() {
	$plugin = plugin_basename( __FILE__ );
	deactivate_plugins( $plugin );
}

add_action( 'plugins_loaded', 'cred_commerce_load_or_deactivate', 1 );

require CRED_COMMERCE_CLASSES_PATH . '/IForm_Handler.php';
require CRED_COMMERCE_CLASSES_PATH . '/CRED_Form_Meta_Data.php';
require CRED_COMMERCE_CLASSES_PATH . '/CRED_Commerce_Forms_Meta_Handler.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/form_command_interface.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/base.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/onordercreated.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/onorderchange.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/onordercomplete.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/onorderreceived.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/onpaymentfailed.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/onpaymentcomplete.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/onhold.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/onrefund.php';
require CRED_COMMERCE_CLASSES_PATH . '/event/oncancel.php';
require CRED_COMMERCE_CLASSES_PATH . '/notification/form_handler_base.php';
require CRED_COMMERCE_CLASSES_PATH . '/notification/post_handler.php';
require CRED_COMMERCE_CLASSES_PATH . '/notification/user_handler.php';

require_once( CRED_COMMERCE_PLUGIN_PATH . '/loader.php' );
CREDC_Loader::load( 'CLASS/CRED_Commerce' );
CRED_Commerce::init();