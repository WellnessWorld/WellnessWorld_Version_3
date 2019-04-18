<?php

namespace OTGS\Toolset\Access\Controllers\Filters;

use OTGS\Toolset\Access\Controllers\Import;
use OTGS\Toolset\Access\Models\ExportImport;
use OTGS\Toolset\Access\Models\Settings;
use OTGS\Toolset\Access\Models\UserRoles;
use OTGS\Toolset\Access\Models\Capabilities;
use OTGS\Toolset\Access\Viewmodels\PostMetabox as PostMetabox;

/**
 * Class collect backend filters methods
 *
 * @package OTGS\Toolset\Access\Controllers\Filters
 *
 * @since 2.7
 */
class BackendFilters {

	private static $instance;

	private $exclude_caps_array;


	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public static function initialize() {
		self::get_instance();
	}


	public function __construct() {
		$this->exclude_caps_array = array(
			'edit_users' => 1,
			'delete_users' => 1,
			'manage_options' => 1,
			'edit_theme_options' => 1,
			'manage_links' => 1,
			'edit_plugins' => 1,
			'ddl_edit_layout' => 1,
			'delete_users' => 1,
			'edit_themes' => 1,
			'manage_network' => 1,
			'manage_sites' => 1,
			'manage_privacy_options' => 1,
			'wpseo_manage_options' => 1,
			'manage_woocommerce' => 1,
			'manage_translations' => 1,
			'gravityforms_edit_forms' => 1,
			'gravityforms_delete_forms' => 1,
			'gravityforms_edit_entry_notes' => 1,
			'gravityforms_edit_settings' => 1,
			'gravityforms_delete_entries' => 1,
			'gravityforms_edit_entries' => 1,
			'manage_network' => 1,
			'manage_network_users' => 1,
			'manage_network_plugins' => 1,
			'manage_network_themes' => 1,
			'manage_network_options' => 1,
			'edit_dashboard' => 1,
			'delete_site' => 1,
			'delete_plugins' => 1,
			'delete_themes' => 1,
			'delete_sites' => 1,
		);
		if ( is_admin() ) {
			if ( ! class_exists( 'Import' ) ) {
				require_once( TACCESS_PLUGIN_PATH . '/application/controllers/import.php' );
			}
			$access_import = Import::get_instance();
			add_action( 'admin_init', array( $this, 'check_add_media_permissions' ) );
			add_filter( 'icl_get_extra_debug_info', array( $this, 'add_access_extra_debug_information' ) );
			add_action( 'wp_loaded', array( $access_import, 'access_import_on_form_submit' ) );
			add_action( 'admin_notices', array( $access_import, 'access_import_notices_messages' ) );
			add_action( 'admin_notices', array( $this, 'toolset_access_admin_notice' ) );
			add_action( 'admin_head', array( $this, 'admin_add_help' ) );
			if ( class_exists( 'WPDD_Layouts_Users_Profiles' )
				&& ! method_exists( 'WPDD_Layouts_Users_Profiles', 'wpddl_layouts_capabilities' ) ) {
				add_filter( 'wpcf_access_custom_capabilities', 'wpcf_access_layouts_capabilities', 12 );
			}
			add_filter( 'wpcf_access_custom_capabilities', 'wpcf_access_general_capabilities', 9 );
			add_filter( 'wpcf_access_custom_capabilities', 'wpcf_access_wpml_capabilities', 10 );
			add_filter( 'wpcf_access_custom_capabilities', 'wpcf_access_woocommerce_capabilities', 13 );
			add_filter( 'wpcf_access_custom_capabilities', 'wpcf_access_access_capabilities', 11 );
		}
	}


	/*
	 * @since 2.2
	 * toolset_access_admin_notice
	 * Show admin notice that access settings was converted to role based system
	*/
	public static function toolset_access_admin_notice() {
		global $current_user, $pagenow, $wpdb;
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( $pagenow == 'plugins.php' || ( $pagenow == 'admin.php' && $page == 'types_access' ) ) {
			$user_id = $current_user->ID;
			if ( get_user_meta( $user_id, 'toolset_access_conversion_ignore_notice' ) ) {
				$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key='toolset_access_conversion_ignore_notice'" );
			}
		}
	}


