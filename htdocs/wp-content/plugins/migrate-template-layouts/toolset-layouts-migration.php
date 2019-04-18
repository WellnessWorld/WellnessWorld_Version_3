<?php
/*
Plugin Name: Toolset Layouts Migration
Plugin URI: http://wp-types.com
Description: Migration plugin from old Layouts to Layouts 1.9 and later.
Author: OnTheGoSystems
Author URI: http://www.onthegosystems.com
Version: 1.0
Text Domain: dd-layouts
License URI: https://www.gnu.org/licenses/gpl-2.0.html
License: GPLv2
*/

// Version
if( ! defined( 'TLM_VERSION' ) ) {
	define( 'TLM_VERSION', '1.0' );
}

define( 'TLM_LAYOUTS_MIN_VERSION', '1.9.2' );
define( 'TLM_OPTIONS_GENERAL', 'tlm_options' );

define( 'TLM_PLUGIN_ABSPATH', plugin_basename( __FILE__ ) );
define( 'TLM_ABSPATH', dirname( __FILE__ )  );
define( 'TLM_LIBRARY_ABSPATH', TLM_ABSPATH . '/library'  );
define( 'TLM_PUBLIC_ABSPATH', TLM_ABSPATH . '/public' );
define( 'TLM_APPLICATION_ABSPATH', TLM_ABSPATH . '/application' );
define( 'TLM_TEMPLATES_ABSPATH', TLM_APPLICATION_ABSPATH . '/templates'  );

define( 'TLM_URI', plugins_url( basename( dirname( __FILE__ ) ), dirname( __FILE__ ) ) );
define( 'TLM_PUBLIC_URI', plugins_url( basename( TLM_PUBLIC_ABSPATH ), TLM_PUBLIC_ABSPATH ) );

define( 'TLM_MIGRATION_DATA_META', '_tlm_migration_meta');
define( 'TLM_MIGRATION_BOOL_META', '_tlm_bool_meta');
define( 'TLM_LAYOUTS_META_KEY', '_tlm_template');
define( 'TLM_NAME_PREFIX', __('Layout for ', 'ddl-layouts') );

$autoloader_dir = TLM_LIBRARY_ABSPATH;
if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
	$autoloader = $autoloader_dir . '/autoload.php';
} else {
	$autoloader = $autoloader_dir . '/autoload_52.php';
}
require_once $autoloader;

// Activate
function tlm_activate( ) {

	if( defined( 'WPDDL_VERSION' ) && version_compare( WPDDL_VERSION, TLM_LAYOUTS_MIN_VERSION ) !== -1 ) {

		$defaults = array(
			'general' => 'Start Migration Process',
			'version' => TLM_VERSION,
			'Layouts' => WPDDL_VERSION
		);

		if( false === get_option( TLM_OPTIONS_GENERAL ) ) {
			update_option( TLM_OPTIONS_GENERAL, $defaults );
		}

		add_action( 'after_setup_theme', 'tlm_plugin_setup', 12 );

	} else {

		add_action( 'admin_init', 'tlm_embedded_deactivate' );
		add_action( 'admin_notices', 'tlm_embedded_deactivate_notice' );
	}

}
add_action( 'plugins_loaded', 'tlm_activate', 1 );

function tlm_embedded_deactivate() {
	deactivate_plugins( TLM_PLUGIN_ABSPATH );
}

function tlm_embedded_deactivate_notice() {
	$plugin = (object) get_plugin_data( __FILE__, false, true );
	?>
	<div class="error settings-error notice is-dismissible">
		<p>
			<?php printf( __( '%s %s requires Toolset Layouts %s or higher to be installed and activated.', 'ddl-layouts' ), $plugin->Name, $plugin->Version, TLM_LAYOUTS_MIN_VERSION ); ?>
		</p>
	</div>
	<?php
}

add_action( 'init', 'tlm_plugin_setup' );
function tlm_plugin_setup(){
	return TLM_Main::getInstance();
}

/**
 * PHP 5.2 support.
 *
 * get_called_class() is only in PHP >= 5.3, this is a workaround.
 * This function is needed by WPDDL_Theme_Integration_Abstract.
 */
if ( !function_exists( 'get_called_class' ) ) {
	function get_called_class() {
		$bt = debug_backtrace();
		$l = 0;
		do {
			$l++;

			if( isset( $bt[ $l ] ) && isset( $bt[ $l ]['file'] ) ){
				$lines = file( $bt[ $l ]['file'] );
				$callerLine = $lines[ $bt[ $l ]['line'] - 1 ];
				preg_match( '/([a-zA-Z0-9\_]+)::' . $bt[ $l ]['function'] . '/', $callerLine, $matches );
			}

		} while( isset( $matches ) && isset( $matches[1] ) && $matches[1] === 'parent');

		return isset( $matches ) && isset( $matches[1] ) ? $matches[1] : null;
	}
}