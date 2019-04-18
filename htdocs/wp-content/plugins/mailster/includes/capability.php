<?php

$mailster_capabilities = array(

	'edit_newsletters' => array(
		'title' => __( 'edit campaigns', 'mailster' ),
		'roles' => array( 'contributor', 'author', 'editor' ),
	),

	'publish_newsletters' => array(
		'title' => __( 'send campaigns', 'mailster' ),
		'roles' => array( 'author', 'editor' ),
	),

	'delete_newsletters' => array(
		'title' => __( 'delete campaigns', 'mailster' ),
		'roles' => array( 'contributor', 'author', 'editor' ),
	),

	'edit_others_newsletters' => array(
		'title' => __( 'edit others campaigns', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'delete_others_newsletters' => array(
		'title' => __( 'delete others campaigns', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'duplicate_newsletters' => array(
		'title' => __( 'duplicate campaigns', 'mailster' ),
		'roles' => array( 'author', 'editor' ),
	),

	'duplicate_others_newsletters' => array(
		'title' => __( 'duplicate others campaigns', 'mailster' ),
		'roles' => array( 'author', 'editor' ),
	),

	'mailster_edit_autoresponders' => array(
		'title' => __( 'edit autoresponders', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_edit_others_autoresponders' => array(
		'title' => __( 'edit others autoresponders', 'mailster' ),
		'roles' => array( 'editor' ),
	),


	'mailster_change_template' => array(
		'title' => __( 'change template', 'mailster' ),
		'roles' => array( 'editor' ),
	),
	'mailster_save_template' => array(
		'title' => __( 'save template', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_see_codeview' => array(
		'title' => __( 'see codeview', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_change_plaintext' => array(
		'title' => __( 'change text version', 'mailster' ),
		'roles' => array( 'editor' ),
	),


	'mailster_edit_subscribers' => array(
		'title' => __( 'edit subscribers', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_add_subscribers' => array(
		'title' => __( 'add subscribers', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_delete_subscribers' => array(
		'title' => __( 'delete subscribers', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_edit_forms' => array(
		'title' => __( 'edit forms', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_add_forms' => array(
		'title' => __( 'add forms', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_delete_forms' => array(
		'title' => __( 'delete forms', 'mailster' ),
		'roles' => array( 'editor' ),
	),


	'mailster_manage_subscribers' => array(
		'title' => __( 'manage subscribers', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_import_subscribers' => array(
		'title' => __( 'import subscribers', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_import_wordpress_users' => array(
		'title' => __( 'import WordPress Users', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_export_subscribers' => array(
		'title' => __( 'export subscribers', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_bulk_delete_subscribers' => array(
		'title' => __( 'bulk delete subscribers', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_add_lists' => array(
		'title' => __( 'add lists', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_edit_lists' => array(
		'title' => __( 'edit lists', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_delete_lists' => array(
		'title' => __( 'delete lists', 'mailster' ),
		'roles' => array( 'editor' ),
	),



	'mailster_manage_addons' => array(
		'title' => __( 'manage addons', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_manage_templates' => array(
		'title' => __( 'manage templates', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_edit_templates' => array(
		'title' => __( 'edit templates', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_delete_templates' => array(
		'title' => __( 'delete templates', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_upload_templates' => array(
		'title' => __( 'upload templates', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_update_templates' => array(
		'title' => __( 'update templates', 'mailster' ),
		'roles' => array(),
	),


	'mailster_dashboard' => array(
		'title' => __( 'access dashboard', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_dashboard_widget' => array(
		'title' => __( 'see dashboard widget', 'mailster' ),
		'roles' => array( 'editor' ),
	),

	'mailster_manage_capabilities' => array(
		'title' => __( 'manage capabilities', 'mailster' ),
		'roles' => array(),
	),

	'mailster_manage_licenses' => array(
		'title' => __( 'manage licenses', 'mailster' ),
		'roles' => array(),
	),

);

$mailster_capabilities = apply_filters( 'mymail_capabilities', apply_filters( 'mailster_capabilities', $mailster_capabilities ) );
