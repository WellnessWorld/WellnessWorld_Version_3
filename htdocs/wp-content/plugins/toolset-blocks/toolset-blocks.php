<?php
/**
 * Plugin Name: Toolset Blocks
 * Plugin URI: https://wordpress.org/plugins/toolset-blocks/
 * Description: A collection of customizable blocks which support dynamic content from several sources.
 * Author URI: OnTheGoSystems
 * Text Domain: toolset-blocks
 * Domain Path: /languages
 * Version: 0.9.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package toolset-blocks
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TB_VERSION', '0.9.0' );

define( 'TB_PATH', __DIR__ );

define( 'TB_URL', plugin_dir_url( __FILE__ ) );

if( ! defined( 'TB_BUNDLED_SCRIPT_PATH' ) ) {
	define( 'TB_BUNDLED_SCRIPT_PATH', TB_URL . 'public/js' );
	define( 'TB_HMR_RUNNING', false );
} else {
	define( 'TB_HMR_RUNNING', true );
}

// Don't touch anything below this point!!!
if ( file_exists( TB_PATH . '/build/server/bootstrap.php' ) ) {
	// Load the bootstrap for production.
	require TB_PATH . '/build/server/bootstrap.php';
} else {
	// Load the bootstrap for development.
	require TB_PATH . '/server/bootstrap.php';
}


