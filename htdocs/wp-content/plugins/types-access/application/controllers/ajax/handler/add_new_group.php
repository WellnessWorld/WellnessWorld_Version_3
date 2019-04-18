<?php
/**
 * Class Access_Ajax_Handler_Add_New_Group
 * Generate 'add new Post Group' form
 *
 * @since 2.7
 */

class Access_Ajax_Handler_Add_New_Group extends Toolset_Ajax_Handler_Abstract {

	/**
	 * Access_Ajax_Handler_Add_New_Group constructor.
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

		$out = '<form method="" id="wpcf-access-set_error_page">';
		$act = 'Add';
		$title = $id = '';
		$settings_access = $access_settings->get_types_settings( true, true );

		if ( isset( $_POST['modify'] ) ) {
			$act = 'Modify';
			$id = $_POST['modify'];
			$current_role = $settings_access[ $id ];
			$title = $current_role['title'];
		}

		$out .= '
			<p>
				<label for="wpcf-access-new-group-title">' . __( 'Group title', 'wpcf-access' ) . '</label><br>
				<input type="text" id="wpcf-access-new-group-title" value="' . $title . '">
			</p>
			<div class="js-error-container"></div>
			<input type="hidden" value="add" id="wpcf-access-new-group-action">
			<input type="hidden" value="' . $id . '" id="wpcf-access-group-slug">';

		$out .= '<div class="otgs-access-search-posts-container">
                <label for="wpcf-access-new-group-title">'
			. __( 'Choose which posts belongs to this group', 'wpcf-access' )
			. '</label><br>
                <select class="js-otgs-access-suggest-posts otgs-access-suggest-posts" style="width:72%;">                  
                </select>
                <select class="js-otgs-access-suggest-posts-types otgs-access-suggest-posts-types" style="width:25%;">
                  <option selected="selected" value="">'
			. __( 'All post types', 'wpcf-access' )
			. '</option>';
		$post_types = get_post_types( array( 'public' => true ), 'object' );
		$post_types_array = array();
		foreach ( $post_types as $post_type ) {
			if ( $post_type->name != 'attachment' ) {
				$is_option_disabled = (
					! isset( $settings_access[ $post_type->name ] )
					|| 'not_managed' === $settings_access[ $post_type->name ]['mode']
				);
				$out .= '<option value="'
					. $post_type->name
					. '" '
					. disabled( $is_option_disabled, true, false )
					. '>'
					. $post_type->labels->name
					. '</option>';
				$post_types_array[] = $post_type->name;
			}
		}

		$out .= '</select>
            </div>
            <div class="js-otgs-access-posts-listing otgs-access-posts-listing">';
		if ( $act == 'Modify' ) {
			$args = array(
				'posts_per_page' => - 1,
				'post_status' => 'publish',
				'post_type' => $post_types_array,
				'meta_query' => array(
					array(
						'key' => '_wpcf_access_group',
						'value' => $id,
					),
				),
			);
			$the_query = new WP_Query( $args );
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$out .= '<div class="js-assigned-access-post js-assigned-access-post-'
						. esc_attr( get_the_ID() )
						. '" data-postid="'
						. esc_attr( get_the_ID() )
						. '">'
						. get_the_title()
						. '
								 <a href="" class="js-wpcf-unassign-access-post" data-id="'
						. esc_attr( get_the_ID() )
						. '"> <i class="fa fa-times"></i></a></div>';
				};
			}
		}
		$out .= '</div>';


		$out .= '</div>';
		$out .= '</form>';
		wp_send_json_success( $out );
	}
}