<?php
/**
 * Created by PhpStorm.
 * User: gen
 * Date: 05/04/18
 * Time: 10:44
 */

class Toolset_Framework_Installer_Import_Db extends Toolset_Framework_Installer_Install_Step {

	/**
	 * Step 3: import database and configure settings
	 * @return array
	 */
	function import_db() {

		$saved_options = $this->get_saved_options_list();

		$replace_path_placeholder = $this->proccess_database();

		$this->update_saved_options( $saved_options );

		$this->activate_plugins();

		$this->database_recursive_array_replace( $replace_path_placeholder );

		$this->fix_layouts_images( );

		$this->update_woocommerce_views();

		$data = $this->generate_respose_error( true, __( 'Database successfully imported and configured', 'wpvdemo' ) );

		return $data;
	}

	/**
	 * Replace URL inside layouts cells
	 */
	function fix_layouts_images( ) {
		global $wpdb;

		$upload_dir = preg_replace( "/.*(\/wp-content.*)/", "$1", $this->upload_dir['basedir'] ) . '/';

		$search = str_replace( '/', '\/', $this->current_site->site_url . '/files/' );
		$replace = str_replace( '/', '\/', $this->site_url . $upload_dir );

		$sql = "UPDATE {$wpdb->prefix}postmeta set meta_value = REPLACE( meta_value, '%s', '%s' ) WHERE meta_key = '_dd_layouts_settings'";
		$wpdb->query( $wpdb->prepare( $sql, $search, $replace) );

	}

	/**
	 * Restore WooCommerce Views templates path
	 */
	function update_woocommerce_views(){
		global $wpdb;

		$site_plugins = (array)$this->current_site->plugins;
		$views_woocommerce = 'views-woocommerce';


		if ( isset( $site_plugins[ $views_woocommerce ] ) ) {
			$plugin = ( array ) $site_plugins[ $views_woocommerce ];
			$search_dir = dirname( $plugin['file'] );
		} else {
			return;
		}

		$all_plugins = get_plugins();

		foreach( $all_plugins as $plugin_name => $plugin_info ) {
			if ( $plugin_info['Name'] == 'Toolset WooCommerce Views' ) {
				$replace_dir = dirname( $plugin_name );
			}
		}

		$sql = "UPDATE {$wpdb->prefix}options SET option_value = REPLACE( option_value, %s, %s ) WHERE ".
		       " option_name = 'woocommerce_views_theme_template_file' OR option_name = 'woocommerce_views_theme_archivetemplate_file' ";

		$wpdb->query( $wpdb->prepare( $sql, $search_dir, $replace_dir ) );

	}

