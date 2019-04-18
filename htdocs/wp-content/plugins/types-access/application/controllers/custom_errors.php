<?php

namespace OTGS\Toolset\Access\Controllers;

use OTGS\Toolset\Access\Controllers\PermissionsRead as PermissionsReads;
use OTGS\Toolset\Access\Models\Settings as Settings;
use OTGS\Toolset\Access\Models\UserRoles as UserRoles;
use OTGS\Toolset\Access\Controllers\Actions\FrontendActions as FrontendActions;
use OTGS\Toolset\Access\Controllers\Filters\FrontendFilters as FrontendFilters;

/**
 * Manage custom read errors for single posts and archives
 *
 * Class CustomErrors
 *
 * @package OTGS\Toolset\Access\Models
 * @since 2.7
 */
class CustomErrors {

	private static $instance;

	private $custom_read_permissions;

	private $read_permissions_set;


	/**
	 * @return CustomErrors
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public static function initialize() {
		self::get_instance();
	}


	/**
	 * CustomErrors constructor.
	 */
	public function __construct() {
		if ( empty( $this->access_settings ) ) {
			$this->access_settings = Settings::get_instance();
			$this->access_roles = UserRoles::get_instance();
		}

	}


	/**
	 * @param $post_type
	 * @param $post_id
	 */
	public function set_archive_custom_read_errors( $post_type, $post_id ) {
		global $wp_post_types;
		$permissions_read = PermissionsReads::get_instance();
		if ( $post_type !== 'attachment' ) {

			$custom_archive_error_info = \Access_Cacher::get( 'wpcf-access-archive-permissions-' . $post_type );
			if ( false === $custom_archive_error_info ) {
				$custom_archive_error = $this->get_archive_custom_errors( $post_type );
				\Access_Cacher::set( 'wpcf-access-archive-permissions-' . $post_type, $custom_archive_error_info );
			}

			if ( class_exists( 'WPDD_Layouts' ) && apply_filters( 'ddl-is_integrated_theme', false ) ) {
				$wp_post_types[ $post_type ]->public = true;
			}

			if ( is_array( $custom_archive_error ) && empty( $post_id ) ) {

				list( $action, $source, $item_id ) = $custom_archive_error;

				if ( $action == 'unhide' ) {
					$permissions_read->hidden_post_types = array_diff( $permissions_read->hidden_post_types, array( $post_type ) );

					$frontend_actions = FrontendActions::get_instance();

					if ( $source == 'view' ) {
						if ( function_exists( 'wpv_force_wordpress_archive' ) ) {
							add_filter( 'wpv_filter_force_wordpress_archive', array(
								$frontend_actions,
								'toolset_access_replace_archive_view',
							) );
						}
					}
					if ( $source == 'layout' && ! empty( $item_id ) ) {
						add_filter( 'ddl-is_ddlayout_assigned', array(
							$frontend_actions,
							'toolset_access_load_layout_archive_is_assigned',
						) );
						add_action( 'wp_head', array(
							$frontend_actions,
							'toolset_access_error_template_archive_layout',
						) );
					}
					if ( $source == 'php' ) {
						add_action( 'template_redirect', array(
							$frontend_actions,
							'toolset_access_replace_archive_php_template',
						) );
					}
				}
			}
		}
	}


