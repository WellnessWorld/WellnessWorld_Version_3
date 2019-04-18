<?php

/**
 * Show dashboard tutorials or welcome message after a site is installed
 *
 * Class Toolset_Framework_Installer_Dashboard
 *
 * @since 3.0.1
 */
class Toolset_Framework_Installer_Dashboard {

	public $site_id = '';
	public $fidemo_tutorial_settings = '';
	public $showdiscover_dashboard = false;
	public $we_are_standalone = true;
	public $robot_icon_exported = false;
	/**
	 * Toolset_Framework_Installer_Dashboard constructor.
	 */
	public function __construct() {
		global $frameworkinstaller;

		add_filter( 'fidemo_filter_tutorial_url',array( $this, 'filter_tutorial_url_func' ), 10, 3 );
		add_filter( 'fidemo_filter_tutorial_shortdescription', array( $this, 'filter_tutorial_shortdescription_func' ), 10, 3 );

		$site_name = get_option( 'fidemo_installed' );
		$this->site_id = $frameworkinstaller->wpvdemo_get_id_given_refsiteslug( $site_name );

		$this->site_info = wpvdemo_get_site_settings( $this->site_id );

		$this->fidemo_tutorial_settings = $this->generate_tutorial_settings( );
		if ( ! $this->fidemo_tutorial_settings ) {
			return;
		}

		add_action( 'admin_init',array( $this,'wpvdemo_deactivate_framework_installer_standalone' ), 10, 1 );

		// Unified customized Dashboard display (Discover-WP and Standalone, same handler)
		// Since Framework installer 1.8.4
		// Hook to display message on dashboard is added here
		// Discover-WP and standalone handler

		// We will load if the following conditions are true
		// ->non-multisite dashboard
		// ->multisite dashboard but not the main site
		$is_main_discover = apply_filters( 'fidemo_is_discover_main', false );
		if ( ! $is_main_discover ) {
			$this->showdiscover_dashboard = true;
		}

		if ( $frameworkinstaller->is_discoverwp() ) {
			$this->we_are_standalone = false;
		}

		$this->robot_icon_exported = ( isset( $this->site_info->show_robot_icon ) ? true : false );

		if ( ! is_multisite() || $this->showdiscover_dashboard ) {
			if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && is_admin() ) {
				wp_enqueue_style( 'fidemo-standalone-dashboard-override-style', FIDEMO_RELPATH . '/assets/css/dashboard-override-style.css', array(), FIDEMO_VERSION );
				wp_enqueue_script( 'fidemo-standalone-dashboard-override-new', FIDEMO_RELPATH . '/assets/js/dashboard-override-new.js', array( 'jquery' ), FIDEMO_VERSION );

				wp_localize_script( 'fidemo-standalone-dashboard-override-new', 'fi_new_welcome_panel',
					array(
						'are_you_sure_msg'   	=> esc_js( __( 'Are you sure', 'wpvdemo' ) . '?' ),
						'we_are_standalone'		=> $this->we_are_standalone,
						'robot_icon_exported' 	=> $this->robot_icon_exported,
						'expand'   	=> esc_js( __( 'Expand', 'wpvdemo' ) ),
						'minimize'   	=> esc_js( __( 'Minimize', 'wpvdemo' ) ),
					)
				);

				remove_action( 'welcome_panel', 'wp_welcome_panel' );
				add_action( 'welcome_panel', array( $this, 'fidemo_new_welcome_panel' ) );
			}
		}
	}

	/**
	 * @param $tut_url
	 * @param $location
	 * @param $linktext
	 *
	 * @return string
	 */
	function filter_tutorial_url_func( $tut_url, $location, $linktext ) {
		$tut_url=$this->append_google_analytics_arguments_to_url( $tut_url, $location, $linktext );
		return $tut_url;
	}

	/**
	 * @param $tut_shortdescription
	 * @param $location
	 * @param $linktext
	 *
	 * @return string
	 */
	function filter_tutorial_shortdescription_func( $tut_shortdescription, $location, $linktext ) {
		if ( ! is_string( $tut_shortdescription ) ) {
			$tut_shortdescription = '';
			return $tut_shortdescription;
		}
		$tut_shortdescription = trim( $tut_shortdescription );

		//Check if text has URL otherwise bypass
		$haslink = strstr( $tut_shortdescription, 'href' );
		if ( $haslink  && ! empty( $tut_shortdescription ) ) {
			if ( ! empty( $tut_shortdescription ) ) {
				$dom = new DOMDocument;
				$dom->encoding = 'utf-8';
				@$dom->loadHTML( mb_convert_encoding( $tut_shortdescription, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
				$links = $dom->getElementsByTagName( 'a' );

				$updated_array = 0;
				foreach ( $links as $link ){
					$the_link_text = $link->nodeValue;
					$the_link_text = trim( $the_link_text );

					$the_href = $link->getAttribute('href');

					$new_url_with_arguments = $this->append_google_analytics_arguments_to_url( $the_href, $location, $the_link_text );

					if ( $the_href != $new_url_with_arguments ) {
						$updated_array++;
					}

					$link->setAttribute( 'href', $new_url_with_arguments );

				}
				if ( $updated_array > 0 ) {
					$tut_shortdescription = $dom->saveHTML( $dom->documentElement );
				}
			}
		}
		return $tut_shortdescription;
	}


	/**
	 * @param $tut_url
	 * @param $location
	 * @param $linktext
	 *
	 * @return string
	 */
	function append_google_analytics_arguments_to_url( $tut_url, $location, $linktext ) {

		$tut_url = (string)( $tut_url );
		$parsed_url= parse_url( $tut_url );
		if ( is_array( $parsed_url )  &&  ! empty( $parsed_url ) ) {
			if ( isset( $parsed_url['host'] ) ) {
				$host = $parsed_url['host'];
				$already_added = false;

				if ( isset( $parsed_url['query'] ) ) {
					$querypart = $parsed_url['query'];
					if ( strpos( $querypart, 'utm_medium=wpadmin' ) !== false ) {
						$already_added=true;
					}
				}
				if ( ( 'wp-types.com' === $host || 'toolset.com' === $host ) && ! $already_added ) {
					$google_analytics_arguments = $this->wpvdemo_formulate_google_analytics_arguments( $location, $linktext );
					$tut_url = add_query_arg( $google_analytics_arguments, $tut_url );
				}
			}
		}
		return $tut_url;

	}

	/**
	 * [ok]utm_source=discover-wp   (always)
	 * [ok]utm_medium=wpadmin   (always, for links coming from WP admin pages)
	 * [ok]utm_campaign=discover-wp   (always for discover-wp.com)
	 * [ok]utm_content=welcome-box or post-setup-box   (this tells us where the link is placed)
	 * [ok]utm_term=get-started   (this need to be the text in the link being clicked)
	 *
	 * For local reference sites:
	 * [ok]utm_source=local-ref-site
	 * [ok]utm_campaign=framework-installer
	 *
	 * All other arguments are the same as for discover-wp.com
	 *
	 * @param $location
	 * @param $linktext
	 *
	 * @return array
	 */
	function wpvdemo_formulate_google_analytics_arguments( $location, $linktext ) {
		global $frameworkinstaller;

		$is_discoverwp = $frameworkinstaller->is_discoverwp();
		$utm_source = 'local-ref-site';
		if ( $is_discoverwp ) {
			$utm_source='discover-wp';
		}

		$utm_medium = 'wpadmin';
		if ( ! is_admin() ) {
			$utm_medium ='frontend';
		}

		$utm_campaign = 'framework-installer';
		if ( $is_discoverwp ) {
			$utm_campaign = 'discover-wp';
		}

		$utm_content ='welcome-box';
		if ( ! empty( $location ) ) {
			$utm_content = $location;
		}

		$utm_term= 'get-started';

		if ( is_string( $linktext ) ) {
			if ( ! empty( $linktext ) ) {
				$linktext = sanitize_title( $linktext );
				$utm_term = $linktext;
			}
		}

		$google_analytics_arguments = array(
			'utm_source' 	=> $utm_source,
			'utm_medium' 	=> $utm_medium,
			'utm_campaign'	=> $utm_campaign,
			'utm_content'   => $utm_content,
			'utm_term' 		=> $utm_term
		);

		return $google_analytics_arguments;
	}

	/**
	 * @param $site_title
	 *
	 * @return string
	 */
	function generate_site_title_html( $site_title ) {

		$site_title_html = $site_title;
		if ( isset( $this->site_info->tutorial_url ) ) {
			$tut_url =  $this->site_info->tutorial_url;
			if ( is_string( $tut_url ) ) {
				$tut_url = trim( $tut_url );
				if ( ! empty( $tut_url ) && '#' != $tut_url ) {
					$tut_url = apply_filters( 'fidemo_filter_tutorial_url', $tut_url, 'welcome-box', $site_title );
					$site_title_html = '<a href="' . esc_attr( $tut_url ) . '">' . $site_title . '</a>';
				}
			}
		}
		return $site_title_html;
	}

	/**
	 * One dashboard message to rule them all.
	 * Customized Welcome Panel (Framework installer 1.8.2 ++ )
	 * Merged with Discover-WP dashboard display since 1.8.4
	 */
	function fidemo_new_welcome_panel() {

		if ( isset( $this->fidemo_tutorial_settings['ID'] ) ) {

			$site_title = $this->fidemo_tutorial_settings['title'];
			$site_tut_intro_text = $this->site_info->tutorial_intro_text;
			$site_tut_intro_text = (string)$site_tut_intro_text;
			$site_tut_intro_text = str_replace( "\\", "", $site_tut_intro_text );
			$toolset_helper_icon = FIDEMO_RELPATH . '/assets/images/Toolset-help-character.png';

			global $frameworkinstaller;

			if ( isset( $this->site_info->show_robot_icon ) ) {
				$robot_icon_exported = true;
				$robot_icon_class = 'wpvlive-robot';
			} else {
				$robot_icon_class =' wpvlive-norobot';
			}

			$message_title = $this->site_info->message_title;
			$message_title = (string)$message_title;
			$site_title_html = $this->generate_site_title_html( $site_title );

			$copyrightnotice  = '<p>'.__('This site is now an exact copy of the Toolset reference site','wpvdemo').' - "' . $site_title_html . '". '.__('You can edit everything on this site and use it as the basis of your client projects.','wpvdemo').'</p>';
			$copyrightnotice  .= '<p>'.__("Please note that text and graphics in this site needs to be completely replaced if you use it for a client site. The images used here are stock images that don't have a license for multi-site usage.","wpvdemo").'</p>';

			$disable_framework_installer  = '<form id="wpvdemo_client_fi_confirmation_form" action="" method="post">';
			$disable_framework_installer  .= '<label><input type="checkbox" id="wpvdemo_read_understand_checkbox" name="wpvdemo_fi_read_understand" value="yes"> '.__('I read and understand','wpvdemo').'</label>';
			$disable_framework_installer  .= '<input type="submit" id="wpvdemo_read_understand_button" class="button button-primary" value="Disable Framework Installer and customize this site"></form>';

			if ( empty( $site_tut_intro_text ) ) {
				$site_tut_intro_text = '<p>' . __( "We've built this site using", "wpvlive" ) . ' ' .
				     '<a href="http://wp-types.com/">Toolset</a>' . ' '
				     . __( "and no coding at all. It's fully functional with sample content and everything configured. You can edit content and experiment with new custom types, Views and View Templates.", "wpvlive" ) . '</p>';
			}
			$message_title = trim( $message_title );
			if ( empty( $message_title ) ) {
				$message_title = __( "Welcome to your test site!", "wpvlive" );
			}

			$the_dashboard_html  ='';
			$the_dashboard_html .= '<div class="' . $robot_icon_class . '">';
			$the_dashboard_html .= '<div  class="wpvlive-image">';
			$the_dashboard_html .= '<img src="' . $toolset_helper_icon . '"/>';
			$the_dashboard_html .= '</div>';
			$the_dashboard_html .= '<div class="wpvlive-container">';
			$the_dashboard_html .= '<h2 id="tut_header_panel" class="wpvlive-header">' . $message_title . '</h2>';
			$the_dashboard_html .= '<div class="wpvlive-content">';

			$site_tut_intro_text= apply_filters( 'fidemo_filter_tutorial_shortdescription', $site_tut_intro_text, 'welcome-box', 'get-started' );
			if ( ! $frameworkinstaller->is_discoverwp() ) {
				$site_tut_intro_text .= $copyrightnotice;
				$site_tut_intro_text .= $disable_framework_installer;
			}
			$the_dashboard_html .= $site_tut_intro_text;
			$the_dashboard_html .= '</div>';
			$the_dashboard_html .= '</div>';
			$the_dashboard_html .= '<a class="wpvlive-toggle expanded">Minimize<span class="dashicons dashicons-arrow-up"></span></a>';
			$the_dashboard_html .= '</div>';

			$wpvdemo_source_target_media_equivalence = get_option('wpvdemo_source_target_media_equivalence');
			if ( is_array( $wpvdemo_source_target_media_equivalence ) && ! empty( $wpvdemo_source_target_media_equivalence ) ) {

				$source_media_path = key( $wpvdemo_source_target_media_equivalence );
				$target_media_path = reset( $wpvdemo_source_target_media_equivalence );
				$the_dashboard_html = str_replace( $source_media_path, $target_media_path, $the_dashboard_html );

			}
			echo $the_dashboard_html;
		}
	}

	/**
	 * @param $fidemo_tutorial_settings
	 *
	 * @return bool
	 */
	function generate_tutorial_settings(  ) {

		if( ! empty( $this->site_info->title ) && ! empty( $this->site_info->tutorial_title )
		    && ! empty( $this->site_info->ID ) && ! empty( $this->site_info->tutorial_url ) ) {
			$output = array(
				'title' => $this->site_info->title,
				'ID' => $this->site_info->ID,
				'tutorial_title' => $this->site_info->tutorial_title,
				'tutorial_url' => $this->site_info->tutorial_url
			);
			return $output;
		}

		return false;
	}

	/**
	 * Deactivate Framework Installer from Dashboard
	 */
	function wpvdemo_deactivate_framework_installer_standalone() {
		if ( isset( $_POST['wpvdemo_fi_read_understand'] ) ) {
			$wpvdemo_read_understand = trim( $_POST['wpvdemo_fi_read_understand'] );
			if ( ! defined('WPVLIVE_VERSION') && 'yes' === $wpvdemo_read_understand && defined('FIDEMO_ABSPATH') && current_user_can( 'activate_plugins' ) ) {
				$fi_main_file= FIDEMO_ABSPATH . DIRECTORY_SEPARATOR . 'framework-installer.php';
				deactivate_plugins( plugin_basename( $fi_main_file ) );
				header( 'location:' . admin_url() );
				die();
			}
		}
	}

}