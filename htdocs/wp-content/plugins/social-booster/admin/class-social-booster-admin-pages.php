<?php

/**
 * The admin-specific functional pages of plugin.
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Social_Booster
 * @subpackage Social_Booster/admin
 */

class Social_Booster_Admin_Pages {

	function socialbooster_add_admin_pages() {

		add_menu_page( 'Socialbooster Plugin', 'Social Booster', 'manage_options', 'socialbooster_autopost', array( $this, 'socialbooster_admin_index'),plugins_url('/icon/icon.png', __FILE__) , 110);

		add_submenu_page( 'socialbooster_autopost', 'Socialbooster Plugin', 'Facebook','manage_options', 'socialbooster_autopost', array( $this, 'socialbooster_admin_index'));

		add_submenu_page( 'socialbooster_autopost', 'Socialbooster twitter', 'Twitter','manage_options', 'socialbooster_twitter', array( $this, 'socialbooster_admin_twindex'));

		add_submenu_page( 'socialbooster_autopost', 'Socialbooster gplus', 'GPlus','manage_options', 'socialbooster_gplus', array( $this, 'socialbooster_admin_gpindex'));

		add_submenu_page( 'socialbooster_autopost', 'Socialbooster instragram', 'Instagram','manage_options', 'socialbooster_instragram', array( $this, 'socialbooster_admin_insindex'));

		add_submenu_page( 'socialbooster_autopost', 'Socialbooster tumblr', 'Tumblr','manage_options', 'socialbooster_tumblr', array( $this, 'socialbooster_admin_tmindex'));

		add_submenu_page( 'socialbooster_autopost', 'Socialbooster schedule', 'Schedules','manage_options', 'socialbooster_schedule', array( $this, 'socialbooster_admin_schindex'));
	}

	function socialbooster_admin_index() {

        require_once plugin_dir_path(__FILE__) . '/partials/socialbooster_fb.php';
	}

	function socialbooster_admin_twindex() {

		require_once plugin_dir_path(__FILE__) . '/partials/socialbooster_tw.php';

	}
	function socialbooster_admin_gpindex() {

		require_once plugin_dir_path(__FILE__) . '/partials/socialbooster_gp.php';

	}
	function socialbooster_admin_insindex() {

		require_once plugin_dir_path(__FILE__) . '/partials/socialbooster_ins.php';

	}

	function socialbooster_admin_tmindex() {

		require_once plugin_dir_path(__FILE__) . '/partials/socialbooster_tm.php';

	} 

	function socialbooster_admin_schindex() {

		require_once plugin_dir_path(__FILE__) . '/partials/socialbooster_sch.php';

	}

}