	/**
	 * @param $post_type
	 *
	 * @return array|void
	 */
	private function get_archive_custom_errors( $post_type ) {

		$role = $this->access_roles->get_main_role();
		if ( $role == 'administrator' ) {
			return;
		}

		$settings_access = $this->access_settings->get_types_settings();

		$error_types = array(
			'error_ct' => 'view',
			'error_layouts' => 'layout',
			'error_php' => 'php',
			'default_error' => '404',
			'default' => '404',
		);

		if ( ! isset( $settings_access['_archive_custom_read_errors'][ $post_type ]['permissions']['read'] ) ) {
			return;
		}

		$custom_error_types = $settings_access['_archive_custom_read_errors'][ $post_type ]['permissions']['read'];
		$custom_error_values = ( isset( $settings_access['_archive_custom_read_errors_value'][ $post_type ]['permissions']['read'] )
			? $settings_access['_archive_custom_read_errors_value'][ $post_type ]['permissions']['read'] : '' );

		if ( isset( $custom_error_types[ $role ] ) ) {

			$error_type = $custom_error_types[ $role ];

			if ( ! empty( $custom_error_values ) ) {
				$error_value = $custom_error_values[ $role ];
				\Access_Cacher::set( 'wpcf_archive_error_value_' . $post_type, $error_value );

				return array( 'unhide', $error_types[ $error_type ], $error_value );
			} else {
				return;
			}
		}

		if ( isset( $custom_error_types['everyone'] ) && ! empty( $custom_error_types['everyone'] ) ) {
			$error_type = $custom_error_types['everyone'];

			if ( ! empty( $custom_error_values ) ) {
				$error_value = $custom_error_values['everyone'];

				\Access_Cacher::set( 'wpcf_archive_error_value_' . $post_type, $error_value );

				return array( 'unhide', $error_types[ $error_type ], $error_value );
			} else {
				return;
			}
		}
	}


	/**
	 * @param $post_type
	 *
	 * @return bool
	 */
	private function get_post_type_permissions( $post_type ) {
		global $current_user;
		$hide = true;
		$user_roles = $this->access_roles->get_current_user_roles();
		$settings_access = $this->access_settings->get_types_settings();

		if ( ( ! isset( $settings_access[ $post_type ] ) || $settings_access[ $post_type ]['mode'] == 'follow' )
			&& isset( $settings_access['post'] ) ) {
			$data = $settings_access['post']['permissions']['read'];
		} else {
			if ( isset( $settings_access[ $post_type ] ) ) {
				$data = $settings_access[ $post_type ]['permissions']['read'];
			} else {
				return false;
			}
		}

		$users = $roles = array();
		if ( isset( $data['users'] ) ) {
			$users = $data['users'];
		}
		if ( ! empty( $data['roles'] ) ) {
			$roles = $data['roles'];
		}

		if ( $this->access_settings->is_wpml_installed() ) {
			$wpml_settings = $this->access_settings->get_language_permissions();
			$current_post_language = apply_filters( 'wpml_current_language', null );
			$data_language = $wpml_settings[ $post_type ][ $current_post_language ]['read'];
			//Specific user
			if ( isset( $data_language['roles'] ) ) {
				$roles = $data_language['roles'];
			}
			if ( isset( $data_language['users'] ) ) {
				$users = $data_language['users'];
			}
		}

		// If user added as specific user
		if ( ! empty( $current_user->ID ) && in_array( $current_user->ID, $users ) !== false ) {
			$hide = false;
		}

		if ( $hide ) {
			if ( $this->access_settings->roles_in_array( $user_roles, $roles ) ) {
				$hide = false;
			}
		}

		return $hide;
	}


	/**
	 * @param $post_type
	 * @param $post_id
	 *
	 * @return array
	 */
	public function set_custom_errors( $post_type, $post_id ) {
		$role = $this->access_roles->get_main_role();
		if ( 'administrator' === $role ) {
			return array( 1, 'unhide' );
		}

		$return = 0;
		$do = '';

		$template = \Access_Cacher::get( 'wpcf-access-post-permissions-' . $post_id );
		if ( false === $template ) {
			$template = $this->get_custom_error( $post_id );
			\Access_Cacher::set( 'wpcf-access-post-permissions-' . $post_id, $template );
		}

		$custom_error = toolset_getarr( $template, 0, '' );
		$custom_error_value = toolset_getarr( $template, 1, '' );

		if ( 'error_ct' === $custom_error ) {
			$this->disable_the_content_hooks();
		}

		$disable_comments = false;
		$frontend_filters = FrontendFilters::get_instance();
		$frontend_actions = FrontendActions::get_instance();

		if ( ! empty( $custom_error_value ) && $custom_error == 'error_ct' ) {
			$do = 'unhide';
			$return = 1;
			$disable_comments = true;
			add_filter( 'wpv_filter_force_template', array(
				$frontend_filters,
				'toolset_access_error_content_template',
			), 20, 3 );
		}
		if ( ! empty( $custom_error_value ) && $custom_error == 'error_php' && ! $template[2] ) {
			$do = 'unhide';
			$return = 1;
			add_action( 'template_redirect', array(
				$frontend_actions,
				'toolset_access_error_php_template',
			), $custom_error_value );
		}
		if ( ! empty( $custom_error_value ) && $custom_error == 'error_layouts' ) {
			$do = 'unhide';
			$return = 1;
			add_action( 'wp', array( $frontend_actions, 'toolset_access_error_template_layout' ) );
		}
		if ( $custom_error == 'error_404' && ! $template[2] ) {
			$do = 'hide';
			add_action( 'pre_get_posts', array(
				$frontend_actions,
				'toolset_access_exclude_selected_post_from_single',
			), 0 );
			$return = 1;
		}
		if ( $template[2] ) {
			$do = 'unhide';
			$return = 1;
		}
		if ( ! $template[2] && empty( $custom_error ) ) {
			$do = 'hide';
			$return = 1;
		}


		return array( $return, $do, $disable_comments );
	}


