<?php
/*
Plugin Name: Mailster - Email Newsletter Plugin for WordPress
Plugin URI: https://mailster.co
Description: Advanced Newsletter Plugin for WordPress. Create, Send and Track your Newsletter Campaigns
Version: 2.3.15
Author: EverPress
Author URI: https://everpress.io
Text Domain: mailster
*/

if ( defined( 'MAILSTER_VERSION' ) || ! defined( 'ABSPATH' ) ) {
	return;
}

define( 'MAILSTER_VERSION', '2.3.15' );
define( 'MAILSTER_BUILT', 1544021550 );
define( 'MAILSTER_DBVERSION', 20170201 );
define( 'MAILSTER_DIR', plugin_dir_path( __FILE__ ) );
define( 'MAILSTER_URI', plugin_dir_url( __FILE__ ) );
define( 'MAILSTER_FILE', __FILE__ );
define( 'MAILSTER_SLUG', basename( MAILSTER_DIR ) . '/' . basename( __FILE__ ) );

$upload_folder = wp_upload_dir();

if ( ! defined( 'MAILSTER_UPLOAD_DIR' ) ) {
	define( 'MAILSTER_UPLOAD_DIR', trailingslashit( $upload_folder['basedir'] ) . 'mailster' );
}
if ( ! defined( 'MAILSTER_UPLOAD_URI' ) ) {
	define( 'MAILSTER_UPLOAD_URI', trailingslashit( $upload_folder['baseurl'] ) . 'mailster' );
}

require_once MAILSTER_DIR . 'includes/check.php';
require_once MAILSTER_DIR . 'includes/functions.php';
require_once MAILSTER_DIR . 'includes/deprecated.php';
require_once MAILSTER_DIR . 'includes/3rdparty.php';
require_once MAILSTER_DIR . 'classes/mailster.class.php';

global $mailster_options, $mailster, $mailster_tags, $mailster_mystyles;

$mailster_options = get_option( 'mailster_options', array() );

$mailster = new mailster();

// if( !$mailster_options ) mailster( 'settings' )->maybe_repair_settings();
if ( ! $mailster->wp_mail && mailster_option( 'system_mail' ) == 1 ) {

	function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		return mailster()->wp_mail( $to, $subject, $message, $headers, $attachments );
	}
}
