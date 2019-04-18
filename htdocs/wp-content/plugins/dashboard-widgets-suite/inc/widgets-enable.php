<?php // Dashboard Widgets Suite - Add Enabled Widgets
	
if (!defined('ABSPATH')) exit;

function dashboard_widgets_suite_enable_widgets() {
	
	global $dws_options_general, $dws_options_log_debug, $dws_options_log_error, $dws_options_feed_box, 
	$dws_options_social_box, $dws_options_notes_user, $dws_options_system_info,  $dws_options_list_box, $dws_options_widget_box;
	
	$suite  = '<span class="dws-subtitle"> Widgets Suite</span>';
	
	// control panel
	
	if (isset($dws_options_general['widget_control_panel']) && $dws_options_general['widget_control_panel']) {
		
		$display_control_panel = isset($dws_options_general['widget_control_view']) ? $dws_options_general['widget_control_view'] : null;
		
		if (dashboard_widgets_suite_check_role($display_control_panel)) {
			
			require_once DWS_DIR .'widgets/widget-control-panel.php';
			
			$link = '<a class="dws-widget-settings" href="'. admin_url('options-general.php?page=dashboard_widgets_suite&tab=tab1') .'"><span class="fa fa-cog"></span></a>';
			
			wp_add_dashboard_widget('dashboard_widgets_suite_control_panel', esc_html__('Control Panel', 'dashboard-widgets-suite') . $suite . $link, 'dashboard_widgets_suite_control_panel');
			
		}
		
	}
	
	// debug log
	
	if (isset($dws_options_log_debug['widget_log_debug']) && $dws_options_log_debug['widget_log_debug']) {
		
		$display_log_debug = isset($dws_options_log_debug['widget_log_debug_view']) ? $dws_options_log_debug['widget_log_debug_view'] : null;
		
		if (dashboard_widgets_suite_check_role($display_log_debug)) {
			
			require_once DWS_DIR .'widgets/widget-log-debug.php';
			
			$link = '<a class="dws-widget-settings" href="'. admin_url('options-general.php?page=dashboard_widgets_suite&tab=tab8') .'"><span class="fa fa-cog"></span></a>';
			
			wp_add_dashboard_widget('dashboard_widgets_suite_log_debug', esc_html__('Debug Log', 'dashboard-widgets-suite') . $suite . $link, 'dashboard_widgets_suite_log_debug');
			
		}
		
	}
	
	// error log
	
	if (isset($dws_options_log_error['widget_log_error']) && $dws_options_log_error['widget_log_error']) {
		
		$display_log_error = isset($dws_options_log_error['widget_log_error_view']) ? $dws_options_log_error['widget_log_error_view'] : null;
		
		if (dashboard_widgets_suite_check_role($display_log_error)) {
			
			require_once DWS_DIR .'widgets/widget-log-error.php';
			
			$link = '<a class="dws-widget-settings" href="'. admin_url('options-general.php?page=dashboard_widgets_suite&tab=tab9') .'"><span class="fa fa-cog"></span></a>';
			
			wp_add_dashboard_widget('dashboard_widgets_suite_log_error', esc_html__('Error Log', 'dashboard-widgets-suite') . $suite . $link, 'dashboard_widgets_suite_log_error');
			
		}
		
	}
	
	// feed box
	
	if (isset($dws_options_feed_box['widget_feed_box']) && $dws_options_feed_box['widget_feed_box']) {
		
		$display_feed_box = isset($dws_options_feed_box['widget_feed_box_view']) ? $dws_options_feed_box['widget_feed_box_view'] : null;
		
		if (dashboard_widgets_suite_check_role($display_feed_box)) {
			
			require_once DWS_DIR .'widgets/widget-feed-box.php';
			
			$link = '<a class="dws-widget-settings" href="'. admin_url('options-general.php?page=dashboard_widgets_suite&tab=tab3') .'"><span class="fa fa-cog"></span></a>';
			
			wp_add_dashboard_widget('dashboard_widgets_suite_feed_box', esc_html__('Feed Box', 'dashboard-widgets-suite') . $suite . $link, 'dashboard_widgets_suite_feed_box');
			
		}
		
	}
	
	// social box
	
	if (isset($dws_options_social_box['widget_social_box']) && $dws_options_social_box['widget_social_box']) {
		
		$display_social_box = isset($dws_options_social_box['widget_social_box_view']) ? $dws_options_social_box['widget_social_box_view'] : null;
		
		if (dashboard_widgets_suite_check_role($display_social_box)) {
			
			require_once DWS_DIR .'widgets/widget-social-box.php';
			
			$link = '<a class="dws-widget-settings" href="'. admin_url('options-general.php?page=dashboard_widgets_suite&tab=tab4') .'"><span class="fa fa-cog"></span></a>';
			
			wp_add_dashboard_widget('dashboard_widgets_suite_social_box', esc_html__('Social Box', 'dashboard-widgets-suite') . $suite . $link, 'dashboard_widgets_suite_social_box');
			
		}
		
	}
	
	// list box
	
	if (isset($dws_options_list_box['widget_list_box']) && $dws_options_list_box['widget_list_box']) {
		
		$display_list_box = isset($dws_options_list_box['widget_list_box_view']) ? $dws_options_list_box['widget_list_box_view'] : null;
		
		if (dashboard_widgets_suite_check_role($display_list_box)) {
			
			require_once DWS_DIR .'widgets/widget-list-box.php';
			
			$link = '<a class="dws-widget-settings" href="'. admin_url('options-general.php?page=dashboard_widgets_suite&tab=tab5') .'"><span class="fa fa-cog"></span></a>';
			
			wp_add_dashboard_widget('dashboard_widgets_suite_list_box', esc_html__('List Box', 'dashboard-widgets-suite') . $suite . $link, 'dashboard_widgets_suite_list_box');
			
		}
		
	}
	
	// widget box
	
	if (isset($dws_options_widget_box['widget_widget_box']) && $dws_options_widget_box['widget_widget_box']) {
		
		$display_widget_box = isset($dws_options_widget_box['widget_widget_box_view']) ? $dws_options_widget_box['widget_widget_box_view'] : null;
		
		if (dashboard_widgets_suite_check_role($display_widget_box)) {
			
			require_once DWS_DIR .'widgets/widget-widget-box.php';
			
			$link = '<a class="dws-widget-settings" href="'. admin_url('options-general.php?page=dashboard_widgets_suite&tab=tab6') .'"><span class="fa fa-cog"></span></a>';
			
			wp_add_dashboard_widget('dashboard_widgets_suite_widget_box', esc_html__('Widget Box', 'dashboard-widgets-suite') . $suite . $link, 'dashboard_widgets_suite_widget_box');
			
		}
		
	}
	
	// system info
	
	if (isset($dws_options_system_info['widget_system_info']) && $dws_options_system_info['widget_system_info']) {
		
		$display_system_info = isset($dws_options_system_info['widget_system_info_view']) ? $dws_options_system_info['widget_system_info_view'] : null;
		
		if (dashboard_widgets_suite_check_role($display_system_info)) {
			
			require_once DWS_DIR .'widgets/widget-system-info.php';
			
			$link = '<a class="dws-widget-settings" href="'. admin_url('options-general.php?page=dashboard_widgets_suite&tab=tab7') .'"><span class="fa fa-cog"></span></a>';
			
			wp_add_dashboard_widget('dashboard_widgets_suite_system_info', esc_html__('System Info', 'dashboard-widgets-suite') . $suite . $link, 'dashboard_widgets_suite_system_info');
			
		}
		
	}
	
	// user notes
	
	if (isset($dws_options_notes_user['widget_notes_user']) && $dws_options_notes_user['widget_notes_user']) {
		
		require_once DWS_DIR .'widgets/widget-notes-user.php';
		
		$link = '<a class="dws-widget-settings" href="'. admin_url('options-general.php?page=dashboard_widgets_suite&tab=tab2') .'"><span class="fa fa-cog"></span></a>';
		
		wp_add_dashboard_widget('dashboard_widgets_suite_notes_user', esc_html__('User Notes', 'dashboard-widgets-suite') . $suite . $link, 'dashboard_widgets_suite_notes_user');
		
	}
	
	// 
	
}

add_action('wp_dashboard_setup', 'dashboard_widgets_suite_enable_widgets');
