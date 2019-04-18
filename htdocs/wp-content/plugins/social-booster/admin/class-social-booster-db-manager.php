<?php

/**
 * The admin-specific database of the plugin.
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Social_Booster
 * @subpackage Social_Booster/admin
 */

class Social_Booster_Db_Manager {


		function __construct(){
			$this->socialbooster_status_dbtable();
			$this->socialbooster_postnow_dbtable();
			$this->socialbooster_fb_dbtable();
			$this->socialbooster_tw_dbtable();
			$this->socialbooster_gp_dbtable();
			$this->socialbooster_ins_dbtable();
			$this->socialbooster_tm_dbtable();
		}

		function socialbooster_status_dbtable() {

			global $wpdb;
			$easypost_plugin_status_table = $wpdb->prefix . 'socialbooster_statustable';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE IF NOT EXISTS $easypost_plugin_status_table (
				eppostid int NOT NULL AUTO_INCREMENT,
				schno int  NOT NULL UNIQUE,
				interv bigint  NOT NULL,
				fb int  NOT NULL,
				tw int  NOT NULL,
				gp int  NOT NULL,
				ins int  NOT NULL,
				tm int  NOT NULL,
				status varchar(1000) NOT NULL,
				link varchar(1000) NOT NULL,
				image varchar(1000) NOT NULL,
				PRIMARY KEY  (eppostid)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		}

		function socialbooster_postnow_dbtable() {

			global $wpdb;
			$easypost_plugin_postnow_table = $wpdb->prefix . 'socialbooster_postnowtable';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE IF NOT EXISTS $easypost_plugin_postnow_table (
				eppostid int NOT NULL AUTO_INCREMENT,
				schno int  NOT NULL UNIQUE,
				fb int  NOT NULL,
				tw int  NOT NULL,
				gp int  NOT NULL,
				ins int  NOT NULL,
				tm int  NOT NULL,
				status varchar(1000) NOT NULL,
				link varchar(1000) NOT NULL,
				image varchar(1000) NOT NULL,
				PRIMARY KEY  (eppostid)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		} 

		function socialbooster_fb_dbtable() {

			global $wpdb;
			$easypost_plugin_fb_table = $wpdb->prefix . 'socialbooster_fbtable';	
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $easypost_plugin_fb_table (
				username varchar(100) NOT NULL,
				appid varchar(500) NOT NULL,
				appsecret varchar(500) NOT NULL,
				accsesstokensecret varchar(500) NOT NULL,
				redirecturl varchar(500) NOT NULL,
				authorization int  NOT NULL,
				PRIMARY KEY  (username)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		}

		function socialbooster_tw_dbtable() {

			global $wpdb;
			$easypost_plugin_tw_table = $wpdb->prefix . 'socialbooster_twtable';
 			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $easypost_plugin_tw_table (
				username varchar(100) NOT NULL,
				consumerkey varchar(500) NOT NULL,
				consumersecret varchar(500) NOT NULL,
				accesstoken varchar(500) NOT NULL,
				accsesstokensecret varchar(500) NOT NULL,
				authorization int  NOT NULL,
				PRIMARY KEY  (username)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		}

		function socialbooster_gp_dbtable() {

			global $wpdb;
			$easypost_plugin_gp_table = $wpdb->prefix . 'socialbooster_gptable';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $easypost_plugin_gp_table (
				username varchar(100) NOT NULL,
				appid varchar(500) NOT NULL,
				appsecret varchar(500) NOT NULL,
				accesstoken varchar(500) NOT NULL,
				accsesstokensecret varchar(500) NOT NULL,
				PRIMARY KEY  (username)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		}

		function socialbooster_ins_dbtable() {

			global $wpdb;
			$easypost_plugin_ins_table = $wpdb->prefix . 'socialbooster_instable';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $easypost_plugin_ins_table (
				username varchar(100) NOT NULL,
				password varchar(500) NOT NULL,
				authcode varchar(500) NOT NULL,
				verified varchar(500) NOT NULL,
				PRIMARY KEY  (username)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		}

		function socialbooster_tm_dbtable() {

			global $wpdb;
			$easypost_plugin_tm_table = $wpdb->prefix . 'socialbooster_stmtable'; 
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $easypost_plugin_tm_table (
				blogname varchar(100) NOT NULL,
				consumerkey varchar(500) NOT NULL,
				consumersecret varchar(500) NOT NULL,
				token varchar(500) NOT NULL,
				tokensecret varchar(500) NOT NULL,
				authorization int  NOT NULL,
				PRIMARY KEY  (blogname)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		}	

}