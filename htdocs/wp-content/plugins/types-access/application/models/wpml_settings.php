<?php

namespace OTGS\Toolset\Access\Models;

use OTGS\Toolset\Access\Controllers\PermissionsPostTypes;

/**
 * WPML Permissions class
 * Class WPMLSettings
 *
 * @package OTGS\Toolset\Access\Controllers\Model
 * @since 2.7
 */
class WPMLSettings {

	private static $instance;


	/**
	 * @return  WPMLSettings
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
	 *  WPMLSettings constructor.
	 */
	function __construct() {

	}


	/**
	 *  Update wpcf_access defaults if WPML is active and configured
	 */
	public function toolset_access_wpml_loaded() {
		global $wpcf_access;

		$wpcf_access->wpml_installed = apply_filters( 'wpml_setting', false, 'setup_complete' );
		$wpcf_access->wpml_installed_groups = false;
		$wpcf_access->active_languages = array();
		$wpcf_access->current_language = apply_filters( 'wpml_current_language', null );

		$access_roles = UserRoles::get_instance();
		$role = $access_roles->get_main_role();
		if ( $wpcf_access->wpml_installed ) {
			if ( wpml_version_is( '3.3', '>=' ) ) {
				$wpcf_access->active_languages = apply_filters( 'wpml_active_languages', '', array( 'skip_missing' => 0 ) );
				foreach ( $wpcf_access->active_languages as $lang => $lang_array ) {
					$keys_to_preserve = array( 'code', 'english_name', 'native_name', 'active' );
					$wpcf_access->active_languages[ $lang ] = array_intersect_key( $lang_array, array_fill_keys( $keys_to_preserve, null ) );
				}
				$wpcf_access->wpml_installed_groups = true;
				add_filter( 'wpml_active_languages_access', array(
					$this,
					'toolset_access_check_language_edit_permissions',
				), 10, 2 );
				add_filter( 'wpml_override_is_translator', array(
					$this,
					'toolset_access_wpml_override_is_translator',
				), 10, 3 );
				add_filter( 'wpml_link_to_translation', array( $this, 'toolset_access_filter_wpml_link' ), 11, 4 );
				add_filter( 'wpml_icon_to_translation', array( $this, 'toolset_access_filter_wpml_icon' ), 9, 4 );
				add_filter( 'wpml_text_to_translation', array( $this, 'toolset_access_filter_wpml_text' ), 9, 4 );
				add_filter( 'wpml_css_class_to_translation', array(
					$this,
					'toolset_access_filter_wpml_css_class',
				), 9, 4 );

			} else {
				$wpcf_access->wpml_installed = false;
			}
		}
	}


	/**
	 * @param $status
	 *
	 * @return mixed
	 * Return true when WPML plugin active and configured
	 */
	public function is_wpml_installed( $status ) {
		global $wpcf_access;
		$status = $wpcf_access->wpml_installed_groups;

		return $status;
	}


	/**
	 * Get current language selected
	 *
	 * @return mixed
	 */
	public function get_current_language() {
		global $wpcf_access;

		return $wpcf_access->current_language;
	}


	public function get_default_language() {
		$default_lang = apply_filters( 'wpml_default_language', null );

		return $default_lang;
	}


	/**
	 * Get post language by post id
	 *
	 * @param $id
	 *
	 * @return array|string
	 */
	public function get_language_by_post_id( $id ) {
		$access_cache_user_has_cap_key = md5( 'access::post_language_' . $id );
		$cached_caps = \Access_Cacher::get( $access_cache_user_has_cap_key, 'access_cache_post_languages' );
		if ( false === $cached_caps ) {
			$post_language = apply_filters( 'wpml_post_language_details', '', $id );
			\Access_Cacher::set( $access_cache_user_has_cap_key, $post_language, 'access_cache_post_languages' );
		} else {
			$post_language = $cached_caps;
		}

		return $post_language;
	}


