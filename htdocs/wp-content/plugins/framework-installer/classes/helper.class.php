<?php

/**
 * Class Toolset_Framework_Installer_Install_Step
 * @since 3.0
 */
abstract class Toolset_Framework_Installer_Install_Step {

	public $site_id;
	public $sites;
	public $current_site;
	public $dest;
	public $site_url;


	public function __construct(){

		$this->site_id = intval( $_POST['site_id'] );
		$this->sites = fidemo_get_reference_sites();
		$this->current_site = $this->sites[ $this->site_id ];
		$this->upload_dir = wp_upload_dir();
		$this->download_file_name = sanitize_title( $this->current_site->title );
		$this->dest = $this->upload_dir['basedir'] . '/'. $this->download_file_name .'.zip';
		$this->theme_dest = $this->upload_dir['basedir'] . '/theme.zip';
		$this->theme_parent_dest = $this->upload_dir['basedir'] . '/theme_parent.zip';
		$this->site_url = get_site_url();
		$this->wp_dir = get_home_path();
		$this->theme = '';
		$this->theme_parent = '';

		@ini_set('memory_limit', '256M');
		@ini_set("max_execution_time", '5000');
		@ini_set("max_input_time", '5000');
		@ini_set('default_socket_timeout', '5000');
		@set_time_limit( 5000 );

		add_action('fidemo_log_refsites_to_toolset', array( $this, 'fidemo_log_refsites_to_toolset' ), 10 );

	}

	/**
	 * Check if current site is multilingual
	 * @return bool
	 */
	function is_wpml_site(){
		$site_plugins = $this->current_site->plugins;
		foreach( $site_plugins as $plugin_name => $plugin_info ) {
			$plugin_name = $plugin_info->title;
			if ( ( strpos( $plugin_name, 'WPML') !== false || strpos( $plugin_name, 'Multilingual') !== false ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $title string
	 *
	 * @return string
	 */
	function get_plugin_directory_by_title( $title ) {
		$plugin_dir = '';
		$all_plugins = get_plugins();

		foreach( $all_plugins as $plugin_name => $plugin_info ) {
			if ( $plugin_info['Name'] == $title ) {
				$plugin_dir = dirname( $plugin_name );
			}
		}
		return $plugin_dir;
	}

	/**
	 * Log installation on wp-types.com
	 */
	function fidemo_log_refsites_to_toolset() {

		if ( ! class_exists( 'Framework_Installer_Log_Refsites_Class' ) ) {
			require_once FIDEMO_ABSPATH . '/classes/class-log-refsites.php';
		}

		//require_once WPVDEMO_ABSPATH . '/classes/class-absolute-url-references.php';
		if ( ! isset( $fi_log_refsite ) ) {
			//Instantiate class
			$fi_log_refsite= new Framework_Installer_Log_Refsites_Class();

			if ($fi_log_refsite->wpvdemo_exclude_implementation()) {

				//Get reference site type name
				$refsite_type_name = $this->current_site->title;

				//Get language version installed
				$refsite_language_version = $fi_log_refsite->wpvdemo_get_language_version_installed();

				//Get site URL
				$refsite_target_url = $this->get_site_url();

				if (  ! empty( $refsite_type_name ) && ! empty( $refsite_language_version ) && ! empty( $refsite_target_url ) ) {

					//Call the remote log
					$fi_log_refsite->wpvdemo_remote_post_data( $refsite_type_name,$refsite_language_version,$refsite_target_url );
				}
			}
		}
	}

	/**
	 * @param bool $return_protocol
	 *
	 * @return mixed
	 */
	public function get_site_url( $return_protocol = false ) {

		$get_site_url= site_url();
		if ( $return_protocol ) {
			return $get_site_url;
		}
		$get_site_url_clean = str_replace( parse_url( $get_site_url, PHP_URL_SCHEME ) . '://', '', $get_site_url );

		return $get_site_url_clean;
	}

	/**
	 * @return mixed
	 */
	function get_selected_theme() {
		$theme = $this->current_site->themes->theme;
		if ( isset( $_POST['multiple_themes'] ) && ! empty( $_POST['multiple_themes'] ) ) {
			$theme = sanitize_text_field( $_POST['multiple_themes'] );
		}
		return $theme;
	}

	/**
	 * @return mixed
	 */
	function get_theme_version() {
		$version = $this->current_site->themes->theme_version;
		if ( isset( $_POST['multiple_themes'] ) && ! empty( $_POST['multiple_themes'] ) ) {
			$theme = sanitize_text_field( $_POST['multiple_themes'] );
			$version = $this->current_site->themes->additional_themes->$theme->version;
		}
		return $version;
	}

	/**
	 * @return string
	 */
	function get_parent_theme_version() {
		if ( isset( $this->current_site->themes->parent_theme_version ) ) {
			return $this->current_site->themes->parent_theme_version;
		}
		return '';
	}

	/**
	 * @return string
	 */
	function get_parent_theme() {
		$theme = '';
		if ( isset( $this->current_site->themes->theme_parent ) ) {
			$theme = $this->current_site->themes->theme_parent;
		}
		return $theme;
	}

	/**
	 * Generate response array
	 * @param $success
	 * @param $message
	 * @param string $nonce
	 *
	 * @return array
	 */
	function generate_respose_error( $success, $message, $nonce = '' ) {
		$status = $success ? 'complete' : 'error';
		$data = array( 'status' => $status, 'message' => $message );
		if ( ! empty( $nonce ) ) {
			$data['nonce'] = $nonce;
		}
		return $data;
	}

	/**
	 * @param $theme
	 * @param $version
	 *
	 * @return bool
	 */
	function is_theme_installed( $theme, $version ) {
		$themes = wp_get_themes( array( 'allowed' => 'network' ) );
		if ( array_key_exists( $theme, $themes ) ) {
			$theme_version = $themes[ $theme ]->display('Version');
			if ( version_compare( $theme_version, $version ) >= 0 ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Test download speed
	 * @return bool|string
	 */
	function connection_test() {
		global $frameworkinstaller;
		$connection_test = get_option( 'fidemo_connection_test', false );
		if ( $connection_test !== false ) {
			return $connection_test;
		}

		$test_file = FIDEMO_URL . '/_reference_sites/speed_test.txt';

		$time = explode( " ",microtime() );
		$start = $time[0] + $time[1];

		$status = $frameworkinstaller->download_file( $test_file, $this->upload_dir['basedir'] . '/speed_test.txt' );
		if ( ! $status ) {
			return false;
		}
		$filesize = filesize($this->upload_dir['basedir'] . '/speed_test.txt') / 1024;

		$time = explode( " ",microtime() );
		$finish = $time[0] + $time[1];
		$deltat = $filesize / ( $finish - $start );

		$return = 'slow';
		if ( $deltat > 500 ){
			$return = 'fast';
		}

		update_option( 'fidemo_connection_test', $return );
		return $return;
	}

	/**
	 * @return string
	 */
	function use_optimized_version() {
		$connection_test = $this->connection_test();
		if ( ( defined( 'FIDEMO_OPTIMIZED' ) && FIDEMO_OPTIMIZED === true ) || $connection_test === 'slow' ) {
			return true;
		}
		return false;
	}

	/**
	 * @return string
	 */
	function use_optimized_prefix() {
		if ( $this->use_optimized_version() ) {
			return '_original';
		}
	}

	/**
	 *
	 */
	function clean_uploads_dir() {
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $this->upload_dir['basedir'], RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $fileinfo ) {
			$todo = ( $fileinfo->isDir() ? 'rmdir' : 'unlink' );
			$todo( $fileinfo->getRealPath() );
		}
	}

}