	/**
	 * @param $post_id
	 *
	 * @return array
	 */
	public function get_custom_error( $post_id ) {
		global $current_user;
		$role = $this->access_roles->get_main_role();

		$settings_access = $this->access_settings->get_types_settings();

		$post_type = get_post_type( $post_id );
		$post_status = get_post_status( $post_id );

		$template_id = $show = '';
		$group = get_post_meta( $post_id, '_wpcf_access_group', true );
		$go = true;
		$read = false;


		if ( isset( $settings_access[ $post_type ]['permissions']['read'] )
			&& $settings_access[ $post_type ]['mode']
			== 'permissions' ) {
			$check_cap = $settings_access[ $post_type ]['permissions']['read'];
		} else {
			$check_cap = isset( $settings_access['post']['permissions']['read'] )
				? $settings_access['post']['permissions']['read'] : null;
			$post_type = 'post';
		}

		if ( ! isset( $check_cap['roles'] )
			|| ! isset( $settings_access[ $post_type ] )
			|| $settings_access[ $post_type ]['mode'] === 'not_managed' ) {
			return array( $show, '', true );
		}

		//Read permissions by Language
		if ( $this->access_settings->is_wpml_installed() ) {
			$wpml_settings = $this->access_settings->get_language_permissions();
			$current_post_language = apply_filters( 'wpml_current_language', null );
			if ( isset( $wpml_settings[ $post_type ][ $current_post_language ] ) ) {
				$check_cap = $wpml_settings[ $post_type ][ $current_post_language ];
				if ( isset( $check_cap['group'] ) ) {
					$group = $check_cap['group'];
				} else {
					$check_cap = $check_cap['read'];
				}
			}
		}

		//If group assigned to this post
		if ( isset( $group ) && ! empty( $group ) && isset( $settings_access[ $group ] )
			&& $post_status
			== 'publish' ) {
			$show = '';
			$group_permissions = $settings_access[ $group ]['permissions']['read'];
			if ( isset( $current_user->ID ) ) {
				if ( isset( $group_permissions['users'] )
					&& in_array( $current_user->ID, $group_permissions['users'] )
					!== false ) {
					return array( $show, '', true );
				}
			}
			if ( in_array( $role, $group_permissions['roles'] ) !== false ) {
				return array( $show, '', true );
			} else {
				$read = false;
			}


			//Check if current post and role have specific error.
			if ( isset( $settings_access['_custom_read_errors'][ $group ]['permissions']['read'][ $role ] ) && $go ) {
				$error_type = $settings_access['_custom_read_errors'][ $group ]['permissions']['read'][ $role ];
				$custom_error = isset( $settings_access['_custom_read_errors_value'][ $group ]['permissions']['read'][ $role ] )
					? $settings_access['_custom_read_errors_value'][ $group ]['permissions']['read'][ $role ] : '';
				if ( $error_type == 'error_404' ) {
					$show = $error_type;
					$go = false;
				}
				if ( ( $error_type == 'error_ct' || $error_type == 'error_layouts' ) && ! empty( $custom_error ) ) {
					$show = $error_type;
					$template_id = $custom_error;
					$go = false;
					$read = true;
				}
				if ( $error_type == 'error_php' && ! empty( $custom_error ) ) {
					$show = $error_type;
					$template_id = $custom_error;
					$go = false;
				}
			}

			//Check if current group have specific error
			if ( isset( $settings_access['_custom_read_errors'][ $group ]['permissions']['read']['everyone'] )
				&& $go ) {
				$error_type = $settings_access['_custom_read_errors'][ $group ]['permissions']['read']['everyone'];
				$custom_error = isset( $settings_access['_custom_read_errors_value'][ $group ]['permissions']['read']['everyone'] )
					? $settings_access['_custom_read_errors_value'][ $group ]['permissions']['read']['everyone'] : '';
				if ( $error_type == 'error_404' ) {
					$show = $error_type;
				}
				if ( ( $error_type == 'error_ct' || $error_type == 'error_layouts' ) && ! empty( $custom_error ) ) {
					$show = $error_type;
					$template_id = $custom_error;
				}
				if ( $error_type == 'error_php' && ! empty( $custom_error ) ) {
					$show = $error_type;
					$template_id = $custom_error;
				}
			}

			return array( $show, $template_id, $read );

		}

		// Check post type permissions
		if ( isset( $check_cap['roles'] )
			&& ( is_array( $check_cap['roles'] )
				&& in_array( $role, $check_cap['roles'] )
				!== false )
			|| ( array_key_exists( 'users', $check_cap ) && is_array( $check_cap['users'] )
				&& in_array( $current_user->ID, $check_cap['users'] ) !== false )
		) {
			return array( $show, '', true );
		}


		if ( $go ) {

			//Check if current post and role have specific error.
			if ( isset( $settings_access['_custom_read_errors'][ $post_type ]['permissions']['read'][ $role ] )
				&& $go ) {

				$error_type = $settings_access['_custom_read_errors'][ $post_type ]['permissions']['read'][ $role ];
				$custom_error = isset( $settings_access['_custom_read_errors_value'][ $post_type ]['permissions']['read'][ $role ] )
					? $settings_access['_custom_read_errors_value'][ $post_type ]['permissions']['read'][ $role ] : '';
				if ( $error_type == 'error_404' ) {
					$show = $error_type;
					$go = false;
					$read = false;
				}
				if ( ( $error_type == 'error_ct' || $error_type == 'error_layouts' ) && ! empty( $custom_error ) ) {
					$show = $error_type;
					$template_id = $custom_error;
					$go = false;
					$read = true;
				}
				if ( $error_type == 'error_php' && ! empty( $custom_error ) ) {
					$show = $error_type;
					$template_id = $custom_error;
					$go = false;
				}
			}

			//Check if current group have specific error
			if ( isset( $settings_access['_custom_read_errors'][ $post_type ]['permissions']['read']['everyone'] )
				&& $go ) {
				$error_type = $settings_access['_custom_read_errors'][ $post_type ]['permissions']['read']['everyone'];
				$custom_error = isset( $settings_access['_custom_read_errors_value'][ $post_type ]['permissions']['read']['everyone'] )
					? $settings_access['_custom_read_errors_value'][ $post_type ]['permissions']['read']['everyone']
					: '';
				if ( $error_type == 'error_404' ) {
					$show = $error_type;
					$go = false;
					$read = false;
				}
				if ( ( $error_type == 'error_ct' || $error_type == 'error_layouts' ) && ! empty( $custom_error ) ) {
					$show = $error_type;
					$template_id = $custom_error;
				}
				if ( $error_type == 'error_php' && ! empty( $custom_error ) ) {
					$show = $error_type;
					$template_id = $custom_error;
				}
			}

		}

		return array( $show, $template_id, $read );
	}