	/**
	 * Load WPML groups permissions if exists
	 */
	public function toolset_load_wpml_groups_caps() {
		global $wpcf_access;
		$wpcf_access->language_permissions = array();
		$settings_access = $wpcf_access->settings->types;
		// Load language permissions from groups
		if ( is_array( $settings_access ) && ! empty( $settings_access ) ) {
			foreach ( $settings_access as $group_slug => $group_data ) {
				if ( strpos( $group_slug, 'wpcf-wpml-group-' ) !== 0 ) {
					continue;
				}
				if ( ! apply_filters( 'wpml_is_translated_post_type', null, $group_data['post_type'] ) ) {
					continue;
				}
				if ( isset( $group_data['languages'] )
					&& is_array( $group_data['languages'] )
					&& ! empty( $group_data['languages'] ) ) {
					foreach ( $group_data['languages'] as $lang => $lang_data ) {
						$wpcf_access->language_permissions[ $group_data['post_type'] ][ $lang ] = $group_data['permissions'];
						$wpcf_access->language_permissions[ $group_data['post_type'] ][ $lang ]['group'] = $group_slug;
					}
				}
			}
		}
		$this->load_wpml_languages_permissions();
	}


	/**
	 * Load missed WPML permissions
	 */
	public function load_wpml_languages_permissions() {
		global $wpcf_access;

		$access_settings = Settings::get_instance();
		$settings_access = $access_settings->get_types_settings();
		$_post_types = $access_settings->get_post_types_names();

		//Load language permissions from post_type, if group for language not exists
		$wpml_active_languages = $wpcf_access->active_languages;
		foreach ( $_post_types as $post_type ) {
			foreach ( $wpml_active_languages as $language => $language_data ) {
				//print_r($language_data);exit;
				if (
					! isset( $wpcf_access->language_permissions[ $post_type ][ $language ] )
					&& isset( $settings_access[ $post_type ]['permissions'] )
					&& $settings_access[ $post_type ]['mode'] != 'not_managed'
				) {
					$wpcf_access->language_permissions[ $post_type ][ $language ] = $settings_access[ $post_type ]['permissions'];
				} elseif (
					isset( $settings_access[ $post_type ]['permissions'] )
					&& $settings_access[ $post_type ]['mode'] == 'not_managed'
					&& isset( $settings_access['post']['permissions'] )
					&& $settings_access['post']['mode'] != 'not_managed'
				) {
					$wpcf_access->language_permissions[ $post_type ][ $language ] = $settings_access['post']['permissions'];
				}
			}
		}
	}


	/*
        Replace Translation management permissions with Access settings
    */
	public function toolset_access_wpml_override_is_translator( $is_translator, $user_id, $args ) {
		return true;
	}


	/**
	 * @param $text
	 * @param $post_id
	 * @param $lang
	 * @param $trid
	 *
	 * @return mixed
	 */
	public function toolset_access_filter_wpml_css_class( $css_class, $post_id, $lang, $trid ) {
		$post_type_permissions = PermissionsPostTypes::get_instance();
		$status = $this->wpml_check_access_by_post_id( $post_id, $lang );
		if ( ! $status['edit_any'] && ! $status['edit_own'] ) {
			if ( $css_class == 'otgs-ico-add' ) {
				$css_class = ' otgs-ico-add otgs-ico-add-disabled';
			} else {
				$css_class = ' otgs-ico-edit otgs-ico-edit-disabled';
			}
		}

		return $css_class;
	}


	/**
	 * @param $text
	 * @param $post_id
	 * @param $lang
	 * @param $trid
	 *
	 * @return mixed
	 */
	public function toolset_access_filter_wpml_text( $text, $post_id, $lang, $trid ) {
		$post_type_permissions = PermissionsPostTypes::get_instance();
		$status = $this->wpml_check_access_by_post_id( $post_id, $lang );
		if ( ! $status['edit_any'] && ! $status['edit_own'] ) {
			$text = __( 'You do not have permissions', 'wpcf-access' );
		}

		return $text;
	}


	/**
	 * @param $link
	 * @param $post_id
	 * @param $lang
	 * @param $trid
	 *
	 * @return string
	 */
	public function toolset_access_filter_wpml_link( $link, $post_id, $lang, $trid ) {
		$post_type_permissions = PermissionsPostTypes::get_instance();
		$status = $this->wpml_check_access_by_post_id( $post_id, $lang );
		if ( ! $status['edit_any'] && ! $status['edit_own'] ) {
			$link = '#no_privileges';
		} else {
			$link = remove_query_arg( 'return_url', $link );
		}

		return $link;
	}


