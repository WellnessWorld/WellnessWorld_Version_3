<?php
/**
 * Class Access_Ajax_Handler_Add_New_Group_Process
 * Process new Post Group
 *
 * @since 2.7
 */

class Access_Ajax_Handler_Add_New_Group_Process extends Toolset_Ajax_Handler_Abstract {

	/**
	 * Access_Ajax_Handler_Add_New_Group_Process constructor.
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
		global $wp_roles;

		$access_settings = \OTGS\Toolset\Access\Models\Settings::get_instance();

		$nice = 'wpcf-custom-group-' . md5( sanitize_title( $_POST['title'] ) );
		$posts = array();
		if ( isset( $_POST['posts'] ) ) {
			$posts = array_map( 'intval', $_POST['posts'] );
		}

		$settings_access = $access_settings->get_types_settings( true, true );

		if ( isset( $settings_access['post']['permissions']['read']['roles'] ) ) {
			$roles = $settings_access['post']['permissions']['read']['roles'];
		} else {
			$ordered_roles = $access_settings->order_wp_roles();
			$roles = array_keys( $ordered_roles );
		}
		$groups[ $nice ] = array(
			'title' => sanitize_text_field( $_POST['title'] ),
			'mode' => 'permissions',
			'permissions' => array( 'read' => array( 'roles' => $roles ) ),
		);

		$process = true;
		if ( ! empty( $settings_access ) ) {
			foreach ( $settings_access as $permission_slug => $data ) {
				if ( $permission_slug === $nice ) {
					$process = false;
				}
			}
		}

		if ( ! $process ) {
			wp_send_json_error( 'error' );
		}

		for ( $i = 0, $limit = count( $posts ); $i < $limit; $i ++ ) {
			update_post_meta( $posts[ $i ], '_wpcf_access_group', $nice );
		}
		$settings_access = array_merge( $settings_access, $groups );
		$access_settings->updateAccessTypes( $settings_access );
		$group['id'] = $nice;

		wp_send_json_success( $group['id'] );
	}
}