	/**
	 * Remove the_content filters from Elementor when render Content Template custom error
	 */
	public function disable_the_content_hooks() {
		global $wp_filter;
		$filters = $wp_filter['the_content'];
		foreach ( $filters as $priority => $filters_array ) {
			foreach ( $filters_array as $filter_index => $filter ) {
				if ( isset( $filter['function'][1] ) && is_object( $filter['function'][0] )
					&& 'Elementor\Frontend' == get_class( $filter['function'][0] )
					&& 'apply_builder_in_content' === $filter['function'][1] ) {
					remove_filter( 'the_content', array(
						$filter['function'][0],
						$filter['function'][1],
					), $priority );
				}
			}
		}
	}


	/**
	 * @return bool|string
	 */
	public function wpcf_access_get_current_page() {
		// Avoid breaking CLI
		if ( ! isset( $_SERVER['HTTP_HOST'] ) || ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return '';
		}

		$protocol = stripos( $_SERVER['SERVER_PROTOCOL'], 'https' ) === true ? 'https://' : 'http://';
		$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$post_types = get_post_types( '', 'names' );
		$stored_post_types = \Access_Cacher::get( 'wpcf-access-current-post-types' );
		if ( false === $stored_post_types ) {
			\Access_Cacher::set( 'wpcf-access-current-post-types', $post_types );
			$check_post_id = true;
		} else {
			if ( $post_types == $stored_post_types ) {
				$check_post_id = false;
			} else {
				\Access_Cacher::set( 'wpcf-access-current-post-types', $post_types );
				$check_post_id = true;
			}
		}

		$post_id = \Access_Cacher::get( 'wpcf-access-current-post-id' );
		if ( false === $post_id || $check_post_id ) {
			global $sitepress;
			if ( is_object( $sitepress ) ) {
				remove_filter( 'url_to_postid', array( $sitepress, 'url_to_postid' ) );
				$post_id = url_to_postid( $url );
				add_filter( 'url_to_postid', array( $sitepress, 'url_to_postid' ) );
				if ( empty( $post_id ) ) {
					$post_id = url_to_postid( $url );
				}
			} else {
				$post_id = url_to_postid( $url );
			}

			if ( ! isset( $post_id ) || empty( $post_id ) || $post_id == 0 ) {
				if ( count( $_GET ) == 1 && get_option( 'permalink_structure' ) == '' ) {
					foreach ( $_GET as $key => $val ) {
						$val = $this->wpcf_esc_like( $val );
						$key = $this->wpcf_esc_like( $key );
						if ( post_type_exists( $key ) ) {
							global $wpdb;
							$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = '%s' and post_type='%s'", $val, $key ) );
						}
					}
				}
			}

			if ( empty( $post_id ) ) {
				$homepage = get_option( 'page_on_front' );
				if ( get_home_url() . '/' == $url && $homepage != '' ) {
					$post_id = $homepage;
				}
			}

			if ( ! isset( $post_id ) || empty( $post_id ) ) {
				$post_id = '';
			} else {
				\Access_Cacher::set( 'wpcf-access-current-post-id', $post_id );
			}

			$post_id = \Access_Cacher::get( 'wpcf-access-current-post-id' );

		}

		return $post_id;
	}


	/**
	 * @param $text
	 *
	 * @return mixed
	 */
	public function wpcf_esc_like( $text ) {
		global $wpdb;
		if ( method_exists( $wpdb, 'esc_like' ) ) {
			return $wpdb->esc_like( $text );
		} else {
			return like_escape( esc_sql( $text ) );
		}
	}


	/**
	 * @return array
	 */
	public function get_hidden_post_types() {
		$permissions_read = PermissionsRead::get_instance();

		return $permissions_read->hidden_post_types;
	}


	/**
	 * Set read permissions
	 */
	public function set_frontend_read_permissions_action() {

		if ( $this->read_permissions_set ) {
			return;
		}
		if ( ! empty( $this->custom_read_permissions ) ) {
			for ( $i = 0; $i < count( $this->custom_read_permissions ); $i ++ ) {
				$this->set_frontend_read_permissions( $this->custom_read_permissions[ $i ][1] );
			}
			$this->read_permissions_set = true;
		}
	}
}