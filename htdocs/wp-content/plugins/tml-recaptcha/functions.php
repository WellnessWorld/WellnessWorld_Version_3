<?php

/**
 * Theme My Login reCAPTCHA Functions
 *
 * @package Theme_My_Login_Recaptcha
 * @subpackage Functions
 */

/**
 * Get the reCAPTCHA plugin instance.
 *
 * @since 1.0
 *
 * @return Theme_My_Login_Recaptcha The reCAPTCHA plugin instance.
 */
function tml_recaptcha() {
	return theme_my_login()->get_extension( 'theme-my-login-recaptcha' );
}

/**
 * Enqueue scripts.
 *
 * @since 1.0
 */
function tml_recaptcha_enqueue_scripts() {
	wp_enqueue_script( 'recaptcha', add_query_arg( array(
		'hl' => str_replace( '_', '-', get_locale() )
	), 'https://www.google.com/recaptcha/api.js' ) );
}

/**
 * Add reCAPTCHA to the appropriate forms.
 *
 * @since 1.0
 */
function tml_recaptcha_add() {
	if ( get_site_option( 'tml_recaptcha_show_on_login' ) ) {
		tml_add_form_field( 'login', 'recaptcha', array(
			'type'     => 'custom',
			'content'  => tml_recaptcha_get(),
			'priority' => 25,
		) );
	}

	if ( get_site_option( 'tml_recaptcha_show_on_register', true ) ) {
		tml_add_form_field( 'register', 'recaptcha', array(
			'type'     => 'custom',
			'content'  => tml_recaptcha_get(),
			'priority' => 25,
		) );
	}
}

/**
 * Get the reCAPTCHA markup.
 *
 * @since 1.0
 *
 * @return string The reCAPTCHA markup.
 */
function tml_recaptcha_get( $errors = null ) {
	return '<div class="g-recaptcha" data-sitekey="'
		. esc_attr( get_site_option( 'tml_recaptcha_public_key' ) )
		. '" data-theme="'
		. esc_attr( get_site_option( 'tml_recaptcha_theme' ) )
		. '"></div>';
}

/**
 * Validate the reCAPTCHA response.
 *
 * @since 1.0
 *
 * @return bool|WP_Error True on success or WP_Error on error.
 */
function tml_recaptcha_validate( $response, $remote_ip = '' ) {

	if ( empty( $remote_ip ) ) {
		$remote_ip = $_SERVER['REMOTE_ADDR'];
	}

	$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
		'body' => array(
			'secret'   => get_site_option( 'tml_recaptcha_private_key' ),
			'response' => $response,
			'remoteip' => $remote_ip
		)
	) );

	$response_body    = wp_remote_retrieve_body( $response );
	$response_code    = wp_remote_retrieve_response_code( $response );
	$response_message = wp_remote_retrieve_response_message( $response );

	if ( 200 == $response_code ) {

		$result = json_decode( $response_body, true );

		if ( $result['success'] ) {
			return true;
		}

		$errors = new WP_Error;

		foreach ( $result['error-codes'] as $error_code ) {
			switch ( $error_code ) {
				case 'missing-input-secret' :
				case 'invalid-input-secret' :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: Invalid reCAPTCHA secret key.', 'theme-my-login-recaptcha' ), $error_code );
					break;

				case 'missing-input-response' :
				case 'invalid-input-response' :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: Please check the box to prove that you are not a robot.', 'theme-my-login-recaptcha' ), $error_code );
					break;

				case 'bad-request' :
				default :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: The reCAPTCHA request is invalid or malformed.', 'theme-my-login-recaptcha' ), $error_code );
			}
		}

		return $errors;
	}

	return new WP_Error( 'recaptcha', __( '<strong>ERROR</strong>: Unable to reach the reCAPTCHA server.', 'theme-my-login-recaptcha' ) );
}

/**
 * Validate reCAPTCHA on registration.
 *
 * @since 1.0
 *
 * @param WP_Error $errors The WP_Error object.
 * @return WP_Error The WP_Error object.
 */
function tml_recaptcha_validate_registration( $errors ) {
	global $pagenow;

	if ( 'wp-login.php' == $pagenow ) {
		return $errors;
	}

	$response = isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : '';

	$result = tml_recaptcha_validate( $response );
	if ( is_wp_error( $result ) ) {
		foreach ( $result->get_error_codes() as $code ) {
			$message = $result->get_error_message( $code );
			$errors->add( $code, $message );
		}
	}

	return $errors;
}

/**
 * Validate reCAPTCHA on login.
 *
 * @since 1.0
 *
 * @param WP_User|WP_Error $user A WP_User or WP_Error object.
 * @return WP_User|WP_Error WP_User on success or WP_Error on failure.
 */
function tml_recaptcha_validate_login( $user = null ) {
	global $pagenow;

	if ( 'wp-login.php' == $pagenow ) {
		return $user;
	}

	$response = isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : '';

	$result = tml_recaptcha_validate( $response );
	if ( is_wp_error( $result ) ) {
		return $result;
	}

	return $user;
}
