<?php

namespace OTGS\Toolset\Access\Controllers\Filters;

use OTGS\Toolset\Access\Models\Settings as Settings;
use OTGS\Toolset\Access\Models\UserRoles as UserRoles;

/**
 * Class ErrorPreview
 *
 * @package OTGS\Toolset\Access\Controllers\Filters
 * @since 2.7
 */
class ErrorPreview {

	private static $instance;

	private $posts;


	/**
	 * @return ErrorPreview
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
	 * @param $query
	 *
	 * @return mixed
	 */
	function show_post_preview( $query ) {

		/**
		 * filter_posts_results - save queried $post if user has preview_any permission
		 * WP by default erase $post if post is draft and user has no capability edit_posts
		 * filter_the_posts retrun $post
		 *
		 * posts_results use priority 9 to run the filter before Views
		 */
		add_filter( 'posts_results', array( $this, 'filter_posts_results' ), 9, 2 );
		add_filter( 'the_posts', array( $this, 'filter_the_posts' ), 10, 2 );


		return $query;
	}


	/**
	 * Check if current user can preview a post and save post object to $preview_posts
	 *
	 * @param array $posts
	 * @param WP_Query $query
	 *
	 * @global $preview_posts
	 *
	 * @return array|void
	 * @since 2.4
	 */
	function filter_posts_results( array $posts, $query ) {
		$access_frontend = \OTGS\Toolset\Access\Controllers\Frontend::get_instance();
		remove_filter( 'pre_get_posts', array( $access_frontend, 'wpcf_access_show_post_preview' ) );
		remove_filter( 'posts_results', array( $this, 'filter_posts_results' ), 10, 2 );

		if ( empty( $posts ) ) {
			return array();
		}

		$post_id = $posts[0]->ID;

		$access_settings = Settings::get_instance();
		$settings_access = $access_settings->get_types_settings();

		$post_type = get_post_type( $post_id );

		$user_roles = UserRoles::get_instance();
		$role = $user_roles->get_main_role();

		if ( isset( $settings_access[ $post_type ] ) && $settings_access[ $post_type ]['mode'] == 'permissions' ) {
			if ( isset( $settings_access[ $post_type ]['permissions']['read_private']['roles'] ) ) {
				if ( in_array( $role, $settings_access[ $post_type ]['permissions']['read_private']['roles'] )
					!== false ) {
					$this->posts = $posts;
				} else {
					remove_filter( 'the_posts', array( $this, 'filter_the_posts' ) );
				}
			}
		}

		return $posts;
	}


	/**
	 * @param $posts
	 * @param WP_Query $query
	 *
	 * @return array
	 */
	function filter_the_posts( $posts, $query ) {
		if ( ! empty( $this->posts ) ) {
			$posts = $this->posts;
			$this->posts = array();
		}

		return $posts;
	}
}