	/**
	 * Recursively replace data
	 * @param $find
	 * @param $replace
	 * @param $data
	 *
	 * @return array|mixed
	 */
	function recursive_array_replace( $find, $replace, &$data ){
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					$this->recursive_array_replace( $find, $replace, $data[ $key ] );
				} else {
					if ( is_string( $value ) ) {
						$data[ $key ] = str_replace( $find, $replace, $value );
					}
				}
			}
		} else {
			if ( is_string( $data ) ) {
				$data = str_replace( $find, $replace, $data );
			}
		}
		return $data;
	}

	/**
	 * Find and replace site path and url
	 * @param $replace_path_placeholder
	 */
	function database_recursive_array_replace( $replace_path_placeholder = '' ) {
		global $wpdb;

		$upload_dir = preg_replace( "/.*(\/wp-content.*)/", "$1", $this->upload_dir['basedir'] ) . '/';

		$search_for = array( $this->current_site->site_url, '/wp-content/blogs.dir/' . $this->current_site->ID . '/files/', '/files/' );
		$replace_with = array( $this->site_url, $upload_dir, $upload_dir );

		if ( ! empty( $replace_path_placeholder ) ) {
			$search_for[] = $replace_path_placeholder;
			$replace_with[] = $this->wp_dir;
		}

		$search_for[] = '/views-trunk/';
		$replace_with[] = '/' . $this->get_plugin_directory_by_title( 'Toolset Views' ) . '/';


		$sql = "SHOW TABLES FROM `" . DB_NAME . "` WHERE 
				`Tables_in_" . DB_NAME . "` LIKE '{$wpdb->prefix}%';";
		$res = $wpdb->get_results( $sql );

		foreach( $res as $index => $table ) {

			$key = key( $table );
			$table = $table->$key;

			if ( strpos( $table, 'icl_languages_translations') !== false
			     || strpos( $table, 'icl_string_translations') !== false ){
				continue;
			}

			$sql = "DESCRIBE " . $table ;
			$column_name = $table_index = "";
			$i = 0;

			$table_res = $wpdb->get_results( $sql );

			foreach( $table_res as $tab_index => $table_info ) {
				$column_name[ $i++ ] = $table_info->Field;
				if ( $table_info->Key == 'PRI' ) {
					$table_index[ $i ] = true;
				}
			}
			if ( empty( $column_name ) || ! is_array( $column_name ) ) {
				continue;
			}

			$sql = "SELECT * FROM " . $table;
			$data = $wpdb->get_results( $sql );
			if ( empty( $data ) ) {
				continue;
			}
			foreach( $data as $data_index => $row ) {

				$need_to_update = false;
				$UPDATE_SQL = 'UPDATE ' . $table . ' SET ';
				$WHERE_SQL = ' WHERE ';

				$j = 0;

				foreach ( $column_name as $current_column ) {
					$j++;

					$data_to_fix = $row->$current_column;
					$edited_data = $data_to_fix;

					if ( is_serialized( $data_to_fix ) ) {
						$unserialized = @unserialize( $data_to_fix );
						$this->recursive_array_replace( $search_for, $replace_with, $unserialized );
						$edited_data = serialize( $unserialized );

					} else {
						if ( is_string( $data_to_fix ) ) {
							$edited_data = str_replace( $search_for, $replace_with, $data_to_fix );
						}
					}

					if ( $data_to_fix != $edited_data ) {
						if ( $need_to_update != false ) {
							$UPDATE_SQL = $UPDATE_SQL . ',';
						}
						$UPDATE_SQL     = $UPDATE_SQL . ' ' . $current_column . ' = "' . esc_sql( $edited_data ) . '"';
						$need_to_update = true;
					}

					if ( isset( $table_index[ $j ] ) && ! empty( $table_index[ $j ] ) ){
						$WHERE_SQL = $WHERE_SQL . $current_column . ' = "' . $row->$current_column . '" AND ';
					}
				}

				if ( $need_to_update ) {
					$WHERE_SQL = substr( $WHERE_SQL,0,-4 );
					$UPDATE_SQL = $UPDATE_SQL.$WHERE_SQL;
					$wpdb->query( $UPDATE_SQL );
				}
			}
		}
		unset( $data );
		unset( $table_res );
	}

	/**
	 * Activate plugins after installation
	 */
	function activate_plugins(){
		global $wpdb;
		$activate_wpml = false;
		if ( isset( $_POST['wpml'] ) && $_POST['wpml'] === 'wpml' ) {
			$activate_wpml = true;
		}

		$plugins = $added_plugins_titles = array();
		$site_plugins = $this->current_site->plugins;

		$all_plugins = get_plugins();

		foreach( $site_plugins as $plugin_name => $plugin_info ) {
			$plugin_name = $plugin_info->title;
			foreach( $all_plugins as $existing_plugin_name => $existing_plugin_info ) {
				if ( ! $activate_wpml && ( strpos( $existing_plugin_info['Name'], 'WPML') !== false ||
				                           strpos( $existing_plugin_info['Name'], 'Multilingual') !== false ) ) {
					continue;
				}

				if ( in_array( $existing_plugin_name, $plugins ) || in_array( $existing_plugin_info['Name'] , $added_plugins_titles ) ) {
					continue;
				}


				if ( $existing_plugin_info['Name'] === 'Toolset Framework Installer' ) {
					$plugins[] = $existing_plugin_name;
					$added_plugins_titles[] = $existing_plugin_info['Name'];
				}

				if ( $plugin_name === $existing_plugin_info['Name'] ) {
					if ( version_compare($existing_plugin_info['Version'], $plugin_info->version, '<' ) ) {
						continue;
					}
					$plugins[] = $existing_plugin_name;
					$added_plugins_titles[] = $existing_plugin_info['Name'];
				}

			}
		}

		$sql = "UPDATE {$wpdb->prefix}options set option_value='%s' where option_name='active_plugins'";
		$wpdb->query( $wpdb->prepare( $sql, serialize( $plugins ) ) );

	}

	/**
	 * Restore original site options
	 * @param $options
	 */
	function update_saved_options( $options ) {
		global $wpdb;
		foreach ( $options as $option_name => $option_value ) {

			if ( is_array( $option_value ) || is_object( $option_value ) ) {
				$option_value = serialize( $option_value );
			}
			$original_value = $wpdb->get_results("SELECT option_value from {$wpdb->prefix}options where option_name='{$option_name}'");
			if ( ! isset( $original_value[0] ) ) {
				$sql = "INSERT INTO {$wpdb->prefix}options VALUES('',%s,%s,'yes')";
				$wpdb->query( $wpdb->prepare( $sql, $option_name, $option_value ) );
			} else {
				$sql = "UPDATE {$wpdb->prefix}options set option_value=%s where option_name=%s";
				$wpdb->query( $wpdb->prepare( $sql, $option_value, $option_name ) );
			}
			update_option( $option_name, $option_value );
		}

		$sql = "UPDATE {$wpdb->prefix}options set option_value='%s' where option_name='home' or option_name='siteurl'";
		$wpdb->query( $wpdb->prepare( $sql, $this->site_url ) );


		$stylesheet = $this->get_selected_theme();
		$parent_theme = $this->get_parent_theme();
		$template = ( !empty( $parent_theme ) ? $this->get_parent_theme() : $stylesheet );

		$sql = "UPDATE {$wpdb->prefix}options set option_value='%s' where option_name='stylesheet'";
		$wpdb->query( $wpdb->prepare( $sql, $stylesheet ) );

		$sql = "UPDATE {$wpdb->prefix}options set option_value='%s' where option_name='template'";
		$wpdb->query( $wpdb->prepare( $sql, $template ) );

	}

	/**
	 * get WP options to restore after installation
	 * @return array
	 */
	function get_saved_options_list() {

		$saved_options = $this->get_saved_options_array();

		foreach ( $saved_options as $option_name => $option_value ) {
			$option = get_option( $option_name, $option_value );
			$saved_options[ $option_name ] = $option;
		}

		return $saved_options;
	}

	/**
	 * Return array of WP options to restore after installation
	 * @return array
	 */
	function get_saved_options_array(){
		$saved_options = array(
			'default_role' => '',
			'upload_path' => '',
			'upload_url_path' => '',
			'admin_email' => '',
			'mailserver_url' => '',
			'mailserver_login' => '',
			'mailserver_pass' => '',
			'mailserver_port' => '',
			'gmt_offset' => '',
			'new_admin_email' => '',
			'blog_upload_space' => '',
			'logged_in_key' => '',
			'dbprefix_new' => '',
			'dbprefix_old_dbprefix' => '',
			'WPLANG' => '',
			'new_admin_email' => '',
			'logged_in_salt' => '',
			'nonce_salt' => '',
			'nonce_key' => '',
			'auth_salt' => '',
			'auth_key' => '',
			'fidemo_connection_test' => ''
		);
		return $saved_options;
	}

	/**
	 * Errase database and import from dump file
	 * @return string
	 */
	function proccess_database() {
		global $wpdb;

		$templine = '';

		$sql = "SHOW TABLES FROM `" . DB_NAME . "` WHERE 
				`Tables_in_" . DB_NAME . "` LIKE '{$wpdb->prefix}%';";
		$res = $wpdb->get_results( $sql );

		$wpdb->query( "SET FOREIGN_KEY_CHECKS=0;" );
		foreach( $res as $index => $table ) {
			$key   = key( $table );
			$table = $table->$key;
			if ( strpos( $table, 'icl_') !== false || strpos( $table, 'woocommerce_') !== false ) {
				$wpdb->query( "DROP TABLE {$table}" );
			}
		}
		$wpdb->query( "SET FOREIGN_KEY_CHECKS=1;" );

		$fp = fopen( $this->upload_dir['basedir'] . '/fidemo_sql_dump.sql', 'r' );
		$replace_path_placeholder = '';
		while ( ( $line = fgets( $fp ) ) !== false ) {
			if ( substr( $line, 0, 2 ) == '--' || $line == '' ) {
				continue;
			}

			$templine .= $line;
			if (substr(trim($line), -1, 1) == ';') {
				$templine = str_replace( 'REFWPDBPREFIX_', $wpdb->prefix, $templine );
				$templine 	= preg_replace('/^(INSERT INTO)/i', 'INSERT IGNORE INTO', $templine );
				if ( empty( $replace_path_placeholder ) ) {
					preg_match( "/(\{z[z]+\})/", $templine, $res );
					if ( isset ( $res[0] ) ) {
						$replace_path_placeholder = $res[0];
					}
				}
				$wpdb->query( $templine );
				$templine = '';
			}
		}
		fclose($fp);
		unlink( $this->upload_dir['basedir'] . '/fidemo_sql_dump.sql' );
		return $replace_path_placeholder;
	}




}
