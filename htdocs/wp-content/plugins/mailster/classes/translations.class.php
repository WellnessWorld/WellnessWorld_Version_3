<?php

class MailsterTranslations {

	private $endpoint = 'https://translate.mailster.co';


	public function __construct() {

		add_action( 'plugins_loaded', array( &$this, 'init' ), 1 );

	}


	public function init() {

		$this->load();
		add_filter( 'site_transient_update_plugins', array( &$this, 'update_plugins_filter' ), 1 );
		add_action( 'delete_site_transient_update_plugins', array( &$this, 're_check' ) );
		add_action( 'update_option_WPLANG', array( &$this, 're_check' ) );
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function load() {
		if ( is_dir( MAILSTER_UPLOAD_DIR . '/languages' ) ) {
			$custom = MAILSTER_UPLOAD_DIR . '/languages/mailster-' . get_locale() . '.mo';
			if ( file_exists( $custom ) ) {
				load_textdomain( 'mailster', $custom );
			} else {
				load_plugin_textdomain( 'mailster' );
			}
		} else {
			load_plugin_textdomain( 'mailster' );
		}
	}


	/**
	 *
	 *
	 * @param unknown $value
	 * @return unknown
	 */
	public function update_plugins_filter( $value ) {
		// no translation support
		if ( ! isset( $value->translations ) ) {
			return $value;
		}

		$data = $this->get_translation_data();

		if ( ! empty( $data ) ) {
			$value->translations[] = $data;
		}

		return $value;
	}


	/**
	 *
	 *
	 * @param unknown $force (optional)
	 * @return unknown
	 */
	public function get_translation_data( $force = false ) {

		$object = get_option( 'mailster_translation' );
		$now = time();

		// if force, not set yet or expired
		if ( $force || ! $object || $now - $object['expires'] >= 0 ) {

			$object = array(
				'expires' => $now + 86400, // check if a newer version is available once a day
				'data' => false,
				'set' => null,
			);

			$locale = get_locale();
			$base_locale = preg_replace( '/([a-z]+)_([A-Z]+)_(.*)/', '$1_$2', $locale );
			$root_locale = preg_replace( '/([a-z]+)_([A-Z]+)/', '$1', $base_locale );

			if ( 'en_US' == $locale ) {
				update_option( 'mailster_translation', $object );
				return false;
			}

			$file = 'mailster-' . $locale;
			$url = $this->endpoint . '/api/projects/mailster';
			$package = $this->endpoint . '/api/get/mailster/' . $locale;

			$location = WP_LANG_DIR . '/plugins';
			$mo_file = trailingslashit( $location ) . $file . '.mo';
			$filemtime = file_exists( $mo_file ) ? filemtime( $mo_file ) : 0;

			$response = wp_remote_get( $url );
			$body = wp_remote_retrieve_body( $response );

			if ( empty( $body ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
				$object['expires'] = $now + 3600;
				update_option( 'mailster_translation', $object );
				return false;
			}

			$body = json_decode( $body );

			$translation_set = null;
			$lastmodified = 0;

			foreach ( $body->translation_sets as $set ) {
				if ( ! isset( $set->wp_locale ) ) {
					$set->wp_locale = $set->locale;
				}
				if ( $set->locale == $root_locale ) {
					$translation_set = $set;
					$lastmodified = strtotime( $set->last_modified );
				}
				if ( $set->wp_locale == $base_locale ) {
					$translation_set = $set;
					$lastmodified = strtotime( $set->last_modified );
				}
				if ( $set->wp_locale == $locale ) {
					$translation_set = $set;
					$lastmodified = strtotime( $set->last_modified );
					break;
				}
			}

			if ( $translation_set ) {
				$object['set'] = $translation_set;
			}

			if ( $translation_set && $lastmodified - $filemtime > 0 ) {
				$object['data'] = array(
					'type' => 'plugin',
					'slug' => 'mailster',
					'language' => $locale,
					'version' => MAILSTER_VERSION,
					'updated' => date( 'Y-m-d H:i:s', $lastmodified ),
					'current' => $filemtime,
					'package' => $package,
					'autoupdate' => (bool) mailster_option( 'autoupdate' ),
				);
			}

			update_option( 'mailster_translation', $object );
		}

		return is_array( $object['data'] ) ? ( ! empty( $object['data'] ) ? $object['data'] : null ) : false;

	}


	/**
	 *
	 *
	 * @param unknown $new
	 */
	public function on_activate( $new ) {

		try {
			$this->download_language();
			mailster( 'settings' )->define_texts( true );
		} catch ( Exception $e ) {
		}

	}


	public function re_check() {
		update_option( 'mailster_translation', array( 'expires' => 0 ) );
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function download_language() {

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		$upgrader = new Language_Pack_Upgrader( new Automatic_Upgrader_Skin() );

		add_filter( 'site_transient_update_plugins', array( &$this, 'site_transient_update_plugins' ) );
		$result = $upgrader->bulk_upgrade();
		remove_filter( 'site_transient_update_plugins', array( &$this, 'site_transient_update_plugins' ) );

		if ( ! empty( $result[0] ) ) {

			$this->load();
			return true;

		}

		return false;

	}


	/**
	 *
	 *
	 * @param unknown $value
	 * @return unknown
	 */
	public function site_transient_update_plugins( $value ) {

		// no translation support
		if ( ! isset( $value->translations ) ) {
			return $value;
		}

		$value->translations = array();

		$translation_data = $this->get_translation_data( true );

		if ( ! empty( $translation_data ) ) {
			$value->translations[] = $translation_data;
		}

		return $value;

	}



}
