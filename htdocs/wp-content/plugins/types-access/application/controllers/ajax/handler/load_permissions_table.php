<?php
/**
 * Class Access_Ajax_Handler_Load_Permissions_Table
 * Load permission table
 *
 * @since 2.7
 */

class Access_Ajax_Handler_Load_Permissions_Table extends Toolset_Ajax_Handler_Abstract {

	/**
	 * Access_Ajax_Handler_Load_Permissions_Table constructor.
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

		$output = '';
		$section = isset( $_POST['section'] ) ? sanitize_text_field( $_POST['section'] ) : '';
		if ( $section == '' ) {
			$section = "post-type";
		}

		TAccess_Loader::load( 'CLASS/Admin_Edit' );

		switch ( $section ) {
			case 'post-type';
				$output = Access_Admin_Edit::otg_access_get_permission_table_for_posts();
				break;
			case 'taxonomy';
				$output = Access_Admin_Edit::otg_access_get_permission_table_for_taxonomies();
				break;
			case 'third-party';
				$output = Access_Admin_Edit::otg_access_get_permission_table_for_third_party();
				break;
			case 'custom-group';
				$output = Access_Admin_Edit::otg_access_get_permission_table_for_custom_groups();
				break;
			case 'wpml-group';
				$output = Access_Admin_Edit::otg_access_get_permission_table_for_wpml();
				break;
			case 'custom-roles';
				$output = Access_Admin_Edit::otg_access_get_permission_table_for_custom_roles();
				break;
			default;
				$extra_tabs = apply_filters( 'types-access-tab', array() );
				if ( isset( $extra_tabs[ $section ] ) ) {
					$output .= Access_Admin_Edit::otg_access_get_permission_table_for_third_party( $section );
				}
				break;
		}

		$data = array(
			'output' => $output,
		);
		wp_send_json_success( $data );

	}
}