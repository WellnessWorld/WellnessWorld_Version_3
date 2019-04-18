<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Social_Booster
 * @subpackage Social_Booster/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Social_Booster
 * @subpackage Social_Booster/includes
 * @author     Rextheme <info@rextheme.com>
 */
class Social_Booster {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Social_Booster_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $social_booster    The string used to uniquely identify this plugin.
	 */
	protected $social_booster;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SOCIAL_BOOSTER_VERSION' ) ) {
			$this->version = SOCIAL_BOOSTER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->social_booster = 'social-booster';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Social_Booster_Loader. Orchestrates the hooks of the plugin.
	 * - Social_Booster_i18n. Defines internationalization functionality.
	 * - Social_Booster_Admin. Defines all hooks for the admin area.
	 * - Social_Booster_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-social-booster-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-social-booster-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-social-booster-admin.php';

		/**
		 * The class responsible for defining admin page and submenu pages.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-social-booster-admin-pages.php';

		/**
		 * The class responsible for defining all database tables.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-social-booster-db-manager.php';

		/**
		 * The class responsible for sending data to social media graph api.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-social-booster-autopost.php';

		/**
		 * The class responsible for all cron schedules.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-social-booster-cron.php';

		/**
		 * The class responsible for defining all JQuery Ajax.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-social-booster-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-social-booster-public.php';

		$this->loader = new Social_Booster_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Social_Booster_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Social_Booster_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Social_Booster_Admin( $this->get_social_booster(), $this->get_version() );
		$plugin_admin_page = new Social_Booster_Admin_Pages();
		$plugin_admin_cron = new Social_Booster_Autopost();
		$plugin_admin_schedule = new Social_Booster_Cron();
		$plugin_admin_ajax = new Social_Booster_Ajax();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin_page, 'socialbooster_add_admin_pages' );
		$this->loader->add_action( 'crontrol_cron_job', $plugin_admin_cron, 'socialbooster_action_php_cron_event' );
		$this->loader->add_action( 'wp_ajax_meta_results', $plugin_admin_cron, 'socialbooster_post_info' );
		$this->loader->add_action( 'cron_schedules', $plugin_admin_schedule, 'socialbooster_filter_cron_schedules' );
		$this->loader->add_action( 'wp_ajax_tw_results', $plugin_admin_ajax, 'socialbooster_process_tw_info' );
		$this->loader->add_action( 'wp_ajax_fb_results', $plugin_admin_ajax, 'socialbooster_process_fb_info' );
		$this->loader->add_action( 'wp_ajax_delete_form', $plugin_admin_ajax, 'socialbooster_process_delete_info' );
		$this->loader->add_action( 'wp_ajax_instagram_form', $plugin_admin_ajax, 'socialbooster_process_instagram_info' );
		$this->loader->add_action( 'wp_ajax_instagram_auth', $plugin_admin_ajax, 'socialbooster_process_instagram_auth' );
		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Social_Booster_Public( $this->get_social_booster(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_social_booster() {
		return $this->social_booster;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Social_Booster_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
