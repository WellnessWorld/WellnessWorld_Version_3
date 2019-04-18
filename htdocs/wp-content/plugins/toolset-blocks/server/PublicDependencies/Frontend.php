<?php

namespace ToolsetBlocks\PublicDependencies;

use ToolsetBlocks\PublicDependencies\Dependency\IContent;


/**
 * Frontend dependencies
 */
class Frontend {

	/** @var IContent[] */
	private $dependencies_content = array();

	/**
	 * Add a content based dependecy
	 * @param IContent $dependency [description]
	 */
	public function add_content_based_dependency( IContent $dependency ) {
		$this->dependencies_content[] = $dependency;
	}

	/**
	 * Load all previous added dependencies
	 */
	public function load() {
		if( $this->dependencies_content !== null ) {
			add_filter( 'the_content', array( $this, 'load_dependencies_content' ), 8 );
		}
	}

	/**
	 * Load content based dependencies
	 *
	 * @filter 'the_content' 8
	 * @param $content
	 * @return string Untouched content
	 */
	public function load_dependencies_content( $content ) {
		foreach( $this->dependencies_content as $dependency ) {
			if( $dependency->is_required_for_content( $content ) ) {
				$dependency->load_dependencies();
			}
		}

		return $content;
	}
}
