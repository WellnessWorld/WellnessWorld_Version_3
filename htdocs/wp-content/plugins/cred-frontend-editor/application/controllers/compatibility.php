<?php

namespace OTGS\Toolset\CRED\Controller;

use \OTGS\Toolset\CRED\Controller\Compatibility\EditorBlocks\Form as FormEditorBlock;

/**
 * Toolset Forms compatibility manager.
 *
 * @package OTGS\Toolset\CRED\Controller;
 * @since 2.3
 */
class Compatibility {


	/** @var \Toolset_Condition_Plugin_Gutenberg_Active $gutenberg_active */
	private $gutenberg_active;

	/** @var \OTGS\Toolset\CRED\Controller\Compatibility\EditorBlocks\Form $form_editor_block */
	private $form_editor_block;

	/**
	 * Constructor
	 *
	 * @param \Toolset_Condition_Plugin_Gutenberg_Active $gutenberg_active_di For testing purposes.
	 * @param FormEditorBlock                            $form_editor_block_di For testing purposes.
	 */
	public function __construct( \Toolset_Condition_Plugin_Gutenberg_Active $gutenberg_active_di = null, FormEditorBlock $form_editor_block_di = null ) {
		$this->gutenberg_active = $gutenberg_active_di;
		$this->form_editor_block = $form_editor_block_di;
	}

	/**
	 * Initialize the compatibility actions for Forms.
	 *
	 * - Gutenberg Blocks
	 *
	 * @since 2.3
	 */
	public function initialize() {
		$this->load_blocks();
	}

	/**
	 * Loads Gutenberg blocks
	 *
	 * @since 2.3
	 */
	public function load_blocks() {
		$gutenberg_active = $this->gutenberg_active ? $this->gutenberg_active : new \Toolset_Condition_Plugin_Gutenberg_Active();

		if ( ! $gutenberg_active->is_met() ) {
			return;
		}

		$blocks = array(
			$this->form_editor_block ? $this->form_editor_block : new FormEditorBlock(),
		);

		foreach ( $blocks as $block ) {
			$block->init_hooks();
		}
	}
}
