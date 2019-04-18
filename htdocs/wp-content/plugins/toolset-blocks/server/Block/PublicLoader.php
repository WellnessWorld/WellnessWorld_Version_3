<?php

namespace ToolsetBlocks\Block;

use ToolsetBlocks\Utils\Toolset as ToolsetUtils;

/**
 * "Toolset Blocks" plugin's main class.
 *
 * @package toolset-blocks
 */
class PublicLoader {
	const TOOLSET_BLOCKS_CATEGORY_SLUG = 'toolset';
	const TOOLSET_BLOCKS_BLOCK_NAMESPACE = 'toolset-blocks';
	const TOOLSET_BLOCKS_BLOCK_EDITOR_JS_HANDLE = 'toolset_blocks-block-js';
	const TOOLSET_BLOCKS_BLOCK_EDITOR_CSS_HANDLE = 'toolset_blocks-block-editor-css';
	const TOOLSET_BLOCKS_BLOCK_CSS_HANDLE = 'toolset_blocks-style-css';

	/**
	 * Add the necessary hooks for the plugin initialization.
	 */
	public function initialize() {
		add_filter( 'block_categories', array( $this, 'register_toolset_blocks_category' ), 20 );

		add_action( 'init', array( $this, 'enqueue_block_assets' ) );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Register the Toolset blocks category.
	 *
	 * @param array $categories The array with the categories of the Gutenberg widgets.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function register_toolset_blocks_category( $categories ) {
		if ( ! array_search('toolset', array_column( $categories, 'slug' ) ) ) {
			$categories = array_merge(
				$categories,
				array(
					array(
						'slug'  => self::TOOLSET_BLOCKS_CATEGORY_SLUG,
						'title' => __( 'Toolset', 'toolset-blocks' ),
					),
				)
			);
		}

		return $categories;
	}

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 *
	 * @uses {wp-blocks} for block type registration & related functions.
	 * @uses {wp-element} for WP Element abstraction — structure of blocks.
	 * @uses {wp-i18n} to internationalize the block's text.
	 * @uses {wp-editor} for WP editor styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_editor_assets() {
		// Editor Scripts.
		$script_dependencies = array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'lodash' );

		// This is no longer needed when this bug is fixed:
		// https://github.com/webpack-contrib/mini-css-extract-plugin/issues/147
		$script_dependencies = $this->workaround_webpack4_bug( $script_dependencies );

		wp_enqueue_script(
			self::TOOLSET_BLOCKS_BLOCK_EDITOR_JS_HANDLE,
			TB_BUNDLED_SCRIPT_PATH . '/blocks.js',
			$script_dependencies,
			TB_VERSION,
			true // Enqueue the script in the footer.
		);

		$toolset_utils = new ToolsetUtils();

		$localization_array = array(
			'namespace' => self::TOOLSET_BLOCKS_BLOCK_NAMESPACE,
			'category' => self::TOOLSET_BLOCKS_CATEGORY_SLUG,
			'themeColors' => get_theme_support( 'editor-color-palette' ),
			'extra' => [
				'dashiconsURL' => home_url( 'wp-includes/css/dashicons.css' ),
				'isViewsEnabled' => $toolset_utils->is_views_enabled(),
				'isTypesEnabled' => $toolset_utils->is_types_enabled(),
			],
		);

		$localization_array = apply_filters( 'toolset/dynamic_sources/filters/client_side_info', $localization_array );

		$localization_array = apply_filters( 'toolset_blocks/filters/localize', $localization_array );

		wp_localize_script(
			self::TOOLSET_BLOCKS_BLOCK_EDITOR_JS_HANDLE,
			'toolsetBlocksStrings',
			$localization_array
		);

		wp_set_script_translations( self::TOOLSET_BLOCKS_BLOCK_EDITOR_JS_HANDLE, 'toolset-blocks', TB_PATH . '/languages/' );

		// Style
		if( ! TB_HMR_RUNNING ) {
			// only load css when hmr is NOT active, otherwise it's included in the js
			wp_enqueue_style(
				self::TOOLSET_BLOCKS_BLOCK_EDITOR_CSS_HANDLE,
				TB_URL . 'public/css/edit.css',
				array( 'wp-edit-blocks' ),
				TB_VERSION
			);
		}
	}

	/**
	 * Enqueue Gutenberg block assets for both frontend + backend.
	 *
	 * @uses {wp-editor} for WP editor styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_assets() {
		if( TB_HMR_RUNNING && is_admin() ) {
			// not needed when hmr is active
			return;
		}

		// Frontend Styles.
		wp_enqueue_style(
			self::TOOLSET_BLOCKS_BLOCK_CSS_HANDLE,
			TB_URL . 'public/css/style.css',
			array( 'wp-editor' ),
			TB_VERSION
		);
	}

	/**
	 * Workaround for Webpack4 issue
	 * https://github.com/webpack-contrib/mini-css-extract-plugin/issues/147
	 *
	 * Once issue is fixed we can also remove /public/js/edit.js and
	 * /public/js/style.js from our repo.
	 */
	private function workaround_webpack4_bug( $script_dependencies ) {
		if( TB_HMR_RUNNING ) {
				// not needed when hmr is active
				return $script_dependencies;
		}

		wp_register_script(
			'toolset_blocks-block-edit-js',
			TB_BUNDLED_SCRIPT_PATH . '/edit.js'
		);

		wp_register_script(
			'toolset_blocks-block-style-js',
			TB_BUNDLED_SCRIPT_PATH . '/style.js'
		);

		$script_dependencies[] = 'toolset_blocks-block-edit-js';
		$script_dependencies[] = 'toolset_blocks-block-style-js';

		return $script_dependencies;
	}
}
