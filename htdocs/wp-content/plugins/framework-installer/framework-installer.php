<?php
/*
  Plugin Name: Toolset Framework Installer
  Plugin URI: http://toolset.com/documentation/views-demos-downloader/?utm_source=local-ref-site&utm_medium=wpadmin&utm_term=visit-plugin-site&utm_content=plugins-page&utm_campaign=framework-installer
  Description: Download complete reference designs for Types and Views to your local test site.
  Author: OnTheGoSystems
  Author URI: http://www.onthegosystems.com
  Version: 3.1.3
 */
define('FIDEMO_VERSION', '3.1.3');
define('FIDEMO_ABSPATH', dirname(__FILE__));
define('FIDEMO_WPCONTENTDIR',WP_CONTENT_DIR);
define('FIDEMO_RELPATH', plugins_url() . '/' . basename(FIDEMO_ABSPATH));

if ( ! defined( 'FIDEMO_URL' ) ) {
    define( 'FIDEMO_URL', 'https://ref.toolset.com' );
}

if ( ! defined( 'FIDEMO_DEBUG' ) ) {
    define( 'FIDEMO_DEBUG', false );
}

if ( ! get_option('wpv_import_is_done') ) {
  if ( defined( 'FIDEMO_DEBUG' ) ) {
  	if ( ! FIDEMO_DEBUG ) {
  		error_reporting(0);
  	}
  } else {
  	error_reporting(0);
  }
}


if ( ! defined( 'FIDEMO_REMOTE_LOG_URL' ) ) {
	define( 'FIDEMO_REMOTE_LOG_URL', 'https://api.toolset.com/' );
}
if ( ! defined( 'WPVDEMO_TOOLSET_DOMAIN' ) ) {
	define( 'WPVDEMO_TOOLSET_DOMAIN', 'toolset.com' );
}

register_activation_hook(__FILE__, 'wpvdemo_activation_hook');

require_once FIDEMO_ABSPATH . '/classes/installer.class.php';
require_once FIDEMO_ABSPATH . '/classes/ajax.class.php';
require_once FIDEMO_ABSPATH . '/classes/dashboard.class.php';


add_action( 'init', 'init_framework_installer_plugin', 120 );
function init_framework_installer_plugin()
{
	global $frameworkinstaller;
    $frameworkinstaller = new Toolset_Framework_Installer();
    new Toolset_Framework_Installer_Ajax();

	new Toolset_Framework_Installer_Dashboard();

}


add_action( 'activated_plugin', 'fidemo_redirect_after_installation' );
/**
 * on activate plugin redirection
 * @param $plugin
 */
function fidemo_redirect_after_installation( $plugin ){
	if( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ! defined( 'WP_CLI' ) && $plugin == plugin_basename( __FILE__ ) ) {
		exit( wp_redirect( admin_url( 'admin.php?page=manage-refsites' ) ) );
	}
}

/**
 * Get site settings by id
 * @param $site_id
 *
 * @return bool
 */
function wpvdemo_get_site_settings( $site_id ) {
	$sites = fidemo_get_reference_sites();

	if ( empty( $sites ) ) {
		return false;
	}

	foreach ( $sites as $index => $site ) {
		if ( intval( $site->ID ) == intval( $site_id ) ) {
			return $site;
		}
	}
	return false;
}

/**
 * get sites from remote host
 * @return mixed|string
 */
function fidemo_get_reference_sites() {

	$last_updated_date = get_option( 'fidemo_last_update', date( "Y-m-d H:i:s" ) );
	$date = date( "Y-m-d H:i:s" );
	$diff = strtotime( $date ) - strtotime( $last_updated_date );
	$sites = get_option( 'fidemo_ref_sites', array() );
	$ref_sites_url = FIDEMO_URL . '/_reference_sites/demos-index.json';

	//Check reference sites every 2 hours
	if ( $diff > 7200 || empty( $sites ) || ( isset( $_GET['force_refresh'] ) && $_GET['force_refresh'] == 1 ) ) {

		$ref_sites = fidemo_get_content( $ref_sites_url );
		if ( empty( $ref_sites ) ) {
			add_action( 'admin_notices', array( $this, 'connection_refused_notice' ) );
			return '';
		}
		$sites = json_decode( $ref_sites );

		update_option( 'fidemo_ref_sites', $sites );
		update_option( 'fidemo_last_update', date( "Y-m-d H:i:s" ) );
		if ( isset( $_GET['force_refresh'] ) && $_GET['force_refresh'] == 1 ) {
			header( 'Location: admin.php?page=manage-refsites' );
		}
	}

	$sites = fidemo_check_themes_availability( $sites );
	return $sites;
}

