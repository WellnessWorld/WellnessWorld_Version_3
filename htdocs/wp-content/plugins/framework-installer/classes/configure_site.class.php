<?php
/**
 * Date: 05/04/18
 * Time: 10:44
 */
class Toolset_Framework_Installer_Configure extends Toolset_Framework_Installer_Install_Step {
	/**
	 * Step 3: Remove multilingual posts on single installation site
	 * @return array
	 */
	function configure_site() {
		global $frameworkinstaller;

		if ( $this->use_optimized_version() ) {
			$this->regenerate_attachments();
		}

		$this->remove_trash_items();

		$current_user = wp_get_current_user();
		update_user_meta( $current_user->ID, 'show_welcome_panel', 1 );

		$is_wpml_site = $this->is_wpml_site();
		if ( isset( $_POST['wpml'] ) && $_POST['wpml'] === 'no-wpml' && $is_wpml_site ) {
			$download_url = $this->current_site->download_url . '/wpml-single-language.json';
			$status       = $frameworkinstaller->download_file( $download_url, $this->upload_dir['basedir'] . '/wpml-single-language.json' );
			if ( $status ) {
				$wpml_ids = join( '', file( $this->upload_dir['basedir'] . '/wpml-single-language.json' ) );
				$wpml_ids = json_decode( $wpml_ids );

				foreach ( $wpml_ids->posts as $index => $wpml_post_id ) {
					wp_delete_post( $wpml_post_id, true );
				}
			}
		}

		$data = $this->generate_respose_error( true, __( 'Site successfully configured', 'wpvdemo' ) );

		return $data;
	}

	/**
	 * Remove trash items after installation
	 */
	function remove_trash_items() {
		//Remove maps api key
		$maps_options = get_option( 'wpv_addon_maps_options', array() );
		$maps_options['api_key'] = '';
		update_option( 'wpv_addon_maps_options', $maps_options );

		delete_option( 'wp_installer_settings' );

		//Remove traning cpt, taxonomy and fields group for 'Gettings started with Toolset' site
		if ( $this->current_site->shortname === 'gswt' ){
			$this->custom_delete_post_type( 'consultant' );
			$this->custom_delete_tax( 'language' );
			$this->custom_delete_field_group( 'consultant-details');
			$fields_to_remove = array( 'hourly-rate', 'site-visits', 'expertise', 'consultant-phone', 'consultant-email', 'profile-image' );
			$this->custom_delete_fields( $fields_to_remove );
		}

	}

	/**
	 * Regenerate attachments thumbs
	 */
	function regenerate_attachments() {
		//wp_generate_attachment_metadata
		$attachments = get_posts( array(
			'post_type' => 'attachment',
			'posts_per_page' => -1,
		) );
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$filename = get_attached_file( $attachment->ID );

				$file = wp_generate_attachment_metadata( $attachment->ID, $filename );
				wp_update_attachment_metadata( $attachment->ID,  $file );

			}
		}
	}

	/**
	 * Delete post fields  (without deleting entries for the fields)
	 * @param array $field_slug
	 */
	function custom_delete_fields( $field_slugs ) {
		$group_fields = get_option( 'wpcf-fields', array() );
		for ( $i = 0, $field_count = count( $field_slugs ); $i < $field_count; $i ++ ) {
			unset( $group_fields[ $field_slugs[ $i ] ] );
		}
		update_option( 'wpcf-fields', $group_fields );
	}

	/**
	 * Delete Post Type (without deleting posts & relationships)
	 * @param string $post_type_slug
	 */
	function custom_delete_post_type( $post_type_slug ) {
		$types_cpts = get_option( 'wpcf-custom-types', array() );
		unset( $types_cpts[ $post_type_slug ] );
		update_option( 'wpcf-custom-types', $types_cpts );
	}

	/**
	 * Delete Taxonomy (without deleting entries for the taxonomy)
	 * @param string $tax_slug
	 */
	function custom_delete_tax( $tax_slug ) {
		$types_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );
		unset( $types_taxonomies[ $tax_slug ] );
		update_option( 'wpcf-custom-taxonomies', $types_taxonomies );
	}

	/**
	 * Delete Fields Group (without deleting entries for the fields)
	 * @param string $group_slug
	 */
	function custom_delete_field_group( $group_slug ) {
		$find_group = get_posts( array(
			'name'           => $group_slug,
			'post_type'      => 'wp-types-group',
			'post_status'    => 'publish',
			'posts_per_page' => 1
		) );

		if ( $find_group && ! empty( $find_group ) ) {
			wp_delete_post( $find_group[0]->ID, true );
		}
	}

}
