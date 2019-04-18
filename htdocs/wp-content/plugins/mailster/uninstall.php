<?php

// if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

global $wpdb, $wp_roles;

if ( is_network_admin() && is_multisite() ) {

	$old_blog = $wpdb->blogid;
	$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

} else {

	$blogids = array( false );

}

foreach ( $blogids as $blog_id ) {

	if ( $blog_id ) {
		switch_to_blog( $blog_id );
	}

	$mailster_options = get_option( 'mailster_options' );
	if ( empty( $mailster_options ) ) {
		$mailster_options = get_option( 'mailster_options' );
	}
	// stop if data should be kept
	if ( ! $mailster_options['remove_data'] ) {
		continue;
	}

	$path = WP_PLUGIN_DIR . '/' . dirname( WP_UNINSTALL_PLUGIN );

	require $path . '/includes/capability.php';

	$roles = array_keys( $wp_roles->roles );
	$mailster_capabilities = array_keys( $mailster_capabilities );
	$mailster_capabilities = array_merge( $mailster_capabilities, array(
			'read_private_newsletters',
			'delete_private_newsletters',
			'delete_published_newsletters',
			'edit_private_newsletters',
			'edit_published_newsletters',
		)
	);

	foreach ( $roles as $role ) {
		$capabilities = $wp_roles->roles[ $role ]['capabilities'];
		foreach ( $capabilities as $capability => $has ) {
			if ( in_array( $capability, $mailster_capabilities ) ) {
				$wp_roles->remove_cap( $role, $capability );
			}
		}
	}

	$campaigns = get_posts( array(
		'posts_per_page' => -1,
		'post_type' => 'newsletter',
		'post_status' => 'any',
	) );

	if ( is_array( $campaigns ) ) {
		foreach ( $campaigns as $campaign ) {
			wp_delete_post( $campaign->ID, true );
		}
	}

	// delete newsletter homepage
	wp_delete_post( $mailster_options['homepage'], true );

	// remove all options
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient_mailster_%'" );
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient_timeout_mailster_%'" );
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient__mailster_%'" );
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient_timeout__mailster_%'" );
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE '_transient_timeout__mailster_%'" );
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` LIKE 'mailster_%'" );
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `$wpdb->options`.`option_name` = 'mailster'" );

	$wpdb->query( "DELETE FROM `$wpdb->usermeta` WHERE `$wpdb->usermeta`.`meta_key` LIKE '%_newsletter_page_mailster_dashboard%'" );

	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_actions" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_links" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_lists" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_lists_subscribers" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_queue" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_subscribers" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_subscriber_fields" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_subscriber_meta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_forms" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_forms_lists" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mailster_form_fields" );

	require_once $path . '/includes/functions.php';

	// remove folder in the upload directory
	if ( $filesystem = mailster_require_filesystem() ) {
		$upload_folder = wp_upload_dir();
		$filesystem->delete( trailingslashit( $upload_folder['basedir'] ) . 'mailster', true );
	}
}

if ( $blog_id ) {
	switch_to_blog( $old_blog );
}
