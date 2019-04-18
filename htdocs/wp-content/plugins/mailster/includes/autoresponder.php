<?php

$mailster_autoresponder_info = array(

	'units' => array(
		'minute' => __( 'minute(s)', 'mailster' ),
		'hour' => __( 'hour(s)', 'mailster' ),
		'day' => __( 'day(s)', 'mailster' ),
		'week' => __( 'week(s)', 'mailster' ),
		'month' => __( 'month(s)', 'mailster' ),
		'year' => __( 'year(s)', 'mailster' ),
	),

	'actions' => array(
		'mailster_subscriber_insert' => array(
			'label' => __( 'user signed up', 'mailster' ),
			'hook' => 'mailster_subscriber_insert',
		),
		'mailster_subscriber_unsubscribed' => array(
			'label' => __( 'user unsubscribed', 'mailster' ),
			'hook' => 'mailster_subscriber_unsubscribed',
		),
		'mailster_post_published' => array(
			'label' => __( 'something has been published', 'mailster' ),
			'hook' => 'transition_post_status',
		),
		'mailster_autoresponder_timebased' => array(
			'label' => __( 'at a specific time', 'mailster' ),
			'hook' => 'mailster_autoresponder_timebased',
		),
		'mailster_autoresponder_usertime' => array(
			'label' => __( 'a specific user time', 'mailster' ),
			'hook' => 'mailster_autoresponder_usertime',
		),
		'mailster_autoresponder_followup' => array(
			'label' => __( 'a specific campaign', 'mailster' ),
			'hook' => 'mailster_autoresponder_followup',
		),
		'mailster_autoresponder_hook' => array(
			'label' => __( 'a specific action hook', 'mailster' ),
			'hook' => 'mailster_autoresponder_hook',
		),
	),

);

$mailster_autoresponder_info['units'] = apply_filters( 'mymail_autoresponder_units', apply_filters( 'mailster_autoresponder_units', $mailster_autoresponder_info['units'] ) );
$mailster_autoresponder_info['actions'] = apply_filters( 'mymail_autoresponder_actions', apply_filters( 'mailster_autoresponder_actions', $mailster_autoresponder_info['actions'] ) );
