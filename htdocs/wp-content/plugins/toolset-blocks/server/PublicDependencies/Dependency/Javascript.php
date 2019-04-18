<?php
namespace ToolsetBlocks\PublicDependencies\Dependency;

/**
 * Loads frontend JS for blocks that need it
 *
 * @package ToolsetBlocks
 * @since 1.0.0
 */
class Javascript implements IContent {
	/**
	 * @param $content
	 *
	 * @return bool
	 */
	public function is_required_for_content( $content ) {
		if ( preg_match('(data-countdown|data-shareurl|tb-progress-data)', $content ) === 1 ) {
			return true;
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	public function load_dependencies() {
		wp_enqueue_script(
			'tb-frontend-js',
			TB_URL . 'public/js/frontend.js',
			array( 'jquery', 'underscore' ),
			TB_VERSION
		);
	}

}
