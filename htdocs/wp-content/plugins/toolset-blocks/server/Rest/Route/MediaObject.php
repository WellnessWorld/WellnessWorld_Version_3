<?php


namespace ToolsetBlocks\Rest\Route;

class MediaObject extends ARoute {
	protected $name = 'MediaObject';

	protected $version = 1;

	public function callback( \WP_REST_Request $rest_request ) {
		$params = $rest_request->get_json_params();

		$result = [];

		foreach( $params as $url => $param ) {
			$result[ $url ] = $this->get_media_object_by_url( $param[ 'url' ] );
		}

		return $result;
	}

	protected function get_media_object_by_url( $url ) {
		if( $attachment_id = attachment_url_to_postid( $url ) ) {
			return wp_prepare_attachment_for_js( $attachment_id );
		}

		return array();
	}

	public function permission_callback() {
		// @todo check for Toolset Access permissions
		return $this->wp_user->current_user_can( 'edit_posts' );
	}
}