	/**
	 * Add Access help menu
	 */
	public function admin_add_help() {
		$screen = get_current_screen();

		if ( is_null( $screen ) || $screen->base != 'toolset_page_types_access' ) {
			return;
		}

		$help = '<p>'
			. __( "<strong>Post Types</strong>", 'wpcf-access' )
			. '<br>
        '
			. __( 'Control who can do different actions for each post type.', 'wpcf-access' )
			. '
		<a href="https://toolset.com/documentation/user-guides/setting-access-control/" title="'
			. __( 'Access Control for Standard and Custom Content Types', 'wpcf-access' )
			. '" target="_blank"><i class="fa fa-question-circle"></i></a></p>';

		$help .= '<p>'
			. __( "<strong>Taxonomies</strong>", 'wpcf-access' )
			. '<br>
        '
			. __( 'Control who can do different actions for each taxonomy.', 'wpcf-access' )
			. '
		<a href="https://toolset.com/documentation/user-guides/setting-access-control/" title="'
			. __( 'Access Control for Standard and Custom Taxonomies ', 'wpcf-access' )
			. '" target="_blank"><i class="fa fa-question-circle"></i></a></p>';

		$help .= '<p>'
			. __( "<strong>Posts Groups</strong>", 'wpcf-access' )
			. '<br>
        '
			. __( 'Control the read access to individual posts (pages, posts and custom post types). Create ‘post groups’,  which will hold all the items that will have the same read permission. Each group of posts can have as many items as you want.', 'wpcf-access' )
			. '
		<a href="https://toolset.com/documentation/user-guides/limiting-read-access-specific-content/" title="'
			. __( 'Limiting read access to specific content', 'wpcf-access' )
			. '" target="_blank"><i class="fa fa-question-circle"></i></a></p>';

		$help .= '<p>'
			. __( "<strong>WPML Groups</strong>", 'wpcf-access' )
			. '<br>
        '
			. __( 'Control access and editing privileges for users for different languages.', 'wpcf-access' )
			. '
		<a href="https://wpml.org/documentation/translating-your-contents/how-to-use-access-plugin-to-create-editors-for-specific-language/" title="'
			. __( 'How to Use Access Plugin to Create Editors for Specific Language', 'wpcf-access' )
			. '" target="_blank"><i class="fa fa-question-circle"></i></a></p>';

		$help .= '<p>'
			. __( "<strong>Types Fields</strong>", 'wpcf-access' )
			. '<br>
        '
			. __( 'Control who can view and edit custom fields.', 'wpcf-access' )
			. '
			<a href="https://toolset.com/documentation/user-guides/access-control-for-user-fields/" title="'
			. __( 'Access Control for Fields', 'wpcf-access' )
			. '" target="_blank"><i class="fa fa-question-circle"></i></a></p>';

		$help .= '<p>'
			. __( "<strong>Toolset Forms</strong>", 'wpcf-access' )
			. '<br>
        '
			. __( 'Control who has access to different Toolset forms.', 'wpcf-access' )
			. '
			<a href="https://toolset.com/documentation/user-guides/access-control-for-cred-forms/" title="'
			. esc_attr( __( 'Access Control for Toolset Forms', 'wpcf-access' ) )
			. '" target="_blank"><i class="fa fa-question-circle"></i></a></p>';

		$help .= '<p>'
			. __( "<strong>Custom Roles</strong>", 'wpcf-access' )
			. '<br>
        '
			. __( 'Set up custom user roles and control their privileges.', 'wpcf-access' )
			. '
		<a href="https://toolset.com/documentation/user-guides/managing-wordpress-admin-capabilities-access/" title="'
			. __( 'Managing WordPress Admin Capabilities with Access', 'wpcf-access' )
			. '" target="_blank"><i class="fa fa-question-circle"></i></a></p>';

		$screen->add_help_tab(
			array(
				'id' => 'access-help',
				'title' => __( 'Access Control', 'wpcf-access' ),
				'content' => $help,
			)
		);
	}


