<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Social_Booster
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;
$tablestatus = $wpdb->prefix . 'socialbooster_statustable';
$tablespost = $wpdb->prefix . 'socialbooster_postnowtable';
$tablefb = $wpdb->prefix . 'socialbooster_fbtable';
$tabletw = $wpdb->prefix . 'socialbooster_twtable';
$tablegp = $wpdb->prefix . 'socialbooster_gptable';
$tableins = $wpdb->prefix . 'socialbooster_instable';
$tabletm = $wpdb->prefix . 'socialbooster_stmtable';

$wpdb->query("DROP TABLE IF EXISTS $tablestatus");
$wpdb->query("DROP TABLE IF EXISTS $tablespost");
$wpdb->query("DROP TABLE IF EXISTS $tablefb");
$wpdb->query("DROP TABLE IF EXISTS $tabletw");
$wpdb->query("DROP TABLE IF EXISTS $tablegp");
$wpdb->query("DROP TABLE IF EXISTS $tableins");
$wpdb->query("DROP TABLE IF EXISTS $tabletm");