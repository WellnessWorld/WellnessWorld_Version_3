<?php

/**
 * The Theme My Login reCAPTCHA Extension
 *
 * @package Theme_My_Login_Recaptcha
 */

/*
Plugin Name: Theme My Login reCAPTCHA
Plugin URI: https://thememylogin.com/extensions/recaptcha
Description: Adds reCAPTCHA support to Theme My Login.
Author: Theme My Login
Author URI: https://thememylogin.com
Version: 1.0.1
Text Domain: theme-my-login-recaptcha
Network: true
*/

// Bail if TML is not active
if ( ! class_exists( 'Theme_My_Login_Extension' ) ) {
	return;
}

/**
 * The class used to implement the reCAPTCHA extension.
 */
class Theme_My_Login_Recaptcha extends Theme_My_Login_Extension {

	/**
	 * The extension name.
	 *
	 * @var string
	 */
	protected $name = 'theme-my-login-recaptcha';

	/**
	 * The extension version.
	 *
	 * @var string
	 */
	protected $version = '1.0.1';

	/**
	 * The extension's documentation URL.
	 *
	 * @var string
	 */
	protected $documentation_url = 'https://docs.thememylogin.com/category/27-recaptcha';

	/**
	 * The extension's support URL.
	 *
	 * @var string
	 */
	protected $support_url = 'https://thememylogin.com/support';

	/**
	 * The extension's store URL.
	 *
	 * @var string
	 */
	protected $store_url = 'https://thememylogin.com';

	/**
	 * The extension's item ID.
	 *
	 * @var int
	 */
	protected $item_id = 49;

	/**
	 * The option name used to store the license key.
	 *
	 * @var string
	 */
	protected $license_key_option = 'tml_recaptcha_license_key';

	/**
	 * The option name used to store the license status.
	 *
	 * @var string
	 */
	protected $license_status_option = 'tml_recaptcha_license_status';

	/**
	 * Set class properties.
	 *
	 * @since 1.0
	 */
	protected function set_properties() {
		$this->title = __( 'reCAPTCHA', 'theme-my-login-recaptcha' );
	}

	/**
	 * Include extension files.
	 *
	 * @since 1.0
	 */
	protected function include_files() {
		require $this->path . 'functions.php';

		if ( is_admin() ) {
			require $this->path . 'admin.php';
		}
	}

	/**
	 * Add extension actions.
	 *
	 * @since 1.0
	 */
	protected function add_actions() {
		// Add reCAPTCHA to the appropriate forms
		add_action( 'init', 'tml_recaptcha_add' );

		// Enqueue the reCAPTCHA scripts
		add_action( 'wp_enqueue_scripts', 'tml_recaptcha_enqueue_scripts' );
	}

	/**
	 * Add extension filters.
	 *
	 * @since 1.0
	 */
	protected function add_filters() {
		if ( get_site_option( 'tml_recaptcha_show_on_register', true ) ) {
			// Validate reCAPTCHA on registration
			add_filter( 'registration_errors', 'tml_recaptcha_validate_registration' );
		}

		if ( get_site_option( 'tml_recaptcha_show_on_login' ) ) {
			// Validate reCAPTCHA on login
			add_filter( 'wp_authenticate_user', 'tml_recaptcha_validate_login' );
		}
	}

	/**
	 * Get the extension settings page args.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings page args.
	 */
	public function get_settings_page_args() {
		return array(
			'page_title' => __( 'Theme My Login reCAPTCHA Settings', 'theme-my-login-recaptcha' ),
			'menu_title' => __( 'reCAPTCHA', 'theme-my-login-recaptcha' ),
			'menu_slug' => 'theme-my-login-recaptcha',
		);
	}

	/**
	 * Get the extension settings sections.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings sections.
	 */
	public function get_settings_sections() {
		return tml_recaptcha_admin_get_settings_sections();
	}

	/**
	 * Get the extension settings fields.
	 *
	 * @since 1.0
	 *
	 * @return array The extension settings fields.
	 */
	public function get_settings_fields() {
		return tml_recaptcha_admin_get_settings_fields();
	}

	/**
	 * Update the extension.
	 *
	 * @since 1.0
	 */
	protected function update() {
		$version = get_site_option( '_tml_recaptcha_version' );

		if ( version_compare( $version, $this->version, '>=' ) ) {
			return;
		}

		// Initial migration
		$options = get_option( 'theme_my_login_recaptcha', array() );
		if ( ! empty( $options ) ) {
			if ( ! empty( $options['public_key'] ) ) {
				update_site_option( 'tml_recaptcha_public_key', $options['public_key'] );
			}
			if ( ! empty( $options['private_key'] ) ) {
				update_site_option( 'tml_recaptcha_private_key', $options['private_key'] );
			}
			if ( ! empty( $options['theme'] ) ) {
				update_site_option( 'tml_recaptcha_theme', $options['theme'] );
			}
			delete_option( 'theme_my_login_recaptcha' );
		}

		update_site_option( '_tml_recaptcha_version', $this->version );
	}
}

tml_register_extension( new Theme_My_Login_Recaptcha( __FILE__ ) );