	/*
	 * Disable media upload
	 */
	public function check_add_media_permissions() {
		$access_roles = UserRoles::get_instance();
		$roles = $access_roles->get_current_user_roles();
		if ( in_array( 'administrator', $roles ) ) {
			return true;
		}

		$user_can_edit_own = $this->check_if_user_can_do_media( 'attachment', 'edit_own' );
		$user_can_read = $this->check_if_user_can_do_media( 'attachment', 'read' );

		if ( ! $user_can_edit_own ) {
			remove_submenu_page( 'upload.php', 'media-new.php' );
			add_action( 'wp_handle_upload_prefilter', array( $this, 'wpcf_access_disable_media_upload' ), 1 );
		}
		if ( ! $user_can_read ) {
			global $menu;
			if ( isset( $menu ) && is_array( $menu ) ) {
				remove_menu_page( 'upload.php' );
			}
			remove_action( 'media_buttons', 'media_buttons' );
		}

		if ( $_SERVER['SCRIPT_NAME'] == '/wp-admin/upload.php' ) {
			if ( ! $user_can_read ) {
				wp_redirect( get_admin_url() );
				exit;
			}
		}
		if ( $_SERVER['SCRIPT_NAME'] == '/wp-admin/media-new.php' ) {
			if ( ! $user_can_edit_own ) {
				wp_redirect( get_admin_url() . 'upload.php' );
				exit;
			}
		}
	}


	/**
	 * @param $file
	 *
	 * @return mixed
	 */
	public function wpcf_access_disable_media_upload( $file ) {
		$file['error'] = __( 'You have no access to upload files', 'wpcf-access' );

		return $file;
	}


	/**
	 * Check if user have media permission
	 *
	 * @param string $post_type
	 * @param string $action
	 *
	 * @return bool
	 */
	public function check_if_user_can_do_media( $post_type = 'attachment', $action = 'read' ) {
		$access_settings = Settings::get_instance();
		$settings_access = $access_settings->get_types_settings();

		if ( ! isset( $settings_access[ $post_type ] ) ) {
			return true;
		}
		if ( $settings_access[ $post_type ]['mode'] == 'not_managed' ) {
			return true;
		}

		$access_roles = UserRoles::get_instance();
		$roles = $access_roles->get_current_user_roles();

		if ( in_array( 'administrator', $roles ) ) {
			return true;
		}

		// Empty settings
		if ( ! isset( $settings_access[ $post_type ] ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return true;
			} else {
				return false;
			}
		}

		// Follow Posts permissions
		if ( $settings_access[ $post_type ]['mode'] === 'follow' ) {
			if ( isset( $settings_access['post']['permissions'][ $action ]['roles'] ) ) {
				$post_type = 'post';
			} elseif ( current_user_can( 'edit_posts' ) ) {
				return true;
			} else {
				return false;
			}
		}

		if ( ! isset( $settings_access[ $post_type ]['permissions'][ $action ]['roles'] ) ) {
			return false;
		}

		$roles_check = array_intersect( $roles, $settings_access[ $post_type ]['permissions'][ $action ]['roles'] );
		if ( $roles_check ) {
			return true;
		} else {
			return false;
		}

	}


	/**
	 * Get extra debug information.
	 *
	 * Get extra debug information for debug page.
	 *
	 * @param array debug information table
	 *
	 * @return array debug information table
	 */
	public static function add_access_extra_debug_information( $extra_debug ) {
		global $wpcf_access;
		$clone = clone $wpcf_access;
		$extra_debug['access'] = array();
		foreach ( array( 'rules', 'settings' ) as $key ) {
			$extra_debug['access'][ $key ] = (array) $clone->$key;
		}
		unset( $clone );

		return $extra_debug;
	}


	/**
	 * 'has_cap' filter.
	 *
	 * Returns all the modified capabilities. Cached per capability
	 * NOTE cached per cap checked
	 * NOTE maybe it sets them in just the first pass and we do not need one per different cap check
	 *
	 * @global type $current_user
	 * @global type $wpcf_access ->rules->types
	 *
	 * @param array $allcaps All the capabilities of the user
	 * @param array $cap [0] Required capability
	 * @param array $args [0] Requested capability
	 *                       [1] User ID
	 *                       [2] Associated object ID
	 * @param object $user The user ti check capabilities against, added in WP 3.7.0
	 *
	 * @return array
	 */
	public function toolset_access_has_cap_filter( $allcaps, $caps, $args, $user ) {
		$access_roles = UserRoles::get_instance();
		$access_capabilities = Capabilities::get_instance();


		if ( ! isset( $this->exclude_caps_array[ $args[0] ] ) && $access_capabilities->is_managed_capability( $args[0] ) ) {

			$roles = $access_roles->get_current_user_roles( false, $user );
			$main_role = $access_roles->get_current_user_roles( true, $user );

			if ( 'administrator' === $main_role || in_array( 'administrator', $roles ) ) {
				if ( isset( $caps[0] ) ) {
					foreach ( $caps as $val => $cap ) {
						$allcaps = $access_capabilities->add_or_remove_cap( $allcaps, $cap, true, $user );
					}
				}
			} else {
				$access_cache_user_has_cap_key = md5( 'access::user_caps_'
					. implode( '-', $caps )
					. '_'
					. serialize( $args )
					. '#'
					. $user->ID );
				$access_cache_user_has_cap = 'access_cache_user_has_cap';
				$cached_caps = \Access_Cacher::get( $access_cache_user_has_cap_key, $access_cache_user_has_cap );
				if ( false === $cached_caps ) {
					$allcaps = $access_capabilities->get_capabilities_by_user_permissions( $allcaps, $caps, $args, $user );
					\Access_Cacher::set( $access_cache_user_has_cap_key, $allcaps, $access_cache_user_has_cap );
				} else {
					$allcaps = $cached_caps;
				}
			}
		}


		return $allcaps;
	}