/**
 * On multisite, check if required theme exists and active in the Network
 * @param $sites
 *
 * @return object
 */
function fidemo_check_themes_availability( $sites ){
	if ( ! is_multisite() ) {
		return $sites;
	}

	$themes = wp_get_themes( array( 'allowed' => 'network' ) );
	foreach ( $themes as $theme => $theme_object ) {
		$themes[ $theme ] = $themes[ $theme ]->display( 'Version' );
	}

	for ( $i = 0; $i < count( $sites ); $i ++ ) {

		if ( ! isset( $themes[ $sites[ $i ]->themes->theme ] ) || version_compare( $sites[ $i ]->themes->theme_version, $themes[ $sites[ $i ]->themes->theme ] ) !== 0 ) {
			$sites[ $i ]->themes->theme_status = false;
		} else {
			$sites[ $i ]->themes->theme_status = true;
		}

		if ( isset( $sites[ $i ]->themes->additional_themes ) ) {
			foreach ( $sites[ $i ]->themes->additional_themes as $theme => $theme_object ) {
				if ( ! isset( $themes[ $theme ] ) || version_compare( $theme_object->version, $themes[ $theme ] ) !== 0 ) {
					$sites[ $i ]->themes->additional_themes->$theme->theme_status = false;
				} else {
					$sites[ $i ]->themes->additional_themes->$theme->theme_status = true;
				}
			}
		}
	}
	return $sites;
}

/**
 * @return resource
 */
function fidemo_create_stream_context() {
	$context = stream_context_set_default( array(
			'http' => array(
				'timeout' => 1200
			),
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
			)
		)
	);
	return $context;
}

/**
 * @param $url
 *
 * @return string
 */
function fidemo_get_content( $url ) {
	$content = '';
	$context = fidemo_create_stream_context();
	$url_headers = @get_headers ( $url );
	if ( strpos ( $url_headers [0], '200 OK' ) ) {
		$handle = fopen( $url, "r", false, $context );
		while ( ! feof( $handle ) ) {
			$content .= fread( $handle, 8192 );
		}
	}
	return $content;
}

/**
 * @param $site_id
 *
 * @return bool
 */
function fidemo_get_site_settings($site_id) {
	$sites = fidemo_get_reference_sites();
	if ( empty( $sites ) ) {
		return false;
	}
	foreach ( $sites as $index => $site ) {
		if ( intval( $site->ID ) == intval( $site_id ) ) {
			return $site;
		}
	}
	return false;
}

/**
 * Check if wpml enabled
 * @return bool
 */
function wpvdemo_wpml_is_enabled() {
	$is_enabled=false;

	if (( defined( 'ICL_SITEPRESS_VERSION' ) ) && ( defined( 'WPML_ST_VERSION' ) ) )
	{
		$active_plugins=apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		if ((is_array($active_plugins)) && (!(empty($active_plugins)))) {
			foreach ($active_plugins as $k=>$v) {
				if ((strpos($v, 'sitepress.php') !== false)) {
					//Found
					return TRUE;
				}
			}
		}
	}

	return $is_enabled;
}

/**
 * @param $settings
 *
 * @return bool
 */
function fidemo_admin_check_required_site_settings( $settings ) {
	$objectified = true;
	if ( is_array( $settings) ) {
		$objectified = false;
	}
	$required_settings = array( 'title', 'download_url', 'tagline', 'themes', 'tutorial_title', 'tutorial_url', 'shortname' );
	foreach ( $required_settings as $setting ) {
		if ( $objectified ) {
			if ( empty($settings->$setting) ) {
				return false;
			}
		} elseif (! $objectified ) {
			if ( empty( $settings[ $setting ] ) ) {
				return false;
			}
		}
	}
	return true;
}

function fidemo_site_has_wpml( $site_id ) {
	$sites = fidemo_get_reference_sites();
	foreach ( $sites as $index => $site ) {
		if ( intval( $site->ID ) == intval( $site_id ) ) {
			$plugins = $site->plugins;
			foreach( $plugins as $plugin => $plugin_info ) {
				$plugin_name = $plugin_info->title;
				if ( ( strpos( $plugin_name, 'WPML' ) !== false || strpos( $plugin_name, 'Multilingual' ) !== false ) ) {
					return true;
				}
			}
		}
	}

	return false;
}

