<?php


namespace ToolsetBlocks\Rest\Route;

use ToolsetBlocks\Library\WordPress\User;
use ToolsetBlocks\Rest\Route\ShortcodeRender\WithMeta;

class ShortcodeRender extends ARoute {
	protected $name = 'ShortcodeRender';

	protected $version = 1;

	/** @var WithMeta  */
	protected $shortcode_render_with_meta;

	public function __construct( User $wp_user, WithMeta $shortcode_render_with_meta ) {
		parent::__construct( $wp_user );

		$this->shortcode_render_with_meta = $shortcode_render_with_meta;
	}

	public function callback( \WP_REST_Request $rest_request ) {
		$params = $rest_request->get_json_params();

		$result = [];

		foreach( $params as $cachehash => $param ) {
			if( isset( $param[ 'with_meta'] ) && $param[ 'with_meta' ] ) {
				$result[ $cachehash ] = $this->shortcode_render_with_meta->get_response_data( $param['current_post_id'], $param['shortcode'] );
				continue;
			}
			// todo use shortcode id if available instead of current_post_id
			$result[ $cachehash ] = $this->get_content( $param['current_post_id'], $param['shortcode'] );
		}

		return $result;
	}

	protected function get_content( $post_id, $shortcode ) {
		global $post;
		// todo extract dependency
		$post = \WP_Post::get_instance( $post_id );

		$content = do_shortcode( $shortcode );

		if( strpos( $content, '[' ) !== false ) {
			$content = do_shortcode( $content );
		}

		return $content;
	}

	public function permission_callback() {
		// @todo check for Toolset Access permissions
		return $this->wp_user->current_user_can( 'edit_posts' );
	}
}
