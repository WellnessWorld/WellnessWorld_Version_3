<?php

class MailsterTinymce {

	public function __construct() {

		add_action( 'plugins_loaded', array( &$this, 'init' ), 1 );

	}


	public function init() {

		if ( is_admin() ) {
			add_filter( 'mce_external_plugins', array( &$this, 'add_tinymce_plugin' ), 10, 3 );
		}

	}


	/**
	 *
	 *
	 * @param unknown $plugin_array
	 * @return unknown
	 */
	public function add_tinymce_plugin( $plugin_array ) {

		global $post;

		if ( isset( $post ) ) {

			$suffix = SCRIPT_DEBUG ? '' : '.min';

			if ( 'newsletter' == $post->post_type ) {

				$plugin_array['mailster_mce_button'] = MAILSTER_URI . 'assets/js/tinymce-editbar-button' . $suffix . '.js';

				add_action( 'before_wp_tiny_mce', array( &$this, 'editbar_translations' ) );
				add_filter( 'mce_buttons', array( &$this, 'register_mce_button' ) );

			} else {
				$plugin_array['mailster_mce_button'] = MAILSTER_URI . 'assets/js/tinymce-button' . $suffix . '.js';

				add_action( 'before_wp_tiny_mce', array( &$this, 'translations' ) );
				add_filter( 'mce_buttons', array( &$this, 'register_mce_button' ) );

			}
		}

		return $plugin_array;

	}


	/**
	 *
	 *
	 * @param unknown $buttons
	 * @return unknown
	 */
	public function register_mce_button( $buttons ) {
		array_push( $buttons, 'mailster_mce_button' );
		return $buttons;
	}


	/**
	 *
	 *
	 * @param unknown $settings
	 */
	public function editbar_translations( $settings = null ) {

		global $mailster_tags;

		$user = array(
			'firstname' => __( 'First Name', 'mailster' ),
			'lastname' => __( 'Last Name', 'mailster' ),
			'fullname' => __( 'Full Name', 'mailster' ),
			'emailaddress' => __( 'Email address', 'mailster' ),
			'profile' => __( 'Profile Link', 'mailster' ),
		);

		$customfields = mailster()->get_custom_fields();

		foreach ( $customfields as $key => $data ) {
			$user[ $key ] = strip_tags( $data['name'] );
		}

		$tags = array();

		$tags['user'] = array(
			'name' => __( 'User', 'mailster' ),
			'tags' => $user,
		);

		$tags['campaign'] = array(
			'name' => __( 'Campaign related', 'mailster' ),
			'tags' => array(
				'webversion' => __( 'Webversion', 'mailster' ),
				'unsub' => __( 'Unsubscribe Link', 'mailster' ),
				'forward' => __( 'Forward', 'mailster' ),
				'subject' => __( 'Subject', 'mailster' ),
				'preheader' => __( 'Preheader', 'mailster' ),
			),
		);

		$custom = mailster_option( 'custom_tags', array() );
		if ( ! empty( $mailster_tags ) ) {
			$custom += $mailster_tags;
		}
		if ( ! empty( $custom ) ) {
			$tags['custom'] = array(
				'name' => __( 'Custom Tags', 'mailster' ),
				'tags' => $this->transform_array( $custom ),
			);

		};

		if ( $permanent = mailster_option( 'tags' ) ) {
			$tags['permanent'] = array(
				'name' => __( 'Permanent Tags', 'mailster' ),
				'tags' => $this->transform_array( $permanent ),
			);

		};

		$tags['date'] = array(
			'name' => __( 'Date', 'mailster' ),
			'tags' => array(
				'year' => __( 'Current Year', 'mailster' ),
				'month' => __( 'Current Month', 'mailster' ),
				'day' => __( 'Current Day', 'mailster' ),
			),
		);

		echo '<script type="text/javascript">';
		echo 'mailster_mce_button = ' . json_encode( array(
				'l10n' => array(
					'tags' => array(
						'title' => __( 'Mailster Tags', 'mailster' ),
						'tag' => __( 'Tag', 'mailster' ),
						'tags' => __( 'Tags', 'mailster' ),
					),
					'remove' => array(
						'title' => __( 'Remove Block', 'mailster' ),
					),
				),
				'tags' => $tags,
		) );
		echo '</script>';

	}


	/**
	 *
	 *
	 * @return unknown
	 * @param unknown $settings
	 */
	public function translations( $settings ) {

		$forms = mailster( 'forms' )->get_list();

		echo '<script type="text/javascript">';
		echo 'mailster_mce_button = ' . json_encode( array(
				'l10n' => array(
					'title' => 'Mailster',
					'homepage' => array(
						'menulabel' => __( 'Newsletter Homepage', 'mailster' ),
						'title' => __( 'Insert Newsletter Homepage Shortcodes', 'mailster' ),
						'prelabel' => __( 'Text', 'mailster' ),
						'pre' => __( 'Signup for the newsletter', 'mailster' ),
						'confirmlabel' => __( 'Confirm Text', 'mailster' ),
						'confirm' => __( 'Thanks for your interest!', 'mailster' ),
						'unsublabel' => __( 'Unsubscribe Text', 'mailster' ),
						'unsub' => __( 'Do you really want to unsubscribe?', 'mailster' ),
					),
					'button' => array(
						'menulabel' => __( 'Subscriber Button', 'mailster' ),
						'title' => __( 'Insert Subscriber Button Shortcode', 'mailster' ),
						'labellabel' => __( 'Label', 'mailster' ),
						'label' => __( 'Subscribe', 'mailster' ),
						'count' => __( 'Display subscriber count', 'mailster' ),
						'countabove' => __( 'Count above Button', 'mailster' ),
						'design' => __( 'Design', 'mailster' ),
					),
					'form' => __( 'Form', 'mailster' ),
					'forms' => __( 'Forms', 'mailster' ),
				),
				'forms' => $forms,
				'designs' => array(
					'default' => 'Default',
					'twitter' => 'Twitter',
					'wp' => 'WordPress',
					'flat' => 'Flat',
					'minimal' => 'Minimal',
				),
		) );
		echo '</script>';

	}


	/**
	 *
	 *
	 * @param unknown $array
	 * @return unknown
	 */
	private function transform_array( $array ) {

		$return = array();

		foreach ( $array as $tag => $data ) {
			$return[ $tag ] = ucwords( str_replace( array( '-', '_' ), ' ', strip_tags( $tag ) ) );
		}

		return $return;

	}


}
