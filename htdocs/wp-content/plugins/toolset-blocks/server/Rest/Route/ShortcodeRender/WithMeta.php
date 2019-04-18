<?php


namespace ToolsetBlocks\Rest\Route\ShortcodeRender;

use Toolset\DynamicSources\ToolsetSources\CustomFieldService;
use Toolset\DynamicSources\ToolsetSources\FieldModel;

class WithMeta {
	protected $meta_data = array();

	/** @var CustomFieldService  */
	protected $field_service;

	protected $shortcode;

	public function __construct( CustomFieldService $field_service ) {
		$this->register_meta_filters();
		$this->field_service = $field_service;
	}

	public function get_response_data( $current_post_id, $shortcode ) {
		$this->meta_data = array();

		return array(
			// todo use shortcode id if available instead of current_post_id
			'content' => $this->get_content( $current_post_id, $shortcode ),
			'meta' => $this->meta_data
		);
	}

	protected function get_content( $post_id, $shortcode ) {
		global $post;
		$post = \WP_Post::get_instance( $post_id );

		$content = do_shortcode( $shortcode );

		if( strpos( $content, '[' ) !== false ) {
			$content = do_shortcode( $content );
		}

		return $content;
	}

	private function register_meta_filters() {
		add_action( 'wpv_before_shortcode_post_body', array( $this, 'wpv_content_template_meta' ) );
		add_filter( 'wpv_filter_wpv_view_shortcode_output', array( $this, 'wpv_view_meta' ), 10, 2 );
		add_filter( 'types_field_shortcode_parameters', array( $this, 'types_meta' ), 10, 2 );
	}

	public function wpv_content_template_meta() {
		if( preg_match( '#view_template=[\"\'](.*?)[\"\']#', $this->shortcode, $ct ) ) {
			if( $post = get_page_by_path( $ct[1], OBJECT, 'view-template' ) ) {
				$this->meta_data['post_title'] = $post->post_title;
				$this->meta_data['post_edit_link'] = admin_url( 'admin.php?page=ct-editor&ct_id=' . $post->ID );
			}
		};
	}
	public function wpv_view_meta( $content, $id ) {
		// collect meta of view
		$meta_data = apply_filters( 'wpv_filter_wpv_get_view_settings', array(), $id );
		$this->meta_data = array_merge( $this->meta_data, $meta_data );
		$this->meta_data['post_title'] = get_the_title( $id );
		$this->meta_data['post_edit_link'] = admin_url( 'admin.php?page=views-editor&view_id=' . $id );

		// return original content
		return $content;
	}

	public function types_meta( $params, $field_meta ) {
		// todo extract this to let DynamicSource create it
		$field = new FieldModel(
			$field_meta['slug'],
			$field_meta['title'],
			$field_meta['type'],
			$this->field_service->get_categories_for_field_type( $field_meta['slug'] ),
			isset( $field_meta[ 'data' ][ 'options' ] ) ? $field_meta[ 'data' ][ 'options' ] : null,
			false
		);

		$for_toolset_settings = array(
			'label' => $field->get_name(),
			'value' => $field->get_slug(),
			'categories' => $field->get_categories(),
			'type' => $field->get_type(),
			'fieldOptions' => $field->get_options(),
		);
		// end to do
		$field_meta[ 'toolset_settings' ] = $for_toolset_settings;
		$this->meta_data = array_merge( $this->meta_data, $field_meta );

		// return original paramters
		return $params;
	}
}
