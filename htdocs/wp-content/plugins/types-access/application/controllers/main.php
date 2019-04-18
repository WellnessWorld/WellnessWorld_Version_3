<?php

namespace OTGS\Toolset\Access;

use OTGS\Toolset\Access\Controllers\AccessApi as AccessApi;
use OTGS\Toolset\Access\Controllers\Filters\CommonFilters as CommonFilters;
use OTGS\Toolset\Access\Controllers\UploadPermissions as UploadPermissions;

/**
 * Main loader class. Loads classes when they required before init
 *
 * Class Main
 *
 * @package OTGS\Toolset\Access
 *
 * @since 2.7
 */
class Main {

	private static $instance;


	/**
	 * @return Main
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public static function initialize() {
		self::get_instance();
	}


	/**
	 * Main constructor.
	 */
	function __construct() {

		add_action( 'init', array( $this, 'on_init' ) );
		add_action( 'toolset_common_loaded', array( $this, 'register_autoloaded_classes' ), 10 );
		add_action( 'after_setup_theme', array( $this, 'otg_access_blocks_initialize' ), 999 );

		//Load Settings and user roles before init
		require_once( TACCESS_PLUGIN_PATH . '/application/models/access_settings.php' );
		require_once( TACCESS_PLUGIN_PATH . '/application/models/user_roles.php' );
		require_once( TACCESS_PLUGIN_PATH . '/application/models/capabilities.php' );

		\OTGS\Toolset\Access\Models\Settings::get_instance();
		require_once( TACCESS_PLUGIN_PATH . '/application/controllers/filters/backend_filters.php' );
		$backend_filters = \OTGS\Toolset\Access\Controllers\Filters\BackendFilters::get_instance();
		if ( is_admin() ) {

			if ( defined( 'DOING_AJAX' ) ) {

			} else {
				add_action( 'init', array( $backend_filters, 'toolset_access_backend_init' ), 9 );
			}
		} else {
			require_once( TACCESS_PLUGIN_PATH . '/application/controllers/frontend.php' );
			\OTGS\Toolset\Access\Controllers\Frontend::initialize();

			require_once( TACCESS_PLUGIN_PATH . '/application/controllers/permissions_read.php' );
			\OTGS\Toolset\Access\Controllers\PermissionsRead::initialize();
		}

		require_once( TACCESS_PLUGIN_PATH . '/application/controllers/backend.php' );
		$backend = \OTGS\Toolset\Access\Controllers\Backend::get_instance();

		require_once( TACCESS_PLUGIN_PATH . '/application/controllers/permissions_post_types.php' );
		\OTGS\Toolset\Access\Controllers\PermissionsPostTypes::get_instance();

		require_once( TACCESS_PLUGIN_PATH . '/application/controllers/permissions_taxonomies.php' );
		\OTGS\Toolset\Access\Controllers\PermissionsTaxonomies::get_instance();

		require_once( TACCESS_PLUGIN_PATH . '/application/controllers/permissions_third_party.php' );
		\OTGS\Toolset\Access\Controllers\PermissionsThirdParty::get_instance();

		\TAccess_Loader::load( 'CLASS/Post' );
	}


	/**
	 * Initialize the Gutenberg blocks compatibility for Access.
	 *
	 * @since 2.7
	 */
	public function otg_access_blocks_initialize() {
		$toolset_common_bootstrap = \Toolset_Common_Bootstrap::getInstance();
		$toolset_common_sections = array( \Toolset_Common_Bootstrap::TOOLSET_BLOCKS );
		$toolset_common_bootstrap->load_sections( $toolset_common_sections );

		$access_blocks = new \OTGS\Toolset\Access\Controllers\Blocks();
		$access_blocks->init_hooks();
	}


	/**
	 * Main init
	 */
	public function on_init() {
		\OTGS\Toolset\Access\Models\UserRoles::initialize();

		$locale = ( function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale() );
		\TAccess_Loader::loadLocale( 'wpcf-access', 'access-' . $locale . '.mo' );

		if ( is_admin() ) {
			if ( defined( 'DOING_AJAX' ) ) {
				$access_ajax = new \OTGS\Toolset\Access\Ajax;
				$access_ajax->initialize();
			} else {
				//Admin init
				$api_controller = new AccessApi();
				$api_controller->initialize();

				$uploads_controller = new UploadPermissions();
				$uploads_controller->initialize();

				/*
				 * For the future
				 * a filter to manage comments actions on wp-admin/edit-comments.php
				$comments_permissions = CommentsPermissions::get_instance();
				add_filter( 'comment_row_actions', array( $comments_permissions, 'test_filter' ), 10, 2 );
				*/

				do_action( 'wpcf_access_late_init' );
			}
		} else {
			//front-end init

		}

		$common_filters = CommonFilters::get_instance();
		$common_filters->wpcf_access_hooks_collect();
		\OTGS\Toolset\Access\Models\Settings::initialize();
	}


	/**
	 * Register Autoloader
	 */
	public function register_autoloaded_classes() {
		$classmap = include( TACCESS_PLUGIN_PATH . '/application/autoload_classmap.php' );
		do_action( 'toolset_register_classmap', $classmap );
	}


	private $mode = self::MODE_UNDEFINED;

	const MODE_UNDEFINED = '';

	const MODE_AJAX = 'ajax';

	const MODE_ADMIN = 'admin';

	const MODE_FRONTEND = 'frontend';


	/**
	 * @return string
	 */
	public function get_plugin_mode() {
		return $this->mode;
	}


	/**
	 * @param string $new_mode
	 *
	 * @return bool
	 */
	public function set_plugin_mode( $new_mode = self::MODE_UNDEFINED ) {
		if ( ! in_array( $new_mode, array(
			self::MODE_UNDEFINED,
			self::MODE_AJAX,
			self::MODE_ADMIN,
			self::MODE_FRONTEND,
		) ) ) {
			return false;
		}
		$this->mode = $new_mode;

		return true;
	}


	/**
	 * @return bool
	 */
	public function is_admin() {
		return ( $this->get_plugin_mode() == self::MODE_ADMIN );
	}

}