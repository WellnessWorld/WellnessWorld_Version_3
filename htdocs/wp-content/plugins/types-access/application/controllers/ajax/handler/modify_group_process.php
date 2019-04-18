<?php
/**
 * Class Access_Ajax_Handler_Modify_Group_Process
 * Process modify Post Group
 *
 * @since 2.7
 */

class Access_Ajax_Handler_Modify_Group_Process extends Toolset_Ajax_Handler_Abstract {

	/**
	 * Access_Ajax_Handler_Modify_Group_Process constructor.
	 *
	 * @param \OTGS\Toolset\Access\Ajax $access_ajax
	 */
	public function __construct( \OTGS\Toolset\Access\Ajax $access_ajax ) {
		parent::__construct( $access_ajax );
	}


	/**
	 * @param $arguments
	 *
	 * @return array
	 */
	function process_call( $arguments ) {

		$this->ajax_begin( array( 'nonce' => 'wpcf-access-error-pages' ) );

		$_POST['id'] = str_replace( '%', '--ACCESS--', $_POST['id'] );
		$nice = str_replace( '--ACCESS--', '%', sanitize_text_field( $_POST['id'] ) );
		$_POST['id'] = str_replace( '--ACCESS--', '%', $_POST['id'] );
		$posts = array();
		if ( isset( $_POST['posts'] ) ) {
			$posts = array_map( 'intval', $_POST['posts'] );
		}

		$access_settings = \OTGS\Toolset\Access\Models\Settings::get_instance();
		$settings_access = $access_settings->get_types_settings( true, true );
		$process = true;
		if ( isset( $settings_access[ $nice ] ) ) {
			foreach ( $settings_access as $permission_slug => $data ) {
				if ( isset( $data['title'] )
					&& $data['title'] == sanitize_text_field( $_POST['title'] )
					&& $permission_slug != $nice ) {
					$process = false;
				}
			}
		} else {
			$process = false;
		}

		$settings_access[ $nice ]['title'] = sanitize_text_field( $_POST['title'] );
		$access_settings->updateAccessTypes( $settings_access );

		if ( ! $process ) {
			wp_send_json_error( 'error' );
		}

		for ( $i = 0, $posts_limit = count( $posts ); $i < $posts_limit; $i ++ ) {
			update_post_meta( $posts[ $i ], '_wpcf_access_group', $nice );
		}
		$group_output = '';
		$_post_types = $access_settings->object_to_array( $access_settings->get_post_types() );
		$post_types_array = array();
		foreach ( $_post_types as $post_type ) {
			$post_types_array[] = $post_type['name'];
		}
		$args = array(
			'post_type' => $post_types_array,
			'posts_per_page' => 0,
			'meta_key' => '_wpcf_access_group',
			'meta_value' => $nice,
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			$group_output .= '<strong>' . __( 'Posts in this Post Group', 'wpcf-access' ) . ':</strong> ';
			$posts_list = '';
			$show_assigned_posts = 4;
			while ( $the_query->have_posts() && $show_assigned_posts != 0 ) {
				$the_query->the_post();
				$posts_list .= get_the_title() . ', ';
				$show_assigned_posts --;
			}
			$group_output .= substr( $posts_list, 0, - 2 );
			if ( $the_query->found_posts > 4 ) {
				$group_output .= sprintf( __( ' and %d more', 'wpcf-access' ), ( $the_query->found_posts - 2 ) );
			}
		}
		if ( ! empty( $group_output ) ) {
			wp_send_json_success( $group_output );
		} else {
			wp_send_json_error( 'error' );
		}
	}
}