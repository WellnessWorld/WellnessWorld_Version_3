<?php

$mailster_homepage = array(
	'post_title' => __( 'Newsletter', 'mailster' ),
	'post_status' => 'publish',
	'post_type' => 'page',
	'post_name' => _x( 'newsletter-signup', 'Newsletter Homepage page slug', 'mailster' ),
	'post_content' => '[newsletter_signup]' . __( 'Signup for the newsletter', 'mailster' ) . '[newsletter_signup_form id=1][/newsletter_signup] [newsletter_confirm]' . __( 'Thanks for your interest!', 'mailster' ) . '[/newsletter_confirm] [newsletter_unsubscribe]' . __( 'Do you really want to unsubscribe?', 'mailster' ) . '[/newsletter_unsubscribe]',
);
