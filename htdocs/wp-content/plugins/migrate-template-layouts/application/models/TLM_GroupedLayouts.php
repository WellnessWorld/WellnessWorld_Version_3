<?php

if ( class_exists( 'DDL_GroupedLayouts' ) ) {
	/**
	 * Class TLM_GroupedLayouts
	 */
	class TLM_GroupedLayouts extends DDL_GroupedLayouts {

		public static function get_post_by_id( $post_id ) {
			global $wpdb;

			$post = $wpdb->get_results( $wpdb->prepare( "SELECT $wpdb->posts.ID, $wpdb->posts.post_name, $wpdb->posts.post_title, $wpdb->posts.post_type FROM $wpdb->posts WHERE ID = %s", $post_id ) );

			return isset( $post[0] ) ? $post[0] : null;
		}

		public static function get_all_layouts( ) {
			global $wpdb;

			$layouts = $wpdb->get_results( $wpdb->prepare(
				"SELECT 
					$wpdb->posts.ID, 
					$wpdb->posts.post_name, 
					$wpdb->posts.post_title, 
					$wpdb->posts.post_type
				FROM $wpdb->posts 
				LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID AND $wpdb->postmeta.meta_key = %s
				WHERE 
					post_type = %s AND 
					meta_value NOT LIKE %s", WPDDL_LAYOUTS_SETTINGS, WPDDL_LAYOUTS_POST_TYPE, '%"has_child":true%'
			) );

			return isset( $layouts ) ? $layouts : null;
		}

	}

}