	/**
	 * Enqueue assets on edit post for a post group meta box
	 */
	public function toolset_access_select_group_metabox_files() {
		global $post, $pagenow;

		if ( $pagenow === 'post.php' && isset( $post->post_type ) && $post->post_type != 'attachment' ) {
			\TAccess_Loader::loadAsset( 'SCRIPT/wpcf-access-post', 'wpcf-access' );
			\TAccess_Loader::loadAsset( 'STYLE/wpcf-access-post', 'wpcf-access' );

			wp_enqueue_style( 'toolset-notifications-css' );
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'types_access' ) {
			add_action( 'admin_footer', array( $this, 'dependencies_render_js' ) );
		}
	}


	/**
	 * Render Access permission dependencies JS
	 */
	public function dependencies_render_js() {
		$capabilies = \OTGS\Toolset\Access\Models\Capabilities::get_instance();
		$deps = $capabilies->access_dependencies();
		$output = '';
		$output .= "\n\n<script type=\"text/javascript\">\n/*<![CDATA[*/\n";
		$active = array();
		$inactive = array();
		$active_message = array();
		$inactive_message = array();

		$output .= 'var wpcf_access_dep_active_messages_pattern_singular = "'
			. __( "Since you enabled '%cap', '%dcaps' has also been enabled.",
				'wpcf-access' )
			. '";' . "\n";
		$output .= 'var wpcf_access_dep_active_messages_pattern_plural = "'
			. __( "Since you enabled '%cap', '%dcaps' have also been enabled.",
				'wpcf-access' )
			. '";' . "\n";
		$output .= 'var wpcf_access_dep_inactive_messages_pattern_singular = "'
			. __( "Since you disabled '%cap', '%dcaps' has also been disabled.",
				'wpcf-access' )
			. '";' . "\n";
		$output .= 'var wpcf_access_dep_inactive_messages_pattern_plural = "'
			. __( "Since you disabled '%cap', '%dcaps' have also been disabled.",
				'wpcf-access' )
			. '";' . "\n";

		foreach ( $deps as $dep => $data ) {
			$dep_data = $capabilies->get_cap_settings( $dep );
			$output .= 'var wpcf_access_dep_' . $dep . '_title = "'
				. $dep_data['title']
				. '";' . "\n";
			foreach ( $data as $dep_active => $dep_set ) {
				if ( strpos( $dep_active, 'true_' ) === 0 ) {
					$active[ $dep ][] = '\'' . implode( '\', \'', $dep_set ) . '\'';
					foreach ( $dep_set as $cap ) {
						$_cap = $capabilies->get_cap_predefined_settings( $cap );
						$active_message[ $dep ][] = $_cap['title'];
					}
				} else {
					$inactive[ $dep ][] = '\'' . implode( '\', \'', $dep_set ) . '\'';
					foreach ( $dep_set as $cap ) {
						$_cap = $capabilies->get_cap_predefined_settings( $cap );
						$inactive_message[ $dep ][] = $_cap['title'];
					}
				}
			}
		}

		foreach ( $active as $dep => $array ) {
			$output .= 'var wpcf_access_dep_true_' . $dep . ' = ['
				. implode( ',', $array ) . '];' . "\n";
			$output .= 'var wpcf_access_dep_true_' . $dep . '_message = [\''
				. implode( '\',\'', $active_message[ $dep ] ) . '\'];' . "\n";
		}

		foreach ( $inactive as $dep => $array ) {
			$output .= 'var wpcf_access_dep_false_' . $dep . ' = ['
				. implode( ',', $array ) . '];' . "\n";
			$output .= 'var wpcf_access_dep_false_' . $dep . '_message = [\''
				. implode( '\',\'', $inactive_message[ $dep ] ) . '\'];' . "\n";
		}

		$output .= "/*]]>*/\n</script>\n\n";
		echo $output;
	}


