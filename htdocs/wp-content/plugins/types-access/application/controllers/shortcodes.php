<?php

namespace OTGS\Toolset\Access\Controllers;

use OTGS\Toolset\Access\Models\UserRoles as UserRoles;

/**
 *
 * Class Shortcodes
 *
 * @package OTGS\Toolset\Access\Controllers
 * @since 2.7
 */
class Shortcodes {

	private static $instance;

	private $custom_read_permissions;

	private $read_permissions_set;


	/**
	 * @return Shortcodes
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


	/*
	 * Access shortcode: toolset_access
	 *
	 * Description: Set access to part of content in posts/pages/content templates/views
	 *
	 * Parameters:
	 * 'role' => List of roles separated by comma
	 * 'operator' => 'allow|deny'
	 * allow - show content for only listed roles
	 * deny - deny content for listed roles, all other roles will see this content
	 * 'raw' => "false|true", default false
	 *
	 * Note: Roles can be uppercase/lowercase
	 * Note: Shortcodes can be used inside toolset_access
	 *
	 * Example: [toolset_access role="Administrator,guest" operator="allow"]Content here[/toolset_access]
	 *
	*/
	public function create_shortcode_toolset_access( $atts, $content ) {
		extract(
			shortcode_atts(
				array(
					'role' => '',
					'operator' => 'allow',
					'raw' => 'false',
				),
				$atts
			)
		);

		if ( empty( $content ) ) {
			return;
		}

		if ( empty( $role ) ) {
			return;
		}

		global $wp_roles;
		$received_roles = explode( ',', $role );
		$received_roles_normal = explode( ',', strtolower( $role ) );
		$roles = $wp_roles->roles;
		$recived_roles_fixed = array();
		foreach ( $roles as $levels => $roles_data ) {
			if ( in_array( $roles_data['name'], $received_roles )
				|| in_array( $roles_data['name'], $received_roles_normal ) ) {
				$recived_roles_fixed[] = $levels;
			}
			if ( in_array( $levels, $received_roles ) ) {
				$recived_roles_fixed[] = $levels;
			}
		}
		if ( in_array( 'Guest', $received_roles ) || in_array( 'guest', $received_roles_normal ) ) {
			$recived_roles_fixed[] = 'guest';
		}

		$roles = UserRoles::get_instance();
		$current_role = $roles->get_current_user_roles( true );

		if ( in_array( $current_role, $recived_roles_fixed ) ) {
			if ( $operator == 'allow' ) {
				return $this->wpcf_access_do_shortcode_content( $content, $raw );
			}
		} else {
			if ( $operator == 'deny' ) {
				return $this->wpcf_access_do_shortcode_content( $content, $raw );
			}
		}

	}


	/*
	 * Add filters to shortcode content
	 *
	*/
	private function wpcf_access_do_shortcode_content( $content, $raw ) {
		if ( function_exists( 'WPV_wpcf_record_post_relationship_belongs' ) ) {
			$content = WPV_wpcf_record_post_relationship_belongs( $content );
		}

		if ( class_exists( 'WPV_template' ) ) {
			global $WPV_templates;
			$content = $WPV_templates->the_content( $content );
		}

		if ( isset( $GLOBALS['wp_embed'] ) ) {
			global $wp_embed;
			$content = $wp_embed->run_shortcode( $content );
			$content = $wp_embed->autoembed( $content );
		}

		if ( function_exists( 'wpv_resolve_internal_shortcodes' ) ) {
			$content = wpv_resolve_internal_shortcodes( $content );
		}
		if ( function_exists( 'wpv_resolve_wpv_if_shortcodes' ) ) {
			$content = wpv_resolve_wpv_if_shortcodes( $content );
		}


		$content = convert_smilies( $content );
		//Enable wpautop if raw = false
		if ( $raw == 'false' ) {
			$content = wpautop( $content );
		}

		$content = shortcode_unautop( $content );
		$content = prepend_attachment( $content );


		$content = do_shortcode( $content );
		$content = capital_P_dangit( $content );

		return $content;
	}


	/**
	 * @param $custom_inner_shortcodes
	 *
	 * @return array
	 * Add toolset_access shortcode to Views:Third-party shortcode arguments
	 */
	public static function string_in_custom_inner_shortcodes( $custom_inner_shortcodes ) {
		$custom_inner_shortcodes[] = 'toolset_access';

		return $custom_inner_shortcodes;
	}
}