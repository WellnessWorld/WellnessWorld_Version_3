<?php

namespace OTGS\Toolset\Access\Controllers;

use OTGS\Toolset\Access\Models\Capabilities;
use OTGS\Toolset\Access\Models\Settings;
use OTGS\Toolset\Access\Models\UserRoles;
use OTGS\Toolset\Access\Models\WPMLSettings;

/**
 * Main post types controller
 * Set edit, delete and publish permissions
 *
 * Class PermissionsPostTypes
 *
 * @package OTGS\Toolset\Access\Controllers
 * @since 2.7
 */
class PermissionsPostTypes {

	private static $instance;


	/**
	 * @return PermissionsPostTypes
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
	 * PermissionsPostTypes constructor.
	 */
	public function __construct() {
		add_action( 'registered_post_type', array( $this, 'registered_post_type_hook' ), 10, 2 );
	}


	/**
	 * Maps rules and settings for post types registered outside of Types.
	 *
	 * @param type $post_type
	 * @param type $args
	 */
	public function registered_post_type_hook( $post_type, $args ) {
		global $wpcf_access, $wp_post_types;
		$access_settings = Settings::get_instance();
		$access_capabilities = \OTGS\Toolset\Access\Models\Capabilities::get_instance();
		$access_roles = UserRoles::get_instance();
		$settings_access = $access_settings->get_types_settings();

		list( $plural, $singular ) = toolset_access_get_post_type_names( $post_type );
		if ( empty( $plural ) ) {
			return;
		}

		$tmp_post_type_object = unserialize( serialize( $wp_post_types[ $post_type ] ) );
		$capability_type = array( $singular, $plural );
		$tmp_post_type_object->capability_type = $capability_type;
		$tmp_post_type_object->map_meta_cap = true;
		$tmp_post_type_object->capabilities = array();
		$tmp_post_type_object->cap = get_post_type_capabilities( $tmp_post_type_object );

		$is_post_managed = ( isset( $settings_access['post'] ) && $settings_access['post']['mode'] == 'permissions' );

		/**
		 * TODO: change this when WordPress will fix capabilities for child menu elements
		 * This code fix the issue with CPT capability when CPT added as child to other (parent) CPT menu
		 * and current user has no edit permissions for parent CPT
		 * The issue is: Wordpress use parent edit capability for all elements in parent menu.
		 */
		if ( $is_post_managed ) {
			$post_type_data = $wp_post_types[ $post_type ];
			if ( isset( $post_type_data->show_in_menu_page )
				&& strpos( $post_type_data->show_in_menu_page, 'edit.php?post_type=' ) !== false ) {
				$parent_post_type = trim( str_replace( 'edit.php?post_type=', '', $post_type_data->show_in_menu_page ) );
				if ( isset( $wp_post_types[ $parent_post_type ] ) ) {
					$user_can_edit_parent = apply_filters( 'toolset_access_api_get_post_type_permissions', false, 'post', 'edit_own' );
					if ( ! $user_can_edit_parent ) {
						$post_type_data->show_in_menu_page = true;
						$post_type_data->show_in_menu = true;
					}
				}
			}
		}


		$wp_post_types[ $post_type ]->__accessIsCapValid = ! $access_capabilities->check_cap_conflict( array_values( (array) $tmp_post_type_object->cap ) );
		$wp_post_types[ $post_type ]->__accessIsNameValid = $access_settings->is_object_valid( 'type', $access_settings->object_to_array( $tmp_post_type_object ) );
		$wp_post_types[ $post_type ]->__accessNewCaps = $tmp_post_type_object->cap;

		if ( isset( $settings_access[ $post_type ] ) || isset( $settings_access['post'] ) ) {
			$data = isset( $settings_access[ $post_type ] ) ? $settings_access[ $post_type ] : $settings_access['post'];

			// Mark that will inherit post settings
			// TODO New types to be added
			if (
				! in_array( $post_type, array( 'post', 'page', 'attachment', 'media' ) )
				&& ( empty( $wp_post_types[ $post_type ]->capability_type )
					|| $wp_post_types[ $post_type ]->capability_type == 'post' )
			) {
				$wp_post_types[ $post_type ]->_wpcf_access_inherits_post_cap = 1;
			}

			if (
				$data['mode'] == 'not_managed'
				||
				! $wp_post_types[ $post_type ]->__accessIsCapValid
				||
				! $wp_post_types[ $post_type ]->__accessIsNameValid
			) {
				if (
					! $is_post_managed
					||
					! $wp_post_types[ $post_type ]->public
					|| in_array( $post_type, array( 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset' ) )
				) {
					if ( ! isset( $settings_access[ $post_type ]['mode'] ) ) {
						$settings_access[ $post_type ]['mode'] = 'not_managed';
						$access_settings->updateAccessTypes( $settings_access );
					}
				}

				return false;
			}

			$access_capabilities = \OTGS\Toolset\Access\Models\Capabilities::get_instance();
			$caps = $access_capabilities->get_types_caps();

			if ( $data['mode'] !== 'follow' ) {
				$mapped = array();
				// Map predefined
				foreach ( $caps as $cap_slug => $cap_spec ) {
					if ( isset( $data['permissions'][ $cap_spec['predefined'] ] ) ) {
						$mapped[ $cap_slug ] = $data['permissions'][ $cap_spec['predefined'] ];
					} else {
						$mapped[ $cap_slug ] = $cap_spec['predefined'];
					}
				}
				// set singular / plural caps based on names or default for builtins
				$wp_post_types[ $post_type ]->capability_type = $capability_type;
				$wp_post_types[ $post_type ]->map_meta_cap = true;
				$wp_post_types[ $post_type ]->capabilities = array();
				$wp_post_types[ $post_type ]->cap = get_post_type_capabilities( $wp_post_types[ $post_type ] );
				unset( $wp_post_types[ $post_type ]->capabilities );

				// Set rule settings for post type by pre-defined caps
				foreach ( $args->cap as $cap_slug => $cap_spec ) {
					if ( isset( $mapped[ $cap_slug ] ) ) {
						if ( isset( $mapped[ $cap_slug ]['roles'] ) ) {
							$wpcf_access->rules->types[ $cap_spec ]['roles'] = $mapped[ $cap_slug ]['roles'];
						} else {
							$wpcf_access->rules->types[ $cap_spec ]['roles'] = $access_roles->get_roles_by_role( 'administrator' );
						}

						$wpcf_access->rules->types[ $cap_spec ]['users'] = isset( $mapped[ $cap_slug ]['users'] )
							? $mapped[ $cap_slug ]['users'] : array();
						$wpcf_access->rules->types[ $cap_spec ]['types'][$post_type/*$args->name*/] = 1;
					}
				}

				if ( ! isset( $wpcf_access->rules->types['create_posts'] )
					&& isset( $wpcf_access->rules->types['edit_posts'] ) ) {
					$wpcf_access->rules->types['create_posts'] = $wpcf_access->rules->types['edit_posts'];
				}
				if ( ! isset( $wpcf_access->rules->types['create_post'] )
					&& isset( $wpcf_access->rules->types['edit_post'] ) ) {
					$wpcf_access->rules->types['create_post'] = $wpcf_access->rules->types['edit_post'];
				}

			}
		}
		if ( $wpcf_access->wpml_installed ) {
			$wpml_settings_class = WPMLSettings::get_instance();
			$wpml_settings_class->load_wpml_languages_permissions();
		}
	}


	/**
	 * @param $allcaps array
	 * @param $caps array
	 * @param $args array
	 * @param $user object
	 * @param $type string
	 *
	 * @return array|mixed
	 */
	public function get_post_type_caps( $allcaps, $caps, $args, $user, $type ) {
		global $wpcf_access;
		$settings = Settings::get_instance();
		$access_roles = UserRoles::get_instance();
		$requested_capability = $args[0];
		$is_edit_comment = ( 'edit_comment' == $args[0] );
		if ( ! $is_edit_comment ) {
			if ( 'delete' === $type ) {
				$requested_capability = $caps[0];

				$post_type = str_replace( array(
					'delete_others_',
					'delete_private_',
					'delete_published_',
					'delete_',
				), '', $requested_capability );
			} else {
				$post_type = str_replace( array( $type . '_', 'others_' ), '', $requested_capability );
			}

			if ( 'edit_comment' == $args[0] ) {

			}

			if ( isset( $args[2] ) && ! empty( $args[2] ) ) {
				$post_type = $settings->determine_post_type( $args[2] );
			}
		} else {
			if ( isset( $args[2] ) ) {
				$comments_permissions = CommentsPermissions::get_instance();
				$post = $comments_permissions->get_comment_post( $args[2] );
				if ( empty( $post ) ) {
					return $allcaps;
				}
				$post_type = $post->post_type;
			} else {
				return $allcaps;
			}
		}

		list( $plural, $singular ) = $this->get_post_type_names( $caps, $args );
		$post_type_slug = $this->get_post_type_slug_by_name( $post_type, $singular );


		if ( empty( $singular ) || empty( $post_type_slug ) ) {
			return $allcaps;
		}

		$roles = $access_roles->get_current_user_roles();
		$types_settings = $settings->get_types_settings();
		if (
			! isset( $types_settings[ $post_type_slug ] )
			|| (
				isset( $types_settings[ $post_type_slug ]['mode'] )
				&& 'not_managed'
				== $types_settings[ $post_type_slug ]['mode']
			)
		) {
			return $allcaps;
		}

		$post_type_array = array(
			'post_type' => $post_type,
			'plural' => $plural,
			'singular' => $singular,
			'post_type_slug' => $post_type_slug,
		);

		if ( $wpcf_access->wpml_installed ) {
			if ( ! isset( $wpcf_access->post_types_info[ $plural ][2] ) ) {
				$is_translatable = apply_filters( 'wpml_is_translated_post_type', null, $post_type_array['post_type_slug'] );
				$wpcf_access->post_types_info[ $plural ][2] = $is_translatable;
			} else {
				$is_translatable = $wpcf_access->post_types_info[ $plural ][2];
			}

			if ( $is_translatable ) {
				$types_settings = $wpcf_access->language_permissions;
				$wpml_settings = WPMLSettings::get_instance();
				$allcaps = $wpml_settings->set_post_type_permissions_wpml( $allcaps, $args, $caps, $user, $types_settings, $post_type_array, $roles );
			} else {
				$allcaps = $this->set_post_type_permissions( $allcaps, $user, $types_settings, $post_type_array, $roles, $args );
			}
		} else {
			$allcaps = $this->set_post_type_permissions( $allcaps, $user, $types_settings, $post_type_array, $roles, $args );
		}

		return $allcaps;
	}


	/**
	 * @param $allcaps
	 * @param $user
	 * @param $types_settings
	 * @param $post_type
	 * @param $roles
	 * @param $args
	 *
	 * @return mixed
	 */
	public function set_post_type_permissions( $allcaps, $user, $types_settings, $post_type, $roles, $args ) {
		$access_capabilities = Capabilities::get_instance();

		$additional_key = '';
		if ( isset( $args[2] ) && ! empty( $args[2] ) ) {
			$additional_key = 'edit_own' . $args[2];
		}
		$access_cache_posttype_caps_key_single = md5( 'access::postype_language_cap__single_'
			. $post_type['post_type_slug']
			. $additional_key );
		$cached_post_type_caps = \Access_Cacher::get( $access_cache_posttype_caps_key_single, 'access_cache_posttype_languages_caps_single' );
		//Load cached capabilities
		if ( false !== $cached_post_type_caps ) {
			$access_capabilities->bulk_allcaps_update( $cached_post_type_caps, $post_type['post_type'], $user, $allcaps, $post_type['plural'] );

			return $allcaps;
		}

		$requested_capabilties = array(
			'edit_any' => true,
			'edit_own' => true,
			'publish' => true,
			'delete_any' => true,
			'delete_own' => true,
		);
		$user_caps = array( 'edit' => false, 'edit_published' => false, 'edit_others' => false );
		$post_type_cap = $post_type['post_type'];
		if ( isset( $types_settings[ $post_type['post_type_slug'] ] ) ) {
			$post_type_permissions = $types_settings[ $post_type['post_type_slug'] ]['permissions'];
			$parsed_caps = $this->parse_post_type_caps( $post_type_permissions, $requested_capabilties, $roles );
			$user_caps = $this->generate_user_caps( $parsed_caps, $user_caps );
		}

		$allcaps = $access_capabilities->bulk_allcaps_update( $user_caps, $post_type_cap, $user, $allcaps, $post_type['plural'] );
		\Access_Cacher::set( $access_cache_posttype_caps_key_single, $user_caps, 'access_cache_posttype_languages_caps_single' );

		return $allcaps;
	}


	/**
	 * @param $types_settings
	 * @param $requested_capabilities
	 * @param $roles
	 *
	 * @return mixed
	 */
	public function parse_post_type_caps( $types_settings, $requested_capabilities, $roles ) {
		global $current_user;
		$user_id = $current_user->ID;
		$output = $requested_capabilities;

		foreach ( $requested_capabilities as $cap => $status ) {
			if ( ! isset( $types_settings[ $cap ] ) ) {
				$output[ $cap ] = false;
				continue;
			}

			${$cap} = $types_settings[ $cap ]['roles'];

			if ( isset( $types_settings[ $cap ]['users'] ) ) {
				${$cap . '_users'} = $types_settings[ $cap ]['users'];
			}

			$output[ $cap ] = false;

			if ( isset( ${$cap . '_users'} ) && in_array( $user_id, ${$cap . '_users'} ) ) {
				$output[ $cap ] = true;
				continue;
			}
			$roles_check = array_intersect( $roles, ${$cap} );
			if ( ! empty( $roles_check ) ) {
				$output[ $cap ] = true;
				continue;
			}

		}

		return $output;
	}


	/**
	 * @param $parsed_caps
	 * @param $user_caps
	 *
	 * @return mixed
	 */
	public function generate_user_caps( $parsed_caps, $user_caps ) {
		if ( $parsed_caps['publish'] ) {
			$user_caps['publish'] = true;
		} elseif ( ! $parsed_caps['publish'] ) {
			$user_caps['publish'] = false;
		}

		if ( $parsed_caps['edit_any'] ) {
			$user_caps['edit'] = true;
			$user_caps['edit_others'] = true;
			if ( $parsed_caps['publish'] ) {
				$user_caps['edit_published'] = true;
			}
		} elseif ( ! $parsed_caps['edit_any'] && $parsed_caps['edit_own'] ) {
			$user_caps['edit'] = true;
			if ( $parsed_caps['publish'] ) {
				$user_caps['edit_published'] = true;
			}
		}

		if ( $parsed_caps['delete_any'] ) {
			$user_caps['delete'] = true;
			$user_caps['delete_others'] = true;
			if ( $parsed_caps['publish'] ) {
				$user_caps['delete_published'] = true;
			}
		} elseif ( ! $parsed_caps['delete_any'] && $parsed_caps['delete_own'] ) {
			$user_caps['delete'] = true;
			if ( $parsed_caps['publish'] ) {
				$user_caps['delete_published'] = true;
			}
		}

		return $user_caps;
	}


	/**
	 * Proccess disable add new button
	 *
	 * @param string $post_type_slug
	 * @param object $post_type_object
	 */
	public function disable_add_new_button_for_post_type( $post_type_slug, $post_type_object ) {
		$cap = "create_" . $post_type_slug;
		$post_type_object->cap->create_posts = $cap;
		map_meta_cap( $cap, 0 );
	}


	public function get_post_type_slug_by_name( $post_type, $singular ) {
		$settings = Settings::get_instance();
		$_post_types = $settings->get_post_types();
		if ( in_array( $post_type, array( 'posts', 'pages' ) ) ) {
			switch ( $post_type ) {
				case 'pages':
					return 'page';
					break;
				case 'posts':
				default:
					return 'post';
					break;
			}
		}
		foreach ( $_post_types as $post_type_name => $post_type_info ) {
			if ( ( isset( $post_type_info->label ) && strtolower( $post_type_info->label ) == $post_type )
				|| strtolower( $post_type_info->name ) == $singular ) {
				return $post_type_name;
			}
		}

		return $post_type;
	}


	/**
	 * @param $post_type
	 *
	 * @return array
	 */
	public function get_post_type_names( $cap, $args = array() ) {
		global $wpcf_access;
		if ( ! isset( $cap[0] ) ) {
			return array( '', '' );
		}

		$post_type_plural = $post_type = str_replace( array(
			'edit_others_',
			'edit_published_',
			'delete_others_',
			'delete_published_',
			'edit_',
			'delete_',
			'publish_',
		), '', $cap[0] );
		if ( in_array( $post_type, array( 'posts', 'pages' ) ) ) {
			switch ( $post_type ) {
				case 'pages':
					return array( 'pages', 'page' );
					break;
				case 'posts':
				default:
					return array( 'posts', 'post' );
					break;
			}
		}
		if ( ! isset( $wpcf_access->post_types_info[ $post_type_plural ] ) ) {
			$settings = Settings::get_instance();
			$_post_types = $settings->get_post_types();
			$post_type_object = null;
			if ( in_array( $post_type_plural, $_post_types ) ) {
				$post_type_object = get_post_type_object( $post_type_plural );
				$post_type_cap = sanitize_title_with_dashes( strtolower( $post_type_object->labels->name ) );
			} else {
				$post_type_cap = $post_type_plural;
				$post_type = $this->get_post_type_singular_by_plural( $post_type_cap );
			}

			$wpcf_access->post_types_info[ $post_type_plural ] = array( $post_type_cap, $post_type );
		} else {
			$post_type_cap = $wpcf_access->post_types_info[ $post_type_plural ][0];
			$post_type = $wpcf_access->post_types_info[ $post_type_plural ][1];
		}

		return array( $post_type_cap, $post_type );
	}


	/**
	 * @param $post_type_name
	 *
	 * @return int|string
	 * Get post type name by plural name
	 */
	public function get_post_type_singular_by_plural( $post_type_name ) {
		$settings = Settings::get_instance();
		$_post_types = $settings->get_post_types();
		foreach ( $_post_types as $post_type => $post_type_data ) {
			if ( isset( $post_type_data->__accessNewCaps )
				&& $post_type_data->__accessNewCaps->edit_posts == 'edit_'
				. $post_type_name ) {
				$cap = $post_type_data->__accessNewCaps->edit_post;
				$post_type = str_replace( 'edit_', '', $cap );

				return $post_type;
			}
		}

		return '';
	}


	/**
	 * Defines capabilities.
	 *
	 * @return type
	 */
	public function get_types_caps_array() {
		$access_roles = UserRoles::get_instance();
		$caps = array(
			//
			// READ
			//
			'read_post' => array(
				'title' => __( 'Read post', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'read' ),
				'predefined' => 'read',
			),
			'read_private_posts' => array(
				'title' => __( 'Read private posts', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_posts' ),
				'predefined' => 'edit_own',
			),
			//
			// EDIT OWN
			//
			'create_post' => array(
				'title' => __( 'Create post', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_posts' ),
				'predefined' => 'edit_own',
			),
			'create_posts' => array(
				'title' => __( 'Create post', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_posts' ),
				'predefined' => 'edit_own',
			),
			'edit_post' => array(
				'title' => __( 'Edit post', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_posts' ),
				'predefined' => 'edit_own',
			),
			'edit_posts' => array(
				'title' => __( 'Edit post', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_posts' ),
				'predefined' => 'edit_own',
			),
			'edit_comment' => array(
				'title' => __( 'Moderate comments', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_posts' ),
				'predefined' => 'edit_own',//'edit_own_comments',
				'fallback' => array( 'edit_published_posts', 'edit_others_posts' ),
			),
			//
			// DELETE OWN
			//
			'delete_post' => array(
				'title' => __( 'Delete post', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'delete_posts' ),
				'predefined' => 'delete_own',
			),
			'delete_posts' => array(
				'title' => __( 'Delete post', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'delete_posts' ),
				'predefined' => 'delete_own',
			),
			'delete_private_posts' => array(
				'title' => __( 'Delete private posts', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'delete_private_posts' ),
				'predefined' => 'delete_own',
			),
			//
			// EDIT ANY
			//
			'edit_others_posts' => array(
				'title' => __( 'Edit others posts', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_others_posts' ),
				'predefined' => 'edit_any',
				'fallback' => array( 'moderate_comments' ),
			),
			'edit_published_posts' => array(
				'title' => __( 'Edit published posts', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_published_posts' ),
				'predefined' => 'publish',
			),
			'edit_private_posts' => array(
				'title' => __( 'Edit private posts', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_private_posts' ),
				'predefined' => 'edit_any',
			),
			'moderate_comments' => array(
				'title' => __( 'Moderate comments', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'edit_posts' ),
				'predefined' => 'edit_any_comments',
				'fallback' => array( 'edit_others_posts', 'moderate_comments' ),
			),
			//
			// DELETE ANY
			//
			'delete_others_posts' => array(
				'title' => __( 'Delete others posts', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'delete_others_posts' ),
				'predefined' => 'delete_any',
			),
			'delete_published_posts' => array(
				'title' => __( 'Delete published posts', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'delete_published_posts' ),
				'predefined' => 'publish',
			),
			//
			// PUBLISH
			//
			'publish_posts' => array(
				'title' => __( 'Publish post', 'wpcf-access' ),
				'roles' => $access_roles->get_roles_by_role( '', 'publish_posts' ),
				'predefined' => 'publish',
			),
		);

		return apply_filters( 'wpcf_access_types_caps', $caps );
	}


}