	/**
	 * Add a meta box to a edit post to assign a post to a group
	 */
	public function toolset_access_select_group_metabox() {
		global $post, $wp_version, $pagenow;
		if ( isset( $post ) && is_object( $post ) && '' != $post->ID && 'attachment' != $post->post_type ) {
			if ( current_user_can( 'manage_options' )
				|| current_user_can( 'access_change_post_group' )
				|| current_user_can( 'access_create_new_group' ) ) {
				if ( $pagenow == 'post.php' ) {
					$metabox = new PostMetabox();
					add_meta_box( 'access_group', __( 'Post group', 'wpcf-access' ), array(
						$metabox,
						'meta_box',
					), $post->post_type, 'side', 'high' );
				}
			}
		}
	}


	/**
	 * Access init
	 */
	public function toolset_access_backend_init() {
		$access_export_import = ExportImport::get_instance();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'toolset_filter_register_menu_pages', array( $this, 'register_access_pages_in_menu' ), 20 );
		add_filter( 'toolset_filter_register_export_import_section', array(
			$access_export_import,
			'register_export_import_section',
		), 50 );
		add_action( 'toolset_action_admin_init_in_toolset_page', array(
			$access_export_import,
			'load_assets_in_shared_pages',
		), 30 );
	}


	/**
	 * Enqueue script for Access settings page
	 */
	public static function admin_enqueue_scripts() {
		global $pagenow;
		if (
			$pagenow == 'admin.php'
			&& isset( $_GET['page'] )
			&& ( $_GET['page'] == 'types_access' )
		) {
			\TAccess_Loader::loadAsset( 'STYLE/wpcf-access-dev', 'wpcf-access' );
			\TAccess_Loader::loadAsset( 'SCRIPT/wpcf-access-dev', 'wpcf-access' );
			\TAccess_Loader::loadAsset( 'STYLE/wpcf-access-dialogs-css', 'wpcf-access-dialogs-css' );
			\TAccess_Loader::loadAsset( 'STYLE/notifications', 'notifications' );

			$select2_version = '4.0.3';
			if ( ! wp_script_is( 'toolset_select2', 'registered' ) ) {
				wp_register_script(
					'toolset_select2',
					TACCESS_PLUGIN_URL . '/toolset/toolset-common/res/lib/select2/select2.js',
					array( 'jquery' ),
					$select2_version
				);
				wp_deregister_script( 'toolset-select2-compatibility' );
			}

			if ( ! wp_style_is( 'toolset-select2-css', 'registered' ) ) {
				wp_register_style(
					'toolset-select2-css',
					TACCESS_PLUGIN_URL . '/toolset/toolset-common/res/lib/select2/select2.css',
					array(),
					$select2_version
				);
			}
			wp_deregister_style( 'toolset-select2-overrides-css' );
			wp_enqueue_script( 'toolset_select2' );
			wp_enqueue_style( 'toolset-select2-css' );

			wp_enqueue_script( \OTGS_Assets_Handles::TABLE_STICKY_HEADER );

			add_thickbox();
		}
	}


	/**
	 * Register Access Control page
	 *
	 * @param $pages
	 *
	 * @return array
	 */
	public function register_access_pages_in_menu( $pages ) {
		$pages[] = array(
			'slug' => 'types_access',
			'menu_title' => __( 'Access Control', 'wpcf-access' ),
			'page_title' => __( 'Access Control', 'wpcf-access' ),
			'callback' => array( $this, 'toolset_access_admin_menu_page' ),
		);

		return $pages;
	}


	/**
	 * Menu page render hook.
	 */
	public function toolset_access_admin_menu_page() {
		if ( ! class_exists( 'Access_Admin_Edit' ) ) {
			\TAccess_Loader::load( 'CLASS/Admin_Edit' );
		}
		echo "\r\n" . '<div class="wrap">
        <div id="icon-wpcf-access" class="icon32"><br /></div>
        <h1>' . __( 'Access Control', 'wpcf-access' ) . '</h1>' . "\r\n";
		\Access_Admin_Edit::wpcf_access_admin_edit_access();
		echo "\r\n" . '</div>' . "\r\n";
	}

}
