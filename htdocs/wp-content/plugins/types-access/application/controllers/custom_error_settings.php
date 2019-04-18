<?php

namespace OTGS\Toolset\Access\Controllers;

use OTGS\Toolset\Access\Models\Settings as Settings;

/**
 * Generate preview links for custom read errors
 *
 * @package OTGS\Toolset\Access\Models
 * @since 2.7
 */
class CustomErrorSettings {

	private static $instance;

	private $types_caps;

	private $types_caps_predefined;

	private $tax_caps;


	/**
	 * @return Settings
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
	 * @param $post_type | Post type
	 * @param $post_section | string, post - native post, post_group - post assigned to post group, language - WPML
	 *     group
	 * @param string $language
	 *
	 * @return mixed
	 */
	public function get_single_cpt_preview_link( $post_type, $post_section ) {
		global $wpcf_access;
		$wpcf_access->wpml_installed = apply_filters( 'wpml_setting', false, 'setup_complete' );
		$access_settings = Settings::get_instance();
		$types_settings = $access_settings->get_types_settings();
		$url = '';
		if ( $post_section == 'post' ) {
			if ( ! isset( $types_settings[ $post_type ]['mode'] )
				|| $types_settings[ $post_type ]['mode']
				!= 'permissions' ) {
				return 'not_managed';
			}

			if ( $wpcf_access->wpml_installed ) {
				// Exclude languages used in WPML Groups
				$active_languages = $wpcf_access->active_languages;
				global $sitepress;
				$_post_types = $access_settings->object_to_array( $access_settings->get_post_types() );
				$current_language = ICL_LANGUAGE_CODE;
				foreach ( $types_settings as $group_slug => $group_data ) {
					if ( strpos( $group_slug, 'wpcf-wpml-group-' ) !== 0 ) {
						continue;
					}
					if ( ! isset( $_post_types[ $group_data['post_type'] ] )
						|| $group_data['post_type']
						!= $post_type ) {
						continue;
					}
					if ( isset( $group_data['languages'] ) ) {
						$language_keys = array_keys( $group_data['languages'] );
						for ( $i = 0; $i < count( $language_keys ); $i ++ ) {
							if ( ! array_key_exists( $language_keys[ $i ], $active_languages ) ) {
								unset( $active_languages[ $language_keys[ $i ] ] );
							}
						}
					}
				}
				if ( count( $active_languages ) > 0 ) {
					$active_languages = reset( $active_languages );
					$sitepress->switch_lang( $active_languages['code'] );
				} else {
					return '';
				}
			}


			$args = array(
				'post_type' => $post_type,
				'posts_per_page' => 1,
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => '_wpcf_access_group',
						'value' => '',
					),
					array(
						'key' => '_wpcf_access_group',
						'compare' => 'NOT EXISTS',
					),
					'relation' => 'OR',
				),
			);
			$posts_array = query_posts( $args );

			if ( $wpcf_access->wpml_installed ) {
				$sitepress->switch_lang( $current_language );
			}

		} elseif ( $post_section == 'post-group' ) {

			$args = array(
				'post_type' => 'any',
				'posts_per_page' => 1,
				'post_status' => 'publish',
				'meta_key' => '_wpcf_access_group',
				'meta_value' => $post_type,
			);
			$posts_array = query_posts( $args );

		} elseif ( $post_section == 'wpml-group' ) {

			$types_settings = $wpcf_access->settings->types;
			$group_post_type = $types_settings[ $post_type ]['post_type'];
			$group_languages = $types_settings[ $post_type ]['languages'];
			$current_language = ICL_LANGUAGE_CODE;
			$language_keys = array_keys( $group_languages );
			global $sitepress;
			for ( $i = 0; $i < count( $language_keys ); $i ++ ) {
				$sitepress->switch_lang( $language_keys[ $i ] );
				$args = array(
					'post_type' => $group_post_type,
					'posts_per_page' => 1,
					'post_status' => 'publish',
					'meta_query' => array(
						array(
							'key' => '_wpcf_access_group',
							'value' => '',
						),
						array(
							'key' => '_wpcf_access_group',
							'compare' => 'NOT EXISTS',
						),
						'relation' => 'OR',
					),
				);
				$posts_array = query_posts( $args );
				if ( count( $posts_array ) > 0 ) {
					$i = count( $language_keys ) + 1;
				}
			}
			$sitepress->switch_lang( $current_language );
		}


		if ( count( $posts_array ) > 0 ) {
			$url = add_query_arg( 'toolset_access_preview', 1, get_permalink( $posts_array[0] ) );
		}

		return $url;
	}


	/*
	 * Scan directory for php files.
	 */
	public function wpcf_access_scan_dir( $dir, $files = array(), $exclude = '' ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			_e( 'There are security problems. You do not have permissions.', 'wpcf-access' );
			die();
		}

		$file_list = scandir( $dir );
		foreach ( $file_list as $file ) {
			if ( $file != '.'
				&& $file != '..'
				&& preg_match( "/\.php/", $file )
				&& ! preg_match( "/^comments|^single|^image|^functions|^header|^footer|^page/", $file )
				&& $file != $exclude ) {

				if ( ! is_dir( $dir . $file ) ) {
					$files[] = $dir . $file;
				} else {
					$files = self::wpcf_access_scan_dir( $dir . $file . '/', $files );
				}
			}
		}

		return $files;
	}
}