	/**
	 * @param $icon
	 * @param $post_id
	 * @param $lang
	 * @param $trid
	 *
	 * @return string
	 * Replace existing translate post icon enabled/disabled
	 */
	function toolset_access_filter_wpml_icon( $icon, $post_id, $lang, $trid ) {
		$post_type_permissions = PermissionsPostTypes::get_instance();
		$status = $this->wpml_check_access_by_post_id( $post_id, $lang );

		if ( ! $status['edit_any'] && ! $status['edit_own'] ) {
			if ( $icon == 'add_translation.png' ) {
				$icon = 'add_translation_disabled.png';
			} else {
				$icon = 'edit_translation_disabled.png';
			}
		}

		return $icon;
	}


	/**
	 * @param $languages
	 * @param $args
	 *
	 * @return mixed
	 */
	public function toolset_access_check_language_edit_permissions( $languages, $args ) {
		global $wpcf_access, $typenow, $post;
		if ( ! isset( $args['action'] ) ) {
			return $languages;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return $languages;
		}
		$wpml_default_language = apply_filters( 'wpml_setting', '', 'default_language' );
		$action = $args['action'];
		$post_id = isset( $args['post_id'] ) ? $args['post_id'] : '';
		$post_type = isset( $args['post_type'] ) ? $args['post_type'] : '';
		$post_types_permissions = PermissionsPostTypes::get_instance();

		if ( empty( $post_type ) && ! empty( $typenow ) ) {
			$post_type = $typenow;
		}

		if ( empty( $post_id ) && isset( $_GET['post'] ) ) {
			$post_id = $_GET['post'];
		}

		if ( empty( $post_type ) && ! empty( $post_id ) ) {
			$post_type = get_post_field( 'post_type', $post_id );
		}

		if ( empty( $post_type ) && isset( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
		}

		//Get post type on front-end
		if ( ! is_admin() ) {
			if ( isset( $post->post_type ) ) {
				$post_type = $post->post_type;
			}
		}

		if ( empty( $post_type ) ) {
			$post_type = 'post';
		}
		if ( ! isset( $wpcf_access->settings->types[ $post_type ] ) ) {
			$post_type = $post_types_permissions->get_post_type_slug_by_name( $post_type, $post_type );

		}

		if ( ! isset( $wpcf_access->settings->types[ $post_type ] ) ) {
			return $languages;
		}

		$access_settings = $wpcf_access->language_permissions;

		if ( $action == 'read' ) {
			$access_settings = $wpcf_access->language_permissions;
			if ( isset( $access_settings[ $post_type ] ) && ! empty( $access_settings[ $post_type ] ) ) {
				$languages_permissions = $access_settings[ $post_type ];
				foreach ( $languages_permissions as $language => $language_permissions ) {
					$status = $this->wpml_check_access_by_post_id( '', $language, $post_type, array( 'read' => true ) );
					if ( ! $status['read'] ) {
						unset( $languages[ $language ] );
					}
				}
			}
		} else {
			if ( isset( $access_settings[ $post_type ] ) && ! empty( $access_settings[ $post_type ] ) ) {
				$languages_permissions = $access_settings[ $post_type ];
				foreach ( $languages_permissions as $language => $language_permissions ) {
					if ( isset( $args['main'] ) && $args['main'] && $language == $wpml_default_language ) {
						$additional_lang[ $language ] = $languages[ $language ];
					}
					$status = $this->wpml_check_access_by_post_id( '', $language, $post_type );
					if ( ! $status['edit_any'] && ! $status['edit_own'] ) {//&& $language != $wpml_default_language
						unset( $languages[ $language ] );

					}
				}
			}
		}

		return $languages;
	}


	/**
	 * @param $post_id
	 * @param $post_type
	 * @param $user_id
	 * @param $lang
	 *
	 * @return bool
	 */
	public function check_translation_by_post_id( $post_id, $post_type, $user_id, $requested_language ) {
		global $wpcf_access;
		if ( ! has_action( 'wpml_tm_loaded' ) || ! did_action( 'wpml_tm_loaded' ) ) {
			return false;
		}
		$translation_batches = $this->get_translation_batches( $user_id );
		if ( empty( $translation_batches ) ) {
			return false;
		}
		$wpml_active_languagess = $wpcf_access->active_languages;

		foreach ( $translation_batches as $batch_id => $batch ) {
			for ( $i = 0, $count = count( $batch ); $i < $count; $i ++ ) {
				if ( $batch[ $i ]['status'] == 'Complete'
					|| $batch[ $i ]['status'] == 'Translation complete'
					|| ! isset( $batch[ $i ]['original_post_type'] ) ) {
					continue;
				}
				$batch_post_type = substr( $batch[ $i ]['original_post_type'], 5 );
				$language_from = $batch[ $i ]['from_language'];
				$language_to = $batch[ $i ]['to_language'];
				$language_ask = $wpml_active_languagess[ $requested_language ];
				$language_ask = ( isset( $language_ask['translated_name'] ) ? $language_ask['translated_name']
					: $language_ask['english_name'] );
				if ( ! empty( $post_id ) ) {
					if ( $batch_post_type != $post_type ) {
						return false;
					}
					$original_id = $batch[ $i ]['original_doc_id'];

					$access_cache_user_has_cap_key = md5( 'access::post_language_' . $post_id );
					$cached_caps = \Access_Cacher::get( $access_cache_user_has_cap_key, 'access_cache_post_languages' );
					if ( false === $cached_caps ) {
						$post_language = apply_filters( 'wpml_post_language_details', '', $post_id );
						\Access_Cacher::set( $access_cache_user_has_cap_key, $post_language, 'access_cache_post_languages' );
					} else {
						$post_language = $cached_caps;
					}

					$post_language = ( isset( $post_language['translated_name'] ) ? $post_language['translated_name']
						: $post_language['native_name'] );

					if ( $original_id == $post_id ) {
						if ( $post_language == $language_from && $language_ask == $language_to ) {
							return true;
						}
					}
				} else {
					if ( $batch_post_type == $post_type
						&& ( $language_from == $language_ask
							|| $requested_language
							== $batch[ $i ]['language_code'] ) ) {
						return true;
					}
				}
			}

		}

		return false;
	}


	/**
	 * @param string $user
	 *
	 * @return array|bool|string
	 * Check if current user have active translation jobs
	 */
	public function get_translation_batches( $user = '' ) {
		global $wpdb, $current_user;
		if ( empty( $user ) ) {
			$user = $current_user->ID;
		}
		$translation_batches = \Access_Cacher::get( 'wpcf_access_translation_batches_' . $user );

		if ( false !== $translation_batches ) {
			return $translation_batches;
		}

		$translation_jobs_collection = new \WPML_Translation_Jobs_Collection( $wpdb, array(
			'limit_no' => 1000,
			'translator_id' => $user,
			'status__not' => 10,
		) );

		$translation_result = $translation_jobs_collection->get_paginated_batches( 0, 1000 );
		$translation_count = $translation_jobs_collection->get_count();
		if ( $translation_count == 0 ) {
			\Access_Cacher::set( 'wpcf_access_translation_batches_' . $user, '' );

			return '';
		}
		$temp_batches = array();
		foreach ( $translation_result['batches'] as $batch_id => $batch ) {
			$temp_batches[ $batch_id ] = $batch->get_jobs_as_array();
		}

		\Access_Cacher::set( 'wpcf_access_translation_batches_' . $user, $temp_batches );

		return $temp_batches;
	}


	/**
	 * @param $allcaps
	 * @param $post_type
	 * @param null $user
	 *
	 * @return mixed
	 */
	public function check_translation_jobs_exists( $allcaps, $post_type, $user = null ) {
		global $wp_post_types, $current_user;

		if ( empty( $user ) ) {
			$user = $current_user;
		}

		$translation_batches = $this->get_translation_batches( $user->ID );
		if ( empty( $translation_batches ) ) {
			return $allcaps;
		}

		foreach ( $translation_batches as $batch_id => $batch ) {
			$temp_batch = $batch;
			for ( $i = 0, $count = count( $temp_batch ); $i < $count; $i ++ ) {
				if ( $temp_batch[ $i ]['status'] == 'Complete'
					|| $temp_batch[ $i ]['status'] == 'Translation complete'
					|| ! isset( $temp_batch[ $i ]['original_post_type'] ) ) {
					continue;
				}
				$test_post_type = substr( $temp_batch[ $i ]['original_post_type'], 5 );
				if ( $test_post_type == $post_type ) {
					$allcaps['edit'] = true;
					$allcaps['edit_published'] = true;

					return $allcaps;
				}
			}

		}

		return $allcaps;
	}


	/**
	 * Set post type permissions by language
	 *
	 * @param $allcaps array
	 * @param $args array
	 * @param $caps array
	 * @param $user object
	 * @param $types_settings array
	 * @param $post_type array
	 * @param $roles array
	 *
	 * @return mixed
	 */
	public function set_post_type_permissions_wpml( $allcaps, $args, $caps, $user, $types_settings, $post_type, $roles ) {
		$access_capabilities = Capabilities::get_instance();
		$access_settings = Settings::get_instance();
		$post_type_permissions_class = PermissionsPostTypes::get_instance();

		$requested_capabilties = array(
			'edit_any' => true,
			'edit_own' => true,
			'publish' => true,
			'delete_any' => true,
			'delete_own' => true,
		);
		$user_caps = array(
			'edit' => false,
			'edit_published' => false,
			'edit_others' => false,
			'publish' => false,
			'delete' => false,
			'delete_others' => false,
			'delete_published' => false,
		);

		$post_type_cap = $post_type['post_type'];


		if ( isset( $args[2] ) ) {
			$post_id = $args[2];
			if ( isset( $args[3] ) && is_object( $args[3] ) && isset( $args[3]->ID ) ) {
				$post_id = $args[3]->ID;
			}
			$post_language = $this->get_language_by_post_id( $post_id );
			if ( is_object( $post_language ) ) {
				$post_language = $access_settings->object_to_array( $post_language );
			}
			if ( empty( $post_language ) || ! isset( $post_language['language_code'] ) ) {
				$post_language = $this->get_current_language();
			} else {
				$post_language = $post_language['language_code'];
			}
		} else {
			$post_language = $this->get_current_language();
		}

		if ( $post_language == 'all' ) {
			$post_language = $this->get_default_language();
		}

		if ( empty( $post_language ) ) {
			$allcaps = $access_capabilities->bulk_allcaps_update( $user_caps, $post_type_cap, $user, $allcaps, $post_type['plural'] );

			return $allcaps;
		}

		$additional_key = '';
		if ( isset( $args[2] ) && ! empty( $args[2] ) ) {
			$additional_key = 'edit_own' . $args[2];
		}
		$access_cache_posttype_languages_caps_key_single = md5( 'access::postype_language_cap__single_'
			. $post_type_cap
			. $additional_key );
		$cached_post_type_caps = \Access_Cacher::get( $access_cache_posttype_languages_caps_key_single, 'access_cache_posttype_languages_caps_single' );

		//Load cached capabilities
		if ( false !== $cached_post_type_caps ) {
			$access_capabilities->bulk_allcaps_update( $cached_post_type_caps, $post_type_cap, $user, $allcaps, $post_type['plural'] );

			return $allcaps;
		}

		if ( isset( $types_settings[ $post_type['post_type_slug'] ] ) ) {
			$post_type_permissions = $types_settings[ $post_type['post_type_slug'] ];
			$post_type_permissions = $post_type_permissions[ $post_language ];
			$parsed_caps = $post_type_permissions_class->parse_post_type_caps( $post_type_permissions, $requested_capabilties, $roles );
			if ( ! isset( $args[2] ) || empty( $args[2] ) ) {
				$this->disable_add_new_button_wpml( $parsed_caps, $post_language, $post_type, $user );
			}
			// Enable post type menu if a user has no edit permissions for default language
			if ( ! $parsed_caps['edit_own'] && ( ! isset( $args[2] ) || empty( $args[2] ) ) ) {
				foreach ( $types_settings[ $post_type['post_type_slug'] ] as $lang => $lang_data ) {
					if ( $lang != $post_language ) {
						if ( ! $parsed_caps['edit_own'] ) {
							$parsed_caps = $post_type_permissions_class->parse_post_type_caps( $lang_data, $requested_capabilties, $roles );
						} else {
							continue;
						}
					}
				}
			}
			$user_caps = $post_type_permissions_class->generate_user_caps( $parsed_caps, $user_caps );
		}

		if ( ! isset( $args[2] ) && ! $user_caps['edit'] ) {
			if ( has_action( 'wpml_tm_loaded' ) && did_action( 'wpml_tm_loaded' ) ) {
				$user_caps = $this->check_translation_jobs_exists( $user_caps, $post_type['post_type_slug'], $user );
			}
		}
		$user_caps['create'] = true;
		$allcaps = $access_capabilities->bulk_allcaps_update( $user_caps, $post_type_cap, $user, $allcaps, $post_type['plural'] );

		\Access_Cacher::set( $access_cache_posttype_languages_caps_key_single, $user_caps, 'access_cache_posttype_languages_caps_single' );

		return $allcaps;
	}


	/**
	 * Disable 'Add new' button for current language if user has no edit_own permission
	 *
	 * @param string $post_type
	 * @param array $access_settings
	 * @param object $user
	 *
	 * @since 2.2
	 */
	public function disable_add_new_button_wpml( $user_caps, $lang, $post_type, $user ) {
		global $wpcf_access;
		$post_type_permissions_class = PermissionsPostTypes::get_instance();
		$post_type = $post_type['post_type_slug'];
		$_post_types = get_post_types( array(), 'objects' );
		if ( ! $user_caps['edit_own'] && $wpcf_access->current_language == $lang ) {
			$post_type_permissions_class->disable_add_new_button_for_post_type( $post_type, $_post_types[ $post_type ] );
		}

		if ( ! $user_caps['edit_own']
			&& $wpcf_access->current_language == 'all'
			&& isset( $_post_types[ $post_type ] ) ) {
			$post_type_permissions_class->disable_add_new_button_for_post_type( $post_type, $_post_types[ $post_type ] );
		}
	}


	/**
	 * Check WPML permissions by post id
	 *
	 * @param $post_id
	 * @param $lang
	 * @param string $post_type
	 * @param array $caps_to_check
	 * @param string $user
	 *
	 * @return array
	 */
	public function wpml_check_access_by_post_id(
		$post_id, $lang, $post_type = '', $caps_to_check = array(
		'edit_any' => true,
		'edit_own' => true,
	), $user = ''
	) {
		global $wpcf_access, $current_user, $typenow;

		$access_roles = UserRoles::get_instance();
		$post_type_permissions_class = PermissionsPostTypes::get_instance();

		$user_id = $current_user->ID;

		if ( empty( $post_id ) && isset( $_GET['post'] ) ) {
			$post_id = $_GET['post'];
		}

		if ( empty( $post_type ) && ! empty( $typenow ) ) {
			$post_type = $typenow;
		}

		if ( empty( $post_type ) && ! empty( $post_id ) ) {
			$post_type = get_post_field( 'post_type', $post_id );
		}

		if ( empty( $post_type ) && isset( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
		}

		if ( empty( $post_type ) ) {
			$post_type = 'post';
		}

		if ( empty( $user ) ) {
			$user = $current_user;
		}

		$output = $caps_to_check;

		$access_settings = $wpcf_access->language_permissions;
		$roles = $access_roles->get_current_user_roles();

		if ( isset( $access_settings[ $post_type ][ $lang ] ) && ! empty( $access_settings[ $post_type ][ $lang ] ) ) {
			$language_permissions = $access_settings[ $post_type ][ $lang ];

			if ( ! empty( $post_id ) ) {
				$post_author = get_post_field( 'post_author', $post_id );
			} else {
				$post_author = $user_id;
			}

			foreach ( $caps_to_check as $cap => $status ) {
				if ( ! isset( $language_permissions[ $cap ] ) ) {
					continue;
				}

				${$cap} = $language_permissions[ $cap ]['roles'];
				if ( isset( $language_permissions[ $cap ]['users'] ) ) {
					${$cap . '_users'} = $language_permissions[ $cap ]['users'];
				}
				$output[ $cap ] = false;
				$_cap = str_replace( '_any', '_own', $cap );
				if ( ! is_array( ${$cap} ) ) {
					${$cap} = array( ${$cap} );
				}
				$roles_check = array_intersect( $roles, ${$cap} );
				if ( strpos( $cap, 'own' ) == ''
					&& ( ! empty( $roles_check )
						|| ( isset( ${$cap . '_users'} )
							&& in_array( $user_id, ${$cap . '_users'} ) ) ) ) {
					$output[ $cap ] = true;
					$output[ 'temp_' . $_cap ] = true;
				}

				if ( strpos( $cap, 'own' ) > 0 && $user_id == $post_author
					&& ( ! empty( $roles_check )
						|| ( isset( ${$cap . '_users'} )
							&& in_array( $user_id, ${$cap
								. '_users'} ) ) ) ) {
					$output[ $cap ] = true;
				}
				if ( strpos( $cap, 'own' ) > 0 && isset( $output[ 'temp_' . $_cap ] ) && $output[ 'temp_' . $_cap ] ) {
					$output[ $cap ] = true;
					unset( $output[ 'temp_' . $_cap ] );
				}
				if ( ! $output[ $cap ] ) {
					$output[ $cap ] = $this->check_translation_by_post_id( $post_id, $post_type, $user_id, $lang );
				}
			}
		} else {
			foreach ( $output as $cap => $status ) {
				$output[ $cap ] = false;
			}
		}

		return $output;

	}

}