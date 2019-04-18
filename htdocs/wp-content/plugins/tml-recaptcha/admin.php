<?php

/**
 * Theme My Login reCAPTCHA Admin Functions
 *
 * @package Theme_My_Login_Recaptcha
 * @subpackage Administration
 */

/**
 * Get the recaptcha settings sections.
 *
 * @since 1.0
 *
 * @return array The recaptcha settings sections.
 */
function tml_recaptcha_admin_get_settings_sections() {
	return array(
		// General
		'tml_recaptcha_settings_general' => array(
			'title' => '',
			'callback' => '__return_null',
			'page' => 'theme-my-login-recaptcha',
		),
	);
}

/**
 * Get the recaptcha settings fields.
 *
 * @since 1.0
 *
 * @return array The recaptcha settings fields.
 */
function tml_recaptcha_admin_get_settings_fields() {
	return array(
		// General
		'tml_recaptcha_settings_general' => array(
			// Public key
			'tml_recaptcha_public_key' => array(
				'title' => __( 'Site Key', 'theme-my-login-recaptcha' ),
				'callback' => 'tml_admin_setting_callback_input_field',
				'sanitize_callback' => 'sanitize_text_field',
				'args' => array(
					'label_for' => 'tml_recaptcha_public_key',
					'value' => get_site_option( 'tml_recaptcha_public_key' ),
					'input_class' => 'regular-text',
				),
			),
			// Private key
			'tml_recaptcha_private_key' => array(
				'title' => __( 'Secret Key', 'theme-my-login-recaptcha' ),
				'callback' => 'tml_admin_setting_callback_input_field',
				'sanitize_callback' => 'sanitize_text_field',
				'args' => array(
					'label_for' => 'tml_recaptcha_private_key',
					'value' => get_site_option( 'tml_recaptcha_private_key' ),
					'input_class' => 'regular-text',
				),
			),
			// Theme
			'tml_recaptcha_theme' => array(
				'title' => __( 'Theme', 'theme-my-login-recaptcha' ),
				'callback' => 'tml_admin_setting_callback_dropdown_field',
				'sanitize_callback' => 'sanitize_text_field',
				'args' => array(
					'label_for' => 'tml_recaptcha_theme',
					'options' => array(
						'light' => _x( 'Light', 'recaptcha theme', 'theme-my-login-recaptcha' ),
						'dark' => _x( 'Dark', 'recaptcha theme', 'theme-my-login-recaptcha' ),
					),
					'selected' => get_site_option( 'tml_recaptcha_theme' ),
				),
			),
			// Show on login
			'tml_recaptcha_show_on_login' => array(
				'title' => __( 'Show On Forms', 'theme-my-login-recaptcha' ),
				'callback' => 'tml_admin_setting_callback_checkbox_group_field',
				'sanitize_callback' => 'intval',
				'args' => array(
					'legend' => __( 'Show on forms', 'theme-my-login-recaptcha' ),
					'options' => array(
						'tml_recaptcha_show_on_login' => array(
							'label' => __( 'Login', 'theme-my-login' ),
							'value' => '1',
							'checked' => get_site_option( 'tml_recaptcha_show_on_login' ),
						),
						'tml_recaptcha_show_on_register' => array(
							'label' => __( 'Register', 'theme-my-login' ),
							'value' => '1',
							'checked' => get_site_option( 'tml_recaptcha_show_on_register', true ),
						),
					),
				),
			),
			// Show on register
			'tml_recaptcha_show_on_register' => array(
				'sanitize_callback' => 'intval',
			),
		),
	);
}
