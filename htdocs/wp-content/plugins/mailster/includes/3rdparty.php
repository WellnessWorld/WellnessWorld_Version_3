<?php

// remove campaigns from Visual Composer
add_filter( 'vc_settings_exclude_post_type', function( $post_types ) {
	$post_types[] = 'newsletter';
	return $post_types;
} );


// do not cache newsletter homepage on WP Rocket
add_filter( 'rocket_cache_reject_uri', function( $uri ) {

	if ( $link = get_permalink( mailster_option( 'homepage' ) ) ) {
		$uri[] = '(.*)/' . basename( $link ) . '/(.*)';
	}
	return $uri;
} );


// do stuff on newsletter homepage updated
add_action( 'mailster_update_homepage', function( $post ) {

	// WP Rocket
	function_exists( 'flush_rocket_htaccess' ) && flush_rocket_htaccess();
	function_exists( 'rocket_generate_config_file' ) && rocket_generate_config_file();

});

// WP Offload S3 - disabled
add_action( '_as3cf_init', function( $as3cf ) {
	// remove this filter so images paths stay the same
	remove_filter( 'content_save_pre', array( $as3cf->filter_s3, 'filter_post' ) );
});
