<?php

namespace ToolsetBlocks;

/*
 * ON INIT
 */
add_action( 'init', function() {
	$dic = DicLoader::get_instance()->get_dic();

	// Frontend
	if( ! is_admin() ) {
		// Public Dependencies
		$frontend = $dic->make( '\ToolsetBlocks\PublicDependencies\Frontend' );
		// - Lightbox
		$frontend->add_content_based_dependency( $dic->make( '\ToolsetBlocks\PublicDependencies\Dependency\Lightbox' ) );
		// - Dashicons
		$frontend->add_content_based_dependency( $dic->make( '\ToolsetBlocks\PublicDependencies\Dependency\Dashicons' ) );
		// - ExternalResources
		$frontend->add_content_based_dependency( $dic->make( '\ToolsetBlocks\PublicDependencies\Dependency\ExternalResources' ) );
		// - Blocks frontend JS
		$frontend->add_content_based_dependency( $dic->make( '\ToolsetBlocks\PublicDependencies\Dependency\Javascript' ) );

		// - Load Dependecies
		$frontend->load();
	}

	// Dynamic Sources.
	$dynamic_sources = $dic->make( '\Toolset\DynamicSources\DynamicSources' );
	$dynamic_sources->initialize();

	$toolset_utils = $dic->make( '\ToolsetBlocks\Utils\Toolset' );
	if ( $toolset_utils->is_views_enabled() ) {
		$toolset_views_integration = $dic->make(
			Integrations\Toolset\Views::class,
			array(
				':view_get_instance' => array(
					'\WPV_View_Embedded',
					'get_instance',
				),
				':content_template_get_instance' => array(
					'\WPV_Content_Template_Embedded',
					'get_instance',
				),
				':content_template_post_type' => \WPV_Content_Template_Embedded::POST_TYPE,
			)
		);

		$toolset_views_integration->initialize();
	}

	// Toolset Blocks.
	$tb = $dic->make( '\ToolsetBlocks\Block\PublicLoader' );
	$tb->initialize();

	// i18n.
	$tb = $dic->make( '\ToolsetBlocks\Block\I18n' );
	$tb->initialize();
}, 1 );


add_action( 'rest_api_init', function() {
	// Backend (is_admin() does not work on rest requests itself, so we also need to load on any rest request)
	if( is_admin() || ( defined( 'REST_REQUEST') && REST_REQUEST ) ) {
		$dic = DicLoader::get_instance()->get_dic();

		// Rest API
		$rest_api = $dic->make( '\ToolsetBlocks\Rest\API' );

		// - Add Dynamic Shortcode
		$rest_api->add_route( $dic->make( '\ToolsetBlocks\Rest\Route\ShortcodeRender' ) );
		$rest_api->add_route( $dic->make( '\ToolsetBlocks\Rest\Route\MediaObject' ) );

		// -> Init routes
		$rest_api->rest_api_init();
	}
}, 1 );


/*
 * Scripts
 */
// admin
add_action( 'admin_print_scripts', function() {
	$dic = DicLoader::get_instance()->get_dic();

	$script_data = $dic->make( '\ToolsetBlocks\Utils\ScriptData' );
	$script_data->admin_print_scripts();
}, 1 );
