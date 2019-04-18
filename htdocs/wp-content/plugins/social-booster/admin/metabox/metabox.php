<?php

add_action( 'cmb2_admin_init', 'social_booster_metabox' );

function social_booster_metabox() {

	$options_arr = array();
	global $wpdb;
    $tabletw = $wpdb->prefix . 'socialbooster_twtable';
    $twdetail = $wpdb->get_var("SELECT * FROM $tabletw ");
    if ($twdetail) {

    	$options_arr['check1']=esc_html__( 'Twitter');
    }

    $tablefb = $wpdb->prefix . 'socialbooster_fbtable';
    $fbdetail = $wpdb->get_var("SELECT appid FROM $tablefb ");
    if ($fbdetail) {

    	$options_arr['check2']=esc_html__( 'Facebook');
    }

    $tableins = $wpdb->prefix . 'socialbooster_instable';
    $insdetail = $wpdb->get_var("SELECT username FROM $tableins ");
    if ($insdetail) {

    	$options_arr['check3']=esc_html__( 'Instagram');
    }

    $tabletm = $wpdb->prefix . 'socialbooster_stmtable';
    $tmdetail = $wpdb->get_var("SELECT consumerkey FROM $tabletm ");
    if ($tmdetail) {

    	$options_arr['check4']=esc_html__( 'Tumblr');
    }

	$cmb_demo = new_cmb2_box( array(
		'id'            => 'metabox',
		'title'         => esc_html__( 'Social Booster'),
		'object_types'  => array( 'post' ),
	) );

	$cmb_demo->add_field( array(
		'name' => esc_html__( 'Caption Box'),
		'desc' => esc_html__( 'Add caption ( Max character limit 280 for twitter)'),
		'id'   => 'soctextareasmall',
		'type' => 'textarea_small',
	) );

	$cmb_demo->add_field( array(
		'name'    => esc_html__( 'Choose social community' ),
		'desc'    => esc_html__( ''),
		'id'      => 'socmulticheckbox',
		'type'    => 'multicheck',
		'options' => $options_arr,

	) );

		$cmb_demo->add_field( array(
		'name'             => esc_html__( 'Post Now'),
		'desc'             => esc_html__( ''),
		'id'               => 'socsocradio_inlinepostnow',
		'type'             => 'radio_inline',
		'show_option_none' => 'Off',
		'options'          => array(
			'on' => esc_html__( 'On',true),
		),
		  'attributes' => array(
	      'data-conditional-id'    => 'socradio_inline',
	      'data-conditional-value' => 'none',
	    ),
	) );

	$cmb_demo->add_field( array(
		'name'             => esc_html__( 'Scheduling'),
		'desc'             => esc_html__( ''),
		'id'               => 'socradio_inline',
		'type'             => 'radio_inline',
		'default' 		   => 'none',
		'options'          => array(
			'none' => esc_html__( 'Once'),
			'weekly' => esc_html__( 'Weekly'),
			'monthly'   => esc_html__( 'Monthly'),
			'yearly'     => esc_html__( 'Yearly'),
		),
	) );
}






























