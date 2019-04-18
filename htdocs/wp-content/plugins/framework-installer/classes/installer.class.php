<?php

/**
 * Class Toolset_Framework_Installer
 */
class Toolset_Framework_Installer {

	public $refsite;

	public function __construct(){

	    $is_main_discover = apply_filters( 'fidemo_is_discover_main', false);
        if ( ! $is_main_discover ) {
	        add_action( 'admin_menu', array( $this, 'add_reference_sites_administration_page' ), 10 );
        }
		add_filter( 'toolset_filter_register_menu_pages', array(&$this,'wpvdemo_unified_menu'), 100 );
		$this->refsites = apply_filters( 'fidemo_reference_sites_array', fidemo_get_reference_sites() );

		add_action( 'admin_notices', array( $this, 'check_write_protection' ) );

        $this->wpvdemo_plugin_localization();
	}



	function wpvdemo_plugin_localization() {
		$locale = get_locale();
		load_textdomain( 'wpvdemo', FIDEMO_ABSPATH . '/locale/views-demo-' . $locale . '.mo' );
	}

	/**
     * Get site shortname
	 * @param $site_id
	 *
	 * @return bool
	 */
	function wpvdemo_get_shortname_given_id( $site_id ) {
		$site_id = intval( $site_id );
		if ( $site_id > 0 ) {
			$sites = $this->refsites;
            if ( empty( $sites ) ){
                return false;
            }
			foreach ( $sites as $index => $site ) {
				if ( intval( $site->ID ) === $site_id ) {
					if ( !  empty( $site->shortname ) ) {
						return $site->shortname;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Check if current site ready to install
	 */
	function check_write_protection() {
        $site_is_ready = true;

		if ( ! is_writeable( get_theme_root() ) ) {
			echo '<div class="message error"><p>' .
                 sprintf(__( 'The theme directory is not writable and the images for the demo sites can’t be installed. Please change the ownership of directory <strong>%s</strong> so that the web server can write to it.',
                     'wpvdemo'), get_theme_root() ) . '<br /><br /><a href="http://wp-types.com/documentation/views-demos-downloader/" target="_blank">' . __("Instructions for setting up demo sites",
					'wpvdemo') . '</a></p></div>';
			$site_is_ready = false;
        }

		$wp_upload_dir = wp_upload_dir();
		$uploads_directory_path = $wp_upload_dir['basedir'];
		$uploads_directory_path = rtrim( $uploads_directory_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
		if ( ! is_writeable( $uploads_directory_path ) ) {
			echo '<div class="message error"><p>' .
			     sprintf(__( 'The media directory is not writable and the images for the demo sites can’t be installed. Please change the ownership of directory <strong>%s</strong> so that the web server can write to it.',
				     'wpvdemo'), $wp_upload_dir['basedir'] ) . '<br /><br /><a href="http://wp-types.com/documentation/views-demos-downloader/" target="_blank">' . __("Instructions for setting up demo sites",
					'wpvdemo') . '</a></p></div>';
			$site_is_ready = false;
		}


		$is_zip_class = class_exists('ZipArchive');
        if ( ! $is_zip_class ) {
	        echo '<div class="message error"><p>'
	             . __("PHP ZipArchive extension missing.",
			        'wpvdemo') . '</p></div>';
	        $site_is_ready = false;
        }

		if ( ! ini_get('allow_url_fopen') ) {
			echo '<div class="message error"><p>'
			     . __("Framework Installer plugin requires PHP allow_url_fopen to be enabled. Please enabled it in your php.ini. Contact your webhost if you are not sure how to do this.",
					'wpvdemo') . '</p></div>';
			$site_is_ready = false;
		}

        if ( ini_get( 'max_execution_time' ) < 120 && ini_get( 'max_execution_time' ) != 0 ) {
            if ( ini_set( 'max_execution_time', '120' ) == false ) {
	            echo '<div class="message error"><p>'
	                 . __( "Framework Installer plugin requires PHP max_execution_time to be 120 seconds. Please change it in your php.ini. Contact your webhost if you are not sure how to do this.",
			            'wpvdemo' ) . '</p></div>';
	            $site_is_ready = false;
            }
        }

        $memory_limit = ini_get( 'memory_limit' );
        if ( version_compare( $memory_limit, '128M' ) < 0 ) {
	        if ( ini_set( 'memory_limit', '120M' ) == false ) {
		        echo '<div class="message error"><p>'
		             . __( "Framework Installer plugin requires PHP memory_limit to be 128 MB or more. Please change it in your php.ini. Contact your webhost if you are not sure how to do this.",
				        'wpvdemo' ) . '</p></div>';
		        $site_is_ready = false;
	        }
        }

		if ( ! $site_is_ready ) {
            echo '<input type="hidden" value="0" class="fidemo-site-is-not-ready">';
        }

    }

	/**
	 * Show notice if can't connect to reference sites server
	 */
	function connection_refused_notice() {
		print  '<div class="notice"><p>' .
		       sprintf( __( 'Connection to %s refused', 'wpvdemo' ), FIDEMO_URL ) .
		       '</p></div>';
	}

	/**
	 * @param $url
	 *
	 * @return string
	 */
	function get_content( $url ) {
		$content = '';
		$context = $this->wpv_create_stream_context();
		$url_headers = @get_headers ( $url );
		if ( strpos ( $url_headers [0], '200 OK' ) ) {
			$handle = fopen( $url, "r", false, $context );
			while ( ! feof( $handle ) ) {
				$content .= fread( $handle, 8192 );
			}
		}
		return $content;
	}

	/**
     * ownload file and save it to $dest path
	 * @param $url
	 * @param $dest
	 *
	 * @return bool
	 */
	function download_file( $url, $dest ) {

	    if ( function_exists('curl_version') ) {

            $status = $this->copySecureFile( $url, $dest );
	        if ( $status ) {
	            return true;
            }
        }
		$context = $this->wpv_create_stream_context();

		$file = fopen ( $url, 'rb', false, $context );
		if ( $file ) {
			$newf = fopen ( $dest, 'wb' );
			if ( $newf ) {
				while( ! feof( $file ) ) {
					fwrite( $newf, fread( $file, 1024 * 8 ), 1024 * 8 );
				}
			}
		}

		if ( $file ) {
			fclose( $file );
		}
		if ( $newf ) {
			fclose( $newf );
		}

		return file_exists( $dest );

	}

	function copySecureFile( $FromLocation, $ToLocation ) {
		$Channel = curl_init( $FromLocation );
    	$File = fopen ($ToLocation, "wb");
		curl_setopt($Channel, CURLOPT_FILE, $File);
		curl_setopt($Channel, CURLOPT_HEADER, 0);
		curl_setopt($Channel, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($Channel, CURLOPT_SSL_VERIFYHOST, false);
		curl_exec($Channel);
		curl_close($Channel);
		fclose($File);

		return file_exists($ToLocation);
	}

	/**
	 * Create default stream content with ssl support
	 * @return resource
	 */
	function wpv_create_stream_context() {
		$context = stream_context_set_default( array(
				'http' => array(
					'timeout' => 1200
				),
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
				)
			)
		);
		return $context;
	}

	/**
	 *
	 * @param $pages
	 *
	 * @return array
	 */
	function wpvdemo_unified_menu( $pages ) {

		//Add admin screen only when all required plugins are activated    	
		$pages[] = array(
			'slug'          => 'manage-refsites',
			'menu_title'    => __( 'Reference sites', 'wpvdemo' ),
			'page_title'    => __( 'Select a reference site to install', 'wpvdemo' ),
			'callback'      => array( &$this, 'manage_reference_sites_admin_page' ),
			'load_hook'   	=> array( &$this, 'wpvdemo_admin_menu_import_hook' ),
			'contextual_help_hook'      => array( &$this, 'manage_reference_sites_add_help_tab' )
		);

		return $pages;
	}

	/**
	 * Add menu pages
	 */
	function add_reference_sites_administration_page(){

		if ( ! $this->wpvdemo_can_implement_unified_menu() ) {
			$views_demo_icon = plugins_url('assets/images/discover_wp_icon.png', dirname(__FILE__) );
			$page_title = __( 'Select a reference site to install', 'wpvdemo' );
			$menu_title = __( 'Manage sites', 'wpvdemo' );
			$capability = 'manage_options';
			$menu_slug = 'manage-refsites';


			$manage_refsites_page= add_menu_page(
				$page_title,
				$menu_title,
				$capability,
				$menu_slug,
				array( &$this, 'manage_reference_sites_admin_page' ),
				$views_demo_icon
			);
			add_action( 'load-' . $manage_refsites_page,  array( $this,'wpvdemo_admin_menu_import_hook' ) );
			add_action( 'load-'.$manage_refsites_page, array( $this, 'manage_reference_sites_add_help_tab' ) );
		}
	}

	/**
	 * Generate list of sites to install
	 */
	function manage_reference_sites_admin_page(){
        global $current_user;
		$sites = $this->refsites;
		$current_site = get_option( 'fidemo_installed', '');
		do_action('wpvdemo_start_demo_page');
		echo '<h2>' . esc_html( 'Select a reference site to install' ) . '</h2>';
		if ( defined( 'FIDEMO_ALLOW_REFRESH' ) || is_super_admin ( $current_user->ID ) ) {
			echo '<div class="fidemo-actions">
            <button class="js-fidemo-force-refresh button-secondary">' . esc_html( 'Force sites refresh' ) . '</button>
            </div>';
		}


		echo '<div class="wrap" id="managemysites_refsiteheader">
            <div class="fidemo-refsites">';
		foreach( $sites as $index => $site ) {
			?>
			<div class="fidemo-refsite js-fidemo-refsite<?php echo ( $current_site === $site->shortname ? ' fidemo_installed' : '' )?>" data-site="<?php esc_attr_e( $index ) ?>"
                 data-shortname="<?php esc_attr_e( $site->shortname ) ?>"
                 data-id="<?php esc_attr_e( $site->ID ) ?>">
				<div class="fidemo-refsite-screenshot">
                    <img src="<?php esc_attr_e( $site->large_image )?>" >
                </div>
                <span class="more-details" id="twt-action"><?php _e( 'Reference Site Details' ) ?></span>
                <div class="fidemo-refsite-name">
					<?php esc_html_e( $site->title ) ?>
                </div>
                <div class="fidemo-refsite-actions">
                    <button class="button button-secondary activate"><?php _e( 'Install', 'wpvdemo' ) ?></button>
                </div>

			</div>

			<?php

		}
		echo '</div></div>';

	}

	/**
     * Get site id by shortname
	 * @param $refsite_slug
	 *
	 * @return bool
	 */
    function wpvdemo_get_id_given_refsiteslug( $refsite_slug ) {
	    $sites = $this->refsites;

	    foreach ( $sites as $index => $site ) {
		    if ( $site->shortname == $refsite_slug ) {
			    return $site->ID;
		    }
	    }
	    return false;
    }

	/**
	 * @param $site_id
	 * @param string $requested_info
	 *
	 * @return bool
	 */
	function wpvdemo_get_sitespecific_info_given_id( $site_id, $requested_info='shortname' ) {
		$sites = $this->refsites;
		$site_id = intval($site_id);
		if ( $site_id > 0 ) {
			foreach ( $sites as $index => $site ) {
				if ( intval($site->ID) === $site_id ) {
					$requested_data = $site->$requested_info;
					return $requested_data;
				}
			}
		}
		return false;
	}


	/**
	 * Admin menu page hook.
	 */
	function wpvdemo_admin_menu_import_hook() {
		if ( ! wp_style_is( 'font-awesome', 'registered' ) ) {
			wp_register_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css' );
		}
		wp_enqueue_style( 'fidemo', FIDEMO_RELPATH . '/assets/css/style.css', array( 'wp-jquery-ui-dialog', 'font-awesome' ), FIDEMO_VERSION );

		wp_enqueue_script( 'fidemo-basic', FIDEMO_RELPATH . '/assets/js/basic.js', array( 'jquery','jquery-ui-dialog' ), FIDEMO_VERSION );

		$installation_steps = json_encode( array(
			__( 'Downloading site files', 'wpvdemo' ),
			__( 'Unpacking', 'wpvdemo' ),
			__( 'Importing and processing the database', 'wpvdemo' ),
			__( 'Configuring the site', 'wpvdemo' ),
			__( 'Finalizing', 'wpvdemo' ),
        ));

		$verification_string = '';


		if ( $this->is_discoverwp() ) {
			$current_user = wp_get_current_user();
			if ( isset( $current_user->ID ) ) {
				$user_id = $current_user->ID;
				$string = $user_id . 'discover-wp';
				$verification_string = md5($string);
			}
		}

		$fidemo_dialog_texts = array(
		        'close' => __( 'Close', 'wpvdemo' ),
		        'install' => apply_filters( 'fidemo_action_button', __( 'Install', 'wpvdemo' ) ),
		        'tutorial' => __( 'Tutorial', 'wpvdemo' ),
		        'live_preview' => __( 'Live preview', 'wpvdemo' ),
		        'required_plugins' => __( 'Required plugins', 'wpvdemo' ),
		        'install_notice' => __( 'Framework Installer requires a blank website before you can install a new demo site.', 'wpvdemo' ),
                'install_notice4' => __( 'When you click the "Install" button your site database will be erased and your current content will be deleted. Please backup your site before installation.', 'wpvdemo' ),
		        'install_notice2' => __( 'I have a backup of my database and all my files.', 'wpvdemo' ),
		        'install_notice3' => __( 'I understand that all my data will be erased from this website.', 'wpvdemo' ),
		        'fidemo_nonce' => wp_create_nonce( 'fidemo_nonce' ),
		        'installation_steps' => $installation_steps,
		        'installation_proccess' => __( 'We\'re setting up your new %s test site', 'wpvdemo' ),
		        'existing_plugins' => json_encode( $this->get_existing_plugins() ),
		        'available_themes' => __( 'Available themes', 'wpvdemo' ),
		        'language_select_title' => __( 'One language or multilingual', 'wpvdemo' ),
		        'one_language_label' => __( 'Only one language (English)', 'wpvdemo' ),
		        'multi_language_label' => __( 'Multilingual with WPML', 'wpvdemo' ),
		        'plugin_outdated' => __( 'The plugin versions do not match, please update to %s', 'wpvdemo' ),
		        'plugin_no_exists' => __( 'Not found in the plugins directory. Please go to the <a href="%s" target="_blank">plugins page</a> , download the plugin and install it on the current site. Do not activate it yet.', 'wpvdemo' ),
		        'requirements_not_met' => __( 'Installation requirements not met. Click for details', 'wpvdemo' ),
		        'site_already_installed' => __( 'This site is already installed', 'wpvdemo' ),
		        'cannot_install_site' => __( 'This demo site cannot be installed. <a href="https://wp-types.com/documentation/user-guides/using-framework-installer-to-install-reference-sites/" target="_blank">Please ensure that you meet all plugin and site requirements before installing a demo site.</a>', 'wpvdemo' ),
		        'multi_language_label' => __( 'Multilingual with WPML', 'wpvdemo' ),
		        'sites' => json_encode( $this->refsites ),
                'current_installed_site' => get_option( 'fidemo_installed', ''),
                'is_discover' => $this->is_discoverwp(),
		        'is_multisite' => is_multisite() && ! $this->is_discoverwp(),
                'verification_string' => $verification_string,
                'maximum_alowed_sites' => __( 'Only a maximum of 2 %s sites can be created.', 'wpvdemo' ),
                'site_size_notice' => __( 'This site needs to download %s zip. It might take a while.', 'wpvdemo' ),
                'theme_required_single' => __( 'You need to have %s theme installed and active in your network.', 'wpvdemo' ),
                'theme_not_found' => __( 'theme not found or does not meet the version requirement', 'wpvdemo' ),
                'themes_required' => __( 'You need one of Astra, GeneratePress or OceanWP theme installed and active in your network.', 'wpvdemo' )

        );


		wp_localize_script( 'fidemo-basic', 'fidemo_dialog_texts', $fidemo_dialog_texts );

	}

	/**
     * Generate array of installed plugins
	 * @return array
	 */
	function get_existing_plugins() {
		$all_plugins    = get_plugins();

		$plugins = array();
		foreach( $all_plugins as $plugin_name => $plugin_info ) {
			$plugin_file_check = basename( $plugin_name, '.php' );
			$key               = sanitize_title( $plugin_file_check );
			if ( 'plugin' == $plugin_file_check ) {
				//Let's adjust to something more unique
				$key = sanitize_title( $plugin_info['Name'] );
			}
            $plugins[ $key ] = array(
                'name' => $plugin_info['Title'],
                'version' => $plugin_info['Version']
            );
        }
	    return $plugins;
    }

	/**
	 *
	 */
	function manage_reference_sites_add_help_tab(){
		$help_overview =
			'<p>' . __('Install complete Toolset reference sites and use as basis for your client work.') . '</p>' .
			'<p>' . __('Need to build complete e-commerce, magazine, real estate, classifieds and other complex sites? You can use the Toolset Framework Installer to speed up your development work.') . '</p>' .
			'<p>' . __('You will get a fully-functional site, with a theme, everything configured and working and even sample content. Then, edit whatever you need and deliver to your clients.') . '</p>';

		get_current_screen()->add_help_tab( array(
			'id'      => 'overview',
			'title'   => __('Overview'),
			'content' => $help_overview
		) );

		$help_installing =
			'<p>' . sprintf(__('These are pre-built sites, created using <a href="%s" target="_blank">Toolset plugins</a>, which you can use as the basis for your projects.'), 'http://wp-types.com/') . '</p>' .
			'<p>'. __(' You will need to:') . '</p>' .
			'<ul><li>'. __('Create a blank WordPress site') . '</li>' .
			'<li>'. __('Install the Framework Installer plugin') . '</li>' .
			'<li>'. __('Choose a site and install it') . '</li></ul>';

		get_current_screen()->add_help_tab( array(
			'id'      => 'installing',
			'title'   => __('Downloading and Installing'),
			'content' => $help_installing
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . __('<a href="http://wp-types.com/documentation/views-demos-downloader/" target="_blank">Documentation on how to use Framework Installer</a>') . '</p>' .
			'<p>' . __('<a href="http://wp-types.com/forums/" target="_blank">Support Forums</a>') . '</p>'
		);


	}

	/**
	 * Added support for Toolset unified menu checks.
	 * Returns TRUE if existing Toolset common library can support unified menu
	 * @return boolean
	 */
	public function wpvdemo_can_implement_unified_menu() {

		$unified_menu = false;

		$is_discoverwp = $this->is_discoverwp();

		if ( false === $is_discoverwp ) {

			$is_available = apply_filters( 'toolset_is_toolset_common_available', false );
			if ( TRUE === $is_available ) {
				$unified_menu = true;
			}

		} else {
			//Discover-WP multisite checks
			global $live_demo_registration;
			if ( is_object( $live_demo_registration ) ) {
				if ( method_exists( $live_demo_registration , 'wpvlive_can_implement_unified_menu' ) ) {
					$is_available = $live_demo_registration->wpvlive_can_implement_unified_menu();
					if ( TRUE === $is_available ) {
						$unified_menu = true;
					}
				}
			}
		}

		return $unified_menu;
	}

	/**
	 * Check if current site is discover-wp
	 * @return bool1
	 */
	function is_discoverwp() {
		$is_discover = false;

		if (is_multisite()) {
			$authorized_discover_sites = $this->get_discover_sites();
			$parts = parse_url( network_site_url() );
			if ( isset( $parts['host'] ) ) {
				$the_host = trim( $parts['host'] );
				if ( is_string( $the_host ) ) {
					if ( ! empty( $the_host ) && in_array( $the_host, $authorized_discover_sites ) ) {
						$is_discover = true;
					}
				}

			}
		}
		return $is_discover;
	}

	/**
	 * Get array of discover-wp sites
	 * @return array
	 */
	function get_discover_sites() {
		$sites = array(
			'discover-wp.com',
			'discover-wp.dev',
			'views-live-demo.local',
			'discover-wp.tld',
			'discover.host',
			'dev.discover-wp.tld',
			'dev.discover-wp.com'
		);
		return $sites;
	}

}