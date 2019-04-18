<?php
/**
 * WooCommerce Views Class
 * 
 * The WooCommerce Views Class aims PHP code-free implementation of WooCommerce Plugin with Toolset.
 *
 * @class 		Class_WooCommerce_Views
 * @version		2.7.4
 * @package		WooCommerce-Views/Classes
 * @category	Class
 * @author 		OnTheGoSystems
 */ 
class Class_WooCommerce_Views {

	/** @public array WooCommerce Views functions for [wpv-if] conditional evaluations */
	public $wcviews_functions = array();
	
	/** @public array WooCommerce Views shortcodes associated post types */
	public $wcviews_associated_posttypes =array();

	/** @public array WooCommerce Views shortcodes */
	public $wcviews_final_shortcodes =array();
	
	/** @protected array WooCommerce Views texturize status */
	protected $wcviews_disabled_texturize;
	
	/** @protected array WooCommerce Views Toolset packager slug */
	protected $wcviews_toolset_packager_slug;
	
	/** @protected array WooCommerce core settings slug */
	protected $wcviews_wc_core_slug;
	
	/** @private boolean WooCommerce Views default WooCommerce body class loaded */
	private $wcviews_woocommerce_default_class_loaded;
	
	/** @private boolean WooCommerce Views shortcode execution */
	private $wcviews_shortcode_executed;
	
	/** @private string WooCommerce Views import errors */
	private $wcviews_import_errors;
	
	/** @private string WooCommerce Views import messages */
	private $wcviews_import_messages;
	
	/** @public string WooCommerce Views template name */
	public $wcviews_the_template_name;
	
	/**
	 * Hook in methods
	 */
	
	public function __construct() {
		
		/** {ENCRYPTION PATCH HERE} **/
			
		add_action('plugins_loaded', array(&$this,'wcviews_init'),2);
				
		//Aux
		if ( !defined('WPV_WOOCOMERCE_VIEWS_SHORTCODE') ) {
			define('WPV_WOOCOMERCE_VIEWS_SHORTCODE', 'wpv-wooaddcart');
		}
		if ( !defined('WPV_WOOCOMERCEBOX_VIEWS_SHORTCODE') ) {
			define('WPV_WOOCOMERCEBOX_VIEWS_SHORTCODE', 'wpv-wooaddcartbox');
		}
		
		//Menus
		add_action( 'admin_menu', array(&$this,'woocommerce_views_add_this_menupage'),50);
		add_filter( 'toolset_filter_register_menu_pages', array(&$this,'wcviews_unified_menu'), 45 );
		
		// Scripts
		add_action('wp_enqueue_scripts', array(&$this,'woocommerce_views_scripts_method'));

		// Disabled in 2.7.8
		//add_action('system_cron_execution_hook',array(&$this,'ajax_process_wc_views_batchprocessing'));

		// Deprecated in 2.7.8
		// Use wp_ajax_wc_views_process_products_fields instead
		add_action('wp_ajax_wc_views_ajax_response_admin',array(&$this,'ajax_process_wc_views_batchprocessing'));		
		
		// Batch update fields in products
		// Since 2.7.8
		add_action( 'wp_ajax_wc_views_process_products_fields', array( $this, 'ajax_process_products_fields' ) );		
		// Helper for background batch updating fields in products
		// Since 2.7.8
		add_action( 'init', array( $this, 'maybe_process_products_fields' ) );
		
		add_action('admin_enqueue_scripts', array(&$this,'woocommerce_views_scripts_method_backend'));
		add_action('init',array(&$this,'prefix_setup_schedule'));			
		add_action('admin_init',array(&$this,'reset_all_wc_admin_screen'));
		
		//Old shortcodes-all deprecated still added for outputting deprecation notices		
		add_shortcode('wpv-wooaddcart', array(&$this,'wpv_woo_add_to_cart'));
		add_shortcode('wpv-wooaddcartbox', array(&$this,'wpv_woo_add_to_cart_box'));
		add_shortcode('wpv-wooremovecart', array(&$this,'wpv_woo_remove_from_cart'));
		add_shortcode('wpv-woo-carturl', array(&$this,'wpv_woo_cart_url'));
						
		/** WooCommerce Views shortcodes and its callback function */	
		$this->wcviews_final_shortcodes = array(
				//1.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-breadcrumb
				//Scope: Single products and Listings.
				'wpv-woo-breadcrumb'             =>array(
														'callback'				=>'wpv_woo_breadcrumb_func',
														'loop_use'				=>true,
														'single_product_usage'	=>true,
														'listings_usage'		=>true,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//2.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-show-upsell-items
				//Scope: Single products only
				'wpv-woo-show-upsell-items'      =>array(
														'callback'				=>'wpv_woo_show_upsell_func',
														'loop_use'				=>false,
														'single_product_usage'	=>true,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//3.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-productcategory-images
				//Scope: Product category loops only
				'wpv-woo-productcategory-images' =>array(
														'callback'				=>'wpv_woo_productcategory_images_func',
														'loop_use'				=>true,
														'single_product_usage'	=>false,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>true
													),
				//4.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-products-rating-listing
				//Scope: Product listings only
				'wpv-woo-products-rating-listing'=>array(
														'callback'				=>'wpv_woo_products_rating_on_listing_func',
														'loop_use'				=>true,
														'single_product_usage'	=>false,
														'listings_usage'		=>true,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//5.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-single-products-rating
				//Scope: Single products only
				'wpv-woo-single-products-rating' =>array(
														'callback'				=>'wpv_woo_single_products_rating_func',
														'loop_use'				=>false,
														'single_product_usage'	=>true,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//6.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-related_products
				//Scope: Single products only
				'wpv-woo-related_products'       =>array(
														'callback'				=>'wpv_woo_related_products_func',
														'loop_use'				=>false,
														'single_product_usage'	=>true,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//7.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-list_attributes
				//Scope: Single products and listings.
				'wpv-woo-list_attributes'        =>array(
														'callback'				=>'wpv_woo_list_attributes_func',
														'loop_use'				=>true,
														'single_product_usage'	=>true,
														'listings_usage'		=>true,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//8.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-buy-or-select
				//Scope: Product listings only
				'wpv-woo-buy-or-select'          =>array(
														'callback'				=>'wpv_woo_buy_or_select_func',
														'loop_use'				=>true,
														'single_product_usage'	=>false,
														'listings_usage'		=>true,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//9.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-product-price
				//Scope: Single products and listings.
				'wpv-woo-product-price'          =>array(
														'callback'				=>'wpv_woo_product_price_func',
														'loop_use'				=>true,
														'single_product_usage'	=>true,
														'listings_usage'		=>true,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//10.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-product-image
				//Scope: Single products and listings.
				'wpv-woo-product-image'          =>array(
														'callback'				=>'wpv_woo_product_image_func',
														'loop_use'				=>true,
														'single_product_usage'	=>true,
														'listings_usage'		=>true,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//11.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-buy-options
				//Scope: Single products only
				'wpv-woo-buy-options'            =>array(
														'callback'				=>'wpv_woo_buy_options_func',
														'loop_use'				=>false,
														'single_product_usage'	=>true,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//12.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-add-to-cart-message
				//Scope: Single products and listings
				'wpv-add-to-cart-message'        =>array(
														'callback'				=>'wpv_show_add_cart_success_func',
														'loop_use'				=>true,
														'single_product_usage'	=>true,
														'listings_usage'		=>true,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//13.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-display-tabs
				//Scope: Single products only
				'wpv-woo-display-tabs'           =>array(
														'callback'				=>'wpv_woo_display_tabs_func',
														'loop_use'				=>false,
														'single_product_usage'	=>true,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//14.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-onsale
				//Scope: Single products and listing
				'wpv-woo-onsale'                 =>array(
														'callback'				=>'wpv_woo_onsale_func',
														'loop_use'				=>true,
														'single_product_usage'	=>true,
														'listings_usage'		=>true,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//15.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-product-meta
				//Scope: Single products only
				'wpv-woo-product-meta'           =>array(
														'callback'				=>'wpv_woo_product_meta_func',
														'loop_use'				=>true,
														'single_product_usage'	=>true,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//16.) Scope: Single products and listings
				'wpv-woo-cart-count'             =>array(
														'callback'				=>'wpv_woo_cart_count_func',
														'loop_use'				=>true,
														'single_product_usage'	=>true,
														'listings_usage'		=>true,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//17.) https://toolset.com/documentation/user-guides/views-shortcodes/#wpv-woo-reviews
				//Scope: Single products only
				'wpv-woo-reviews'                =>array(
														'callback'				=>'wpv_woo_show_displayreviews_func',
														'loop_use'				=>false,
														'single_product_usage'	=>true,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				//18.) Scope: Inside a WooCommerce order loops only
				//Not meant for listings or single products.
				'wpv-ordered-product-ids'           =>array(
														'callback'				=>'wpv_show_ordered_product_ids_func',
														'loop_use'				=>true,
														'single_product_usage'	=>false,
														'listings_usage'		=>false,
														'order_usage'			=>true,
														'product_cat_usage'		=>false
													),
				
				/*====================================
				 * WooCommerce Addons Shortcodes
				 *==================================*/
				// Storefront Product Sharing
				'wpv-storefront-product-sharing'   =>array(
														'callback'				=>'wpv_storefront_product_sharing_func',
														'loop_use'				=>false,
														'single_product_usage'	=>true,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													),
				// Storefront Product Sharing
				'wpv-storefront-product-pagination'   =>array(
														'callback'				=>'wpv_storefront_product_pagination_func',
														'loop_use'				=>false,
														'single_product_usage'	=>true,
														'listings_usage'		=>false,
														'order_usage'			=>false,
														'product_cat_usage'		=>false
													)
		);
		
		foreach ($this->wcviews_final_shortcodes as $wcviews_shortcode_name => $wcviews_shortcode_callback) {
			$the_callback_func=$wcviews_shortcode_callback['callback'];
			add_shortcode($wcviews_shortcode_name,array(&$this,$the_callback_func));
		}
		
		//Template loading		
		
		/** We give a priority of PHP_INT_MAX since some theme uses template_redirect to load their own template files.
		 *  We want to load them first before doing the final WC Views template redirect.
		 */
		
		add_action( 'template_redirect', array(&$this,'woocommerce_views_activate_template_redirect' ),PHP_INT_MAX);
		add_action( 'template_redirect', array(&$this,'woocommerce_views_activate_archivetemplate_redirect' ),PHP_INT_MAX);
		add_action( 'switch_theme', array(&$this,'wc_views_reset_wc_default_after_theme_switching' ));
		add_action( 'switch_theme', array(&$this,'wc_views_reset_wc_defaultarchive_after_theme_switching' ));
		add_action( 'after_switch_theme', array(&$this,'wc_views_after_theme_switched' ));
		add_action( 'init',array($this,'wcviews_review_templates_handler'));
		
		//Save post meta values when saving the products or updating		
		add_action('save_post', array( $this,'compute_postmeta_of_products_woocommerce_views'), 300 );
		add_action('wcml_before_sync_product', array( $this,'compute_postmeta_of_products_woocommerce_views'), 10 );
		
		// WP-Views plugin hooks	
		// WooCommerce Views 2.4 category image shortcode on category View
		// Deprecated since Views 2.3 for the Fields and Views dialogs
		// but still needed for the Loop Wizard
		add_filter('editor_addon_menus_wpv-views', array(&$this,'wpv_woo_add_shortcode_in_views_popup_cat'),50);
		add_filter('editor_addon_menus_wpv-views', array(&$this,'wpv_woo_add_shortcode_in_views_popup'));
		add_filter('toolset_editor_addon_post_fields_list',array(&$this,'wpv_woo_add_shortcode_in_views_layout_wizard'));
		
		//Register the computed values as Types fields		
		add_action('wp_loaded',array(&$this,'wpv_register_typesfields_func'));
		add_action('wp_loaded',array(&$this,'wcviews_set_groups_to_product'),250);
		add_action( 'wp_loaded', array( &$this,'wcviews_updated_price_field_to_numeric' ),50 );
		
		//CT template override on product pages when using default WC template
		add_action('wp',array(&$this,'wc_views_override_template_on_loaded'));
		
		//WC wrapper hook
		add_action('wp',array(&$this,'wc_views_woocommerce_wrapper_override_loaded'));
		
		//Make sure single-product.php is fully under WooCommerce control		
		add_action( 'init', array( $this, 'wc_views_dedicated_template_loader' ),50);
		add_filter('body_class',array( $this, 'wc_views_add_woocommerce_to_body_class'),9999);
		
		//Make sure archive-product.php is fully under WooCommerce control
		add_action( 'init', array( $this, 'wc_views_dedicated_archivetemplate_loader' ),50);
		
		//WooCommerce Views breadcrumb handler
		add_action('wp',array(&$this,'wc_views_remove_breadcrumb_from_template'));
		
		//Add Layouts rendering support to products template
		add_action('wp_loaded',array(&$this,'wc_views_add_render_view_template'));
		add_action('admin_enqueue_scripts', array($this, 'remove_template_warning_if_layoutset'),20);

		//Put the WooCommerce Views submenu just above Views settings		
		add_filter( 'custom_menu_order', array(&$this,'assign_proper_submenu_order_wcviews'),30 );

		//Layouts support	
		add_filter('get_layout_id_for_render',array(&$this,'use_layouts_shop_if_assigned'), 20,2);
		add_action('wp',array(&$this,'wc_views_check_if_anyproductarchive_has_layout'));
		
		//Fix WooCommerce 2.3.4 - Menu voices take a different name when using with Layouts plugin
		add_action('wp',array($this, 'wcviews_remove_filter_for_wc_endpoint_title'),777);
		
		//Set values for WooCommerce Views functions for conditional evaluation		
		$this->wcviews_functions =		
		array(
				'woo_product_on_sale',
				'woo_product_in_stock',				
				'wpv_woo_single_products_rating_func',
				'wpv_woo_list_attributes_func',
				'wpv_woo_show_upsell_func',
				'wpv_woo_products_rating_on_listing_func',
				'woo_has_product_subcategory',
				'woo_shop_display_is_categories',
				'wpv_woo_product_belongs_to_this_order'
			  );
		
		//WooCommerce Views shortcodes associated post types
		//Shortcodes can only be added on the edit section of these post types
		//To prevent misuse of these shortcodes in other places.
		
		$this->wcviews_associated_posttypes =
		array(
				'dd_layouts',
				'view',
				'view-template'
		);	

		// Ensure cart contents are updated when products are added to the cart via AJAX (place the following in functions.php)
		// This hooked is used by WooCommerce Views cart count shortcode.
		add_filter( 'woocommerce_add_to_cart_fragments', array(&$this,'woocommerce_views_add_to_cart_fragment' ),10,1);
		
		//Catch situations where are doing a Views loop
		add_action( 'wpv-before-display-post', array($this,'wcviews_before_display_post'), 99, 2 );
		add_action( 'wpv-after-display-post', array($this,'wcviews_after_display_post'), 99, 2 );
		
		//Support for add to cart AJAX in product listing page with quantity fields
		add_action( 'wp_footer', array($this,'wcviews_custom_loop_addtocart_ajax_with_quantity' ));
		
		//Do other things in very late init
		add_action('init',array(&$this,'wcviews_do_other_things_in_init'),999);
		
		//Set to false, enabled texturize
		$this->wcviews_disabled_texturize =false;
		
		//Set to false, by default we assume default WC body class is not loaded yet
		$this->wcviews_woocommerce_default_class_loaded = false;
		
		//Set to false, we are not yet executed any shortcode
		$this->wcviews_shortcode_executed = false;
		
		//Set Toolset packager slug for WooCommerce Views
		$this->wcviews_toolset_packager_slug ='woocommerce-views';
		
		//Set WooCommerce core settings slug
		$this->wcviews_wc_core_slug ='woocommerce-settings';
		
		//Remove 'woocommerce' class from the body tag for pages that does involved WooCommerce at all.
		add_action('get_footer',array($this,'wcviews_remove_woocommerce_class' ),999);
		
		//Added compatibility of WooCommerce Views settings with Toolset packager.
		add_filter( 'wpv_filter_view_extra_fields_for_import_export',array($this,'wcviews_embedded_support_basic' ),10,1);
		add_action( 'installer_ep_plugins_import_complete',array($this,'wcviews_toolset_packager_import' ),10,1);		
		
		//WooCommerce Views 2.5.9+ Support for release notes link
		add_filter( 'plugin_row_meta', array($this, 'wcviews_plugin_plugin_row_meta'), 10, 4 );
		
		//WooCommerce Views 2.6.2+ Append hook output of 'woocommerce_before_single_product_summary' before main image
		add_filter( 'wcviews_third_party_hooks_api_image', array($this, 'wcviews_woocommerce_before_single_product_summary'), 10, 3 );
		
		//WooCommerce Views 2.6.2+ Append hook output of 'woocommerce_before_shop_loop_item' before main image inside a customized WordPress product listing archives
		//Example for WooCommerce shop page, product categories. This is useful to integrate output of third party plugins using this hook
		add_filter( 'wcviews_third_party_hooks_api_image', array($this, 'wcviews_woocommerce_before_shop_loop_item'), 10, 3 );
		
		//WooCommerce Views 2.6.2+ Implement stock inventory control on product listing pages for add to cart button with quantity selector.
		add_filter( 'woocommerce_quantity_input_args', array($this, 'wcviews_woocommerce_quantity_input_args' ), 10, 2 );
		
		//WooCommerce Views 2.6.6+ Added GUI for export/import in the Toolset shared import/export screen
		add_filter( 'toolset_filter_register_export_import_section', array( $this, 'wcviews_register_export_import_section' ) ,65 );
		add_action( 'wp_loaded', array( $this, 'wcviews_export_handler' ) );
		add_action( 'wp_loaded', array( $this, 'wcviews_import_handler' ) );
		add_action( 'admin_notices', array( $this, 'wcviews_import_notices_errors' ) );
		add_action( 'admin_notices', array( $this, 'wcviews_import_messages' ) );
		
		//Additional WooCommerce 3.0 + compatibility
		add_action( 'wp_enqueue_scripts', array( $this, 'wcviews_load_new_wcthree_scripts' ), 90 );

		//WooCommerce Views 2.7.1 - WooCommerce default sort functionality compatibility.
		add_action( 'wpv_action_apply_archive_query_settings', array( $this, 'wcviews_analyzed_sorting' ), 1, 3 );
		
		//WooCommerce Views 2.7.2 - WCV filter fields usability improvement at backend
		add_action( 'add_meta_boxes', array( $this, 'wcviews_filter_fields_improvement_metabox' ), 11 );
		
		//WooCommerce Views 2.7.2 - dynamically add custom star class directly to shortcode output using a filter
		add_filter( 'woocommerce_product_get_rating_html', array( $this, 'wcviews_product_get_rating_html' ), 10, 2 );
		
		//WooCommerce Views 2.7.2 - Layouts archive overwrites default WooCommerce taxonomy queries that handles a lot of things.
		//Restore this.
		add_action( 'wpv_action_apply_archive_query_settings', array( $this, 'wcviews_restore_wc_taxonomy_queries' ), 99, 3 );
		add_filter( 'wpv_filters_add_filter', array( $this, 'wcviews_include_product_visibility_filters' ), 999, 2 );
		
		//woocommerceviews-115: Hooked to 'woocommerce_register_post_type_shop_order' filter to make WooCommerce post type queryable by Views.
		add_filter( 'woocommerce_register_post_type_shop_order', array( $this, 'wcviews_make_shop_order_views_queryable' ), 10, 1 );
		add_filter( 'toolset_filter_edit_post_link_publish_statuses_allowed', array( $this, 'toolset_edit_post_link_publish_statuses_allowed' ), 10, 2 );
		add_filter( 'post_password_required', array( $this, 'wcviews_allow_frontend_visibility_order_post_type' ), 10, 2 );
		add_filter( 'woocommerce_register_shop_order_post_statuses', array( $this, 'wcviews_allow_frontend_visibility_order_post_type_statuses' ), 10, 1 );
		add_filter( 'wpv_custom_inner_shortcodes', array( $this, 'wcviews_add_ordered_products_ids_shortcode'), 10, 1 );
		add_action( 'pre_get_posts', array( $this, 'wcviews_enable_preview_order_post_type_layouts' ) );
		
		//woocommerceviews-124: Make WooCommerce Views filter field group uneditable
		add_filter( 'clean_url', array( $this, 'wcviews_add_readonly_filter_wcvfieldgroup'), 10, 3 );
		add_action( 'current_screen', array( $this, 'wcviews_remove_wcv_filtergroup_edit_cap_filter_func') );
		
		//woocommerceviews-32: 
		//Usability - dont display loop related shortcodes on Layouts when set to display single products
		add_filter( 'wcviews_filter_group_data_shortcodes', array( $this, 'wcviews_filter_loop_related_shortcodes_layouts'), 10, 2 );
		
		//Usability - dont display single-product only shortcodes on Views edit because Views is meant to display listings.
		add_filter( 'wcviews_filter_group_data_shortcodes', array( $this, 'wcviews_filter_nonloop_related_shortcodes_views'), 11, 2 );
		add_filter( 'wcviews_filter_shortcode_usage', array( $this, 'wcviews_filter_nonloop_related_shortcodes_views'), 11, 1 );
		
		//Usability - only display WooCommerce order related shortcodes in Layouts that displays Orders
		add_filter( 'wcviews_filter_group_data_shortcodes', array( $this, 'wcviews_filter_nonorder_related_shortcodes_layouts'), 12, 2 );

		//Usability - dont display loop related shortcodes on Content Templates when set to display single products
		add_filter( 'wcviews_filter_group_data_shortcodes', array( $this, 'wcviews_filter_loop_related_shortcodes_ct'), 13, 2 );
		
		//Usability - dont display non-order related shortcodes on Views edit when its set to display only WooCommerce orders
		add_filter( 'wcviews_filter_group_data_shortcodes', array( $this, 'wcviews_filter_nonorder_related_shortcodes_views'), 14, 2 );
		add_filter( 'wcviews_filter_shortcode_usage', array( $this, 'wcviews_filter_nonorder_related_shortcodes_views'), 14, 1 );
		
		//Usability - dont display non-loop related shortcodes on Views WordPress archives edit that displays products
		add_filter( 'wcviews_filter_group_data_shortcodes', array( $this, 'wcviews_filter_nonloop_related_shortcodes_wparchives'), 15, 2 );
		add_filter( 'wcviews_filter_shortcode_usage', array( $this, 'wcviews_filter_nonloop_related_shortcodes_wparchives'), 15, 1 );
		
		//Usability - dont display non-order related shortcodes on Views WordPress archives edit that displays orders
		add_filter( 'wcviews_filter_group_data_shortcodes', array( $this, 'wcviews_filter_nonorder_related_shortcodes_wparchives'), 16, 2 );
		add_filter( 'wcviews_filter_shortcode_usage', array( $this, 'wcviews_filter_nonorder_related_shortcodes_wparchives'), 16, 1 );
		
		//Usability - dont display WCV shortcodes on Views edit that cannot be used in product category loops
		add_filter( 'wcviews_filter_group_data_shortcodes', array( $this, 'wcviews_filter_nonprodcat_related_shortcodes'), 17, 2 );
		add_filter( 'wcviews_filter_shortcode_usage', array( $this, 'wcviews_filter_nonprodcat_related_shortcodes'), 17, 1 );
		
		//Properties
		$this->wcviews_import_errors 		= null;
		$this->wcviews_import_messages		= null;
		$this->wcviews_the_template_name	= 'WooCommerce Views plugin default single product template';
	}

	/**
	 * Main init.
	 *
	 * @access public
	 * @return void
	 */
	
	public function wcviews_init(){
		
		add_action('wp_loaded',array(&$this,'run_wp_loaded_check_required_plugins'));			
		$using_default_wc_template=$this->wc_views_check_if_using_woocommerce_default_template();
		
		if (!($using_default_wc_template)) {
			//If is not using WooCommerce Default Templates, it assumes the user wants to override the default templates.
			//Therefore for this to work, the user should also have Content Template assigned to products.
			 
			//Let's checked..
			$has_content_template_set=$this->check_if_content_template_has_assigned_to_products_wcviews();
			if (!($has_content_template_set)) {
				//Oops, none, let's show a notice to the user.			
				add_action('admin_notices', array(&$this,'no_content_template_set_error'));
			}
		}
		
		//add_filter('wpv_add_media_buttons', 'add_media_button');	
		add_action('admin_enqueue_scripts', array(&$this,'additional_css_js'));
		
		//Remove this hook so users can customize add to cart messages in main shop pages, etc.
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_show_messages', 10 );
		
		//Hook on reducing stock level in WooCommerce
		add_action('woocommerce_reduce_order_stock',array(&$this,'wcviews_reduce_stock_level_field'),10,1);
	
		if ( true === $this->wc_views_two_point_seven_above() ) {
			//WooCommerce 2.7.0 +
			/** WooCommerce 2.7.0 compatibility - using new filter name: woocommerce_product_get_gallery_image_ids */
			add_filter('woocommerce_product_get_gallery_image_ids',array( $this,'remove_gallery_on_main_image_at_listings'), 20, 2 );			
		} else {
			//Backward compatibility
			//By default, don't include gallery images in image shortcode at listings
			add_filter('woocommerce_product_gallery_attachment_ids',array( $this,'remove_gallery_on_main_image_at_listings'), 20, 2 );
		}
		
		/** Add default WooCommerce Views conditional functions to Views */
		$this->wc_views_add_to_views_conditional_evaluation();

	}

	/**
	 * Layouts uses Views function 'render_view_template' to render Content Template and not the native 'the_content()'
	 * Let's add this automatically to Theme support for Content Templates
	 * So any hooks and filters will be executed.
	 *
	 * @access public
	 * @return void
	 */
	
	public function wc_views_add_render_view_template() {		
		
		if( defined('WPDDL_VERSION') ) {
			
			//Layouts plugin activated
			//Let's check first if all dependencies are set
			$missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();
		
			//Define default functions
			$wcv_views_default_functions='render_view_template';
		
			if (empty($missing_required_plugin)) {
				
				//All required dependencies are set
				//Get Views setting
				global $wpddlayout;
				
				if (is_object($wpddlayout)) {
					
					//Access Layouts post type object
					$layout_posttype_object=$wpddlayout->post_types_manager;
					
					if (method_exists($layout_posttype_object,'get_layout_to_type_object')) {					
					
						//Check if product post type has been assigned with Layouts					
						$result=$layout_posttype_object->get_layout_to_type_object( 'product' );
					
						if ($result) {
							
							//Product has now layouts assigned
							//Get Views setting	and let's ensure that the render_view_template is added to theme functions					
							$view_settings=get_option('wpv_options');
							
							//Add render_view_template to theme function

							if (isset($view_settings['wpv-theme-function'])) {
								//Set, check value
								$val=$view_settings['wpv-theme-function'];
								if ('render_view_template' != $val) {
									
									//Not updated
									$view_settings['wpv-theme-function']=$wcv_views_default_functions;
									
									//Update back
									update_option( 'wpv_options', $view_settings );									
								}
							} else {
								//Not set, set
								$view_settings['wpv-theme-function']=$wcv_views_default_functions;
								
								//Update back
								update_option( 'wpv_options', $view_settings );
							}						
						}
					}
					
				}

			}		
		} else {
			
			//Layouts plugin not activated
			//Get Views settings
			$view_settings=get_option('wpv_options');
				
			//Remove render_view_template to theme function
			
			if (isset($view_settings['wpv-theme-function'])) {
				//Set, check value
				$val=$view_settings['wpv-theme-function'];
				if ('render_view_template' == $val) {
					
					//Let's roll back
					$view_settings['wpv-theme-function']='';
													
						//Update back
					update_option( 'wpv_options', $view_settings );
				}
				
			} 		
		}
		
	}
	/**
	 * Removes default breadcrumb from WooCommerce Template hooks
	 * So it can be overriden with WooCommerce Views breadcrumb shortcode.
	 * Hook is removed ONLY if using non-default WooCommerce Templates.
	 * This will customize the breadcrumbs on single product pages only.
	 * Breadcrumbs on WooCommerce shop listing for example is not affected.
	 * @access public
	 * @return void
	 */	
	
	public function wc_views_remove_breadcrumb_from_template() {
		
		$using_default_wc_template			= $this->wc_views_check_if_using_woocommerce_default_template();
		$using_default_wc_archive_template 	= $this->wc_views_check_if_using_woocommerce_default_archive_template();
		$check_wp_views_archive_assigned	= $this->check_if_wp_archive_has_already_been_assigned_wc_views();
		
		global $woocommerce;
		if (is_object($woocommerce)) {
			if (is_product()) {
				//Remove default WooCommerce breadcrumb so it will replaceable with WooCommerce Views breadcrumb shortcode
				//Remove only if not using default WooCommerce Templates
				if (!($using_default_wc_template)) {
					remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );					
				}
			} 			
		}		
		
	}
	/**
	 * Adds default WC Views functions to be used for WP Views plugin wpv-if statements:
	 * woo_product_on_sale() 
	 * woo_product_in_stock()
	 * wpv_woo_single_products_rating_func()
	 * This will automatically these functions to Views -> Settings -> Functions inside conditional evaluations
	 *
	 * @access public
	 * @return void
	 */	
	
	public function wc_views_add_to_views_conditional_evaluation() {
		
		//Let's check first if all dependencies are set
		$missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();
		
		//Define default functions
		$wcv_views_default_functions=$this->wcviews_functions;
		
		if (empty($missing_required_plugin)) {
			//All required dependencies are set
			//Get Views setting
			$views_setting= get_option('wpv_options');
			if ($views_setting) {
			   //Views settings exists
			   //Check if conditional functions are set by user previously
			   if (isset($views_setting['wpv_custom_conditional_functions'])) {
			   	  
			   	  //User has already set this, retrieved existing setting
			   	  $existing_conditional_functions_setting=$views_setting['wpv_custom_conditional_functions'];
	
			   	  if (is_array($existing_conditional_functions_setting)) {
			   	  	//$existing_conditional_functions_setting should be an array
			   	  	//Check if WCV Views functions are already there
			   	  	$all_set=$this->wc_views_all_conditional_functions_set($wcv_views_default_functions,$existing_conditional_functions_setting);
			   	  	
			   	  	if ($all_set === TRUE) {
			   	  		//Already there, do nothing...
			   	  	} else {
			   	  		//Not yet, let's add..loop through the default functions.
			   	  		foreach ($all_set as $k=>$v) {
			   	  			$views_setting['wpv_custom_conditional_functions'][]=$v;
			   	  		}
			   	  				   	  		
			   	  		//Let's update back
			   	  		update_option( 'wpv_options',$views_setting );
			   	  	} 	
			   	  	
			   	  }
			   } else {
			   	 	 //Not yet set
			   		foreach ($wcv_views_default_functions as $k=>$v) {
			   			$views_setting['wpv_custom_conditional_functions'][]=$v;
			   		}
	
			   		//Let's update back
			   		update_option( 'wpv_options',$views_setting );
			   }
			}		
		}
	}

	/**
	 * Aux method to check if all functions for conditional evaluations are set
	 *
	 * @access public
	 * @param  array $wcv_views_default_functions
	 * @param  array $existing_conditional_functions_setting
	 * @return mixed
	 */
	
	public function wc_views_all_conditional_functions_set($wcv_views_default_functions,$existing_conditional_functions_setting) {

		//Let's looped through the $wcv_views_default_functions array and check if they are all in $existing_conditional_functions_setting
		$not_set=array();
		foreach ($wcv_views_default_functions as $k=>$v) {
			
			if (!(in_array($v,$existing_conditional_functions_setting))) {
				//Not in array
				$not_set[]=$v;
			}			
		}
		
		if (empty($not_set)) {
			//All in array, return TRUE
			return TRUE;
		} else {
			//Not all is there, return FALSE
			return $not_set;
		}
		
	}
	
	/**
	 * Adds default WooCommerce div classes.
	 * This is configurable in 
	 * Woocommerce Views Settings -> WooCommerce Styling -> Add a container DIV around the post body for WooCommerce styling. 
	 *
	 * @access public
	 * @return void
	 */	
	
	public function wc_views_woocommerce_wrapper_override_loaded() {
		
		global $woocommerce;
		
		if (is_object($woocommerce)) {
			
			//WooCommerce plugin activated
			$is_product=is_product();
			$settings_wrapper_woocommerce= get_option('woocommerce_views_wrap_the_content');
			
			if (!($settings_wrapper_woocommerce)) {
			
				//Not yet set, use "yes" as default
				$settings_wrapper_woocommerce='yes';
			
			}			
			
			//Fall back to Content Template if Layouts plugin is activated but no Layouts has been assigned to WooCommerce Products
			add_filter('get_layout_content_for_render', array(&$this,'wc_views_fallback_to_ct'), 10,4 );

			if (($settings_wrapper_woocommerce=='yes') && ($is_product)) {
				
				/** Yes to wrapping and this is a product page */
				
				//User wants to wrap the DIV with WooCommerce classes, add the filter
				add_filter('wpv_filter_content_template_output', array(&$this,'wc_views_prefix_add_wrapper'), 10, 4);

				//Layouts support
				add_filter('get_layout_content_for_render', array(&$this,'wc_views_prefix_add_wrapper_layouts'), 20,4 );
			}
		}	
	}
	
	/**
	 * Filter for adding WooCommerce Views classes wrapping around a div container outputted by Layouts
	 *
	 * @access public
	 * @return string
	 */
	
	public function wc_views_prefix_add_wrapper_layouts( $content, $object_passed, $layout, $args ) {

		global $wpddlayout,$post,$layout_disable_texturize;
		
		if (is_object($wpddlayout)) {
				
			//Access Layouts post type object
			$layout_posttype_object=$wpddlayout->post_types_manager;
				
			if (method_exists($layout_posttype_object,'get_layout_to_type_object')) {
					
				//Check if product post type has been assigned with Layouts
				$result=$layout_posttype_object->get_layout_to_type_object( 'product' );
					
				if ($result) {
					//WooCommmerce product post type has assigned Layouts
					
					if (isset($post->ID)) {
						$post_id=$post->ID;
						$post_classes = get_post_class( 'clearfix', $post_id );
						global $post_classes_wc_added;
						if (!($post_classes_wc_added)) {
							$post_classes_wc_added=TRUE;
							$woocommerce_before_single_product = $this->wcviews_execute_woocommerce_before_single_product();
							$content = '<div class="' . implode( ' ', $post_classes ) . '">'. $content . '</div>';
						}						
					}					
					
				}
			}
		}
		if (isset($layout_disable_texturize)) {
			if ($layout_disable_texturize) {
				//Add back
				add_filter( 'the_content', 'wptexturize'        );
				$layout_disable_texturize=false;
			}
		}
		
		return $content;
	
	}
	
	/**
	 * Method for falling back to use Content Templates if Layouts plugin is activated but no Layouts has been assigned to products
	 * @access public
	 * @return string
	 */
	
	public function wc_views_fallback_to_ct( $content, $object_passed, $layout, $args ) {
	
		global $wpddlayout,$post;
		$is_product=is_product();
		$settings_wrapper_woocommerce= get_option('woocommerce_views_wrap_the_content');
		
		if ((is_object($wpddlayout)) && ($is_product)) {
			
			//Layouts plugin activated and this is product
			//Access Layouts post type object
			$layout_posttype_object=$wpddlayout->post_types_manager;
	
			if (method_exists($layout_posttype_object,'get_layout_to_type_object')) {
					
				//Check if product post type has been assigned with Layouts
				$result=$layout_posttype_object->get_layout_to_type_object( 'product' );
					
				if ($result) {
					//Layouts assigned, do nothing...						
				} else {
					//Products has not been assigned with Layouts
					//Let's checked if a Content Template has been assigned instead.
					$has_ct=$this->check_if_content_template_has_assigned_to_products_wcviews();
					if ($has_ct) {
						//Has content template assigned						
						$content='';						
						$content_template_options=get_option('wpv_options');
						
						if (isset($content_template_options)) {
							if (!(empty($content_template_options))) {
								if (isset($content_template_options['views_template_for_product'])) {
									//Product content template is set
									//Check if its not null
									$null_check=$content_template_options['views_template_for_product'];
									$null_check=intval($null_check);
									if ($null_check > 0) {
										//Sensible id for CT, use it
										if (is_object($post)) {
											$content = render_view_template($null_check, $post );
											
											if ('yes' == $settings_wrapper_woocommerce) {
												//WooCommerce Classes wrapping
												if (isset($post->ID)) {
													$post_id=$post->ID;
													$post_classes = get_post_class( 'clearfix', $post_id );
													global $post_classes_wc_added;
													if (!($post_classes_wc_added)) {
														$post_classes_wc_added=TRUE;
														$woocommerce_before_single_product = $this->wcviews_execute_woocommerce_before_single_product();
														$content = '<div class="' . implode( ' ', $post_classes ) . '">'. $content . '</div>';
													}
												}																
											}

										}
									}
								}
							}
						}					
					}
				}
			}
		}
	
		return $content;
	
	}	
	/**
	 * Force Template override.
	 * If using WooCommerce default templates on a product page,
	 * no Content Template should be applied to posts if using WooCommerce default templates.
	 *
	 * @access public
	 * @return void
	 */
	
	public function wc_views_override_template_on_loaded() {
		
		global $woocommerce;
		if (is_object($woocommerce)) {
			//WooCommerce plugin activated
			$is_product=is_product();
			$check_if_currently_using_wc_defaults=$this->wc_views_check_if_using_woocommerce_default_template();
			if (($check_if_currently_using_wc_defaults) && ($is_product)) {
				//No content template should be applied to single products if using WooCommerce default templates
				add_filter('wpv_filter_wpv_override_content_template_for_single', array(&$this,'wc_views_override_any_content_templates_default_wc'), 10, 2);
			}	
		}
	}
	
	/**
	 * Helper method: Make sure single-product.php is fully under WooCommerce control.
	 * Adds a filter hooked to template_include
	 *
	 * @access public
	 * @return void
	 */
		
	public function wc_views_dedicated_template_loader() {
		
		add_filter( 'template_include',array( $this, 'wc_views_template_loader' ) );
			
	}
	
	/**
	 * Helper method: Adds WooCommerce class to body class.
	 * Hooked to body_class filter
	 * @access public
	 * @param  array $classes
	 * @return array
	 */	
	
	public function wc_views_add_woocommerce_to_body_class($classes) {
		
		//Check if WooCommerce is activated
		if (class_exists('woocommerce')){
	
			//Check woocommerce class exist
			if (!(in_array('woocommerce',$classes))) {
					
				//Does not exist
				$classes[] = 'woocommerce';
								
			} else {
				//Does exist
				$this->wcviews_woocommerce_default_class_loaded =TRUE;				
			}
				
		}
		return $classes;	
		
	}
	
	/**
	 * Helper method: Filter function for $template.
	 * Ensures it returns single-product.php from the WooCommerce plugin templates.
	 * Hooked to template_include filter.
	 * @access public
	 * @param  string $template
	 * @return string
	 */	
	
	public function wc_views_template_loader($template) {
		
		global $woocommerce;	
		
		$is_single= is_single();
		$get_post_type_var=get_post_type();
		
		if ( is_single() && get_post_type() == 'product' ) {	
			
			//Verify that the loaded page is not any edit product page return by Dokan plugin
			$is_dokan_edit_product=$this->wcviews_is_dokan_edit_product();
			
			if (!($is_dokan_edit_product)) {
				$file='single-product.php';
				$template = $woocommerce->plugin_path() . '/templates/' . $file;
			}
		
		}
		
		return $template;
	}
	
	/**
	 * Check required plugins for WooCommerce Views.
	 * Hooked to wp_loaded.
	 * @access public
	 * @return void
	 */
		
	public function run_wp_loaded_check_required_plugins() {
		
		$this->run_woocommerce_views_required_plugins();
		
	}
	
	/**
	 * Check for missing plugins when WooCommerce Views is activated.
	 * @access public
	 * @return array
	 */
		
	public function check_missing_plugins_for_woocommerce_views() {
		
		$missing_required_plugin=array();
		
		//Check plugin requirements
		if (!class_exists('woocommerce')){
		
			//WooCommerce plugin is not activated
			$missing_required_plugin[]='woocommerce';
		}
		if (!(defined('WPV_VERSION'))){
		
			//Views plugin is not activated
			$missing_required_plugin[]='views';
		}
		if (!(defined('WPCF_VERSION'))){
		
			//Types plugin is not activated
			$missing_required_plugin[]='types';
		}
	
		return $missing_required_plugin;
		
	}
	
	/**
	 * Output missing plugins notices.
	 * Hooked to admin_notices.
	 * @access public
	 * @return void
	 */	
	
	public function missing_plugins_wcviews_check() {
		
	 global $custom_missing_required_plugin;
	 $embedded_scenario_check= $this->wcviews_check_installer_origin();
	 if (!($embedded_scenario_check)) {	
	 ?>
	<div class="message wcviews_plugin_error error">
		<p><?php _e('The following plugins are required for WooCommerce Views to run properly:','woocommerce_views');?></p>
		<ol>
			<?php 
				  foreach ($custom_missing_required_plugin as $k=>$v) {
			?>
			<li>
			<?php
			if ($v=='views') {
			?>
			<a target="_blank"
				href="https://toolset.com/home/toolset-components/#views">Views</a>
			 <?php
			} elseif ($v=='types') {
			?>
		   <a target="_blank" href="http://wordpress.org/plugins/types/">Types</a>
			<?php
			} elseif ($v=='woocommerce') {
			?>
			<a target="_blank" href="http://wordpress.org/plugins/woocommerce/">WooCommerce</a>
			 <?php
			}
			?>
			</li>
			<?php
			 }
			 ?>
			 </ol>
	</div>
<?php						
	 }			
	}

	/**
	 * Output missing WooCommerce pages.
	 * Hooked to admin_notices
	 * @access public
	 * @return void
	 */
	
	public function missing_woocommerce_pages_wc_views() {
	
		?>
<div class="message wcviews_plugin_error error">
	<p><?php _e('Please install WooCommerce Pages before you can fully start with WooCommerce Views.','woocommerce_views');?></p>
</div>
<?php						
				
	}
	
	/**
	 * Check if a Content Template is assigned to WooCommerce products.
	 * Returns TRUE if as Content Template has been assigned, otherwise FALSE.
	 * @access public
	 * @return boolean
	 */	
	
	public function check_if_content_template_has_assigned_to_products_wcviews( $ret = '') {
		if ( empty( $ret ) ) {
			$ret	= 'boolean';
		}
		//Check if a content template has been assigned to a product	
		$content_template_options=get_option('wpv_options');	
		
		if (isset($content_template_options)) {
			if (!(empty($content_template_options))) {
				if (isset($content_template_options['views_template_for_product'])) {
					//Product content template is set
					//Check if its not null
					$null_check = $content_template_options['views_template_for_product'];
					$null_check = intval( $null_check );
					if ($null_check > 0) {
						if ( 'boolean' === $ret ) {
							return TRUE;
						} else {
							return $null_check;
						}
					} else {
					   //Template exist but not assigned
					   return FALSE;	
					}
				} else {
					
					return FALSE;
				}
		
		
			} else {
				
			   return FALSE;	
			}
		
		} else {
	
		return FALSE;
			
		}
	}
	
	/**
	 * Check if a Views WP Archive is assigned to WooCommerce products archive loop.
	 * Returns TRUE if an WP archive has been assigned, otherwise FALSE.
	 * @access public
	 * @return boolean
	 */
		
	public function check_if_wp_archive_has_already_been_assigned_wc_views() {
		
		$content_template_options = get_option('wpv_options');
		if (isset($content_template_options)) {
			if (!(empty($content_template_options))) {
				if (isset($content_template_options['view_cpt_product'])) {
					//Archive for shop page already defined
					return TRUE;
				} else {
		
					return FALSE;
				}
		
		
			} else {
					
				return FALSE;
			}
		
		} else {
		
			return FALSE;
		
		}	
		
	}
	
	/**
	 * Run plugin dependency checks as required by WooCommerce Views.
	 * Returns admin notices if missing plugins exists.
	 * @access public
	 * @return mixed
	 */	
	
	public function run_woocommerce_views_required_plugins() {
		global $custom_missing_required_plugin;
		$custom_missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();
		
		//Check if WooCommerce pages are successfully installed
		$wc_needs_pages=get_option('_wc_needs_pages');    
		
		//Check if there is a missing plugin required
		if (!(empty($custom_missing_required_plugin))) {
		
			//Some required plugin is missing, pass
			add_action('admin_notices',array(&$this,'missing_plugins_wcviews_check'));
	
			return false;
		} elseif ((empty($custom_missing_required_plugin)) && ($wc_needs_pages)) {
			//Plugins are secured, but WooCommerce pages are not installed
			 
			add_action('admin_notices',array(&$this,'missing_woocommerce_pages_wc_views'));
			return false;
		} 
	}
	
	/**
	 * Enqueue script on WordPress front end.
	 * This method includes enqueing needed JS and CSS.
	 * Hooked to wp_enqueue_scripts
	 * @access public
	 * @return void
	 */	
	
	public function woocommerce_views_scripts_method() {
		global $post,$woocommerce;
		if (is_object($woocommerce)) {
			//WooCommerce plugin activated
			$lightbox_en_woocommerce= get_option( 'woocommerce_enable_lightbox' ) == 'yes' ? true : false;
			$suffix_woocommerce	= defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$woocommerce_plugin_url=$woocommerce->plugin_url();
			$woocommerce_version=$woocommerce->version;
		
			//Enqueue prettyPhoto   
			if ($lightbox_en_woocommerce)  {  
				wp_enqueue_script( 'prettyPhoto', $woocommerce_plugin_url . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix_woocommerce . '.js', array( 'jquery' ), '3.1.5', true );
				wp_enqueue_script( 'prettyPhoto-init', $woocommerce_plugin_url . '/assets/js/prettyPhoto/jquery.prettyPhoto.init' . $suffix_woocommerce . '.js', array( 'jquery' ), $woocommerce->version, true );
				wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce_plugin_url . '/assets/css/prettyPhoto.css' );   
			}
		}
		// Enqueue frontend assets, load only if woocommerce plugin is set
		// TODO Enqueue only when showing products on the page
		if ((is_object($woocommerce)) && (defined('WC_VIEWS_VERSION'))) {
			//WooCommerce plugin and WooCommerce Views activated
			$wc_views_version=WC_VIEWS_VERSION;
			if (!(empty($wc_views_version))) { 
				
				/**
				 * Some themes de-queue default WooCommerce CSS and loads their own WooCommerce CSS.
				 * Let's give them a filter so they can set their own WooCommerce CSS as the correct dependency instead of Default WC plugin CSS.
				 */

				$default_css = array( 'woocommerce-general' );
				//woocommerceviews-104
				if ( false === $this->wcviews_wccorecss_registered_enqueued() ) {
					//General CSS is dequeued/un-registered
					//Removed from dependency
					$default_css	= array();
				}
				if ( 'twentyseventeen' === get_template() ) {
					$default_css = array( 'woocommerce-twenty-seventeen' );
				}

				$woocommerce_css_loaded = apply_filters( 'wcviews_woocommmerce_css_override' , $default_css);

				wp_enqueue_style('woocommerce_views_onsale_badge', plugins_url('res/css/wcviews-onsalebadge.css',__FILE__),$woocommerce_css_loaded,$wc_views_version);
				wp_enqueue_script('woocommerce_views_frontend_js', plugins_url('res/js/wcviews-frontend.js',__FILE__),array('jquery'),$wc_views_version);
			}
		}
	}
	
	/**
	 * Check if WooCommerce general css is enqueued or registered
	 * @since 2.7.1
	 */
	public function wcviews_wccorecss_registered_enqueued() {
		$registered_enqueued	= false;
		if ( ( wp_script_is( 'woocommerce-general', 'enqueued' ) ) || ( wp_script_is( 'woocommerce-general', 'registered' ) ) ) {
			$registered_enqueued	= true;
			
		}	
		return $registered_enqueued;
	}
	/**
	 * Enqueue script on WordPress backend.
	 * This method includes enqueing needed JS and CSS.
	 * Hooked to admin_enqueue_scripts.
	 *
	 * @since unknown
	 */
	public function woocommerce_views_scripts_method_backend() {
		
		global $woocommerce;
		
		$screen_output_wc_views= get_current_screen();
		$screen_output_id= $screen_output_wc_views->id;

		//Get backward/forward compatible screen ID
		$canonical_screen_id = $this->wcviews_unified_current_screen();
		
		//Get WooCommerce activated template path ->Default with backward compatibility
		$single_product_wc_template_path='';
		$archive_product_wc_template_path='';
		if (is_object($woocommerce)){
			
			if (function_exists('WC')) {
				
				if (method_exists('woocommerce','plugin_path')) {
					$wc_plugin_path=WC()->plugin_path();
					$single_product_wc_template_path = WC()->plugin_path() . '/templates/single-product.php';
				} else {
					
					$single_product_wc_template_path=$woocommerce->plugin_path() . '/templates/single-product.php';
				}
				
			} else {				
				$single_product_wc_template_path=$woocommerce->plugin_path() . '/templates/single-product.php';
			}
			
			if (function_exists('WC')) {
			
				if (method_exists('woocommerce','plugin_path')) {
					$wc_plugin_path=WC()->plugin_path();
					$archive_product_wc_template_path = WC()->plugin_path() . '/templates/archive-product.php';
				} else {
					$archive_product_wc_template_path=$woocommerce->plugin_path() . '/templates/archive-product.php';
				}
			} else {
				$archive_product_wc_template_path=$woocommerce->plugin_path() . '/templates/archive-product.php';
			}
		}

		//Show path and hide path translatable text
		$show_path=__('Show template','woocommerce_views');
		$hide_path=__('Hide template','woocommerce_views');
		
		// Used for wizard, check if custom fields updating are done
		// This seems deprecated and never used
		$check_if_done_cf_updating_wcviews=get_option('woocommerce_last_run_update');
		if ($check_if_done_cf_updating_wcviews) {
		   $cf_field_status_wizard="true";       
		} else {
		   $cf_field_status_wizard="false";
		}
		
		$admin_url_wcviews=admin_url().'admin.php?page=wpv_wc_views';
		
		/*Handle for enabling and disabling of next button in wizard*/
		//Step1. Saving PHP templates
		$check_if_template_already_defined_wizard=get_option('woocommerce_views_theme_template_file');
		if ($check_if_template_already_defined_wizard) {
		   $localize_php_template_already_defined="true";
		} else {
		   $localize_php_template_already_defined="false";
		} 
		
		/*Handle for content templates next button enabling in wizard*/
		
		$check_if_content_template_assigned_wizard=$this->check_if_content_template_has_assigned_to_products_wcviews();
		if ($check_if_content_template_assigned_wizard) {
		 	$localize_contenttemplate_already_defined="true";
		} else {
		 	$localize_contenttemplate_already_defined="false";
		}
	   
	   /*Handle for WP archive next button enabling in wizard*/
		$check_if_wp_archive_has_already_been_assigned_wc_views_wizard=$this->check_if_wp_archive_has_already_been_assigned_wc_views();
		if ($check_if_wp_archive_has_already_been_assigned_wc_views_wizard) {
			$localize_wparchive_already_defined="true";
		} else {
			$localize_wparchive_already_defined="false";
		} 
		  
		if  ($canonical_screen_id == $screen_output_id) {
			//Enqueue only on WC Views admin screen	
			wp_enqueue_script('woocommerce_views_custom_script_backend', plugins_url( 'res/js/woocommerce_custom_js_backend.js', __FILE__ ), array( 'jquery' ));
			
			wp_localize_script('woocommerce_views_custom_script_backend', 'the_ajax_script_wc_views',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'wc_views_ajax_response_admin_nonce'=> wp_create_nonce('wc_views_ajax_response_admin'),
				'wc_views_ajax_response_modulesdownload_nonce'=> wp_create_nonce('wc_views_ajax_response_modulesdownload'),
				'wc_views_ajax_response_thumbnail_overlay_nonce'=> wp_create_nonce('wc_views_ajax_response_thumbnail_overlay'),
				'wc_views_ajax_response_content_template_post_updating_nonce'=> wp_create_nonce('wc_views_ajax_response_content_template_post_updating'),
				'wc_views_ajax_ajax_loader_gif' =>plugins_url( 'res/img/ajax-loader.gif', __FILE__),
				'wc_views_last_run_translatable_text' => __('Calculated Product fields were last updated: ','woocommerce_views'),
				'wc_views_wizard_php_template_already_defined' => $localize_php_template_already_defined,
				'wc_views_wizard_content_template_already_defined' => $localize_contenttemplate_already_defined,
				'wc_views_wizard_wp_archive_already_defined' => $localize_wparchive_already_defined,
				// This seems deprecated and never used
				'wc_views_cf_fields_update_check_wizard' =>$cf_field_status_wizard,
				'batchProductFields' => array(
					'ongoing' => __( 'ongoing', 'woocommerce-views' ),
					'error' => __( 'failed to complete, please reload and try again', 'woocommerce-views' ),
				),
				'wc_views_admin_screen_page_url' => $admin_url_wcviews,
				'wc_views_next_button_text_translatable' =>__('Next','woocommerce_views'),
				'wc_views_finish_wizard_text_translatable'=>__('Skip the setup wizard','woocommerce_views'),
				'wc_views_wc_default_single_product_template' =>$single_product_wc_template_path,
				'wc_views_wc_default_archive_product_template' =>$archive_product_wc_template_path,
				'wc_views_show_path_text' =>$show_path,
				'wc_views_hide_path_text' =>$hide_path
				 )
			);

			//Load common graphics (as of version 2.5.2)

			
			/** Since version 2.5.5
			 * Load Toolset Common Utility from Views plugin
			 */
			if (( defined( 'WPV_URL_EMBEDDED_TOOLSET' )) && ( defined ('WPV_PATH_EMBEDDED_TOOLSET'))) {
				$views_embedded_toolset_url=WPV_URL_EMBEDDED_TOOLSET;						
				//Generate path to notifications CSS
				$notification_css_path=WPV_PATH_EMBEDDED_TOOLSET.DIRECTORY_SEPARATOR.'toolset-common'.DIRECTORY_SEPARATOR.'utility'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'notifications.css';
				if (file_exists($notification_css_path)) {
					$notification_css_url=$views_embedded_toolset_url.'/toolset-common/utility/css/notifications.css';
					wp_register_style( 'wcviews-common-utility', $notification_css_url, array('wcviews-style', 'toolset-notifications-css'), WC_VIEWS_VERSION );
					wp_enqueue_style('wcviews-common-utility');
				} else {
					//Backward compatibility in case that file is removed
					$notification_css_url = plugins_url() . '/' . basename(dirname(__FILE__)) . '/res/css/notifications.css';
					wp_register_style( 'wcviews-common-utility', $notification_css_url, array('wcviews-style', 'toolset-notifications-css'), WC_VIEWS_VERSION );
					wp_enqueue_style('wcviews-common-utility');					
				}
			}
			
		}
		
		//Juan: Check the Views version so we load the dialogs.css and utils.js script files that were moved in 1.3.1 -> 1.4 release and will not be loaded by default in post.php and post-new.php pages
		$screen_output_base = $screen_output_wc_views->base;
		if( defined( 'WPV_VERSION' ) && defined( 'WPV_URL' ) && $screen_output_base == 'post') {
			if ( version_compare( WPV_VERSION, '1.4' ) < 0 ) {
				
				wp_deregister_script( 'toolset-colorbox' );
				wp_register_script( 'toolset-colorbox' , WPV_URL . '/res/js/redesign/lib/jquery.colorbox-min.js', array('jquery'), WPV_VERSION);
				wp_deregister_script( 'views-select2-script' );
				wp_register_script( 'views-select2-script' , WPV_URL . '/res/js/redesign/lib/select2/select2.min.js', array('jquery'), WPV_VERSION);
				wp_deregister_script( 'views-utils-script' );
				wp_register_script( 'views-utils-script' , WPV_URL . '/res/js/redesign/utils.js', array('jquery','toolset-colorbox', 'views-select2-script'), WPV_VERSION);
				if ( !wp_script_is( 'views-utils-script' ) ) {
					wp_enqueue_script( 'views-utils-script');
					$help_box_translations = array(
					'wpv_dont_show_it_again' => __("Got it! Don't show this message again", 'woocommerce_views'),
					'wpv_close' => __("Close", 'woocommerce_views')
					);
					wp_localize_script( 'views-utils-script', 'wpv_help_box_texts', $help_box_translations );
				}
				
				wp_deregister_style( 'views-dialogs-css' );
				wp_register_style( 'views-dialogs-css', WPV_URL . '/res/css/dialogs.css', array(), WPV_VERSION );
				if ( !wp_style_is( 'views-dialogs-css' ) ) {
					wp_enqueue_style('views-dialogs-css');
				}
				
			}
		}
		
		//Remove 'product' on Content Template archive usage option
		if ($this->is_woocommerce_activated()) {
			if (is_object($woocommerce)){
				//'Product' post type should be in WooCommerce control
				if (('views_page_view-templates' == $screen_output_base) || ('views_page_ct-editor' == $screen_output_base)) {
					
					//On Content Template backend page
					wp_enqueue_script('wc_views_remove_product_ct_archive', plugins_url( 'res/js/wcviews-remove-product_ct_archive.js', __FILE__ ), array( 'jquery' ),WC_VIEWS_VERSION);
					
					$ct_usage='';	
					$post_archive_parameter_set='no';
					
					if (isset($_GET['usage'])) {
						//Usage is set
						$ct_usage=$_GET['usage'];
					}	
					
					if ('post-archives' == $ct_usage) {
							$post_archive_parameter_set='yes';	    					
					}
					$product_only_archive= 'no';
					$product_only_archive= $this->wcviews_product_the_only_archive_loop($product_only_archive);
					wp_localize_script('wc_views_remove_product_ct_archive', 'wc_views_remove_product_ct_archive_localize',
							array(
									'wcviews_pt_archive_usage' => $post_archive_parameter_set,
									'wcviews_product_only_archive' => $product_only_archive
							)
					);
					
				}    		
			}
			
		}
		
		//woocommerceviews-124: Remove any action links in a readonly page
		if ( ( $this->is_woocommerce_activated() ) && ( true === $this->wcviews_editing_wcv_filter_field_groups() ) ) {
			//We are in a field edit group page
			wp_enqueue_script('wc_views_remove_any_action_links_editpage', plugins_url( 'res/js/wcviews-remove-action-links-editgrouppage.js', __FILE__ ), array( 'jquery' ),WC_VIEWS_VERSION );
			
		}
	}
	
	/**
	 * Check if we are editing WCV filter field groups
	 * @since 2.7.4
	 * @return boolean
	 */
	private function wcviews_editing_wcv_filter_field_groups() {
		$editing	= false;
		global $current_screen;		
		if ( ( is_object( $current_screen ) ) && ( isset( $current_screen->id ) ) ) {
			if ( 'toolset_page_wpcf-view-custom-field' === $current_screen->id ) {
				//We are on Types custom field edit page, check if we are editing WCV field
				if ( ( isset( $_GET['group_id'] ) ) && ( isset( $_GET['page'] ) ) ) {
					if ( 'wpcf-view-custom-field' === $_GET['page'] ) {
						$group_id_loaded	= (int) $_GET['group_id'];
						$wcv_group_id	= $this->wcv_get_groups_id();
						$wcv_group_id	= (int )$wcv_group_id;
						if ( $wcv_group_id === $group_id_loaded ) {
							//ID matched, we are editing indeed
							$editing	= true;
						}
					}
				}
			}
		}		
		return $editing;
	}
	
	
	/** Check if only 'product' is the archive loop available */
	public function wcviews_product_the_only_archive_loop($product_only_archive) {
		
		global $WPV_view_archive_loop;
		if (is_object($WPV_view_archive_loop)){
			if (method_exists($WPV_view_archive_loop,'get_archive_loops')) {
				$post_type_loops = $WPV_view_archive_loop->get_archive_loops( 'post_type', false, true );
				if (is_array($post_type_loops)) {
				   $counted= count($post_type_loops);
				   if (1 === $counted) {
				   		foreach ($post_type_loops as $k=>$v) {				   		  
				   		   //Check 'slug'
				   		   if (isset($v['slug'])) {
				   		   	 $the_slug= $v['slug'];
				   		   	 if ('cpt_product'== $the_slug) {
				   		   	 	return 'yes';				   		   	 	
				   		   	 }				   		   	
				   		   }				   			
				   		}				   	
				   }
				}
			}
			
		}
		
		return $product_only_archive;		
		
	}
	/**
	 * Auto-register the computed values as Types fields.
	 * This method automatically creates the Woocommerce Views filter custom fields and group.
	 * Hooked to wp_loaded
	 * @access public
	 * @return void
	 */	
	
	public function wpv_register_typesfields_func() {
		
		//Define WC Views canonical custom field array
		$wc_views_custom_fields_array=array('views_woo_price','views_woo_on_sale','views_woo_in_stock');
		
		//Preparation to Types control
		$wc_views_fields_array=array();
		$string_wpcf_not_controlled=md5( 'wpcf_not_controlled');		

		foreach ($wc_views_custom_fields_array as $key=>$value) {
			$wc_views_fields_array[]=$value.'_'.$string_wpcf_not_controlled;		
		}
		
	   if (defined('WPCF_INC_ABSPATH')) {
	   	   //First, check if WC Views Types Group field does not exist
	   	   if (!($this->check_if_types_group_exist('WooCommerce Views filter fields'))) {
		   	require_once WPCF_INC_ABSPATH . '/fields.php';
		   	//Part 1: Assign to Types Control
		   	//Get Fields
		   	$fields = wpcf_admin_fields_get_fields(false, true);
		   	$fields_bulk = wpcf_types_cf_under_control('add',array('fields' => $wc_views_fields_array));
		   
		   	foreach ($fields_bulk as $field_id) {
		
				  if (isset($fields[$field_id])) {
						$fields[$field_id]['data']['disabled'] = 0;
				  }
		
		   	}
		   	//Save fields
		   	wpcf_admin_fields_save_fields($fields);    
	
		   	//Retrieve updated fields
		   	$fields = wpcf_admin_fields_get_fields(false, false);
		   
		   	//Assign names
		   	foreach ($fields as $key=>$value) {
		   		  if ($key=='views_woo_price') {
		   		  	$fields['views_woo_price']['name']='WooCommerce Product Price';
		   		  } elseif ($key=='views_woo_on_sale') {
		   		  	$fields['views_woo_on_sale']['name']='Product On Sale Status';
		   		  } elseif ($key=='views_woo_in_stock') {
		   		  	$fields['views_woo_in_stock']['name']='Product In Stock Status';
		   	 	 }       	
		   	}
		   
		   	//Save fields
		   	wpcf_admin_fields_save_fields($fields);
		   	
		   	//Define group
		   	$group=array(
		   	'name' => 'WooCommerce Views filter fields',
		   	'description' => '',
		   	'filters_association' => 'any',
		   	'conditional_display' => array('relation'=>'AND','custom'=>''),
		   	'preview' =>	'edit_mode',
		   	'admin_html_preview' =>'',
		   	'admin_styles' =>'',
		   	'slug' => 'wc-views-types-groups-fields');
		   
		   	//Save group
		   	$group_id=wpcf_admin_fields_save_group($group);
		   
		   	//Save group fields       
		   	wpcf_admin_fields_save_group_fields($group_id,$fields_bulk);  
	   		}   
		   
	   }
	}
	
	/**
	 * Helper method to check if the WooCommerce Views filter groups field already exist.
	 * Parameter is the $title which is the Types group post title in WordPress post table.
	 * @param $title
	 * @access public
	 * @return boolean
	 */	
	
	public function check_if_types_group_exist( $title ) {
		global $wpdb;		
		$return= $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title=%s && post_status = 'publish' && post_type = 'wp-types-group' ", $title),'ARRAY_N');
		if( empty( $return ) ) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Adds WooCommerce Views admin settings menu page on backend.
	 * Hooks to admin_menu
	 * @access public
	 * @return void
	 */	
	
	public function woocommerce_views_add_this_menupage() {		    
		
		if ( ! ( $this->wcviews_can_implement_unified_menu() ) ) {
			//Cannot implement unified menu Add backward compatibility
			//Retrieved missing plugins information
			$missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();
			
			//Add admin screen only when all required plugins are activated
			if (empty($missing_required_plugin)) {			
	
				/** WooCommerce Views 2.4+ -> Transfer the WC Views menu to Views menu as one of its submenu */
				
				add_submenu_page( 
								//Parent slug
								'views',
								//Page Title
								__('WooCommerce Views', 'woocommerce_views'),
								//Menu Title
								__('WooCommerce Views', 'woocommerce_views'),
								//Capability
								'manage_options', 
								//Menu Slug
								'wpv_wc_views',
								//Function
				 				array(&$this, 'woocommerce_views_admin_screen')
								);
			}
		}
	}
	
	/**
	 * Setup WP Cron processing for WooCommerce Views filter custom field batch updates.
	 * Hooks to init.
	 * @access public
	 * @return void
	 */

	public function prefix_setup_schedule() {
		
		//Retrieved current batch processing settings	
		$batch_processing_settings_saved=get_option('woocommerce_views_batch_processing_settings');
		$settings_set=$batch_processing_settings_saved['woocommerce_views_batchprocessing_settings'];
		$intervals_set=$batch_processing_settings_saved['batch_processing_intervals_woocommerce_views'];
	
		//Retrieved available schedules and formulate cron hook name dynamically
		$available_cron_schedules=wp_get_schedules();
		$cron_hookname=array();
		foreach ($available_cron_schedules as $key_cron=>$value_cron) {
			$cron_hookname['prefix_'.trim($key_cron).'_event']=$key_cron;
		}
		
		//Run this function only if using wordpress cron
		if ($settings_set=='using_wordpress_cron') {
			//Using WP cron
			//Dynamically scheduled events based on user settings
			if (is_array($cron_hookname) && (!(empty($cron_hookname)))) {
				
				foreach ($cron_hookname as $key_hookname=>$value_hookname) {
					//If hook is not scheduled AND also the current settings; schedule this event
					if ((!wp_next_scheduled($key_hookname)) && ($intervals_set==$value_hookname)) {
						wp_schedule_event( time(), $value_hookname, $key_hookname);
					}		
				}
				
			}
			//Dynamically add hooks based on user settings
			if (is_array($cron_hookname) && (!(empty($cron_hookname)))) {
				foreach ($cron_hookname as $key_hookname=>$value_hookname) {
					if ($intervals_set==$value_hookname) {
						add_action( $key_hookname, array( $this,'start_processing_products_fields' ) );					
					}	
				}			
			}
		} else {
			//Not using WP Cron, make sure all schedules are cleared
			if (is_array($cron_hookname) && (!(empty($cron_hookname)))) {
				foreach ($cron_hookname as $key_hookname=>$value_hookname) {				
					wp_clear_scheduled_hook($key_hookname);		
				}
			}
		}	
	
	}
	
	/**
	 * Method to get all WooCommerce product IDs in database.
	 * @access public
	 * @return array
	 */	
	
	public function wc_views_get_all_product_ids_clean() {
	
		global $wpdb;
		$all_product_ids=$wpdb->get_results("SELECT ID FROM $wpdb->posts where post_status='publish' AND post_type='product'",ARRAY_N);
		$clean_ids_for_processing=array();
		if ((is_array($all_product_ids)) && (!(empty($all_product_ids)))) {
			foreach ($all_product_ids as $key=>$value) {
				$clean_ids_for_processing[]=reset($value);
			}
		}
	
		return $clean_ids_for_processing;
	
	}
	
	/**
	 * Reset WooCommerce Views products Content Template
	 * @access public
	 * @return void
	 */	

	public function wc_views_reset_products_content_template() {
	
		$clean_ids_for_processing=$this->wc_views_get_all_product_ids_clean();
		
		//Reset product template to none
		//Set their templates to none
		if ((is_array($clean_ids_for_processing)) && (!(empty($clean_ids_for_processing)))) {
			foreach ($clean_ids_for_processing as $k=>$v) {
				$success_updating_template=update_post_meta($v, '_views_template', '');
			}
		}	
	}
	
	/**
	 * Runtime template checker.
	 * Detects changes in theme templates used with WooCommerce Views.
	 * @access public
	 * @return void
	 */	
	
	public function wc_views_runtime_template_checker() {
	
		//Runtime template checker for single products
	
		$runtime_active_template=get_stylesheet();
		$template_in_db_wc_template=get_option('woocommerce_views_theme_template_file');
		if ((is_array($template_in_db_wc_template)) && (!(empty($template_in_db_wc_template))))  {
			$template_in_db_wc_template_value=key($template_in_db_wc_template);
		
			if ($runtime_active_template != $template_in_db_wc_template_value) {
				 
				//User must have been switched to a different template, use default
				//Update to dB
				$runtime_settings_value=array();
				$runtime_option_name='woocommerce_views_theme_template_file';
				$runtime_settings_value[$runtime_active_template]='Use WooCommerce Default Templates';
				$runtime_updating_success=update_option( $runtime_option_name, $runtime_settings_value);
				
				//Reset content templates
				$this->wc_views_reset_products_content_template();

				//Archives too
				$archiveruntime_settings_value=array();
				$archiveruntime_option_name='woocommerce_views_theme_archivetemplate_file';
				$archiveruntime_settings_value[$runtime_active_template]='Use WooCommerce Default Archive Templates';
				$archiveruntime_updating_success=update_option( $archiveruntime_option_name, $archiveruntime_settings_value);
				
				//Reset archives
				$this->reset_wp_archives_wcviews_settings();				
			}
		}
	
		//Runtime template checker for product archives
		$template_in_db_wc_archivetemplate=get_option('woocommerce_views_theme_archivetemplate_file');
		if (!($template_in_db_wc_archivetemplate)) {
			
		   //No template set, could be first time use.
		   //Let's checked whether this user overrides archive template in theme
		   //Since WooCommerce plugin uses that, we will use this as default too
		   $archive_template_theme_override=$this->wc_views_check_if_product_archive_template_exists();
		   if ($archive_template_theme_override) {
		   	 //Archive template override exist
		   	 $this->wcviews_save_php_archivetemplate_settings($archive_template_theme_override);
		   }
		}
		
	}
	
	/**
	 * Save WooCommerce Views template settings to the options table.
	 * Automatically assign Content Templates based on user selection.
	 * @param  string $woocommerce_views_template_to_override
	 * @access public
	 * @return void
	 */	
	
	public function wcviews_save_php_template_settings($woocommerce_views_template_to_override) {
	
		//Save template settings to options table
		$option_name='woocommerce_views_theme_template_file';
		
		//Template validation according to the status of Layouts plugin		
		$layouts_plugin_status=$this->wc_views_check_status_of_layouts_plugin();
		$woocommerce_views_supported_templates= $this->load_correct_template_files_for_editing_wc();
		$woocommerce_views_template_to_override_slashed_removed=stripslashes(trim($woocommerce_views_template_to_override));
		
		//Let's handle if user is originally using non-Layout supported PHP templates
		//Then user activates Layouts plugin
		if ($layouts_plugin_status) {
			
			//Layouts activated
			if (!(in_array($woocommerce_views_template_to_override_slashed_removed,$woocommerce_views_supported_templates))) {
					
				//User saved a PHP template which is not Layouts supported
				//Automatically use default WooCommerce Templates
				$woocommerce_views_template_to_override = 'Use WooCommerce Default Templates';
			}
		} elseif (!(($layouts_plugin_status))) {
			//Layouts deactivated			
			
			if (!(in_array($woocommerce_views_template_to_override_slashed_removed,$woocommerce_views_supported_templates))) {
					
				//User saved a PHP template which is not Loops supported
				//Automatically use default WooCommerce Templates
				$woocommerce_views_template_to_override = 'Use WooCommerce Default Templates';
			}				
		}
		
		$template_associated=get_stylesheet();
		$settings_value=array();
		$settings_value[$template_associated]=stripslashes(trim($woocommerce_views_template_to_override));
		$success=update_option( $option_name, $settings_value);
	
		//Reset content templates to none if using Default WooCommerce Template
		//Template saved
		$template_saved= stripslashes(trim($woocommerce_views_template_to_override));
	
		$clean_ids_for_processing=$this->wc_views_get_all_product_ids_clean();
		
		if ($template_saved=='Use WooCommerce Default Templates') {
		 
			//Reset product template to none
			//Set their templates to none
			if ((is_array($clean_ids_for_processing)) && (!(empty($clean_ids_for_processing)))) {
				foreach ($clean_ids_for_processing as $k=>$v) {
					$success_updating_template=update_post_meta($v, '_views_template', '');
				}
			}		
	
		} else {
			//Non-Default, switch back to content templates
			global $WP_Views;
			$content_template_options = $WP_Views->get_options();
			if (isset($content_template_options)) {
				if (!(empty($content_template_options))) {
					if (isset($content_template_options['views_template_for_product'])) {
						//Product content template is set, re-assigned
						$content_template_products=$content_template_options['views_template_for_product'];
							if ($content_template_products) {
							if ((is_array($clean_ids_for_processing)) && (!(empty($clean_ids_for_processing)))) {
								foreach ($clean_ids_for_processing as $k=>$v) {
									$success_updating_template=update_post_meta($v, '_views_template', $content_template_products);
								}
							}
						}
		
					}
		
				}	
			}
		}
	}
	
	/**
	 * Save batch processing related settings.
	 * Parameter taken from $_POST:
	 * 
	 * 	raw $_POST['woocommerce_views_batchprocessing_settings'] = $woocommerce_views_cf_batchprocessing_settings
	 *  raw $_POST['batch_processing_intervals_woocommerce_views'] =$woocommerce_views_cf_interval_settings
	 *  raw $_POST['system_cron_access_url'] =$woocommerce_views_cf_syscronurl_settings
	 *  
	 * @param  string $woocommerce_views_cf_batchprocessing_settings
	 * @param  string $woocommerce_views_cf_interval_settings
	 * @param  string $woocommerce_views_cf_syscronurl_settings
	 * @access public
	 * @return boolean mixed
	 */	
	
	public function wcviews_save_batch_processing_related_settings($woocommerce_views_cf_batchprocessing_settings,
															 $woocommerce_views_cf_interval_settings,
															 $woocommerce_views_cf_syscronurl_settings) {
	
		//Save batch processing related settings
		$option_name_batch_processing_settings='woocommerce_views_batch_processing_settings';
		$woocommerce_views_batchprocessing_settings=trim($woocommerce_views_cf_batchprocessing_settings);
		$batch_processing_intervals_woocommerce_views=trim($woocommerce_views_cf_interval_settings);
		$system_cron_access_url=stripslashes(trim($woocommerce_views_cf_syscronurl_settings));
	
		$batch_processing_settings_value=array();
		if (isset($woocommerce_views_batchprocessing_settings)) {
			$batch_processing_settings_value['woocommerce_views_batchprocessing_settings']=$woocommerce_views_batchprocessing_settings;
		}
		if (isset($batch_processing_intervals_woocommerce_views)) {
			$batch_processing_settings_value['batch_processing_intervals_woocommerce_views']=$batch_processing_intervals_woocommerce_views;
		}
		if (isset($system_cron_access_url)) {
			$batch_processing_settings_value['system_cron_access_url']=$system_cron_access_url;
		}
		
		//Update options
		$success_batch_processing_settings=update_option( $option_name_batch_processing_settings, $batch_processing_settings_value);	
		
		if ($success_batch_processing_settings) {
			 return TRUE;
		}
	}
	
	/* Show error when no content templates are set but user is overriding PHP templates*/
	public function no_content_template_set_error() {
		//For sure, no Content Templates assigned to Products here..
		//Run the entire message display only on WCV admin screen
		$screen_output_wc_views= get_current_screen();
		$screen_output_id= $screen_output_wc_views->id;
		
		//Get backward/forward compatible screen ID
		$canonical_screen_id = $this->wcviews_unified_current_screen();
		
		//Define message to null.
		$message='';		
		
		if  ( $canonical_screen_id == $screen_output_id) {
			//WCV admin screen

			if( defined('WPDDL_VERSION') ) {
				 
				//Layouts is activated on this site
			
				global $wpddlayout;
				 
				if (is_object($wpddlayout)) {
					 
					//Access Layouts post type object
					$layout_posttype_object=$wpddlayout->post_types_manager;
					 
					if (method_exists($layout_posttype_object,'get_layout_to_type_object')) {
			
						//Check if product post type has been assigned with Layouts
						$result=$layout_posttype_object->get_layout_to_type_object( 'product' );
						 
						if ($result) {
							//Products post type, now assigned with Layouts. Do nothing.
						} else {
							//Products post type has not been assigned with Layouts.
							//Offer to use Layouts to customize product page
							$layouts_admin_list=admin_url().'admin.php?page=dd_layouts';
							//Revised message when
							//Layouts plugin is active, but no layout assigned to products, let's only ask to create a layout.
							//https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/193346933/comments
			
							$message= __('Congrats','woocommerce-views').'!'.' '.
								__('The products template you selected uses Layouts. Next, you need to','woocommerce-views').' '.
									'<a target="_blank" href="'.$layouts_admin_list.'">'.__('create a layout for products','woocommerce-views').'</a>'.'.';
						}
					}
				}
									 
									 
			} else {
						//No layouts plugin, suggest to use Content Templates
						//Let's make sure no Content Templates are assigned
							
						$this->wc_views_reset_products_content_template();
						$ct_admin_list=admin_url().'admin.php?page=view-templates';
			
						$get_current_settings_wc_template=get_option('woocommerce_views_theme_template_file');
						$woocommerce_views_supported_templates= $this->load_correct_template_files_for_editing_wc();
			
						if 	(($get_current_settings_wc_template) && (is_array($woocommerce_views_supported_templates))) {
			
						//Settings initialized
						$get_key_template=key($get_current_settings_wc_template);
						$get_current_settings_wc_template_path=$get_current_settings_wc_template[$get_key_template];
				   
				   
						//Let's double check first if this is not using default WooCommerce templates
						if (!(in_array($get_current_settings_wc_template_path,$woocommerce_views_supported_templates))) {
				   
						//In this case, user is previously using a PHP template that only supports Layouts not Content Templates,we don't show warning in this case
							//Since it will revert to WooCommerce default template automatically.
			
						} else {
						//Qualified template selected but no Content Template, let's show message
						//If Layouts isn't active and Views is (but no CT assigned to products), display:
							$message= __('Congrats','woocommerce-views').'!'.' '.
							__("You've selected your own blank template for products. Next, you need to",'woocommerce-views').' '.
							'<a target="_blank" href="'.$ct_admin_list.'">'.__('create a Content Template for products','woocommerce-views').'</a>'.'.';
	   					}
	   					}
	   	  }			
		}
	   //Let's check if we have $message to output
	   if (!(empty($message))) {
	   	  //Has message to output	   
	   ?>
<div class="error">
	<p>		        
		   		<?php 
		  			echo $message;
		  		?>
				</p>
</div>
<?php
	   }
	}
		
	/* WooCommerce Views admin screen */
	public function woocommerce_views_admin_screen() {
	
		if (isset($_POST['woocommerce_views_nonce'])) {
			if (( wp_verify_nonce( $_POST['woocommerce_views_nonce'], 'woocommerce_views_nonce' )) && (isset($_POST['woocommerce_views_template_to_override'])))  {
				
				//Save PHP template settings
				$woocommerce_views_template_to_override=$_POST['woocommerce_views_template_to_override'];
				$this->wcviews_save_php_template_settings($woocommerce_views_template_to_override);                 
		  
				//Save PHP archive template settings
				$woocommerce_views_archivetemplate_to_override=$_POST['woocommerce_views_archivetemplate_to_override'];
				$this->wcviews_save_php_archivetemplate_settings($woocommerce_views_archivetemplate_to_override);
								
				//Save WooCommerce wrapper on the_content() div
				
				if (isset($_POST['container_div_wrapper_wc'])) {
					$container_div_wrapper_wc=$_POST['container_div_wrapper_wc'];
					update_option('woocommerce_views_wrap_the_content',$container_div_wrapper_wc);
				} else {
					//Save "no"
					$container_div_wrapper_wc='no';
					update_option('woocommerce_views_wrap_the_content',$container_div_wrapper_wc);
				}

				//Save WooCommerce front end sorting setting
				//This section is only relevant when customizing a WooCommerce site using Views only.
				//Layouts plugin status
				$layouts_plugin_status = $this->wc_views_check_status_of_layouts_plugin();
				if ( false === $layouts_plugin_status ) {
					//Layouts not active, proceed.
					if ( isset( $_POST['wcviews_use_default_wc_sorting'] ) ) {
						//Set
						$wcviews_use_default_wc_sorting = trim( $_POST['wcviews_use_default_wc_sorting'] );
						$wcviews_use_default_wc_sorting	= strtolower( $wcviews_use_default_wc_sorting );
						$possible_values				= array( 'no', 'yes' );
						if ( in_array( $wcviews_use_default_wc_sorting, $possible_values ) ) {
							//Validated
							update_option('woocommerce_views_frontend_sorting_setting', $wcviews_use_default_wc_sorting );
						}
					} else {
						//Not set, its 'no'
						$wcviews_use_default_wc_sorting	= 'no';
						update_option('woocommerce_views_frontend_sorting_setting', $wcviews_use_default_wc_sorting );
					}
				}
				
				//Save batch processing related settings
							
				$woocommerce_views_batchprocessing_settings_post=$_POST['woocommerce_views_batchprocessing_settings'];
				$batch_processing_intervals_woocommerce_views_post=$_POST['batch_processing_intervals_woocommerce_views'];
				$system_cron_access_url_post=$_POST['system_cron_access_url'];
				
				$this->wcviews_save_batch_processing_related_settings($woocommerce_views_batchprocessing_settings_post,
																	  $batch_processing_intervals_woocommerce_views_post,
																	  $system_cron_access_url_post);            
					   
				header("Location: admin.php?page=wpv_wc_views&update=true");		
									
			}
		}	
	
	?>	
		<?php 
		$this->wc_views_runtime_template_checker();
		?>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br />
	</div>
	<h2><?php _e('WooCommerce Views','woocommerce_views');?></h2>
			
			<?php
			 if (isset($_GET['update'])) {
			   $update_value=trim($_GET['update']);
			   if ($update_value=='true') {
			 ?>         
	 		 	<div id="update_settings_div_wc_views"
		class="updated wpv-setting-container">
			 	<?php _e('Settings have been updated.','woocommerce_views');?>         
			 	</div>                
			 <?php                                  
			 	}
			 } elseif (isset($_GET['reset'])) {
				$reset_value=trim($_GET['reset']);
				if ($reset_value=='true') {
			 ?>     
			 <div id="update_settings_div_wc_views"
		class="updated wpv-setting-container">
			 <?php _e('Resetting successful.','woocommerce_views');?>         
			 </div>         
			 <?php 
				}         
			 } elseif (isset($_GET['modulesdownload'])) {
				$downloadsuccess_value=trim($_GET['modulesdownload']);
				if ($downloadsuccess_value=='true') {
					?>
						 <div id="update_settings_div_wc_views"
		class="updated wpv-setting-container">
						 <?php _e('Modules download completed.','woocommerce_views');?>         
						 </div>         
						 <?php 
				}
			 }
			 ?>
			 					
			<form id="woocommerce_views_form"
		action="<?php echo admin_url('admin.php?page=wpv_wc_views&noheader=true'); ?>"
		method="post">
		<input type="submit" id="wcviews-submit-form" class="hidden" />
				<?php 
				wp_nonce_field( 'woocommerce_views_nonce', 'woocommerce_views_nonce'); 			
				$this->wc_views_display_php_template_html();
				$this->wc_views_display_php_archive_template_html();                  
				$this->wc_views_display_gui_adding_container_wc_div();
				
				//This setting section is only relevant when customizing WooCommerce using only Views
				//Layouts plugin status
				$layouts_plugin_status = $this->wc_views_check_status_of_layouts_plugin();
				if ( false === $layouts_plugin_status ) {
					//Layouts activated, don't show this section
					$this->wc_views_display_gui_woocommerce_sorting();
				}
				
				$this->wc_views_display_custom_fields_form_update_html('standard');           
	
	 		if (isset($_GET['update_needed'])) {
				$updateneeded_value=trim($_GET['update_needed']);
				if ($updateneeded_value=='true') {
				?>       	
			 <div id="update_needed_wcviews" class="error">
			 <?php _e('Select "Manually" and then please click "Calculate Now" to update fields.','woocommerce_views');?>         
			 </div>
			 <?php 
				}      
			 }			
			?>		
			<p class="submit">				
				<label class="button-primary" for="wcviews-submit-form"><?php _e('Save all Settings','woocommerce_views');?></label>				
			</p>
		<form id="resetformwoocommerce" method="post" action="">
			<?php wp_nonce_field( 'woocommerce_views_resetnonce', 'woocommerce_views_resetnonce'); ?>	
				<input type="submit" class="button" id="wc_viewsresetbutton"
				value="Restore default settings"
				onclick="return confirm( '<?php echo esc_js(__('Are you sure? This will revert to default settings and your own settings will be lost!','woocommerce_views')); ?>' );"
				name="reset"> <input type="hidden"
				name="wc_views_resetrequestactivated" value="reset" />
		</form>

</div>
<?php				
	}
	
	/*public function to display GUI for adding a container DIV around the post body for WooCommerce styling.*/
	public function wc_views_display_gui_adding_container_wc_div() {
		global $wcviews_edit_help;
		$get_settings_wrapper=get_option('woocommerce_views_wrap_the_content');
		if (!($get_settings_wrapper)) {
		
			//FALSE, stil not set,
			//Default to yes
			$get_settings_wrapper='yes';
	
		}
	?>
<div class="wpv-setting-container">
	<div class="wpv-settings-header wcviews_header_views">
		<h3 id="wc_view_woocommerce_styling_settings"><?php _e('WooCommerce Styling','woocommerce_views');?>
		<i class="icon-question-sign js-wcviews-display-tooltip"
				data-header="<?php echo $wcviews_edit_help['woocommerce_styling']['title']?>"
				data-content="<?php echo $wcviews_edit_help['woocommerce_styling']['content']?>"></i>
		</h3>
	</div>
	<div class="wpv-setting wc_view_woocommerce_styling_class">
		<input type="checkbox"
			<?php if ($get_settings_wrapper=='yes') { echo 'CHECKED'; }?>
			name="container_div_wrapper_wc" value="yes"><?php _e('Add a container DIV around the post body for WooCommerce styling.','woocommerce_views');?>
		</div>
</div>
<?php 
	 if (!(empty($wcviews_edit_help['woocommerce_styling']['message_for_link']))) {
	?>
<div class="toolset-help js-woocommerce-defaultstyling">
	<div class="toolset-help-content">
		<p><?php echo $wcviews_edit_help['woocommerce_styling']['message_for_link']?></p>
	</div>
	<div class="toolset-help-sidebar">
		
	</div>
</div>
<?php }?>
	<?php 
	}

	/*public function to display GUI for front end WooCommerce sorting options*/
	public function wc_views_display_gui_woocommerce_sorting() {
		global $wcviews_edit_help;
		$get_settings_wrapper	=	get_option('woocommerce_views_frontend_sorting_setting');
		if ( !( $get_settings_wrapper ) ) {
			//FALSE, stil not set,
			//Default to yes
			$get_settings_wrapper	=	'yes';
		}
		?>
		<div class="wpv-setting-container">
			<div class="wpv-settings-header wcviews_header_views">
				<h3 id="wc_view_woocommerce_order_settings"><?php _e('Frontend Sorting','woocommerce_views');?>
				<i class="icon-question-sign js-wcviews-display-tooltip"
						data-header="<?php echo $wcviews_edit_help['woocommerce_sorting']['title']?>"
						data-content="<?php echo $wcviews_edit_help['woocommerce_sorting']['content']?>"></i>
				</h3>
			</div>
			<div class="wpv-setting wc_view_woocommerce_styling_class">
				<input type="checkbox"
					<?php if ( 'yes' == $get_settings_wrapper ) { echo 'CHECKED'; }?>
					name="wcviews_use_default_wc_sorting" value="yes"><?php _e('Use default WooCommerce sorting for product archives.','woocommerce_views');?>
				</div>
		</div>
		<?php  
	}
	
	/* public function to display WooCommerce Views Custom Fields filter and updating form */
	public function wc_views_display_custom_fields_form_update_html($wcview_cf_rendering_mode) {
		global $wcviews_edit_help;
	?>
		<?php if ($wcview_cf_rendering_mode=='standard') {?>
<div class="wpv-setting-container">
	<div class="wpv-settings-header wcviews_header_views"> 
		<?php  }?>
		  <h3><?php _e( 'Products Fields for Parametric Searches', 'woocommerce_views' );?>
		  <i class="icon-question-sign js-wcviews-display-tooltip"
				data-header="<?php echo $wcviews_edit_help['batch_processing_options']['title']?>"
				data-content="<?php echo $wcviews_edit_help['batch_processing_options']['content']?>"></i>
		</h3>
		<?php if ($wcview_cf_rendering_mode=='standard') {?>
		   </div>
	<div class="wpv-setting wc_view_batch_process_class">
		<div id="ajax_result_batchprocessing_logging">
			<?php
			$updated_batch_processing_time = get_option( 'woocommerce_last_run_update' );
			$logging_text = '';
			$logging_classname = 'toolset-alert';
			if ( $updated_batch_processing_time ) {
				if ( 'ongoing' === $updated_batch_processing_time ) {
					$logging_classname .= ' toolset-alert-warning';
					$updated_batch_processing_time = __( 'ongoing', 'woocommerce_views' );
				} else {
					$logging_classname .= ' toolset-alert-info';
				}
				$logging_text = sprintf(
					__( 'Calculated Product fields were last updated: %s', 'woocommerce_views' ),
					$updated_batch_processing_time
				);
			} else {
				$updated_batch_processing_time = __( 'never', 'woocommerce_views' );
				$logging_classname .= ' toolset-alert-error';
			}
			$logging_text = sprintf(
				__( 'Calculated Product fields were last updated: %s', 'woocommerce_views' ),
				'<span id="wcv-product-fields-updated-last-time">' . $updated_batch_processing_time . '</span>'
			);
			?>
			<div id="ajax_result_batchprocessing_time" class="<?php echo esc_attr( $logging_classname ); ?>">	
				<?php echo $logging_text; ?>
			</div>
		</div>
		<?php }?>					
		<div id="batchprocessing_woocommerce_views">
		<?php 
		//Retrieved settings from database
		$batch_processing_settings_from_db=get_option('woocommerce_views_batch_processing_settings');
		if (!(empty($batch_processing_settings_from_db))) {
		   //Settings set
						 
		   if (isset($batch_processing_settings_from_db['woocommerce_views_batchprocessing_settings'])) {
	 		  $form_woocommerce_views_batchprocessing_settings=$batch_processing_settings_from_db['woocommerce_views_batchprocessing_settings'];
		   } else {
			  //Default to manually
			  $form_woocommerce_views_batchprocessing_settings='manually';
		   }
	
		   if (isset($batch_processing_settings_from_db['batch_processing_intervals_woocommerce_views'])) {
			   $form_batch_processing_intervals_woocommerce_views=$batch_processing_settings_from_db['batch_processing_intervals_woocommerce_views'];
		   } else {
			   //Default to daily
			   $form_batch_processing_intervals_woocommerce_views='daily';
		   }
		   
		   if (isset($batch_processing_settings_from_db['system_cron_access_url'])) {
			   $form_system_cron_access_url=$batch_processing_settings_from_db['system_cron_access_url'];
		   } else {
			   //Default 
			   //$plugin_abs_path_retrieved=plugin_dir_path( __FILE__ );
			   //Revise to URL path
			   $plugin_abs_path_retrieved=plugins_url( 'system_cron/run_wc_views_cron.php', __FILE__ );
			   $form_system_cron_access_url=$plugin_abs_path_retrieved;
		   }
						  
		} else {
			//Batch processing options not set, define defaults
			$form_woocommerce_views_batchprocessing_settings='manually';
			$form_batch_processing_intervals_woocommerce_views='daily';                        
		   //$plugin_abs_path_retrieved=plugin_dir_path( __FILE__ );
			$form_system_cron_access_url=$this->wc_views_generate_cron_access_url_settings();                                          
	   }
	   ?>
	   <?php if ($wcview_cf_rendering_mode=='standard') {?>
	   <p><?php _e('Select when to update the static product fields:','woocommerce_views');?></p>
	   <?php } ?>
	   <?php if ($wcview_cf_rendering_mode=='wizard') {?>
	   <p><?php _e('To automatically update, simply click the "Next" button, otherwise you can skip this step.','woocommerce_views');?></p>
			<p><?php _e('You can also update these fields manually or automatically after this wizard.','woocommerce_views');?></p>
	   <?php }?>
	   <?php if ($wcview_cf_rendering_mode=='standard') {?>
	   <p>
				<input type="radio"
					name="woocommerce_views_batchprocessing_settings"
					id="system_cron_id_wc_views" value="using_system_cron"
					<?php if ($form_woocommerce_views_batchprocessing_settings=='using_system_cron') { echo "checked"; }?>> <?php _e('Using a system cron, by calling this URL:','woocommerce_views');?><input
					readonly="readonly" type="text" name="system_cron_access_url"
					id="wc_views_sys_cron_path"
					value="<?php echo $form_system_cron_access_url;?>">
			</p>
			<p>
				<input type="radio"
					name="woocommerce_views_batchprocessing_settings"
					id="wp_cron_id_wc_views" value="using_wordpress_cron"
					<?php if ($form_woocommerce_views_batchprocessing_settings=='using_wordpress_cron') { echo "checked"; }?>> <?php _e('Using the WordPress cron','woocommerce_views');?>
		<select name="batch_processing_intervals_woocommerce_views">
		<?php 
		//Dynamically retrieved available schedules for cron
		$available_schedules_for_cron=wp_get_schedules();					
			foreach ($available_schedules_for_cron as $key_schedule=>$value_schedule) {
		?>
			<option
						<?php if ($form_batch_processing_intervals_woocommerce_views==$key_schedule) { echo 'selected';}?>
						value="<?php echo $key_schedule;?>"><?php echo $available_schedules_for_cron[$key_schedule]['display'];?></option>					
	  <?php } ?>
		</select>
			</p>
			<p>
				<input type="radio"
					name="woocommerce_views_batchprocessing_settings"
					id="manual_id_wc_views" value="manually"
					<?php if ($form_woocommerce_views_batchprocessing_settings=='manually') { echo "checked"; }?>> <?php _e('Manually','woocommerce_views');?></p>									
	   <?php } ?>
	   </div>
	   <?php if ($wcview_cf_rendering_mode=='standard') {?>
	   </form>  
	   <?php 
		$this->wc_views_display_calculate_product_attributes_form_html();
		} 
	   ?>
		<?php if ($wcview_cf_rendering_mode=='standard') {?>
		  </div>
</div>
<?php 
		if (!(empty($wcviews_edit_help['batch_processing_options']['message_for_link']))) {
		?>
<div class="toolset-help js-wcviews_batchprocessing">
	<div class="toolset-help-content">
		<p><?php echo $wcviews_edit_help['batch_processing_options']['message_for_link']?></p>
	</div>
	<div class="toolset-help-sidebar">
		
	</div>
</div>
<?php }?>	    
		<?php  }?>
	<?php 
	}
	
	/*public function for generating cron access URL*/
	public function wc_views_generate_cron_access_url_settings() {
	
		//Revise to URL path
		//First time executed, generate secret key
		$length=12;
		$generated_secret_key=$this->wc_views_generaterandomkey($length);
		
		//Store this secret key as options for easy verification
		$value_changed=update_option('wc_views_sys_cron_key',$generated_secret_key);
		$plugin_abs_path_retrieved=plugins_url( 'system_cron/run_wc_views_cron.php?cron_key='.$generated_secret_key, __FILE__ );
	
		return $plugin_abs_path_retrieved;
	}
	
	/* public function to display calculator product attributes form */
	public function wc_views_display_calculate_product_attributes_form_html() {
	   
		//Independently triggered
	?>
<form id="requestformanualbatchprocessing" method="post" action="">
	<input id="woocommerce_batchprocessing_submit" type="submit"
		name="Submit" class="button-secondary"
		onclick="return confirm( '<?php echo esc_js(__('Are you sure you want to manually run this batch processing?','woocommerce_views')); ?>' );"
		value="<?php _e('Calculate Now','woocommerce_views');?>" />
</form>
<?php 
	}
	
	/**
	 * Method for checking the status of Layouts plugin
	 * @access public
	 * @return boolean
	 */
	
	public function wc_views_check_status_of_layouts_plugin() {
		
		global $wpddlayout;
		
		if( defined('WPDDL_VERSION') ) {

			$wpddl_version=WPDDL_VERSION;
			
			if ((!(empty($wpddl_version))) && (is_object($wpddlayout))) {
				
				//Layouts is full activated and ready to use
				return TRUE;				
			} else {
				
				return FALSE;	
			}			
		} else {
			
		   return FALSE;
			
		}
	}	
	
	/**
	 * Method for displaying the PHP template selection
	 * @access public
	 * @return void
	 */
	
	public function wc_views_display_php_template_html($wcview_cf_rendering_mode='standard') {
		
	global $wcviews_edit_help;
	$woocommerce_views_supported_templates= $this->load_correct_template_files_for_editing_wc();
	$single_product_php_template_check=$this->wc_views_check_if_single_product_template_exists();
	$layouts_plugin_status=$this->wc_views_check_status_of_layouts_plugin();
	?>
	<?php 
		if ($wcview_cf_rendering_mode=='standard') {
	?>
		<?php 
		if (!(empty($wcviews_edit_help['top_general_helpbox']['message_for_link']))) {
		?>
<div class="toolset-help js-wcviews_top_general_helpbox">
	<div class="toolset-help-content">
		<p><?php echo $wcviews_edit_help['top_general_helpbox']['message_for_link']?></p>
	</div>
	<div class="toolset-help-sidebar">
		
	</div>
</div>
<?php }?>

<div class="wpv-setting-container">
	<div class="wpv-settings-header wcviews_header_views">
	<?php
 		}
	?>
				<h3>
					<?php _e('Product Template File','woocommerce_views');?>
					<i class="icon-question-sign js-wcviews-display-tooltip"
				data-header="<?php echo $wcviews_edit_help['template_assignment_section']['title']?>"
				data-content="<?php echo $wcviews_edit_help['template_assignment_section']['content']?>"></i>
		</h3>
				
	<?php 
		if ($wcview_cf_rendering_mode=='standard') {
	?>
		   		</div>
	<div class="wpv-setting">
	<?php
   		}
		?>			
					<div id="phptemplateassignment_wc_views">
			<p><?php _e('Select the PHP template which will be used for WooCommerce single-product pages:','woocommerce_views');?></p>
			<p>
					<?php 
					if (!(empty($woocommerce_views_supported_templates))) {
					?>	
	
					<?php
			 		if ($wcview_cf_rendering_mode=='wizard') {
						$var_selector='id="woocommerce_views_template_to_override_unique_id"';
			 		} else {
			 		   $var_selector='';
			 		}
	
					$get_current_settings_wc_template=get_option('woocommerce_views_theme_template_file');
					if 	($get_current_settings_wc_template) {
		
						//Settings initialized	
						$get_key_template=key($get_current_settings_wc_template);
						$get_current_settings_wc_template_path=$get_current_settings_wc_template[$get_key_template];
						
						//Backward compatibility, removing the PHP template with layouts and merging templates into one canonical
						//https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/193344321/comments
						
						if ((strpos($get_current_settings_wc_template_path, 'single-product-layouts.php') !== false)) {
							
							//Using Layouts template, deprecated	
							//Ensure its now using canonical template						
							$canonical_product_template[$get_key_template] = $single_product_php_template_check;
							update_option('woocommerce_views_theme_template_file',$canonical_product_template);
							
							//Use canonical template
							$get_current_settings_wc_template_path=$single_product_php_template_check;	

						}
						
						//Let's handle if user is originally using non-Layout supported PHP templates
						//Then user activates Layouts plugin
						if ($layouts_plugin_status) {														
							//Layouts activated
							if (!(in_array($get_current_settings_wc_template_path,$woocommerce_views_supported_templates))) {
							
								//User originally selected PHP template is not Layouts supported
								//Automatically use default WooCommerce Templates
								$this->wcviews_save_php_template_settings('Use WooCommerce Default Templates');
								$get_current_settings_wc_template_path='Use WooCommerce Default Templates';									
							}							
						} elseif (!(($layouts_plugin_status))) {
						   //Layouts deactivated
							if (!(in_array($get_current_settings_wc_template_path,$woocommerce_views_supported_templates))) {
									
								//User originally selected PHP template is not Layouts supported
								//Automatically use default WooCommerce Templates
								$this->wcviews_save_php_template_settings('Use WooCommerce Default Templates');
								$get_current_settings_wc_template_path='Use WooCommerce Default Templates';
							}						   	
							
						}
	
						if ((is_array($woocommerce_views_supported_templates)) && (!(empty($woocommerce_views_supported_templates)))) {
						 	$counter_p=1;
				  		 	foreach ($woocommerce_views_supported_templates as $template_file_name=>$theme_server_path) {				
					?>
					<?php 
					$p_id='ptag_'.$counter_p;
					?>
					
			
			
			<div class="template_selector_wc_views_div" id="<?php echo $p_id;?>">
				<input <?php echo $var_selector;?> type="radio"
					name="woocommerce_views_template_to_override"
					value="<?php echo $theme_server_path?>"
					<?php if ($get_current_settings_wc_template_path==$theme_server_path) { echo "CHECKED";} ?>>				
						<?php 
							if ('Use WooCommerce Default Templates' ==$template_file_name) {
							   //Clarity
								if ($layouts_plugin_status) {
									$template_file_name = "WooCommerce Plugin Default Template (doesn't display layouts)";
								} else {
									$template_file_name = 'WooCommerce Plugin Default Templates';
								}
								
							}
							echo $template_file_name;
						?>
						<a class="show_path_link" href="javascript:void(0)"><?php _e('Show template','woocommerce_views');?></a>
				<div class="show_path_wcviews_div" style="display: none;">
					<textarea rows="2" cols="50" class="inputtextpath" readonly />
					</textarea>
				</div>
			</div>
					<?php 
					$counter_p++;
					?>
			 			<?php
						  }
					 	} else {
					 	//not loaded                  
			 			?>
					 		<p>
				<input type="radio" name="woocommerce_views_template_to_override"
					value="Use WooCommerce Default Templates">
					 		<?php _e('Use WooCommerce Default Templates','woocommerce_views');?>
					 		</p>             			
					 	<?php
 						}
 						?>
				 <?php
 				 } else {
					 	
					 	//Not initialized 
		
					 	//Check if no template is saved yet.
					 	$status_template=$this->wc_views_check_if_using_woocommerce_default_template();
					 	
					 	if ((is_array($woocommerce_views_supported_templates)) && (!(empty($woocommerce_views_supported_templates)))) {
					 		$counter_p=1;
					 		foreach ($woocommerce_views_supported_templates as $template_file_name=>$theme_server_path) {
		   				 $file_basename=basename($theme_server_path);  
		   				
		   				 $p_id='ptag_'.$counter_p;
		   				 
		   		 ?>             
	 				<div class="template_selector_wc_views_div"
				id="<?php echo $p_id;?>">
				<input <?php echo $var_selector;?> type="radio"
					name="woocommerce_views_template_to_override"
					value="<?php echo $theme_server_path?>"
					<?php 
	 					if ($file_basename=='single-product.php') {
	 						//Don't checked this if there is still no template set
	 						if (!($status_template)) {
								echo "CHECKED";
	 						}
		   			 } elseif (($file_basename=='page.php') && (!($single_product_php_template_check))) {
		   				 //Should be checked if single-product is not available
		   				 if ($wcview_cf_rendering_mode=='wizard') { 
						 		echo "CHECKED";
							} else {
								if ($get_current_settings_wc_template) {
									//Not being resetting on admin screen
				 				   echo "CHECKED";
				 			   }
				 		   }
						} elseif ($file_basename=='Use WooCommerce Default Templates')  {
		
			   		   echo "CHECKED";
			  			
			  		  } 
						 ?>>
						 <?php
						 if ('Use WooCommerce Default Templates' ==$template_file_name) {
						 	//Clarity
						 		if ($layouts_plugin_status) {
									$template_file_name = "WooCommerce Plugin Default Template (doesn't display layouts)";
								} else {
									$template_file_name = 'WooCommerce Plugin Default Templates';
								}
						 }
						 
						 echo $template_file_name;
						 ?>
						<a class="show_path_link" href="javascript:void(0)"><?php _e('Show template','woocommerce_views');?></a>
				<div class="show_path_wcviews_div" style="display: none;">
					<textarea rows="2" cols="50" class="inputtextpath" readonly />
					</textarea>
				</div>
			</div> 
					<?php $counter_p++;?>           		
				  	<?php }     
		   		  	}    
		   		  }
		  		   ?>
					<?php    
						if (!($single_product_php_template_check)) {
					?>              
							<strong><?php _e('Tip','woocommerce_views')?></strong>: <?php _e('You can actually create your own single-product.php template. Please refer to this','woocommerce_views');?> <a
				href="#"><?php _e('explanation','woocommerce_views');?></a>.
					<?php
 						} 
 					?>
			<?php 
				} else {
			?>
					<p class="no_template_found_wc_views_class"><?php _e('ERROR: Your theme does not have compatible templates with WooCommerce Views.','woocommerce_views');?></p>
			<?php 
				}
			?>
					</p>
		</div>	
						<?php 
						if ($wcview_cf_rendering_mode=='standard') {
						?>
	  			</div>
</div>
<?php if (!(empty($wcviews_edit_help['template_assignment_section']['message_for_link']))) {?>
<div class="toolset-help js-phptemplatesection">
	<div class="toolset-help-content">
		<p><?php echo $wcviews_edit_help['template_assignment_section']['message_for_link']?></p>
	</div>
	<div class="toolset-help-sidebar">
		
	</div>
</div>
<?php }?>
						<?php
 						}
 						?>
	<?php 
	}
	
	/* Reset admin screen settings to default */
	public function reset_all_wc_admin_screen() {
	
		if(isset($_REQUEST['wc_views_resetrequestactivated']))
		{		
			//Verify nonce
			if (isset($_POST['woocommerce_views_resetnonce'])) {
				if ( wp_verify_nonce( $_POST['woocommerce_views_resetnonce'], 'woocommerce_views_resetnonce' ))  {		
			
					//reset to defaults
		   		 //Option names
					$option_name_one='woocommerce_views_theme_template_file';					
					$option_name_two='woocommerce_views_batch_processing_settings';
					$option_name_three='woocommerce_last_run_update';
					$option_name_four='woocommerce_views_wrap_the_content';
					$option_name_five='woocommerce_views_theme_archivetemplate_file';
					$option_name_six	=	'woocommerce_views_frontend_sorting_setting';
					
					delete_option($option_name_one);
					delete_option($option_name_two);
					delete_option($option_name_three);	
					delete_option($option_name_four);
					delete_option($option_name_five);
					delete_option( $option_name_six );
					
					$clean_ids_for_processing_reset=$this->wc_views_get_all_product_ids_clean();
					
					//Reset product template to none
							
					if ((is_array($clean_ids_for_processing_reset)) && (!(empty($clean_ids_for_processing_reset)))) {
						foreach ($clean_ids_for_processing_reset as $k=>$v) {
							$success_updating_template_reset=update_post_meta($v, '_views_template', '');
						}
					}	
	

					$this->reset_wp_archives_wcviews_settings();

					//redirect to reset =true
					header("Location: admin.php?page=wpv_wc_views&reset=true");
					
				}
			}
		}
	
	}
	
	/*public function to display HTML of exiting wizard*/
	public function display_wc_views_user_exit_wizard_form_html() {
	?>
<form id="request_for_exit_wizard" method="post" action="">
				<?php wp_nonce_field('wcviews_exit_wizard_nonce','wcviews_exit_wizard_nonce') ?>
			   	<input id="exit_wizard_button_id" type="submit"
		name="Submit" class="button-secondary"
		onclick="return confirm( '<?php echo esc_js(__('Are you sure you want to exit wizard?','woocommerce_views')); ?>' );"
		value="<?php _e('Exit wizard','woocommerce_views');?>" /> <input
		type="hidden" name="wc_views_exit_wizard_requested"
		value="exit_this_wizard" />
</form>
<?php 
	}
	
	/*public function to display HTML of skipping steps in wizard*/
	public function display_wc_views_user_skipstep_in_wizardform($steps) {
	?>

<form id="request_for_skippingstep_wizard" method="post" action="">
				<?php wp_nonce_field('wcviews_skipstep_wizard_nonce','wcviews_skipstep_wizard_nonce') ?>
			   	<input id="skipstep_wizard_button_id" type="submit"
		name="Submit" class="button-secondary"
		value="<?php _e('Skip Step','woocommerce_views');?>" /> <input
		type="hidden" name="wc_views_skipstep_wizard_requested"
		value="<?php echo $steps;?>" />
</form>
<?php 
	}

	/**
	 * Count published products
	 *
	 * @return int
	 * @since 2.7.8
	 */
	private function count_published_products() {
		$count_posts = wp_count_posts( 'product' );
		return $count_posts->publish;
	}

	/**
	 * Get the number of items to process at once when manually updating products fields.
	 *
	 * @return int
	 * @since 2.7.8
	 */
	private function get_products_fields_manual_batch_update_limit() {
		/**
		 * @param int
		 * @return int
		 * @since 2.7.8
		 */
		return apply_filters( 'woocommerce_views_products_fields_manual_batch_update_limit', 100 );
	}

	/**
	 * Get the number of items to process at once when automatically updating products fields.
	 *
	 * @return int
	 * @since 2.7.8
	 */
	private function get_products_fields_automatic_batch_update_limit() {
		/**
		 * @param int
		 * @return int
		 * @since 2.7.8
		 */
		return apply_filters( 'woocommerce_views_products_fields_automatic_batch_update_limit', 100 );
	}

	/**
	 * Query the database to collect products thta need their fields updated.
	 *
	 * @param array $args {
	 *     @type int $start The post ID to start with, or 0 to start anew
	 *     @type int $limit The query limit, as number of items to process per batch
	 * }
	 * @return int[]
	 * @since 2.7.8
	 */
	public function get_products_to_process_fields( $args ) {
		$defaults = array(
			'start' => 0,
			'limit' => $this->get_products_fields_manual_batch_update_limit(),
		);
		$args = wp_parse_args( $args, $defaults );

		global $wpdb;
		
		$woocommerce_product_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts
				WHERE post_status = 'publish'
				AND post_type = 'product'
				AND ID > %d
				LIMIT %d",
				array(
					$args['start' ],
					$args['limit'] + 1,
				)
			)
		);

		return $woocommerce_product_ids;
	}

	/**
	 * Process the fields for the provided products IDs.
	 *
	 * @param int[] $product_ids
	 * @since 2.7.8
	 */
	private function process_product_fields( $product_ids ) {
		$views_woo_price = 'views_woo_price';
		$views_woo_on_sale = 'views_woo_on_sale';
		$views_woo_in_stock = 'views_woo_in_stock';

		foreach ( $product_ids as $product_id ) {
			$product_post = get_post( $product_id );         
			$product = $this->wcviews_setup_product_data( $product_post );
			if ( null !== $product ) {
				// Product price
				$product_price = $product->get_price();
				update_post_meta( $product_id, $views_woo_price, $product_price );
				
				// Product is on sale
				$product_on_sale_boolean = $this->for_views_null_equals_zero_adjustment( $product->is_on_sale() );
				update_post_meta( $product_id, $views_woo_on_sale, $product_on_sale_boolean );
				
				// Product is in stock
				$product_on_stock_boolean = $this->for_views_null_equals_zero_adjustment( $product->is_in_stock() );
				update_post_meta( $product_id, $views_woo_in_stock, $product_on_stock_boolean );
			}
		}
	}

	/**
	 * Batch round to process products to set their fields.
	 *
	 * @param array $args {
	 *     @type int $start The ID to start with, or 0 to start anew
	 *     @type int $limit The amount of posts to process at once
	 *     @type string $status The batch process status
	 * }
	 * @return array {
	 *     @type int $start The ID of the last processed product
	 *     @type int $limit The amount of posts to process at once
	 *     @type string $status The batch process status after this round
	 * }
	 * @since 2.7.8
	 */
	private function batch_process_products( $args ) {
		$defaults = array(
			'start' => 0,
			'limit' => $this->get_products_fields_manual_batch_update_limit(),
			'status' => 'ongoing',
		);
		$args = wp_parse_args( $args, $defaults );
		$available_products = $this->count_published_products();

		if ( 0 === (int) $available_products ) {
			// There are no products to update
			$args['status'] = 'completed';
			return $args;
		}

		if ( $available_products < $args['limit'] ) {
			$args['limit'] = $available_products;
		}

		$products_ids_to_update = $this->get_products_to_process_fields( $args );

		if ( 0 === count( $products_ids_to_update ) ) {
			// Last round processed all remaining products
			$args['status'] = 'completed';
			return $args;
		}

		$this->process_product_fields( $products_ids_to_update );

		// Set the starting point for the next iteration, in case it is needed
		$args['start'] = max( $products_ids_to_update );

		// Set the batch status to completed if:
		// - there are less products than the lower limit
		// (so we will process them all at once)
		// - the current iteration is processing less products than the lower limit
		// (so it is the last iteration on the remainders)
		if (
			$available_products <= $args['limit']
			|| count( $products_ids_to_update ) <= $args['limit']
		) {
			$args['status'] = 'completed';
		}

		return $args;
	}

	/**
	 * Callback to process products fields manually.
	 *
	 * @since 2.7.8
	 */
	public function ajax_process_products_fields() {
		if (
			! isset( $_POST['nonce'] )
			|| ! wp_verify_nonce( $_POST['nonce'], 'wc_views_ajax_response_admin' )
		) {
			wp_send_json_error(
				array(
					'message' => __( 'Batch processing output is not successful because nonce is invalid.', 'woocommerce_views'),
				)
			);
		}

		$start = isset( $_POST['start'] ) ? (int) $_POST['start'] : null;

		if ( null === $start ) {
			wp_send_json_error(
				array(
					'message' => __( 'Batch processing output is not successful.', 'woocommerce_views'),
				)
			);
		}

		$args = array(
			'start' => $start,
		);
		$outcome = $this->batch_process_products( $args );

		if ( 'completed' === $outcome['status'] ) {
			$last_updated = date_i18n('Y-m-d G:i:s');

			$wcv_options = get_option( 'woocommerce_views_options', array() );
			if ( isset( $wcv_options['last_product_processed'] ) ) {
				unset( $wcv_options['last_product_processed'] );
				update_option( 'woocommerce_views_options', $wcv_options );
			}
		} else {
			$last_updated = 'ongoing';
		}

		$outcome['lastUpdated'] = $last_updated;

		update_option( 'woocommerce_last_run_update', $last_updated );

		wp_send_json_success(
			array(
				'outcome' => $outcome,
			)
		);
	}

	/**
	 * Helper to see if an automatic batch update of products fields is ongoing,
	 * and push an extra step on it.
	 *
	 * Fired when the batch update was initialized by a WordPress or system cron.
	 *
	 * @since 2.7.8
	 */
	public function maybe_process_products_fields() {
		$wcv_options = get_option( 'woocommerce_views_options', array() );
		if ( isset( $wcv_options['last_product_processed'] ) ) {
			$args = array(
				'start' => (int) $wcv_options['last_product_processed'],
				'limit' => $this->get_products_fields_automatic_batch_update_limit(),
			);

			$outcome = $this->batch_process_products( $args );

			$this->adjust_options_after_batch_processing_product_fields( $wcv_options, $outcome );
		}
	}

	/**
	 * Start a batch update. Can be triggered by three callers:
	 * - a WordPress cron job.
	 * - a system cron job.
	 * - an activation hook.
	 *
	 * Do nothing if a batch update is already ongoing.
	 *
	 * @since 2.7.8
	 */
	public function start_processing_products_fields() {
		$wcv_options = get_option( 'woocommerce_views_options', array() );
		if ( isset( $wcv_options['last_product_processed'] ) ) {
			// There is already an ongoing update happening
			return;
		}

		$args = array(
			'start' => 0,
			'limit' => $this->get_products_fields_automatic_batch_update_limit(),
		);
		$outcome = $this->batch_process_products( $args );

		$this->adjust_options_after_batch_processing_product_fields( $wcv_options, $outcome );
	}

	/**
	 * Adjust the options stored when a batch update round is completed:
	 * - set the last processed product ID if the batch needs another round
	 * - delete the flag if the batch is completed
	 *
	 * @param array $wcv_options
	 * @param array $outcome
	 * @since 2.7.8
	 */
	private function adjust_options_after_batch_processing_product_fields( $wcv_options, $outcome ) {
		if ( 'completed' === $outcome['status'] ) {
			unset( $wcv_options['last_product_processed'] );
		} else {
			$wcv_options['last_product_processed'] = $outcome['start'];
		}
		
		update_option( 'woocommerce_views_options', $wcv_options );
	}

	/**
	 * Maybe start processing products fields, on the activation hook.
	 * Do not run the batch update if:
	 * - the update was run in the past at least once
	 * - AND the batch update is scheduled on a WordPress or system cron already
	 * Otherwise, start a batch update unless there is one already ongoing.
	 *
	 * @since 2.7.8
	 */
	public function maybe_start_processing_products_fields() {
		$last_updated = get_option( 'woocommerce_last_run_update' );
		$fields_processing_settings = get_option( 'woocommerce_views_batch_processing_settings', array() );	                     
		if (
			$last_updated
			&& isset( $fields_processing_settings['woocommerce_views_batchprocessing_settings'] )
			&& 'manually' !== $fields_processing_settings['woocommerce_views_batchprocessing_settings']
		) {
			// There is already a cron update scheduled
			return;
		}

		$this->start_processing_products_fields();
	}
	
	/**
	 * @depecated 2.7.8
	 */
	public function ajax_process_wc_views_batchprocessing( $wc_view_woocommerce_orderobject = '' ) {
		$response = array();
		$response['status'] ='error';
		$response['batch_processing_output'] = __( 'This method is deprecated.', 'woocommerce_views' );
		echo json_encode( $response );
		die();
	} 
	
	public function for_views_null_equals_zero_adjustment($boolean_test) {
	
		if (!($boolean_test)) {
		//False
		return '0';
		
		} else {
		//True
		return '1';
		}
	
	}
	//Generate random key
	public function wc_views_generaterandomkey($length) {
	
		$string='';
		$characters = "0123456789abcdef";
		for ($p = 0; $p < $length ; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters) - 1)];
		}
	
		return $string;
	}
	
	public function wc_views_filter_only_relevant_wc_templates_innerdir($complete_template_files_list) {
	
	  if ((is_array($complete_template_files_list)) && (!(empty($complete_template_files_list)))) {
	
		  $sanitized_complete_template_files_list=array();
		  
		  //Loop through the array and sanitize the array
		  foreach ($complete_template_files_list as $unclean_template_name=>$template_path) {
			 
			 $template_name=basename($unclean_template_name);
			 
			 if ($template_name != $unclean_template_name) {
	
				 //File belongs one directory deeper
				 //Check if its a WooCommerce single-products.php template
				 //Two possibilities- single-product.php and archive-product.
				 //This is all we need for now with WooCommerce Views.
				 $unclean_template_name=strtolower($unclean_template_name);
				 if ($unclean_template_name =='woocommerce/archive-product.php') {
					 $sanitized_complete_template_files_list[$template_name]=$template_path;
				 } elseif ($unclean_template_name =='woocommerce/single-product.php') {
				 	$sanitized_complete_template_files_list[$template_name]=$template_path;
				 }

			 } else {
	
				 //Usual flat directory theme files
				 $sanitized_complete_template_files_list[$unclean_template_name]=$template_path;
			 }        
		 }
		 return $sanitized_complete_template_files_list;
	  }
	}
	
	/**
	 * Aux method to retrieved equivalence between template path and theme name
	 * @access public
	 * @return array
	 */
	public function theme_name_and_template_path($theme) {
		
		$stylesheet=$theme->stylesheet;
		$template=$theme->template;
		
		$information=array();
		
		//First, let's check if site is using child theme
		$is_using_child_theme=FALSE;
		if ($stylesheet != $template ) {
			
			//Templates and Stylesheet are not the same.
			//Site is using child theme
			$is_using_child_theme=TRUE;
			
		}
		
		if ($is_using_child_theme) {
			
			//Let's store the child theme name
			$child_theme_name=$theme->get( 'Name' );
			$information[$stylesheet] = $child_theme_name;
			
			//Let's get the parent theme name
			$parent_theme_name=$theme->parent_theme;
			$information[$template]=$parent_theme_name;	
			
		} else {

			//Only parent theme activated
			$information[$template]=$theme->get( 'Name' );
			
		}
		
		return $information;
	}
	
	/**
	 * Method to check for the existence and use of Genesis Frameworks
	 * @access public
	 * @return boolean
	 */
	public function wc_views_is_using_genesis_framework($theme) {

		$using_genesis_framework=false;		
		
		if (is_object($theme)) {
			
			$current_theme_name ='';
			$parent_headers_name ='';
			
			if (isset($theme->name)) {				
				$current_theme_name=$theme->name;				
			}			
			if (isset($theme->parent_theme)) {				
				$parent_headers_name =$theme->parent_theme;
			}

			$genesis_func_exist= false;
			if (function_exists('genesis')) {				
				$genesis_func_exist=true;
			}
			
			if ((('Genesis' == $current_theme_name) || ('Genesis' == $parent_headers_name)) && ($genesis_func_exist)) {				
				$using_genesis_framework=true;				
			}			
		}	
		
		return $using_genesis_framework;
	}
	
	/**
	 * Method for loading correct template files for using with WooCommerce Views
	 * @access public
	 * @return array
	 */	

	public function load_correct_template_files_for_editing_wc() {
	
		//Get all information about the parent and child theme!
		$theme = wp_get_theme();
		$get_custom_theme_info=$this->theme_name_and_template_path($theme);
		$complete_template_files_list = $theme->get_files( 'php', 1,true);
		$complete_template_files_list = $this->wc_views_filter_only_relevant_wc_templates_innerdir($complete_template_files_list );
		$headers_for_theme_files=$theme->get_page_templates();

		//Retrieve stylesheet directory URI for the current theme/child theme 
		$get_stylesheet_directory_data=get_stylesheet_directory();
		
		//Checked for Genesis Frameworks which uses specialized loops
		$is_using_genesis= $this->wc_views_is_using_genesis_framework($theme);
	
		if ((is_array($complete_template_files_list)) && (!(empty($complete_template_files_list)))) {
		$correct_templates_list= array();
		$layouts_plugin_status=$this->wc_views_check_status_of_layouts_plugin();
		
		foreach ($complete_template_files_list as $key=>$values) {
		   $pos_single = stripos($key, 'single');
		   $pos_page =  stripos($key, 'page');       
		   if (($pos_single !== false) || ($pos_page !== false)) {

			  //https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/193344239/comments
			  //When Layouts plugin is active, only show templates that have the_ddlayouts integration	  
			  $is_theme_template_has_ddlayout =FALSE;
			  $is_theme_template_looped =FALSE;
			  
			  if ($layouts_plugin_status) {
			  	global $wpddlayout;
			  	//Layouts activated

			  	//Ensure single-product is checked at correct path
			  	$template_lower_case= strtolower($key);
			  	if ((strpos($template_lower_case, 'single-product.php') !== false)) {
			  		//This is a single product template at the user theme directory
			  		$key = str_replace($get_stylesheet_directory_data, "", $values);	          			
			  		$key =ltrim($key,'/');          			
			  	}
			  	
			  	$is_theme_template_has_ddlayout= $this->wcviews_template_have_layout($key);	          		
			  				  	
			  } else {
			  	//Layouts inactive, lets fallback to usual PHP looped templates
			  	//Emerson: Qualified theme templates should contain WP loops for WC hooks and Views to work
			  	$is_theme_template_looped= $this->check_if_php_template_contains_wp_loop($values, $is_using_genesis);	          	
			  }
			  
			  //Add those qualified PHP templates only once
			  if ($is_theme_template_looped) {
			  	$correct_templates_list[$key]=$values;
			  } elseif ($is_theme_template_has_ddlayout) {
			  	 //This has a call to ddlayout
			  	$correct_templates_list[$key]=$values;	          	
			  }
		   }      
		}
		   
		   if (!(empty($correct_templates_list))) {
	
			   //Has templated loops to return
		   	   $correct_templates_list['Use WooCommerce Default Templates']='Use WooCommerce Default Templates';
	
		   	   //Append the template name to the file names
		   	   $correct_template_list_final=$this->wcviews_append_templatename_to_templatefilename($correct_templates_list,$headers_for_theme_files,$get_custom_theme_info);
		   	   
		   	   //Include WooCommerce Views Default single-product.php template
		   	   if(defined('WOOCOMMERCE_VIEWS_PLUGIN_PATH')) {
		   	   	 
		   	   	$template_path=WOOCOMMERCE_VIEWS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'single-product.php';	       	   
		   	   	
		   	   	if (file_exists($template_path)) {
		   	   		//Template exist
		   	   		$correct_template_list_final[ $this->wcviews_the_template_name ]=$template_path;       	   		
		   	   	}	       	   	
		   	   	 
		   	   }
		   	   
			   return $correct_template_list_final; 
					 
		   } else {
			   //In this scenario, no eligible templates are found from the clients theme.
			   //Let's provide the defaults from templates inside the WooCommerce Views plugin
			   
		   		$correct_templates_list['Use WooCommerce Default Templates']='Use WooCommerce Default Templates';
		   	
		   		if(defined('WOOCOMMERCE_VIEWS_PLUGIN_PATH')) {
		   		 
		   			$template_path=WOOCOMMERCE_VIEWS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'single-product.php';	       			
		   	
		   			if (file_exists($template_path)) {
		   			//Template exist
		   				$template_name_layouts	= $this->wcviews_the_template_name.' '.'(single-product.php)';
		   				$correct_templates_list[ $template_name_layouts ]=$template_path;
		   			}
		   		}	       
		   	
			   return $correct_templates_list;
		   }
		}
	}
	
	/**
	 * Method for verifying if the template has a call to ddlayout (with child theme compatibility)
	 * $file is a complete path to the theme directory to be checked
	 * @access public
	 * @return string
	 */
		
	public function wcviews_template_have_layout( $file )
	{
	
		$bool = false;
	
		$file_abs_child  = get_stylesheet_directory() . '/' . $file;	
		$file_abs_parent = get_template_directory() . '/' . $file;
		
		//Check for file existence 
		//In WordPres child themes, if template exist in child theme directory
		//It overrides the parent, so check if it exists in child first
		//If not check if it exists on parent directory
		
		$file_abs ='';
		
		if (file_exists($file_abs_child)) {
			//It exists in child
			$file_abs = $file_abs_child;			
		} elseif (file_exists($file_abs_parent)) {
			$file_abs = $file_abs_parent;	
		}

		if (!(empty($file_abs))) {
			//Let's retrieved the contents of this template
			$file_data = @file_get_contents( $file_abs );
	
			if ($file_data !== false) {
				if (strpos($file_data, 'the_ddlayout') !== false) {
					$bool = true;
				}
			}	
		}	 
	
		return $bool;
	}	
	
	/**
	 * Method for getting the theme name based on template path
	 * @access public
	 * @return string
	 */	
	
	public function get_theme_name_based_on_path($template_path,$get_custom_theme_info) {		
		
		//Set empty string
		$theme_name='';
		
		//Get the first degree folder path
		$template_belong=basename(dirname($template_path));		

		if (isset($get_custom_theme_info[$template_belong])) {
			 
			$theme_name=$get_custom_theme_info[$template_belong];
			 
		} else {
			
			//Maybe an internal folder, climb one step			
			$template_belong=basename(dirname(dirname($template_path)));
			if (isset($get_custom_theme_info[$template_belong])) {
				
				$theme_name=$get_custom_theme_info[$template_belong];
			}
		}
		
		return $theme_name;
	}
	
	/**
	 * Method for appending Theme names to templates for clarity purposes.
	 * @access public
	 * @return array
	 */	
	
	public function wcviews_append_templatename_to_templatefilename($correct_template_list,$headers_for_theme_files,$get_custom_theme_info) {
	
	  $correct_template_list_final=array();
	
	  //The defaults array
	  $defaults_name_array=array('page.php'=>__('Theme Page Template','woocommerce_views'),'single.php'=>__("Theme Single Posts Template","woocommerce_views"));
	  
	  if (is_array($correct_template_list)) {
	
		//Loop through the correct template list
		foreach ($correct_template_list as $template_file_name=>$template_path) {
			
			//Check if the template filename is in the WP core default page template name array
		   if (isset($headers_for_theme_files[$template_file_name])) {
			   //contain in default array
			   $template_name_retrieved=$headers_for_theme_files[$template_file_name];
			   $theme_name=$this->get_theme_name_based_on_path($template_path,$get_custom_theme_info);
			   
			   //Append names correctly
			   $theme_append=$this->theme_append_wcviews_name_correctly($theme_name);
			   $belongs_to_theme=$theme_name.' '.$theme_append;	           
			   $template_name_appended="$belongs_to_theme $template_name_retrieved";
			   $correct_template_list_final[$template_name_appended]=$template_path;           
	
		   } elseif (isset($defaults_name_array[$template_file_name])) {
			   //not included in default WP core page array
			   //Check if included in basic template name array
	 		   $template_name_retrieved=$defaults_name_array[$template_file_name];

	 		   //Append theme name for clarity
	 		   $theme_name=$this->get_theme_name_based_on_path($template_path,$get_custom_theme_info);	 

	 		   //Get correct theme name append
	 		   $theme_append=$this->theme_append_wcviews_name_correctly($theme_name);
			   
	 		   if (empty($theme_append)) {
	 		   	  //Theme name already contains 'theme' word, remove 'theme' from $template_name_retrieved
	 		   	   $template_name_retrieved=str_replace('Theme', '', $template_name_retrieved);	 		   	
	 		   }
	 		   $template_name_appended="$theme_name $template_name_retrieved";
			   $correct_template_list_final[$template_name_appended]=$template_path;          
	
		   } elseif ($template_file_name != 'Use WooCommerce Default Templates') {
				//No match, dissect the filename
				
		   		//Append theme name for clarity
		   		$theme_name=$this->get_theme_name_based_on_path($template_path,$get_custom_theme_info);
		   	
				$dissected_template_file_name=$this->dissect_file_name_to_convert_to_templatename($template_file_name,$theme_name);
				$dissected_template_file_name= $theme_name.' '.$dissected_template_file_name;
				$correct_template_list_final[$dissected_template_file_name]=$template_path;
		   } else {			
				$correct_template_list_final['Use WooCommerce Default Templates']='Use WooCommerce Default Templates';
		   }
	
		}
		
		return $correct_template_list_final; 
	
	  }
	
	}
	
	/**
	 * Return 'theme' word if the theme name does not have it.
	 * @access public
	 * @return string
	 */	
	public function theme_append_wcviews_name_correctly($theme_name) {
		
		$theme_name=strtolower($theme_name);
		
		if ((strpos($theme_name, 'theme') !== false)) {
				
			//Found
			$theme_append='';
			
		} else {
			
			//Not found
			$theme_append=__('Theme','woocommerce_views');
		}	

		return $theme_append;
		
	}
	
	public function dissect_file_name_to_convert_to_templatename($template_file_name,$theme_name) {
	
		$exploded_template_file_name=explode(".",$template_file_name);
	
		$is_a_page_template = $this->wcviews_array_find('page', $exploded_template_file_name);
		$is_a_single_template = $this->wcviews_array_find('single', $exploded_template_file_name);
		$is_a_product_template = $this->wcviews_array_find('product', $exploded_template_file_name);
		$is_a_layouts_template= $this->wcviews_array_find('layouts', $exploded_template_file_name);
		$is_a_prod_archive_template= $this->wcviews_array_find('archive-product', $exploded_template_file_name);
	
		//Append word 'Theme' only when its not on the theme name		
		$theme_append=$this->theme_append_wcviews_name_correctly($theme_name);
		
		if ($is_a_page_template !== false) {	        
			$custom_page_template=$theme_append.' '.__('Custom Page Template','woocommerce_views');
			return $custom_page_template;
		} elseif ($is_a_single_template !== false) {	
			
			//This is a single template, let's check if this is a product
			if ($is_a_product_template  !== false) {
				//Product!
				//Let's check if this is a Layouts template
				
				if ($is_a_layouts_template  !== false) {
					
					//Layouts Template!
					$custom_post_template=$theme_append.' '.__('Custom Product Layouts Template','woocommerce_views');
					
				} else {
					//Not a Layouts template!
					$custom_post_template=$theme_append.' '.__('Custom Product Template','woocommerce_views');					
				}
				
			} else {
				//Nope
				$custom_post_template=$theme_append.' '.__('Custom Post Template','woocommerce_views');				
			}
			
			return $custom_post_template;
		} else {
				
			if ($is_a_prod_archive_template  !== false) {		
				$custom_template=$theme_append.' '.__('Custom Product Archive Template','woocommerce_views');
			} else {
				$custom_template=$theme_append.' '.__('Custom Template','woocommerce_views');
			}
			
			return $custom_template;
		}
	 
	}
	
	public function check_if_php_template_contains_wp_loop($template,$is_using_genesis=false) {
	
		$handle = fopen($template, "r");
		/**
		 * WooCommerce Views 2.5.6 ++
		 * Check for empty templates and bail out processing if we found one.
		 */
		$template_filesize= filesize($template);		
		if ( 0 === $template_filesize) {
			return FALSE;	
		}
		
		$contents = fread($handle,$template_filesize);
		$pieces = explode("\n", $contents);
		$have_post_key = $this->wcviews_array_find('have_posts()', $pieces);
		$the_post_key = $this->wcviews_array_find('the_post()', $pieces);
		$the_loop_key = $this->wcviews_array_find('loop', $pieces);
	
		$the_genesis_key =false;
		
		if ($is_using_genesis) {
			//Genesis Framework Activated, check if this is single products
			$template_basename= basename($template);
			if ('single-product.php' == $template_basename) {
				$the_genesis_key = $this->wcviews_array_find('genesis()', $pieces);
			}
		}
		fclose($handle);
	
		if ((($have_post_key) && ($the_post_key)) || ($the_loop_key) || ($the_genesis_key)) {
			
			/**
			 * Default requirements are meet
			 */
			
			return TRUE;
			
		} elseif ( 'single-product.php' == basename($template)) {
			
			/** 
			 * WooCommerce Views 2.5.6+
			 * Some themes uses single-product.php in their own theme /woocommerce folder
			 * But uses a different custom calls which is unique for their own theme
			 * Let's provide an exemption on this so user can use this template in
			 * Views -> WooCommerce Views -> Product Template File
			 */
			
			return TRUE;
			
		} else {
			
			/**
			 * At this point, this template under processing is not usable as WooCommerce single product templates
			 */
			
			return FALSE;
		}
	}
	
	public function wcviews_array_find($needle, $haystack, $search_keys = false) {
		if(!is_array($haystack)) return false;
		foreach($haystack as $key=>$value) {
			$what = ($search_keys) ? $key : $value;
			if(strpos($what, $needle)!==false) return $key;
		}
		return false;
	}
	
	/**
	 * Adds admin notice.
	 */
	public function wcviews_help_admin_notice(){
		global $pagenow;
		
		/** Let's show this notice only there are WooCommerce products exist AND all required plugins are set! */
		
		//Check if we have any WooCommmerce products here
		$wc_products_available=$this-> wc_views_get_all_product_ids_clean();
		
		//Check if required plugins are settled
		$required_plugins=$this->check_missing_plugins_for_woocommerce_views();
		
		if ((!(empty($wc_products_available))) && (empty($required_plugins))) {
			//In this case, there are products available and all required plugins are there. OK proceed.
			if ( $pagenow == 'plugins.php' ) {
				//Show this only in plugins page
				if(!get_option('dismiss_wcviews_notice')){
	 				
		 		   //Show this notice if products exists and not using embedded Types/Views and NOT a fresh installation
					$embedded_packager=$this->wcviews_check_requisite_toolset_packager_import();
					if ((!defined('WPVDEMO_VERSION'))){
						if (!($embedded_packager)) {
						//Products exists
						//Admin URL to plugins page
						$admin_url_wcviews=admin_url().'admin.php?page=wpv_wc_views&update_needed=true';
						?>
					<div id="message" class="updated message fade"
						style="clear: both; margin-top: 5px;">
						<p><?php _e('WooCommerce Views needs to scan your products once and create calculated fields for Views filters.','woocommerce_views');?> <a
								href="<?php echo $admin_url_wcviews;?>"><strong><?php _e('Run this scan now','woocommerce_views');?></strong></a>
						</p>
					</div>
					<?php	
						//Show this message only once		    				
						}
					} 
					update_option('dismiss_wcviews_notice', 'yes');
				}
	   		}
		}
	}
	
	//Reset dismiss_wcviews_notice option after deactivation
	public function wcviews_request_to_reset_field_option() {
	  delete_option('dismiss_wcviews_notice');
	}
	/**
	 * Adds question mark icon
	 * @return <type>
	 */
	public function add_media_button($output){
		// avoid duplicated question mark icons (post-new.php)
		$pos = strpos($output, "Insert Types Shortcode");
		
		if($pos == false && !(isset($_GET['post_type']) && $_GET['post_type'] == 'view')){
			$output .= '<ul class="editor_addon_wrapper"><li><img src="'. plugins_url() . '/' . basename(dirname(__FILE__)) . "/res/img/question-mark-icon.png" .'"><ul class="editor_addon_dropdown"><li><div class="title">Learn how to use these Views</div><div class="close">&nbsp;</div></li><li><div>These Views let you insert product sliders, grids and tables to your content. <br /><br /><a href="https://toolset.com/documentation/views-inside/woocommerce-views/" target="_blank" style="text-decoration: underline; font-weight: bold; color: blue;">Learn how to use these Views</a></div></li></ul></li></ul>';
		}
	
		return $output;
	}
	
	/**
	 * Adds "OLD" CSS and Custom JS for Views
	 */
	public function additional_css_js() {
	
	//Everything about this is only needed in the WC Views settings page, so load only on this page
	
		$screen_output_wc_views= get_current_screen();
		$screen_output_id= $screen_output_wc_views->id;
	
		//Get backward/forward compatible screen ID
		$canonical_screen_id = $this->wcviews_unified_current_screen();
		if ( $canonical_screen_id == $screen_output_id) {
			
			//Tooltips
			$font_awesome = plugins_url() . '/' . basename(dirname(__FILE__)) . '/res/css/font-awesome/css/font-awesome.min.css';
			wp_enqueue_style('wcviews-fontawesome', $font_awesome,array(),WC_VIEWS_VERSION);
			
			//Main style
			$stylesheet = plugins_url() . '/' . basename(dirname(__FILE__)) . '/res/css/wcviews-style.css';	
			wp_enqueue_style('wcviews-style', $stylesheet,array('wcviews-fontawesome'),WC_VIEWS_VERSION);
			wp_enqueue_script('jquery');			

		}	
	}
	
	//
	//
	//
	//
	//
	//
	// Merged with other plugin
	/*Not anymore used starting version 2.0, public function remains for backward compatibility*/
	public function wpv_woo_add_to_cart($atts) {
	
		_deprecated_function( 'wpv-wooaddcart', '2.5.1','wpv-woo-buy-options' );
	}
	
	/**Emerson: NEW VERSION
	[wpv-woo-buy-or-select]
	Description: Displays 'add to cart' or 'select' button in product listings.
	Will work only in product listing or main shop page.
	
	Attributes/Parameters:
	
	add_to_cart_text = Set the text in the simple product button if desired.
	link_to_product_text = Set the text in the variation product button if desired.
	
	Example using the two attributes: 
	
	[wpv-woo-buy-or-select add_to_cart_text="Buy this now" link_to_product_text="Product options"]
	
	Defaults to WooCommerce text.
	**/
	public function add_to_cart_buy_or_select_closures( $argument_one = null, $argument_two = null ) {
	
		$is_using_revised_wc	= $this->wcviews_using_woocommerce_two_point_one_above();
		
		if ($is_using_revised_wc) {
			//Check product type
			$product_type_passed	= $this->wc_views_get_product_type( $argument_two );
			if ('simple' == $product_type_passed ) {	
				global $add_to_cart_text_product_listing_translated;
				$add_to_cart_text_product_listing_translated= trim( $add_to_cart_text_product_listing_translated );
				if ((isset( $add_to_cart_text_product_listing_translated ) ) && ( !( empty( $add_to_cart_text_product_listing_translated ) ) ) ) {
					$argument_one = $add_to_cart_text_product_listing_translated;
				}
				
			} elseif ('grouped' == $product_type_passed ) {				
				global $group_add_to_cart_text_product_listing_translated;
				$group_add_to_cart_text_product_listing_translated = trim( $group_add_to_cart_text_product_listing_translated );
				if ( ( isset( $group_add_to_cart_text_product_listing_translated ) ) && ( !( empty( $group_add_to_cart_text_product_listing_translated ) ) ) ) {
					$argument_one = $group_add_to_cart_text_product_listing_translated;
				}				
			}
			//Fixed missing external button text in WooCommerce 2.7.0
			if ( ( 'external' == $product_type_passed ) && ( true === $this->wc_views_two_point_seven_above() ) ) {
				if ( empty( $argument_one ) ) {
					$argument_one = __( 'Buy product', 'woocommerce' );
				}
			}

			return $argument_one;
			
		} else {
		   //Old WC
			global $add_to_cart_text_product_listing_translated;
			return $add_to_cart_text_product_listing_translated;       
			
		}
	
	}
	
	/** External add to cart text handler */
	/** Since version 2.5.5 */
	
	public function external_add_to_cart_buy_or_select_closures($argument_one=null,$argument_two=null) {

		$is_using_revised_wc= $this->wcviews_using_woocommerce_two_point_one_above();
		
		if ($is_using_revised_wc) {
			//Check product type
			$product_type_passed	= $this->wc_views_get_product_type( $argument_two );	    	
			if ('external' ==$product_type_passed) {	
				global $external_add_to_cart_text_product_listing_translated;				
				$external_add_to_cart_text_product_listing_translated=trim($external_add_to_cart_text_product_listing_translated);
				if ((isset($external_add_to_cart_text_product_listing_translated)) && (!(empty($external_add_to_cart_text_product_listing_translated)))) {
					$argument_one= $external_add_to_cart_text_product_listing_translated;
				}				
			}			
			return $argument_one;
			
		} else {
			
		   //Old WC
			global $add_to_cart_text_product_listing_translated;
			return $add_to_cart_text_product_listing_translated;       
			
		}	
				
	}
	
	public function add_to_cart_buy_or_select_closures_listing($argument_one=null,$argument_two=null) {
	
		$is_using_revised_wc= $this->wcviews_using_woocommerce_two_point_one_above();
		
		if ($is_using_revised_wc) {
			//Check product type
			$product_type_passed	= $this->wc_views_get_product_type( $argument_two );			
			if ( in_array( 
				$product_type_passed, 
				$this->get_known_variable_product_types() 
			) ) {
	
				global $link_product_listing_translated;
				return $link_product_listing_translated;
	
			} else {
				return $argument_one;
	
			}
	
		} else {
		  //Old WC
		  global $link_product_listing_translated;
		  return $link_product_listing_translated;
		  
		}
	}
	
	public function wpv_woo_buy_or_select_func($atts) {	
		
		/*Add to cart in loops	  
		 */
	
		global $post, $wpdb, $woocommerce;
		
		if ( 'product' == $post->post_type ) {        
		   
			//Run only on page with products       
			
			$product =$this->wcviews_setup_product_data($post);
			
			if (isset($atts['add_to_cart_text'])) {
				//User is setting add to cart text customized	
				 if (!(empty($atts['add_to_cart_text']))) {
					$add_to_cart_text_product_listing=trim($atts['add_to_cart_text']);
					
					//START support for string translation
					if (function_exists('icl_register_string')) {
						//Register add to cart text product listing for translation
						icl_register_string('woocommerce_views', 'add_to_cart_text',$add_to_cart_text_product_listing);
					}
					global $add_to_cart_text_product_listing_translated;  
					if (!function_exists('icl_t')) {
						//String translation plugin not available use original text
						$add_to_cart_text_product_listing_translated=$add_to_cart_text_product_listing;
						 
					} else {
						//String translation plugin available return translation
						$add_to_cart_text_product_listing_translated=icl_t('woocommerce_views', 'add_to_cart_text',$add_to_cart_text_product_listing);
					}				
					
					$is_using_revised_wc_simple=$this->wcviews_using_woocommerce_two_point_one_above();
					
					if ($is_using_revised_wc_simple) {
					
						//Updated WC
						add_filter('woocommerce_product_add_to_cart_text',array(&$this,'add_to_cart_buy_or_select_closures'),10,2);
							
					} else {
					
						//Old WC
						add_filter('add_to_cart_text', array(&$this,'add_to_cart_buy_or_select_closures'));
							
					}				
					
			
				}
			}
			
			/** START -group product add to cart text */
			if (isset($atts['group_add_to_cart_text'])) {
				//User is setting add to cart text customized
				if (!(empty($atts['group_add_to_cart_text']))) {
					$group_add_to_cart_text_product_listing=trim($atts['group_add_to_cart_text']);
						
					//START support for string translation
					if (function_exists('icl_register_string')) {
						//Register add to cart text product listing for translation
						icl_register_string('woocommerce_views', 'group_add_to_cart_text',$group_add_to_cart_text_product_listing);
					}
					global $group_add_to_cart_text_product_listing_translated;
					if (!function_exists('icl_t')) {
						//String translation plugin not available use original text
						$group_add_to_cart_text_product_listing_translated=$group_add_to_cart_text_product_listing;
							
					} else {
						//String translation plugin available return translation
						$group_add_to_cart_text_product_listing_translated=icl_t('woocommerce_views', 'group_add_to_cart_text',$group_add_to_cart_text_product_listing);
					}
	 				
					//Updated WC
					add_filter('woocommerce_product_add_to_cart_text',array(&$this,'add_to_cart_buy_or_select_closures'),10,2);
				}
			}
			
			/** END */

			/** START -external product add to cart text */
			if (isset($atts['external_add_to_cart_text'])) {
				//User is setting add to cart text customized
				if (!(empty($atts['external_add_to_cart_text']))) {
					$external_add_to_cart_text_product_listing=trim($atts['external_add_to_cart_text']);
			
					//START support for string translation
					if (function_exists('icl_register_string')) {
						//Register add to cart text product listing for translation
						icl_register_string('woocommerce_views', 'external_add_to_cart_text',$external_add_to_cart_text_product_listing);
					}
					global $external_add_to_cart_text_product_listing_translated;
					if (!function_exists('icl_t')) {
						//String translation plugin not available use original text
						$external_add_to_cart_text_product_listing_translated=$external_add_to_cart_text_product_listing;
						 
					} else {
						//String translation plugin available return translation
						$external_add_to_cart_text_product_listing_translated=icl_t('woocommerce_views', 'external_add_to_cart_text',$external_add_to_cart_text_product_listing);
					}
					 
					//Updated WC
					if ( true === $this->wc_views_two_point_seven_above() ) {
						add_filter( 'woocommerce_product_add_to_cart_text', array( $this,'external_add_to_cart_buy_or_select_closures'), 10, 2 );
					} else {
						//Backward compatibility
						add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this,'external_add_to_cart_buy_or_select_closures'), 10, 2 );
					}
					
				}
			}	         
			/** END */	        

			if (isset($atts['link_to_product_text'])) {
				//User is setting link to product text customized
	 		   if (!(empty($atts['link_to_product_text']))) {       
				$link_product_listing=trim($atts['link_to_product_text']);
	
				//START support for string translation
				if (function_exists('icl_register_string')) {
					//Register add to cart text product listing for translation
					icl_register_string('woocommerce_views', 'link_to_product_text',$link_product_listing);
				}
				global $link_product_listing_translated;
				if (!function_exists('icl_t')) {
					//String translation plugin not available use original text
					$link_product_listing_translated=$link_product_listing;
				 
				} else {
					//String translation plugin available return translation
					$link_product_listing_translated=icl_t('woocommerce_views', 'link_to_product_text',$link_product_listing);
				}
				
				//END support for string translation        
				$is_using_revised_wc=$this->wcviews_using_woocommerce_two_point_one_above();
				
				if ($is_using_revised_wc) {
				
					add_filter('woocommerce_product_add_to_cart_text',array(&$this,'add_to_cart_buy_or_select_closures_listing'),10,2);
				
				} else {
	
					add_filter('variable_add_to_cart_text',array(&$this,'add_to_cart_buy_or_select_closures_listing'));
	
				}
				
	
			  }
			
			}
			
			//Let's check the rendering template based on quantity field parameter
			//https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/193031409/comments
			if (isset($atts['show_quantity_in_button'])) {
				$show_quantity=	$atts['show_quantity_in_button'];
				$show_quantity =strtolower($show_quantity);
					
				if ('yes' == $show_quantity) {
					//User wants to display quantities next to add to cart button in Loops	        		
					add_filter( 'wc_get_template', array(&$this,'custom_add_to_cart_template_with_qty'),15,5);	
							
				} else {
					
					remove_filter( 'wc_get_template', array(&$this,'custom_add_to_cart_template_with_qty'),15,5);
					
				}
			} else {

				remove_filter( 'wc_get_template', array(&$this,'custom_add_to_cart_template_with_qty'),15,5);
				
			}
			
			
			if (isset($product)) {
				ob_start();	
				if (isset($atts['show_variation_options'])) {
				   //Variation option is set
				   $show_variation_options=trim($atts['show_variation_options']);
				   $show_variation_options=strtolower($show_variation_options);
				   if ('yes' == $show_variation_options) {
				   	//User wants to display variation options on listing pages
					   	if ( in_array( 
							$this->wc_views_get_product_type( $product ), 
							$this->get_known_variable_product_types() 
						) ) {
					   		//This is a variable product, display.
					   		do_action( 'woocommerce_variable_add_to_cart');
					   	} else {
							//Not variable product, ignore it just display the usual thing
					   		woocommerce_template_loop_add_to_cart();
					   	}
				   } else {
				   	 	//Here user sets a different value for show variation options, its not 'yes', so just display the usual thing.
				   		woocommerce_template_loop_add_to_cart();
				   }
				} else {
					//Variation option is not yet, just display normally	
					woocommerce_template_loop_add_to_cart();
				}
					
				/**Let's marked this shortcode execution */
				$this->wcviews_shortcode_executed();
				
				return ob_get_clean();
			} else {
				 return '';
			}  	
		}
	}
	
	/**
	 * List all known variable-like product types, like 'variable-subscription' created by the WooCommerce Subscriptions addon.
	 *
	 * @return array
	 *
	 * @since 2.7.6
	 */
	public function get_known_variable_product_types() {
		return apply_filters( 'woocommerce_views_known_variable_product_types', array( 'variable', 'variable-subscription' ) );
	}
	
	/**
	 * Use custom add to cart listing button template for displaying quantities when a user needs it.
	 * Template comes from WooCommerce: http://docs.woothemes.com/document/override-loop-template-and-show-quantities-next-to-add-to-cart-buttons/
	 * @access public
	 * @return string
	 */	
	
	public function custom_add_to_cart_template_with_qty($located, $template_name, $args, $template_path, $default_path ) {		

		//Ensure we are filtering correctly..
		if ('loop/add-to-cart.php' == $template_name) {
			
			global $new_wc_codes;
			$new_wc_codes= $this->wcviews_using_woocommerce_two_point_one_above();
			
			/**
			 * WooCommerce 2.7.0 compatibility
			 */
			global $new_wc_crudclasses;
			$new_wc_crudclasses	= $this->wc_views_two_point_seven_above();
						
			//Yes, we are filtering the add to cart loop template
			//Define new $located
			//Get setting on 'Enable AJAX add to cart buttons on archives'
			$woocommerce_enable_ajax_add_to_cart=get_option('woocommerce_enable_ajax_add_to_cart');
			
			/** WooCommerce Views 2.5.5 + */
			/** Added support for ajax add to cart with Quantity fields */
			if ('yes' == $woocommerce_enable_ajax_add_to_cart) {
				
			   /** Load ajax add to cart with quantity fields template */	
				$located=WOOCOMMERCE_VIEWS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'custom_shortcode_templates'.DIRECTORY_SEPARATOR.'add_to_cart_with_quantity_ajax.php';
			
			} else {
				
			   /** Non ajax */
			   $located=WOOCOMMERCE_VIEWS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'custom_shortcode_templates'.DIRECTORY_SEPARATOR.'add_to_cart_with_quantity.php';
			}			
			
		}
		
		return $located;		
	}
	
	/**
	 * Loads a JS on footer for displaying quantities when a user needs it with AJAX
	 * Template comes from WooCommerce: https://gist.github.com/claudiosmweb/5114131
	 * @access public
	 * @return string
	 */
		
	public function wcviews_custom_loop_addtocart_ajax_with_quantity() {
		if ( defined('WCVIEWS_DOING_LOOP') ) {
			if (WCVIEWS_DOING_LOOP) {
				//We only need this IF
				//WooCommerce setting 'Enable AJAX add to cart buttons on archives' is checked.
				$woocommerce_enable_ajax_add_to_cart=get_option('woocommerce_enable_ajax_add_to_cart');
					
				/** WooCommerce Views 2.5.5 + */
				/** Added support for ajax add to cart with Quantity fields */
				if ('yes' == $woocommerce_enable_ajax_add_to_cart) {
				?>
					<script>
					jQuery( document ).ready( function( $ ) {
						$( document ).on( 'change', '.quantity .qty', function() {
							$( this ).parent( '.quantity' ).next( '.add_to_cart_button' ).data( 'quantity', $( this ).val() ).attr( 'data-quantity', $( this ).val() );
						});
					});
					</script>
		<?php
				}
			}
		} 
	}
		
	/**Emerson: NEW VERSION
	[wpv-woo-product-price]
	Description: Displays the product price in product listing and single product pages.
	**/
	
	public function wpv_woo_product_price_func($atts) {	
		 
	   global $post,$woocommerce;    
		  
	   $product =$this->wcviews_setup_product_data($post);
	   if (isset($product)) {
	
	   $product_price=$product->get_price_html();
	   
	   /*https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/177801662/comments */
	   /*Output the p class='price' wrapper as part of the price shortcode*/
	   /*Emerson: Added a filter, allowing user to override if needed*/
	   
	   $wrapper_start= apply_filters('wc_views_price_start_wrapper','<p class="price">');
	   $wrapper_end= apply_filters('wc_views_price_end_wrapper','</p>');
	   
	   //Now with wrapper
	   $product_price_with_wrapper = $wrapper_start.$product_price.$wrapper_end;
	   
	   /**Let's marked this shortcode execution */
	   $this->wcviews_shortcode_executed();
	   
	   return $product_price_with_wrapper;   
	   }
	
	}
	
	//
	/**Emerson: NEW VERSION
	Juan: This needs a deep review
	 [wpv-woo-product-image]
	Description: Displays the product image, which starts with the featured image and changes to the variation image.
	
	$atts: size
	Options:
	
	WordPress image sizes (configured at Settings --> Media --> Image sizes):
	
	thumbnail = Wordpress image thumbnail size e.g. 150x150
	medium    = Wordpress image medium size e.g. 300 x 300
	large = Wordpress full image size e.g. 1024 x 1024
	
	WooCommerce specific image sizes (configured at WooCommerce --> Settings --> Catalog --> Image Options)
	shop_single = single product page size equivalent to medium ("Single Product Image")
	shop_catalog= smaller than thumbnail images ("Catalog Images").
	shop_thumbnail =similar to Wordpress thumbnail size ("Product Thumbnails").
	
	Example usage:
	[wpv-woo-product-image size="thumbnail"]
	[wpv-woo-product-image size="shop_single"]
	[wpv-woo-product-image size="medium"]
	
	Defaults to shop_single
	**/
	
	public function wcviews_set_image_size_closures() {
		global $attribute_image_size;	
	
		return $attribute_image_size;
	}
	
	public function wpv_woo_product_image_func($atts) {    
			
		//Filter for display of galleries in listings
		global $wcviews_show_gallery_on_listings;
		
		$filters_to_remove = array();
		
		/**
		 * By default , we don't show gallery on listings along with main image
		 * Only if set via gallery_on_listings parameter
		 */
		$wcviews_show_gallery_on_listings = false;		
		if ((isset($atts)) && (!(empty($atts)))) {
			
			//Process size attributes
			if (isset($atts['size'])) {  		
					global $attribute_image_size;
					$attribute_image_size=$atts['size'];		
					if ( true === $this->wc_views_two_point_seven_above() ) {
						//WooCommerce 2.7.0 compatibility
						add_filter('post_thumbnail_size',array( &$this,'wcviews_set_image_size_closures') );
						$filters_to_remove[] = array(
							'hook' => 'post_thumbnail_size',
							'callback' => array( &$this,'wcviews_set_image_size_closures'),
							'priority' => 10,
							'args' => 1
						);
					} else {
						add_filter('single_product_large_thumbnail_size', array( &$this,'wcviews_set_image_size_closures' ) );
						$filters_to_remove[] = array(
							'hook' => 'single_product_large_thumbnail_size',
							'callback' => array( &$this,'wcviews_set_image_size_closures'),
							'priority' => 10,
							'args' => 1
						);
					}						
			}
						
			//Filter for raw image output, not image link
			//Thumbnail gallery needed
			add_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			
			if (isset( $atts['output'] ) ) {
				if ( $atts['output'] == 'img_tag' ) {
					//Remove raw URL filter
					remove_filter( 'woocommerce_single_product_image_html',array( &$this,'show_raw_image_url_wc_views' ),20,2 );
					
					//Add img filter
					add_filter( 'woocommerce_single_product_image_html',array( &$this,'show_raw_image_html_wc_views' ),10,2 ); 
					$filters_to_remove[] = array(
						'hook' => 'woocommerce_single_product_image_html',
						'callback' => array( &$this,'show_raw_image_html_wc_views'),
						'priority' => 10,
						'args' => 2
					);
					
				} elseif ($atts['output']=='raw') {
					
					//Remove img filter
					remove_filter( 'woocommerce_single_product_image_html',array( &$this,'show_raw_image_html_wc_views' ),10,2 ); 
					
					//Add img raw filter
					add_filter( 'woocommerce_single_product_image_html',array(&$this,'show_raw_image_url_wc_views' ),20,2 );
					$filters_to_remove[] = array(
						'hook' => 'woocommerce_single_product_image_html',
						'callback' => array( &$this,'show_raw_image_url_wc_views'),
						'priority' => 20,
						'args' => 2
					);
					
					/**WooComerce 2.7.0 compatibility filter - NEW */
					if ( $this->wc_views_two_point_seven_above() ) {
						add_filter( 'woocommerce_single_product_image_thumbnail_html' ,array(&$this,'show_raw_image_url_wc_views' ), 20, 1 );
						$filters_to_remove[] = array(
							'hook' => 'woocommerce_single_product_image_thumbnail_html',
							'callback' => array( &$this,'show_raw_image_url_wc_views'),
							'priority' => 20,
							'args' => 1
						);
						//Thumbnail gallery not needed in raw
						remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
					}						
				}   
			}
			
			if ( isset( $atts['gallery_on_listings'] ) ) {
				//Retrieved value
				$gallery_on_listings= $atts['gallery_on_listings'];
				if (!(empty($gallery_on_listings))) {	            		
					$gallery_on_listings = strtolower($gallery_on_listings);
					/**
					 * yes = show gallery on listings
					 * no  = don't show gallery on listings
					 */
					if ('yes' == $gallery_on_listings) {
						//Show gallery on listings
						//TRUE means display	            			
						$wcviews_show_gallery_on_listings = true;
						
						/**
						 * WooCommerce 2.7.0 gallery markup is not anymore compatibible on loops
						 * Add a fall back to use legacy gallery thumbnail markup for users to be able to use
						 * gallery on listings.
						 */
						if  ( $this->wc_views_two_point_seven_above() ) {
							//WooCommerce 2.7.0 above here	            				
							remove_filter('woocommerce_product_get_gallery_image_ids',array( $this,'remove_gallery_on_main_image_at_listings'), 20, 2 );           				
						}	            			
					}
				}
			}	            
   		
		}
	
		//Reordered
		ob_start();	
		global $post,$woocommerce;	
		$product =$this->wcviews_setup_product_data($post);
		
		if ( ( true === $wcviews_show_gallery_on_listings ) && ( is_object( $product) ) ) {
			//Show gallery mode	
			//Resize galleries automatically based on user settings
			if  ( $this->wc_views_two_point_seven_above() ) {
				//We check if this product has galleries
				$attachment_ids = $product->get_gallery_image_ids();
				if ( ( !empty( $attachment_ids) ) && ( is_array( $attachment_ids) ) ) {
					if ( ( isset( $atts['output'] ) ) && ( 'raw' != $atts['output'] ) ) {
						//Not needed in raw format
						add_action( 'woocommerce_product_thumbnails', array( $this,'wcviews_inject_resizing_gallery_filter'), 1 );	
					}
				}
			}		
		}
		//Fix placeholder image size for those without featured image set
		if (!(has_post_thumbnail())) {
			if  ( $this->wc_views_two_point_seven_above() ) {
				//WC 3.0+
				if ( ( isset( $atts['output'] ) ) && ( 'img_tag' === $atts['output'] ) ) {
					//Image tag mode	
					add_filter( 'woocommerce_placeholder_img' ,array( $this,'adjust_wc_views_image_placeholder' ), 20, 1 );
					$filters_to_remove[] = array(
						'hook' => 'woocommerce_placeholder_img',
						'callback' => array( $this,'adjust_wc_views_image_placeholder'),
						'priority' => 20,
						'args' => 1
					);
				} else {
					//Default mode
					add_filter( 'woocommerce_single_product_image_thumbnail_html' ,array( $this,'adjust_wc_views_image_placeholder' ), 20, 2 );
					$filters_to_remove[] = array(
						'hook' => 'woocommerce_single_product_image_thumbnail_html',
						'callback' => array( $this,'adjust_wc_views_image_placeholder'),
						'priority' => 20,
						'args' => 2
					);
				}				
			} else {
				//Backward compat
				add_filter('woocommerce_single_product_image_html',array(&$this,'adjust_wc_views_image_placeholder'),10,2);
				$filters_to_remove[] = array(
					'hook' => 'woocommerce_single_product_image_html',
					'callback' => array( &$this,'adjust_wc_views_image_placeholder'),
					'priority' => 10,
					'args' => 2
				);
			}					
		} else {
			if  ( $this->wc_views_two_point_seven_above() ) {
				//WC 3.0+
				if ( ( isset( $atts['output'] ) ) && ( 'img_tag' === $atts['output'] ) ) {
					//Image tag mode	
					remove_filter( 'woocommerce_placeholder_img' ,array( $this,'adjust_wc_views_image_placeholder' ), 20, 1 );
				} else {
					//Default mode
					remove_filter( 'woocommerce_single_product_image_thumbnail_html' ,array( $this,'adjust_wc_views_image_placeholder' ), 20, 2 );
				}				
			} else {
				//Backward compat
				remove_filter('woocommerce_single_product_image_html',array(&$this,'adjust_wc_views_image_placeholder'),10,2);
			}			
		}
		
		if (isset($product)) {
			/**
			 * WooCommerce Views 2.7.1
			 * When rendering image tag mode in listings
			 * Use standard WooCommerce loop image function for maximum compatibility
			 */
			if ( ( defined( 'WCVIEWS_DOING_LOOP') ) && ( isset( $atts['output'] ) ) && ( 'img_tag' === $atts['output'] ) ) {				
				if ( WCVIEWS_DOING_LOOP ) {
					//We are rendering image tag mode and we are in loops
					if ( !isset( $attribute_image_size ) ) {
						//Not set
						$attribute_image_size = '';
					}
					echo woocommerce_get_product_thumbnail( $attribute_image_size);
				}
			} else {
				//Anything else use the stadard product images render function
				woocommerce_show_product_images();
				
				if  ( $this->wc_views_two_point_seven_above() ) {
					//Remove this filter because its no longer needed.					
					remove_filter( 'wp_get_attachment_image_src', array( $this,'wcviews_resize_galleries'), 10, 4 );
				}
			}
			$image_content = ob_get_contents();
			//Image processing to remove Woocommerce <div> tags around the image HTML if user wants to output img_tag only or raw URL
			if (isset($atts['output'])) {
			   if (($atts['output']=='img_tag') || ($atts['output']=='raw')) {
					$image_content=trim(strip_tags($image_content, '<img>'));
				}
			}
			/**Let's marked this shortcode execution */
			$this->wcviews_shortcode_executed();
			
			ob_end_clean();
		} else {
			$image_content = ob_get_contents();
			
			/**Let's marked this shortcode execution */
			$this->wcviews_shortcode_executed();
			ob_end_clean();
		}
		
		/**
		 * WooCommerce Views 2.6.2
		 * Append hook output of woocommerce_before_single_product_summary before main image
		 */
		
		//Make this extensible
		if ( !(isset( $atts ) ) ) {
			$atts ='';
		}
		if ( !(isset( $post ) ) ) {
			$post = null;
		}
		
		/**WooCommerce Views 2.6.3
		 * Added new shortcode argument to wpv-woo-product-image to disable third party plugins/themes hooking on WooCommerce image.
		 * 
		 */
		//Is raw
		$is_raw = false;
		if ( isset( $atts['output'] ) ) {
			$output_format = $atts['output'];
			if ( ( !(empty( $output_format ) ) ) && ( is_string( $output_format ) ) ) {
				$output_format = strtolower( $output_format );
				$output_format = trim( $output_format );	    		
				if ( 'raw' == $output_format ) {
					//Raw format is used
					$is_raw = true;
				}
			}
		}
		//Disable third party plugin filters by default unless enabled by the user.
		$default_enable_third_party_filters = false;
		if ( isset( $atts['enable_third_party_filters'] ) ) {
			//enable_third_party_filters set..
			$enable_third_party_filters = $atts['enable_third_party_filters'];
			if ( ( !(empty( $enable_third_party_filters ) ) ) && ( is_string( $enable_third_party_filters ) ) ) {
				$enable_third_party_filters = strtolower( $enable_third_party_filters );
				$enable_third_party_filters = trim( $enable_third_party_filters );
				if ( 'yes' == $enable_third_party_filters ) {
					//User is enabling this. set to TRUE
					$default_enable_third_party_filters = true;
				}
			}
		}
		
		/**Compatibility with WooThumbs plugin */
		$is_woothumbs_activated = $this->is_woothumbs_plugin_activated();	    
		if ( true === $is_woothumbs_activated ) {
			$default_enable_third_party_filters = true;	    	
		}
		
		if ( true === $default_enable_third_party_filters ) {
			//Enabled third party filters, apply this filter to run third party hooks...
			//Filters is set to enabled however only if raw is not enabled
			if ( false === $is_raw ) {
				$image_content = apply_filters( 'wcviews_third_party_hooks_api_image', $image_content, $post, $atts );
			}    	
		}
		
		// Restore filters and actions to their state before executing this method
		foreach ( $filters_to_remove as $hook_to_remove ) {
			remove_filter(
				$hook_to_remove['hook'],
				$hook_to_remove['callback'],
				$hook_to_remove['priority'],
				$hook_to_remove['args']
			);
		}
		add_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
		
		return $image_content;	
		
	}	

	public function wc_views_render_compatibible_gallery_on_listings( $located, $template_name, $args, $template_path, $default_path ) {
		
		if ( ( 'single-product/product-thumbnails.php' === $template_name ) && ( defined('WCVIEWS_DOING_LOOP') ) ) {
			//We filter and provide a compatible gallery thumbnail path for WooCommerce 2.7.0 users.
			if  ( ( $this->wc_views_two_point_seven_above() ) && ( true === WCVIEWS_DOING_LOOP ) ) {
				$located	= WOOCOMMERCE_VIEWS_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'custom_shortcode_templates' . DIRECTORY_SEPARATOR . 'product-thumbnails.php';				
			}			
		}
		
		return $located;
		
	}
	/**
	 * Outputs the default WooCommerce on-sale badge icon appended to WooCommerce product image.
	 * Tested to work on single product and WooCommerce product listing pages (including shop page).
	 * IMPORTANT: For this to work , it should be placed right directly 'before' the product image shortcode:
	 * 
	 * @access public
	 * @return void
	 */
		
	public function wpv_woo_onsale_func($atts) {
	
		global $post,$woocommerce; 
		
		$product =$this->wcviews_setup_product_data($post);	
	
		if (isset($product)) {
		
			//Start span wrapper
			$start_span_wrapper='<span class="onsale">';
			$on_sale_text=apply_filters('wpv_woo_onsale_text_display_override',__('Sale!','woocommerce_views'));
			$end_span_wrapper='</span>';
			$on_sale_badge_html=$start_span_wrapper.$on_sale_text.$end_span_wrapper;
			
			//Trapped on-sale products
			//Check if product is on-sale
			
			$onsale_status_check=$this->woo_product_on_sale();
			
			if ($onsale_status_check) {
			
				/**Let's marked this shortcode execution */
				$this->wcviews_shortcode_executed();
				
				return $on_sale_badge_html;
			
			} else {
	
				return '';
			}         
		}
	}

	/**
	 * Placeholder image handling in woocommerce views image shortcode
	 * updated @since 2.7.1
	 */
	public function adjust_wc_views_image_placeholder($imagehtml,$postid = 0) {
	
		//Get user image size
		if  ( $this->wc_views_two_point_seven_above() ) {
			//WC 3.0+	
			global $attribute_image_size;
			if ( isset( $attribute_image_size ) ) {
				$user_image_size_set	=	$attribute_image_size;
			} else {
				$user_image_size_set	=	'shop_single';
			}			
		} else {
			//Backward compat
			$user_image_size_set=apply_filters( 'single_product_large_thumbnail_size', 'shop_single' );
		}		
		
		//Get available image sizes	
		$image_sizes_available=$this->wc_views_list_image_sizes();
		
		//Get image size for user settings
		if (isset($image_sizes_available[$user_image_size_set])) {
		   $image_dimensions_for_place_holder=$image_sizes_available[$user_image_size_set];
	
		} else {
			//Default to thumbnail
			$image_dimensions_for_place_holder=array(0=>'150',1=>'150');
		}   
		$placeholder_width=$image_dimensions_for_place_holder[0];
		$placeholder_height=$image_dimensions_for_place_holder[1];
		
		//WooCommerce 2.7.1: image resizing compatibility for placeholders in WC 3.0
		if ( ( $placeholder_width > 450 ) || ( $placeholder_height > 450 ) ) {
			//Target size is greater than maximum size of placeholder
			//Skip filtering so placeholder images are not stretched.
			return $imagehtml;
		}
		//Catch situations when user is setting 'raw' attribute
		$running_raw	= true;
		if (filter_var( $imagehtml, FILTER_VALIDATE_URL) === FALSE) {
			 //Parsable, not raw. Parse XML
			$running_raw	= false;
			 $image_src_source=simplexml_load_string( $imagehtml );
		} 	    
		if  ( $this->wc_views_two_point_seven_above() ) {
			//WC 3.0+	
			$image_src_source_url= esc_url( wc_placeholder_img_src() );
		} else {
			//Backward compat
			$image_src_source_url= (string) $image_src_source->attributes()->src;
		}	    
		
		//New in version 2.5.1, ensure placeholder width and height is enforced.
		//Use style="width:[$placeholder_width]px;height:[$placeholder_height]px;"
		
		/** In pixels */
		//Set responsive width   
		$placeholder_width_pixels= $placeholder_width.'px';
		$placeholder_height_pixels= $placeholder_height.'px';	    
		
		/**
		 * woocommerceviews-53: Let default WC core styling handles placeholder image sizes
		 * on single products
		 */
		$is_product_listing = $this->wcviews_is_woocommerce_listing();
		$doing_views_loop = false;
		if ( defined('WCVIEWS_DOING_LOOP') ) {
			if ( true === WCVIEWS_DOING_LOOP ) {
				$doing_views_loop = true;
			}
		}
		if ( ( true === $is_product_listing ) || ( true === $doing_views_loop ) ) {
			//Filter
			if ( false === $running_raw ) {
				//now raw, proceed.	
				$output_image_placeholder_html	='<img class="wcviews_image_placeholder" src="'.$image_src_source_url.'" alt="Placeholder" style="width:'.$placeholder_width_pixels.';height:'.$placeholder_height_pixels.'" />'; 
			} else {
				//raw
				$output_image_placeholder_html	= $image_src_source_url;
			}
				 
			return $output_image_placeholder_html;	    	
		
		} elseif ( is_product() ) {
			
			//Unfiltered
			return $imagehtml;
			
		}
		
		//Other cases, return unfiltered
		return $imagehtml;
		
	}
	
	public function wc_views_list_image_sizes(){
		global $_wp_additional_image_sizes;
		$sizes = array();
		foreach( get_intermediate_image_sizes() as $s ){
			$sizes[ $s ] = array( 0, 0 );
			if( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ){
				$sizes[ $s ][0] = get_option( $s . '_size_w' );
				$sizes[ $s ][1] = get_option( $s . '_size_h' );
			}else{
				if( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $s ] ) )
					$sizes[ $s ] = array( $_wp_additional_image_sizes[ $s ]['width'], $_wp_additional_image_sizes[ $s ]['height'], );
			}
		}
	
		return $sizes;
	}
	
	public function show_raw_image_html_wc_views($imagehtml,$id) {
		//Convert image link to raw image src output
		preg_match_all('#<img\b[^>]*>#', $imagehtml, $match);
		$img_tag_html = implode("\n", $match[0]);
		$img_tag_html_array=explode("\n",$img_tag_html);
		if (isset($img_tag_html_array[0])) {
			$imagehtml=$img_tag_html_array[0];
		}
		
		//Return raw output
		return $imagehtml;	
	
	}
	public function show_raw_image_url_wc_views( $imagehtml, $id = 0) {
		preg_match_all('#<img\b[^>]*>#', $imagehtml, $match);
		$img_tag_html = implode("\n", $match[0]);
		$img_tag_html_array=explode("\n",$img_tag_html);
		if (isset($img_tag_html_array[0])) {
			$imagehtml=$img_tag_html_array[0];
		}
	
		$image_src_source=simplexml_load_string($imagehtml);
		$image_src_source_url= (string) $image_src_source->attributes()->src;	
		return $image_src_source_url; 
	}
	//
	
	/**Emerson: NEW VERSION
	[wpv-woo-buy-options]
	Description: Displays 'add to cart' or 'select options' box for single product pages.
	Attributes: add_to_cart_text
	**/
	public function single_add_to_cart_text_closure_func() {
		global $add_to_cart_text_product_page_translated;
		return $add_to_cart_text_product_page_translated;
	}
	
	public function wpv_woo_buy_options_func($atts) {
	
	global $post, $wpdb, $woocommerce;
		
		if ( 'product' == $post->post_type ) {        
		   
			//Run only on single product page
			if (is_product()) {						
				$product =$this->wcviews_setup_product_data($post);
				
				if (isset($atts['add_to_cart_text'])) {
				  if (!(empty($atts['add_to_cart_text']))) {
					//User is setting add to cart text customized
				
					$add_to_cart_text_product_page=trim($atts['add_to_cart_text']);
				
					//START support for string translation
					if (function_exists('icl_register_string')) {
						//Register add to cart text product listing for translation
						icl_register_string('woocommerce_views', 'product_add_to_cart_text',$add_to_cart_text_product_page);
					}
					
					global $add_to_cart_text_product_page_translated;
						
					if (!function_exists('icl_t')) {
						//String translation plugin not available use original text
						$add_to_cart_text_product_page_translated=$add_to_cart_text_product_page;
				
					} else {
						//String translation plugin available return translation
						$add_to_cart_text_product_page_translated=icl_t('woocommerce_views', 'product_add_to_cart_text',$add_to_cart_text_product_page);
					}			
				
					$using_revised_woocommerce=$this->wcviews_using_woocommerce_two_point_one_above();
					
					if ($using_revised_woocommerce) {
						
						add_filter('woocommerce_product_single_add_to_cart_text',array(&$this,'single_add_to_cart_text_closure_func'));
	
					} else {
	
						add_filter('single_add_to_cart_text',array(&$this,'single_add_to_cart_text_closure_func'));
					
					}
				  }
				}
				
				$product_type_passed = $this->wc_views_get_product_type( $product );
					
				ob_start();
			
				if ('simple' == $product_type_passed ) {
								
					do_action( 'woocommerce_simple_add_to_cart');
				
				} elseif ( in_array( 
					$product_type_passed, 
					$this->get_known_variable_product_types() 
				) ) {
	 					
					do_action( 'woocommerce_variable_add_to_cart'); 
					
				} elseif ('grouped' == $product_type_passed ) {
					
					do_action( 'woocommerce_grouped_add_to_cart');
					
				} elseif ('external' == $product_type_passed ) {
					
					do_action( 'woocommerce_external_add_to_cart');
					
				} else {
					
				   /** https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/195444907/comments */
				   /** Let's handle any peculiar WooCommerce product post types not covered above */
				   /** First let's double check if $product->product_type exists */
					
				   if ( isset( $product ) ) {
				   		
				   		if ( !(empty( $product_type_passed ) ) ) {
				   			
				   			//Has sensible value, let's call 'woocommerce_template_single_add_to_cart' core function to display add to cart
				   			if ( function_exists( 'woocommerce_template_single_add_to_cart' ) ) {
								
								//Function exist, call!
				   				woocommerce_template_single_add_to_cart();

				   			}				   	    	
				   		}				   	
				   }
					
				}
				
				/**Let's marked this shortcode execution */
				$this->wcviews_shortcode_executed();
				
				return ob_get_clean();
		  	}
		} 
	}
	
	/**Emerson: NEW VERSION
	[wpv-add-to-cart-message]
	Description: Displays add to cart success message and link to cart for product variation
	**/
	public function wpv_show_add_cart_success_func($atts) {
		global $woocommerce;
		
		$check_if_using_revised_wc=$this->wcviews_using_woocommerce_two_point_one_above();
		
		if ( ! $check_if_using_revised_wc ) {
			if (
				isset( $woocommerce->messages )
				|| isset( $woocommerce->errors )
			) {		   	
				$html_result=$this->wcviews_add_to_cart_success_html(); 
				
				/**Let's marked this shortcode execution */
				$this->wcviews_shortcode_executed();
				return $html_result;
			}
		} else {
			$cart_contents = isset( $woocommerce->cart )
				? $woocommerce->cart
				: null;
			
			$cart_contents_array = isset( $cart_contents->cart_contents )
				? $cart_contents->cart_contents
				: array();           
			   
			if ( ! empty( $cart_contents_array ) ) {
				$html_result = $this->wcviews_add_to_cart_success_html();
				/**Let's marked this shortcode execution */
				$this->wcviews_shortcode_executed();
				
				return $html_result;
			}
	   }

	   return '';
	}
	
	public function wcviews_add_to_cart_success_html() {
		$add_to_cart_success_content = '';
	 
		if (
			$this->wcviews_using_woocommerce_two_point_one_above()
		) {
			$add_to_cart_success_content = wc_print_notices( true );
		} else {
			//Old WC, backward compatibility
			ob_start();
			woocommerce_show_messages();
			$add_to_cart_success_content = ob_get_clean();
		}

	
		return $add_to_cart_success_content;
	}
	
	/**Emerson: NEW VERSION
	Description: woo_product_on_sale() - This public function returns true if the product is on sale
	**/
	public function woo_product_on_sale() {
	
	global $post, $woocommerce;
	
	if ((isset($woocommerce)) && (isset($post))) {
		
		$product =$this->wcviews_setup_product_data($post);	
		
		if (isset($product)) {
			if ($product->is_on_sale()) {
		
				return TRUE;
		
			} else {
		
				return FALSE;
		
			}
		}
	}
	}
	
	/**Emerson: NEW VERSION
	 Description: woo_product_in_stock() - This public function returns true if the product is on stock
	**/
	
	public function woo_product_in_stock() {
		global $post;
		
		if (isset($post->ID)) {
			$post_id = $post->ID;
			$stock_status = get_post_meta($post_id, '_stock_status',true);
			
			if ($stock_status== 'outofstock') {
			
	 		 return FALSE;
	 		 
	 	   } elseif ($stock_status== 'instock') {
		
			  return TRUE;
			  
			}
		}
	}
	
	/**
	 * Enforces the WCV single product PHP template when the settings state so.
	 *
	 * @since 2.4.1	 
	 * @since 2.7.6 Fire the 'template_include' filter without actually applying it, 
	 *     because some third party plugins use it to load extra assets and resources.
	 */
	public function woocommerce_views_activate_template_redirect()
	{			
		
		//This affects the front end
		
		global $woocommerce;
		if (is_object($woocommerce)) {
			
			//WooCommerce plugin activated
			//Check if its not any edit product page added by Third party plugins like Dokan.			
			$is_dokan_edit_product=$this->wcviews_is_dokan_edit_product();
			
			if ((is_product()) && (!($is_dokan_edit_product))) {
				//Single Product page and NOT a Dokan edit product page!
				//Get template settings
				
				$get_template_wc_template=get_option('woocommerce_views_theme_template_file');
				
				if ((is_array($get_template_wc_template)) && (!(empty($get_template_wc_template)))) {
				
					$live_active_template=get_stylesheet();    	
					$template_name_for_redirect=key($get_template_wc_template);
					$template_path_for_redirect=$get_template_wc_template[$template_name_for_redirect];	    			
					
					//https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/193344321/comments#303054421
					//See if we can merge the WCV single-product templates with and without Layouts
					
					if ((strpos($template_path_for_redirect, 'single-product-layouts.php') !== false)) {
						
						//Using Layouts template, deprecated	    					
						$single_product_php_template_check=$this->wc_views_check_if_single_product_template_exists();
						
						if (file_exists($single_product_php_template_check)) {		

							//Ensure this template is updated on database
							$canonical_product_template[$template_name_for_redirect] = $single_product_php_template_check;
							update_option('woocommerce_views_theme_template_file',$canonical_product_template);
							
							//use canonical single-product.php template
							$template_path_for_redirect=$single_product_php_template_check;
						}	    					
					}

						//Make sure this template change makes sense
		  			  if ($live_active_template==$template_name_for_redirect) {      
			
		  			  	//Template settings exists, but don't do anything unless specified
		  		 	 	if (!($template_path_for_redirect=='Use WooCommerce Default Templates')) {
			
		   		 			//Template file selected, load it
		  		 	 		if (file_exists($template_path_for_redirect)) {
		  		 	 			$this->wcviews_support_loading_layouts_integration_plugins();
								$template_path_for_template_include_filter = apply_filters( 'template_include', $template_path_for_redirect );
		  		 		 		include($template_path_for_redirect);
		  				  		exit();
							}
						}
					   }
				}	
			}
		}
		
	}
	
	/**
	 * Added support for Layouts integration plugins.
	 */
	public function wcviews_support_loading_layouts_integration_plugins() {		
		
		if ( class_exists( 'WPDDL_Integration_Theme_Template_Router' ) ) {
			if ( method_exists( 'WPDDL_Integration_Theme_Template_Router', 'get_instance' ) ) {				
				$template_router = WPDDL_Integration_Theme_Template_Router::get_instance();
				if ( is_object( $template_router ) ) {
					global $wcviews_integration_layouts_template_router;
					$wcviews_integration_layouts_template_router = $template_router;
				}
			}
		}		
	}
	/**
	 * WooCommerce hooks to WordPress the_post
	 * add_action( 'the_post', 'wc_setup_product_data' );
	 * When the_post is called, put product data into a global.
	 * This hook is added in woocommerce/includes/wc-template-functions.php
	 *
	 * Before calling WooCommerce core functions to render front end output of WooCommerce Views shortcodes
	 * Let's make sure that the global $product is set up correctly to avoid any fatal errors associated with this.
	 *
	 * @access public
	 * @return void
	 */
	
	public function set_wc_views_products($post) {
	
		//Let's define the globals for WooCommerce
		global $woocommerce,$product;
	
		//Let's proceed only if post and WooCommerce object is set
		if ((is_object($post)) && (is_object($woocommerce))) {
				
			if (function_exists('wc_setup_product_data')) {
				//wp_setup_product_data function exists
				if (!(is_object($product))) {
	
					//Product is not object, its because the the_post hook is not called (in cases where Layouts are used)
					//Setup product data
					wc_setup_product_data( $post );
				}
			}
		}
	}
		
	/**Emerson: NEW VERSION
	[wpv-woo-display-tabs]
	Description: Displays additional information and reviews tab.
	For best results, you might want to disable comment section in products pages in your theme
	so it will be replaced with this shortcode
	This will replace the comment section for WooCommerce single product pages
	**/
	
	public function wpv_woo_display_tabs_func( $atts ) {
	
		global $woocommerce;
		if (is_object($woocommerce)) {
			if (is_product()) {
		
				global $woocommerce, $WPV_templates,$post;
		
				//Check for empty WooCommerce product content, if empty.
				//Apply the removal of filter for the_content only if content is set
		
				if (isset($post->post_content)) {
					$check_product_has_content=$post->post_content;
					if (!(empty($check_product_has_content))) {
		
						//Has content, Remove this filter, run only once -prevent endless loop due to WP core apply_filters on the_content hook
						remove_filter('the_content', array($WPV_templates, 'the_content'), 1, 1);
						
						//https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/193776982/comments
						//User adds the tab shortcode itself inside the Edit products in WC, this can cause infinite loop
						//Lets check if this content has the tab shortcodes itself and handle this.
						$checked_content=$post->post_content;
						if ((strpos($checked_content, '[wpv-woo-display-tabs]') !== false)) {
							
							  //Has instance of tab shortcode							 
							 remove_shortcode('wpv-woo-display-tabs');
						}
					}
				}
	
				ob_start();
				
				//Ensure $product is set
				$this->set_wc_views_products($post);
				
				//WooCommerce Views 2.6.1 +
				//Remove the reviews tab when wpv-woo-display-tabs shortcode is used together with wpv-woo-reviews shortcode
				$disable_reviews_boolean = false;
				if ( isset( $atts['disable_reviews_tab'] ) ) {					
					$disable_reviews_checked =	$atts['disable_reviews_tab'];
					$disable_reviews_checked =strtolower( $disable_reviews_checked );
					$disable_reviews_checked =trim( $disable_reviews_checked );
					if ( 'yes' == $disable_reviews_checked ) {
						$disable_reviews_boolean = true;
					}
				}				
				
				if ( true === $disable_reviews_boolean )  {
						remove_filter('the_content', array( $WPV_templates, 'the_content'), 1, 1);
						add_filter( 'woocommerce_product_tabs', array(&$this,'wcviews_overwrite_tabs') , 35, 1 );					
				}
				
				//External WC core function call
				woocommerce_output_product_data_tabs();
				
				$version_quick_check=$this->wcviews_using_woocommerce_two_point_one_above();
				if ($version_quick_check) {
					 //WC 2.1+
					 global $wcviews_comment_template_filtered;
					 add_filter('comments_template',array(&$this,'wc_views_comments_template_loader'),999);
					 $wcviews_comment_template_filtered = true;
					 
				} elseif (!($version_quick_check)) {
					//Old WC
					remove_filter( 'comments_template', array( $woocommerce, 'comments_template_loader' ) );
				}
				$content = ob_get_contents();
				ob_end_clean();
				
				/**When 'description' tab is removed programmatically using these methods
				 * https://docs.woothemes.com/document/editing-product-data-tabs/
				 * We don't need to re-add the content filter because its already rendered
				 * Otherwise it will cause Toolset shortcodes to stop rendering
				 */
				
				//We need to know if 'description' tab is removed.
				$wctabs = apply_filters( 'woocommerce_product_tabs', array() );
				if ( ( isset( $wctabs['description'] ) ) || ( true === $disable_reviews_boolean ) ) {
					//Description tab exist, add this filter back
					add_filter('the_content', array($WPV_templates, 'the_content'), 1, 1);
				}
				
				/**Let's marked this shortcode execution */
				$this->wcviews_shortcode_executed();
				
				return $content;
			}
		}
	}
	
	public function wcviews_overwrite_tabs( $tabs ) {
		unset( $tabs['reviews'] );
		unset( $tabs['description'] );
		$tabs['description'] = array(
				'title'    => __( 'Description', 'woocommerce' ),
				'priority' => 10,
				'callback' => 'woocommerce_product_description_tab'
		);
		return $tabs;		
	}
	public function wc_views_comments_template_loader($template) {
	
		if (isset($template)) {
	
			$basefile=basename($template);
			if ($basefile=='single-product-reviews.php') {
				//Don't show any redundant comment templates
				return '';
			} else {
				//Return unfiltered
				return $template;
			}
		} else {
			//Return unfiltered
			return $template;
		}
		return '';
	
	}
	
	/*Emerson: NEW VERSION
	public function that runs through all products and calculates computed postmeta from WooCommerce functions
	*/
	public function compute_postmeta_of_products_woocommerce_views() {
	
	//Define custom field names
	$views_woo_price = 'views_woo_price';
	$views_woo_on_sale = 'views_woo_on_sale';
	$views_woo_in_stock = 'views_woo_in_stock';
	
	if (!(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) { 
	
		//Detection when saving and updating a post, not on autosave
		//Updated custom values is on the $_POST
		if ((isset($_POST)) && (!(empty($_POST)))) {
		   
			//Run this hook on WooCommerce edit pages
			if (isset($_POST['post_type'])) {
	
			  if ($_POST['post_type']=='product') {
	
				/*Handle Quick Edit Mode*/
				//Check if doing quick edit
				
				if (isset($_POST['woocommerce_quick_edit_nonce'])) {
	
					   //Doing quick edits!
					   define('WC_VIEWS_DOING_QUICK_EDIT', true);
					   
					   //Now lets define product type
					   if ((empty($_POST['_regular_price'])) && (empty($_POST['_sale_price']))) {
						   
						   //This must be a variation
						   $_POST['product-type']='variable';
						   
					   } else {
	
							//This must be a simple product
							$_POST['product-type']='simple';
					   }                  
				}
			
		   		//$_POST is set
		   		//https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/188028069/comments
		   		if (isset($_POST['ID'])) {
					$post_id_transacted=trim($_POST['ID']);
		   		}
		   		
		   		if (isset($_POST['product-type'])) {
		   			$product_type_transacted= trim($_POST['product-type']);
		   		}       		
		   		if ((isset($post_id_transacted)) && (isset($product_type_transacted))) {
		   			if ($product_type_transacted=='simple') {
		   				//Get the price of simple product
		   				//Check if on sale or not
		   				if (empty($_POST['_sale_price'])) {
						   //Not on sale, get regular price
		   					$simple_product_price=trim($_POST['_regular_price']);
		   					$onsale_status=FALSE;
		   					$onsale_status=$this->for_views_null_equals_zero_adjustment($onsale_status);
		   					$on_sale_success= update_post_meta($post_id_transacted,$views_woo_on_sale,$onsale_status);       				
		   				} else {
							//On sale, get sales price
							$simple_product_price=trim($_POST['_sale_price']);
							//Save custom field on sale
							$onsale_status=TRUE;
							$onsale_status=$this->for_views_null_equals_zero_adjustment($onsale_status);
							$on_sale_success= update_post_meta($post_id_transacted,$views_woo_on_sale,$onsale_status);
						}
		   				//Save as custom field of simple product
		   				if ((!(empty($simple_product_price))) && ($simple_product_price != '0')) {
	 		  				$success= update_post_meta($post_id_transacted,$views_woo_price,$simple_product_price);
		   				}
		   				//Save on stock status for simple products
		   				if (isset($_POST['_stock_status'])) {
							if (!(empty($_POST['_stock_status']))) {
								$on_stock_status=trim($_POST['_stock_status']); 
								if ($on_stock_status=='instock') {
								   $on_stock_status=TRUE;
								   $on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
								} elseif ($on_stock_status=='outofstock') {
									$on_stock_status=FALSE;
									$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);							
				   			 }
				   			 if (isset($on_stock_status)) {
									$success_on_stock_status= update_post_meta($post_id_transacted,$views_woo_in_stock,$on_stock_status);
								}
				   		 }
				  	  }
			   	 } elseif (($product_type_transacted=='variable') && (isset($_POST['variable_regular_price']))) {
					
						//Variable price is only updated when NOT doing quick edit.
					
						if (!defined('WC_VIEWS_DOING_QUICK_EDIT')) {
					
							//Get the price of simple product
							$variable_product_price=array();
							$variable_product_price=$_POST['variable_regular_price'];
							
							//Find the minimum
							if (!(empty($variable_product_price))) {
	 					
	 			 		  $minimum_variation_price_set=min($variable_product_price);
				  	  
	 			  		 }
							//Save as custom field of simple product
							if ((!(empty($minimum_variation_price_set))) && ($minimum_variation_price_set !='0')) {
								$success= update_post_meta($post_id_transacted,$views_woo_price,$minimum_variation_price_set);
							}
						
						}
					
						//Save on stock status for variation products
						if (isset($_POST['_stock_status'])) {
							if (!(empty($_POST['_stock_status']))) {
								$on_stock_status=trim($_POST['_stock_status']);
								
								//Doing quick edit mode
								if (defined('WC_VIEWS_DOING_QUICK_EDIT')) {
									
									if ($on_stock_status=='outofstock') {
										$on_stock_status=FALSE;
										$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
									} else {
										$on_stock_status=TRUE;
										$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
									}
										$success_on_stock_status= update_post_meta($post_id_transacted,$views_woo_in_stock,$on_stock_status);                            
								}
							
							if (isset($_POST['variable_stock'])) {
							   if (is_array($_POST['variable_stock'])) {
								   $total_stock_qty_variation=array_sum($_POST['variable_stock']);
								   $variable_stock_quantity_wcviews=trim($_POST['_stock_status']);
								   if (($on_stock_status=='instock') && ($total_stock_qty_variation > 0)) {
								   	$on_stock_status=TRUE;
								   	$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
								   } elseif ($on_stock_status=='outofstock') {
								   	$on_stock_status=FALSE;
								   	$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
								   } elseif ($total_stock_qty_variation <= 0) {
									$on_stock_status=FALSE;
									$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
								   }
								   if (isset($on_stock_status)) {
								   	$success_on_stock_status= update_post_meta($post_id_transacted,$views_woo_in_stock,$on_stock_status);
								   }
							   }
	
							}
							}
						}	
				   	//Logic on saving variation product is on_sale
				   	if (isset($_POST['variable_sale_price'])) {
						 $variable_sales_price_array=array();
				   	  $variable_sales_price_array=$_POST['variable_sale_price'];
				   	  //Test if sales price exists
				   	  $sum_sales_test=array_sum($variable_sales_price_array);
				   	  if ($sum_sales_test==0) {
				   		 //Product is not on sale
							//Save custom field not on sale
							$onsale_status_variation=FALSE;
							$onsale_status_variation=$this->for_views_null_equals_zero_adjustment($onsale_status_variation);
							$on_sale_success_variation= update_post_meta($post_id_transacted,$views_woo_on_sale,$onsale_status_variation);                    
						 } else {
							//Product is on sale
							//Save custom field on sale
							$onsale_status_variation=TRUE;
							$onsale_status_variation=$this->for_views_null_equals_zero_adjustment($onsale_status_variation);
							$on_sale_success_variation= update_post_meta($post_id_transacted,$views_woo_on_sale,$onsale_status_variation);		
		
					 	}
	
				   	}			
				  }
				}       		
		   	
		   	  }
		   	
		   	}      
		}
	
	}
	}
	
	/*[wpv_woo_add_to_cart_box] is not anymore used starting version 2.0, public function remains for backward compatibility*/
	public function wpv_woo_add_to_cart_box($atts) {    
	
		_deprecated_function( 'wpv-wooaddcartbox', '2.5.1','wpv-woo-buy-options' );
		
	}
	
	public function wpv_woo_remove_from_cart($atts) {
		_deprecated_function( 'wpv-wooremovecart', '2.5.1' );
	}
	
	public function wpv_woo_cart_url($atts) {
		_deprecated_function( 'wpv-woo-carturl', '2.5.1' );
	}
	
	public function wpv_woo_add_shortcode_in_views_popup($items){
	   /*Old shortcode, functions not removed for backward compatibility*/
	   //$items already contains some shortcodes from previous processing
	   //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/194295655/comments#304646798
	   //Those $items should also be returned by this filter unharmed.
	   
		global $post;
		
		//Let's not add the WooCommerce Views shortcodes in the 'Edit Product'
		//To prevent misuse of these shortcodes.
		//Related to this problem:
		//https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/193776982/comments
		
		$associated_posttypes= $this->wcviews_associated_posttypes;
		if (is_object($post)) {
			
			//Post object defined
			if (isset($post->post_type)) {
				
			  $posttype=$post->post_type;
			  if (in_array($posttype, $associated_posttypes)) {

			  	//Bingo, post type is associated with WCV shortcodes, show in Editor.			  	
			  	//OK here we passed $items so other shortcodes will still show after filtering
			  	//https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/194295655/comments#304646798
			  	//WC Views shortcodes will be added in addition to previous $items
		  		$items=$this->wpv_woo_add_shortcode_in_views_popup_aux($items);
			  }				
			}			
		} elseif (!(isset($post))) {
			//In cases where $post is not defined, it could be the shortcode insertion inside the Content Template cell in Layouts
			//OK here we passed $items so other shortcodes will still show after filtering
			//WC Views shortcodes will be added in addition to previous $items
			$items=$this->wpv_woo_add_shortcode_in_views_popup_aux($items);
			
		}
		
		return $items;
	}
	
	public function wpv_woo_add_shortcode_in_views_popup_aux($items) {		
		
		$items['WooCommerce']['wpv-woo-buy-or-select'] = array(
				'Add to cart button - product listing pages',
				'wpv-woo-buy-or-select',
				'Basic',
				"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-buy-or-select', title: '" . esc_js( __( 'Add to cart button - product listing pages', 'woocommerce_views' ) ). "' })"
		);
		//[wpv-woo-product-price]
		$items['WooCommerce']['wpv-woo-product-price'] = array(
				'Product price',
				'wpv-woo-product-price',
				'Basic',
				''
		);
		//[wpv-woo-buy-options]
		$items['WooCommerce']['wpv-woo-buy-options'] = array(
				'Add to cart button - single product page',
				'wpv-woo-buy-options',
				'Basic',
				"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-buy-options', title: '" . esc_js( __( 'Add to cart button - single product page', 'woocommerce_views' ) ). "' })"
		);
		//[wpv-woo-product-image]
		$items['WooCommerce']['wpv-woo-product-image'] = array(
				'Product image',
				'wpv-woo-product-image',
				'Basic',
				"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-product-image', title: '" . esc_js( __( 'Product image', 'woocommerce_views' ) ). "' })"
		);
		//[wpv-show-add-cart-success]
		$items['WooCommerce']['wpv-add-to-cart-message'] = array(
				'Add to cart message',
				'wpv-add-to-cart-message',
				'Basic',
				''
		);
		//[wpv-woo-display-tabs]
		$items['WooCommerce']['wpv-woo-display-tabs'] = array(
				'Product tabs - single product page',
				'wpv-woo-display-tabs',
				'Basic',
				"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-display-tabs', title: '" . esc_js( __( 'Product tabs - single product page', 'woocommerce_views' ) ). "' })"
		);
		//[wpv-woo-onsale]
		$items['WooCommerce']['wpv-woo-onsale'] = array(
				'Onsale badge',
				'wpv-woo-onsale',
				'Basic',
				''
		);
		
		//[wpv-woo-list_attributes]
		$items['WooCommerce']['wpv-woo-list_attributes'] = array(
				'Product attributes',
				'wpv-woo-list_attributes',
				'Basic',
				''
		);
		
		//[wpv-woo-related_products]
		$items['WooCommerce']['wpv-woo-related_products'] = array(
				'Related Products',
				'wpv-woo-related_products',
				'Basic',
				''
		);
		
		//[wpv-woo-single-products-rating]
		$items['WooCommerce']['wpv-woo-single-products-rating'] = array(
				'Product Rating - single product page',
				'wpv-woo-single-products-rating',
				'Basic',
				''
		);
		
		//[wpv-woo-products-rating-listing]
		$items['WooCommerce']['wpv-woo-products-rating-listing'] = array(
				'Product Rating - product listing pages',
				'wpv-woo-products-rating-listing',
				'Basic',
				''
		);
		 
		//[wpv-woo-productcategory-images]
		$items['WooCommerce']['wpv-woo-productcategory-images'] = array(
				'Product Category Image',
				'wpv-woo-productcategory-images',
				'Basic',
				"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-productcategory-images', title: '" . esc_js( __( 'Product category image', 'woocommerce_views' ) ). "' })"
		);
		
		//[wpv-woo-show-upsell-items]
		$items['WooCommerce']['wpv-woo-show-upsell-items'] = array(
				'Product Upsell',
				'wpv-woo-show-upsell-items',
				'Basic',
				''
		);
		
		//[wpv-woo-breadcrumb]
		$items['WooCommerce']['wpv-woo-breadcrumb'] = array(
				'Breadcrumb',
				'wpv-woo-breadcrumb',
				'Basic',
				''
		);
		//[wpv-woo-product-meta]
		$items['WooCommerce']['wpv-woo-product-meta'] = array(
				'Product meta',
				'wpv-woo-product-meta',
				'Basic',
				''
		);
		//[wpv-woo-cart-count]
		$items['WooCommerce']['wpv-woo-cart-count'] = array(
				'Cart Count',
				'wpv-woo-cart-count',
				'Basic',
				''
		);				

		//[wpv-woo-reviews]
		$items['WooCommerce']['wpv-woo-reviews'] = array(
				'Reviews',
				'wpv-woo-reviews',
				'Basic',
				''
		);
		
		//[wpv-ordered-product-ids]
		$items['WooCommerce']['wpv-ordered-product-ids'] = array(
				'Ordered products',
				'wpv-ordered-product-ids',
				'Basic',
				''
		);
		
		
		$items=apply_filters('wcviews_filter_shortcode_usage',$items);
		return $items;
		
	}
	public function wpv_woo_add_shortcode_in_views_popup_cat($items){
			
		global $post;
		
		//Let's not add the WooCommerce Views shortcodes in the 'Edit Product'
		//To prevent misuse of these shortcodes.
		//Related to this problem:
		//https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/193776982/comments
		
		$associated_posttypes= $this->wcviews_associated_posttypes;
		
		if (is_object($post)) {
				
			//Post object defined
			if (isset($post->post_type)) {
		
				$posttype=$post->post_type;
				if (in_array($posttype,$associated_posttypes)) {
					
					//Bingo, post type is associated with WCV shortcodes, show in Editor.
					//[wpv-woo-productcategory-images]
					$items['WooCommerce']['wpv-woo-productcategory-images'] = array(
							'Product Category Image',
							'wpv-woo-productcategory-images',
							'Basic',
							'wcviews_insert_wpv_woo_productcategory_images(); return false;'
					);
					
				}
			}
		} elseif (!(isset($post))) {
			
			//In cases where $post is not defined, it could be the shortcode insertion inside the Content Template cell in Layouts
			$items['WooCommerce']['wpv-woo-productcategory-images'] = array(
					'Product Category Image',
					'wpv-woo-productcategory-images',
					'Basic',
					'wcviews_insert_wpv_woo_productcategory_images(); return false;'
			);
			
		}
		
		$items	= apply_filters('wcviews_filter_shortcode_usage',$items );
		
		return $items;
	}	
	
	public function wpv_woo_add_shortcode_in_views_layout_wizard($items){
	
		//Please sync with wpv_woo_add_shortcode_in_views_popup() above.
	
		$modern_toolbox=$this->wc_views_modern_views_toolbox_check();
		
		if (!($modern_toolbox)) {
			
			//[wpv-woo-buy-or-select]
			$items[] = array(
					'Add to cart button - product listing pages',
					'wpv-woo-buy-or-select',
					'WooCommerce',
					"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-buy-or-select', title: '" . esc_js( __( 'Add to cart button - product listing pages', 'woocommerce_views' ) ). "' })"
			);
			//[wpv-woo-product-price]
			$items[] = array(
					'Product price',
					'wpv-woo-product-price',
					'WooCommerce',
					''
			);
			//[wpv-woo-buy-options]
			$items[] = array(
					'Add to cart button - single product page',
					'wpv-woo-buy-options',
					'WooCommerce',
					"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-buy-options', title: '" . esc_js( __( 'Add to cart button - single product page', 'woocommerce_views' ) ). "' })"
			);
			//[wpv-woo-product-image]
			$items[] = array(
					'Product image',
					'wpv-woo-product-image',
					'WooCommerce',
					"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-product-image', title: '" . esc_js( __( 'Product image', 'woocommerce_views' ) ). "' })"
			);
			//[wpv-show-add-cart-success]
			$items[] = array(
					'Add to cart message',
					'wpv-add-to-cart-message',
					'WooCommerce',
					''
			);
			//[wpv-woo-display-tabs]
			$items[] = array(
					'Product tabs - single product page',
					'wpv-woo-display-tabs',
					'WooCommerce',
					"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-display-tabs', title: '" . esc_js( __( 'Product tabs - single product page', 'woocommerce_views' ) ). "' })"
			);
			//[wpv-woo-onsale]
			$items[] = array(
				'Onsale badge',
				'wpv-woo-onsale',
				'WooCommerce',
				''
			);
		
			//[wpv-woo-list_attributes]
			$items[] = array(
				'Product attributes',
				'wpv-woo-list_attributes',
				'WooCommerce',
				''
			);
			
			//[wpv-woo-related_products]
			$items[] = array(
					'Related Products',
					'wpv-woo-related_products',
					'WooCommerce',
					''
			);	
			
			//[wpv-woo-single-products-rating]
			$items[] = array(
					'Product Rating - single product page',
					'wpv-woo-single-products-rating',
					'WooCommerce',
					''
			);	
			
			//[wpv-woo-products-rating-listing]
			$items[] = array(
					'Product Rating - product listing pages',
					'wpv-woo-products-rating-listing',
					'WooCommerce',
					''
			);
			//[wpv-woo-productcategory-images]
			$items[] = array(
					'Product Category Image',
					'wpv-woo-productcategory-images',
					'WooCommerce',
					"WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-productcategory-images', title: '" . esc_js( __( 'Product category image', 'woocommerce_views' ) ). "' })"
			);	
			
			//[wpv-woo-show-upsell-items]
			$items[] = array(
					'Product Upsell',
					'wpv-woo-show-upsell-items',
					'WooCommerce',
					''
			);
			
			//[wpv-woo-breadcrumb]
			$items[] = array(
					'Breadcrumb',
					'wpv-woo-breadcrumb',
					'WooCommerce',
					''
			);
	
			//[wpv-woo-product-meta]
			$items[] = array(
					'Product meta',
					'wpv-woo-product-meta',
					'WooCommerce',
					''
			);	
	
			//[wpv-woo-cart-count]
			$items[] = array(
					'Cart Count',
					'wpv-woo-cart-count',
					'WooCommerce',
					''
			);	
			//[wpv-woo-reviews]	
			$items[] = array(
					'Reviews',
					'wpv-woo-reviews',
					'WooCommerce',
					''
			);
			//[wpv-ordered-product-ids]
			$items[] = array(
					'Ordered products',
					'wpv-ordered-product-ids',
					'WooCommerce',
					''
			);
			
		}
		
		return $items;
	}
	
	//Returns TRUE if using woocommerce default template
	public function wc_views_check_if_using_woocommerce_default_template() {
	
		$the_active_php_template_option_thumbnails=get_option('woocommerce_views_theme_template_file');
		
		if ((is_array($the_active_php_template_option_thumbnails)) && (!(empty($the_active_php_template_option_thumbnails)))) {
			$the_active_php_template_thumbnails=reset($the_active_php_template_option_thumbnails);
			
			if ($the_active_php_template_thumbnails=='Use WooCommerce Default Templates') {
	
			   return TRUE;
	
			} else {
	
			   return FALSE;
			}	
		} else {
	
			//If option does not exist, return TRUE since it make sense that it defaults to WooCommerce Templates
			return TRUE;
		}
	
	}
	
	//Warning HTML if using default WooCommerce Templates, cannot set Content Template
	public function wc_views_warning_cannot_set_ct_template_using_default_wc() {
	?>
<div class="wcviews_warning">
	<p><?php _e('You cannot select a Content Template for product - WooCommerce default templates have been selected to display products. You need to assign a page template in the settings.','woocommerce_views');?></p>
</div>
<?php 
	}
	
	//Method to check if single-product.php template exist on theme directory
	public function wc_views_check_if_single_product_template_exists() {
	
		$woocommerce_views_supported_templates= $this->load_correct_template_files_for_editing_wc();
		
		$single_product_template_found=FALSE;
		
		//Loop through the PHP templates array
		if ((is_array($woocommerce_views_supported_templates)) && (!(empty($woocommerce_views_supported_templates)))) {
		
			foreach ($woocommerce_views_supported_templates as $template_name=>$template_path) {
	
			   $template_file_name= basename($template_path);
			   if ($template_file_name=='single-product.php') {
					 return $template_path;
					 break;
			   }
			}
		}
		
		return $single_product_template_found; 
		
	}
	
	//Filter on adding WC Classes wrapper around Content Template the_content() modification
	
	public function wc_views_prefix_add_wrapper( $content, $template_selected, $id, $kind ) {
	
		global $woocommerce;
		$views_settings_options=get_option('wpv_options');
		$content_orig=$content;
		if (isset($views_settings_options['views_template_for_product'])) {
		
			//Retrieve content template ID
			$product_template_id=$views_settings_options['views_template_for_product'];
			
			/** WooCommerce Views 2.5.3 */
			/** It's possible that user will assign a Content Template to a product on a per product basis not to the entire products */
		   
			if (( $kind == 'single-product' ) && (is_object($woocommerce))) {
			 

				/** WooCommerce Views 2.5.4 */
				/** User sometimes use [wpv-post-body view_template="None"] inside Content Template cell for sites with layouts
				 * We don't need to wrap them with these classes since they are wrapped already by the Layouts
				 */
				 
				global $wpddlayout;
				$exception=false;
				if (is_object($wpddlayout)) {
					if (method_exists($wpddlayout,'get_layout_slug_for_post_object')) {
						//Site is using Toolset Layouts.
						//Let's checked if there is a Layout assigned to this product
						$layout_assigned_to_product=$wpddlayout->get_layout_slug_for_post_object($id);
						$layout_assigned_to_product=trim($layout_assigned_to_product);
						
						//Let's checked if this function is attempted to wrap its own content
						$product_object= get_post($id);
						$content_to_check= $product_object->post_content;
						if ((!(empty($layout_assigned_to_product))) && ($content_to_check ==$content )) {
						  //Layouts set to this product and we are about to wrap the products own text content
						  $exception=true;
						}
					}
				
				}
				
				/** Here we have products loaded that is controlled by WooCommerce */
				/** Let's wrapped with its classes */
				global $post_classes_wc_added;
				
				/** WooCommerce Views 2.5.5+
				 * We wrapped content with WC Classes only if this Content Template is assigned to this product
				 */
				//Check first if this product has been assigned with a Product Content Template on a general assignment basis
				$specific_ct_product_assignment= get_post_meta($id,'_views_template',TRUE);
				$we_wrap=false;
				if ($template_selected ==$product_template_id) {
					$we_wrap=true;
				//Then we would like to know if this specific Content Template is a product template assign to this loaded product
				} elseif ($specific_ct_product_assignment == $template_selected) {
					$we_wrap=true;
				}
				
				if ((!($post_classes_wc_added)) && (!($exception)) && ($we_wrap)) {
					//Not yet wrapped and no Layouts assigned
					$post_classes_wc_added=TRUE;
					$post_classes = get_post_class( 'clearfix', $id );
					$woocommerce_before_single_product = $this->wcviews_execute_woocommerce_before_single_product();
					$content = '<div class="' . implode( ' ', $post_classes ) . '">'. $content . '</div>';
				}

			}
		}
		if ($content != $content_orig) {
			//Filter applied
			//Disable default WordPress core texturization on WooCommerce Views shortcodes
			remove_filter( 'the_content', 'wptexturize'        );
			$this->wcviews_disabled_texturize =TRUE;
		}
		return $content;
	
	}
	
	//Filter to override  any content templates set with full plugin version when using default WooCommerce templates
	
	public function wc_views_override_any_content_templates_default_wc($template_selected, $id) {
	
			$template_selected=0;
			return $template_selected;		
	
	}
	
	//public function to reset automatically to default WooCommerce templates after theme switching activity
	
	public function wc_views_reset_wc_default_after_theme_switching() {
	
		//Run the method to use WooCommerce default templates   	
		$is_using_wc_default_template=$this->wc_views_check_if_using_woocommerce_default_template();
		
		//Reset this option by deletion
		delete_option('wc_views_nondefaulttemplate_changed');
		if (!($is_using_wc_default_template)) {
			
		   //Using non-default template,
			update_option('wc_views_nondefaulttemplate_changed','yes');    	 
			  	
		} else {
			
			//Using default WooCommmerce template,
			update_option('wc_views_nondefaulttemplate_changed','no');    	
		}
			
		//woocommerceviews-111:  Do not change WooCommerce Views settings on theme change
		$standard_template	= $this->wc_views_return_standard_product_template_path();
		$this->wcviews_save_php_template_settings( $standard_template );
	
	}
	
	/**
	 * Return the standard product template path inside WCV plugin for products
	 * Otherwise revert to default WooCommerce core template inside WC core plugin.
	 * 
	 * since @2.7.2
	 */
	public function wc_views_return_standard_product_template_path() {
		
		$template_path	=	WOOCOMMERCE_VIEWS_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'single-product.php';	

		if ( !( file_exists( $template_path ) ) ) {
			//For some reason, this template does not exist, revert to WooCommerce core default templates
			$template_path	= 'Use WooCommerce Default Templates';
		}
		return $template_path;
		
	}
	public function wc_views_after_theme_switched() {
	
		$wc_views_nondefaulttemplate_changed=get_option('wc_views_nondefaulttemplate_changed');
		
		if ($wc_views_nondefaulttemplate_changed) {			
			if ('yes' == $wc_views_nondefaulttemplate_changed) {
				//Inform user that a theme switch occurs and that he needs to set the WooCommerce Views templates again
				add_action('admin_notices', array(&$this,'wcviews_needs_to_update_templates_notice'));
			}
			
			//Done using this option, delete.
			delete_option('wc_views_nondefaulttemplate_changed');
		}
	
	}
	
	/* Show warning that user needs to update templates again*/
	public function wcviews_needs_to_update_templates_notice() {
		?>
<div class="update-nag">
	<p>
				<?php 
				$admin_url_wcviews=admin_url().'admin.php?page=wpv_wc_views';
				$message  = '<p>'.__( 'You have switched to another theme. Single product templates are resetted to WooCommerce Views defaults.','woocommerce_views').'</p>';
				$message .= '<p>'.__( 'Please go to','woocommerce_views').' '.'<a href="'.$admin_url_wcviews.'">'.__('WooCommerce Views settings','woocommerce_views').'</a>';
				$message .= ' '.__('section. And assign another single product page templates if necessary.', 'woocommerce_views').'</p>';
				?>
				<?php 
				echo $message;
				?>
				</p>
</div>
<?php
	}
	
	/**
	 * Output the list of product attributes in WooCommerce. 
	 * Product attributes are set when editing WooCommerce products in backend. 
	 * Then go to "Product data" -> Attributes. 
	 * Tested to work on single product and WooCommerce shop pages.
	 * @access public
	 * @return void
	 */	
	
	public function wpv_woo_list_attributes_func() {
	
		global $post,$woocommerce;
	
		ob_start();
		
		$product =	$this->wcviews_setup_product_data( $post );
	
		//Check if $product is set
		if (isset( $product ) ) {
			//Let's checked if product type is set							
				//Let's checked if it contains sensible value
				$product_type	= $this->wc_views_get_product_type( $product );
				if ( !( empty( $product_type ) ) ) {
					//Yes product types exist and set
					if ( ( $this->wc_views_two_point_seven_above() ) && ( function_exists( 'wc_display_product_attributes' ) ) ) {
						//version 2.7+					    	 
						wc_display_product_attributes( $product );
					} else {
						$product->list_attributes();
					}					
					
					/**Let's marked this shortcode execution */
					$this->wcviews_shortcode_executed();
					
					return ob_get_clean();					
					
				}			
		}
	
	}
	
	//WooCommerce Views setup product data public function based on WooCommerce functions
	//Updated to be compatible with WC version 2.1+ with backward compatibility
	
	public function wcviews_setup_product_data($post) {
	
		if (function_exists('wc_setup_product_data')) {
			//Using WooCommerce Plugin version 2.1+
			$product_information=wc_setup_product_data( $post );
			return $product_information;
	
		} else {
	
			//Probably still using older woocommerce versions
			global $woocommerce;
	
			if (is_object($woocommerce)) {
				$product_information = $woocommerce->setup_product_data( $post );
				return $product_information;
			}
		}

		return null;
	}
	
	//NEW: Compatibilty public function to check for WooCommerce versions
	//Returns TRUE if using WooCommerce version 2.1.0+
	public function wcviews_using_woocommerce_two_point_one_above() {
	   
	   global $woocommerce;
	   if (is_object($woocommerce)) {
	
	   	$woocommerce_version_running=$woocommerce->version;
	
			if (version_compare($woocommerce_version_running, '2.1.0', '<')) {
	
				return FALSE;
	
			} else {
	
				return TRUE;
				
			}
	   }
	
	}
	
	//NEW: Compatibilty public function to check for WooCommerce versions
	//Returns TRUE if using WooCommerce version 2.2.0+
	public function wc_views_two_point_two_above() {
	
		global $woocommerce;
		if (is_object($woocommerce)) {
	
			$woocommerce_version_running=$woocommerce->version;
	
			if (version_compare($woocommerce_version_running, '2.2.0', '<')) {
	
				return FALSE;
	
			} else {
	
				return TRUE;
	
			}
		}
	
	}
	
	/**
	 * Outputs WooCommerce related products using its own matching algorithm (using product categories and tags).
	 * Tested to work only on Single Product pages. This is not meant to be used on product loops.
	 * @access public
	 * @return void
	 */	
	
	public function wpv_woo_related_products_func() {
		
		global $post,$woocommerce;
		
		ob_start();
	
		//Check if $product is set
		if (is_object( $woocommerce ) ) {
			//WooCommerce plugin activated
			//Get products
			$product =$this->wcviews_setup_product_data( $post );
			
			if ((isset( $product ) ) && ( is_product() ) ) {
				//Executable only on single product page
				
				//We need to verify if product_type is duly set and exist				
				//Set,
				$product_type = $this->wc_views_get_product_type( $product );					
				if (!( empty( $product_type ) ) ) {
					//Set and exist
					//Simple or variable products
					if (function_exists( 'woocommerce_output_related_products' ) ) {
						
						//Call WooCommerce core public function on oututting related products exists.
						add_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
						woocommerce_output_related_products();
						
					}
							
					/**Let's marked this shortcode execution */
					$this->wcviews_shortcode_executed();
						
					return ob_get_clean();					
						
				}				
			}
		}	
	}	

	/**
	 * Outputs WooCommerce product rating on single product pages.
	 * Tested to work only on Single Product pages. This is not meant to be used on product loops.
	 * @access public
	 * @return void
	 */
		
	public function wpv_woo_single_products_rating_func() {
	
		global $post,$woocommerce;
	
		ob_start();
		
		if (is_object($woocommerce)) {
			//WooCommerce plugin activated
			//Get products
			$product =$this->wcviews_setup_product_data($post);
	
			if ((isset($product)) && (is_product())) {
				
				//Let's check if product_type is set					
				//Set
				$product_type = $this->wc_views_get_product_type( $product );
				if (!(empty($product_type))) {
					//Defined, exist
					//Simple or variable products
					if (function_exists( 'woocommerce_template_single_rating' ) ) {
							
						//Call WooCommerce core public function on outputting single product rating on single product page
						woocommerce_template_single_rating();								
					}
						
					/**Let's marked this shortcode execution */
					$this->wcviews_shortcode_executed();						
					return ob_get_clean();						
				}				
			}
		}
	}
	
	/**
	 * Outputs WooCommerce product rating on product listing and loops.
	 * Tested to work only on product listing and loops. Not meant for product pages.
	 * @access public
	 * @return void
	 */
		
	public function wpv_woo_products_rating_on_listing_func() {
	
		global $post,$woocommerce;
	
		ob_start();
	
		if (is_object($woocommerce)) {
			//WooCommerce plugin activated
			
			//Check if this is a product listing page
			$product_listing_check =$this->wcviews_is_woocommerce_listing();
	
			if ($product_listing_check) {
					
				//Executable only on product listing pages with sensible $products
				$product =$this->wcviews_setup_product_data($post);
				
				if (isset($product)) {
					if (function_exists( 'woocommerce_template_loop_rating' )) {
		
						//Call WooCommerce core public function on outputting product ratings on listing pages
						woocommerce_template_loop_rating();
					}
					
					$listing_rating_output=ob_get_clean();					
					$listing_rating_output=trim($listing_rating_output);
					
					/**Let's marked this shortcode execution */
					$this->wcviews_shortcode_executed();
					
					return $listing_rating_output;
				}
			}
		}
	}
	
	//Outputs TRUE if on WooCommerce listing pages
	public function wcviews_is_woocommerce_listing() {
		
	  global $woocommerce;	
		
	  $is_wc_listing_page=FALSE;
	  
	  if (is_object($woocommerce)) {
	  	//WooCommerce plugin activated
	  	//Check if this NOT a product page
	  	
	  	if (!(is_product())) {
	  	   //Not a product page
	  		$is_wc_listing_page=TRUE;   	
	  		
	  	}
	  	
	  }
	  
	  return $is_wc_listing_page;
		
	}
	
	/**
	 * Outputs WooCommerce product category image set in the backend. (Products -> Categories)
	 * Tested to work loops outputting categories.
	 * @access public
	 * @return void
	 */
	
	public function wpv_woo_productcategory_images_func($atts) {
		
		global $woocommerce,$WP_Views;
		$image_content='';
		
		if ((is_object($woocommerce)) && (is_object($WP_Views))) {
		
			//WooCommerce and Views plugin activated
		
			//Get available image sizes
			$image_sizes_available=$this->wc_views_list_image_sizes();
		
			//Let's checked the $atts passed
			if (empty($atts)) {
				
				//No attributes passed , define defaults
				$atts=array();
				$atts['size']='shop_single';
				$atts['output']='raw';                	
				
			}
			
			//Retrieved settings
			if (isset($atts['size'])) {
				
				$size=$atts['size'];        	
			} else {
			   //Not set, use defaults
				$size='shop_single';
			}
			
			if (isset($atts['output'])) {
				 
				$outputformat=$atts['output'];
			} else {
				//Not set, use defaults
				$outputformat='raw';        	
			}
			
			//Check if this is a WooCommerce product category
			//Get Taxonomy info
			$taxonomydata_passed_by_views	=	$WP_Views->taxonomy_data;
			$thumbnail_id					=	0;
			if (!(empty($taxonomydata_passed_by_views))) {
				//Don't proceed further if $taxonomydata_passed_by_views is empty.
				//Get Term info
				$term_info_tax=$taxonomydata_passed_by_views['term'];
			
				//Get Term ID
				$term_id_tax=$term_info_tax->term_id;
			
				//Get Thumbnail ID assigned to that term ID
				$thumbnail_id = get_woocommerce_term_meta( $term_id_tax, 'thumbnail_id', true );			
	
			} else {
				
				//We have $taxonomydata_passed_by_views	
				//Check if we are in product category archive
				if ( is_product_category() ){
					global $wp_query;
					$cat = $wp_query->get_queried_object();
					$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );					

				}				
			}
			
			//Get attachment image
			//Return image content that depends on $output_format
			//$image_content by default is img HTML tag element
			$thumbnail_id	= intval( $thumbnail_id );
			if ( $thumbnail_id > 0 ) {
				//Thumbnail ID set
				$image_content	= wp_get_attachment_image( $thumbnail_id, $size );
					
				if (('raw' == $outputformat) && ( !( empty( $image_content ) ) ) ) {
						
					//Don't run this block if $image_content is empty
					$image_src_source=simplexml_load_string( $image_content );
					$image_content= (string) $image_src_source->attributes()->src;
						
				}				
			}				
		}
		
		/**Let's marked this shortcode execution */
		if (!(empty($image_content))) {
			$this->wcviews_shortcode_executed();
		}
		
		return $image_content;
	}
	
	/**
	 * Outputs items for upsell.
	 * You can configure items for upsell by following this WooCommerce guide.
	 * http://docs.woothemes.com/document/related-products-up-sells-and-cross-sells/
	 * This is tested to work on single-product pages.
	 * @access public
	 * @return void
	 */	
	
	public function wpv_woo_show_upsell_func() {
	
		global $post,$woocommerce;
	
		ob_start();
	
		if (is_object($woocommerce)) {
			//WooCommerce plugin activated
			//Get products
			$product =$this->wcviews_setup_product_data($post);
	
			if ((isset($product)) && (is_product())) {
					
				//Executable only on single product page
				//Check if product_type is set					
				//Set check if defined
				$product_type = $this->wc_views_get_product_type( $product );
					
				if (!(empty($product_type))) {
						
					//Defined
					//Simple or variable products
					if (function_exists( 'woocommerce_upsell_display' ) ) {
						
						//Call WooCommerce core public function on outputting upsell items
						woocommerce_upsell_display();
						
					}
						
					/**Let's marked this shortcode execution */
					$this->wcviews_shortcode_executed();
						
					return ob_get_clean();						
				}				
			}
		}
	}
	
	/**
	 * Outputs the default WooCommerce breadcrumb.
	 * This is meant for single-product pages only.
	 * This won't work in archive pages.
	 * @access public
	 * @return void
	 */	
	public function wpv_woo_breadcrumb_func() {
		
		/** {ENCRYPTION PATCH HERE} **/		
		global $woocommerce;		
		if ( is_object( $woocommerce ) ) {			
			//WooCommerce plugin activated	
			if (function_exists( 'woocommerce_breadcrumb' ) ) {					
				ob_start();				
				//Call WooCommerce core public function on outputting WooCommerce Breadcrumb
				woocommerce_breadcrumb();				
				/**Let's marked this shortcode execution */
				$this->wcviews_shortcode_executed();
				return ob_get_clean();
			}			
		}
		return '';
		
	}
	
	/** Export Woocommerce Views settings */
	/** Returns XML content of export if sensible, otherwise FALSE*/
	public function wcviews_export_settings() {
	
		$woocommerce_views_export_xml =FALSE;
		
		if(defined('WOOCOMMERCE_VIEWS_PLUGIN_PATH')) {
			
			//Define the parser path
			$array_xml_parser=WOOCOMMERCE_VIEWS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'array2xml.php';
		
			if (file_exists($array_xml_parser)) {
	
				//Define array of important WooCommerce Views settings for exporting.
				$woocommerceviews_options_for_exporting=array(
						'woocommerce_views_theme_template_file',
						'woocommerce_views_theme_archivetemplate_file',					
						'woocommerce_views_wrap_the_content'
				);
				
				$layouts_plugin_status = $this->wc_views_check_status_of_layouts_plugin();
				if ( false === $layouts_plugin_status ) {
					//Layouts plugin inactive, this setting is needed
					$woocommerceviews_options_for_exporting[]	=	'woocommerce_views_frontend_sorting_setting';
				}
				
				$woocommerce_views_settings=array();
				
				//Loop through the settings and assign to array
				foreach ($woocommerceviews_options_for_exporting as $key=>$value) {
				
					$the_value=get_option($value);
					if ($the_value) {
						$woocommerce_views_settings[$value]=$the_value;
					}			
				}
				
				//Parser exists, require once
				require_once $array_xml_parser;
				
				//Instantiate
				$xml = new ICL_Array2XML();
	
				//Define anchor name
				$anchor_name='woocommerce_views_export_settings';
				
				//Get XML only if array is not empty
				if (!(empty($woocommerce_views_settings))) {
					$woocommerce_views_export_xml = $xml->array2xml($woocommerce_views_settings, $anchor_name);	
				}		
									
			}
		}
		
		return $woocommerce_views_export_xml;
	}
	/** Import Woocommerce Views settings */
	/** Otherwise FALSE*/
	
	public function wcviews_import_settings($xml) {
		
		if ($xml) {
			//$xml is sensible
			
			//Require $wpdb
			global $wpdb;
			
			if (function_exists('wpv_admin_import_export_simplexml2array')) {
				//public function exists get import data
				$import_data = wpv_admin_import_export_simplexml2array($xml);
				
				//Loop through the settings and update WooCommerce options
				$updated_settings=array();
				foreach ($import_data as $key=>$value) {
					if ('woocommerce_views_theme_template_file' == $key) {
						//Assign compatible templates at import site
						$updated_value=$this->wcviews_fix_theme_templates_after_import($value);
						
					} elseif ('woocommerce_views_theme_archivetemplate_file' == $key) {
						
						//Assign compatible templates at import site
						$updated_value=$this->wcviews_fix_theme_archivetemplates_after_import($value);
						
					} else {
						if ( 'woocommerce_views_frontend_sorting_setting' == $key ) {
							//Before saving this setting, make sure Layout is not active
							$layouts_plugin_status = $this->wc_views_check_status_of_layouts_plugin();
							if ( false === $layouts_plugin_status ) {
								//Layouts plugin inactive, this setting is needed
								update_option( $key, $value);
							}
						} else {
							update_option( $key, $value);
						}
					}
				}
				
			}
		}
	}
	
	public function wcviews_fix_theme_templates_after_import($reference_site_theme_data) {	
		
		//Set woocommerce_views_theme_template_file
		
		//Get currently active theme information
		$theme_information=wp_get_theme();
		
		//Retrieved the currently activated theme name
		$name_of_template=$theme_information->stylesheet;
		
		/**
		 * WooCommerce Views 2.6.6 , retrieved parent theme folder
		 */
		$parent_template = $theme_information->get( 'Template' );
	
		//Retrieved the reference site theme name
		if ((is_array($reference_site_theme_data)) && (!(empty($reference_site_theme_data)))) {
			
			$imported_site_theme= key($reference_site_theme_data);
		
			//Extract PHP template of reference site
			$refsite_template_path= reset($reference_site_theme_data);
			
			$non_default_origin='plugin';
			//Here we checked if non-default comes from inside the theme or the WooCommerce Views plugin itself
			if ((strpos($refsite_template_path, '/themes/') !== false)) {
				$non_default_origin = 'theme';
			}
			
			$reference_site_php_template=basename($refsite_template_path);
			
			if ($name_of_template == $imported_site_theme) {
				
				//Import only if the activated theme matches with the reference site
				//Get theme root path			
				$theme_root_template=$theme_information->theme_root;			
				
				//Define path to new PHP template after import unless its using default WooCommerce Templates
				if ('Use WooCommerce Default Templates' == $reference_site_php_template) {
					//Using default WC Templates
					$path_to_pagetemplate=$reference_site_php_template;		  
					  	
				} else {
					//Non-default
					//Verify origin
					if ('theme' == $non_default_origin) {
						//Child theme probable template path
						$path_to_pagetemplate			=	$theme_root_template.DIRECTORY_SEPARATOR.$name_of_template.DIRECTORY_SEPARATOR.$reference_site_php_template;
						
						//Parent theme probable template path
						$path_to_pagetemplate_parent	=	$theme_root_template.DIRECTORY_SEPARATOR.$parent_template.DIRECTORY_SEPARATOR.$reference_site_php_template;
						
					} elseif ('plugin' == $non_default_origin) {
						$path_to_pagetemplate = WOOCOMMERCE_VIEWS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'single-product.php'; 			    		
					}
				}
				
				if ($path_to_pagetemplate == $reference_site_php_template) {
					//Using default WC Templates
					$this->wcviews_save_php_template_settings($path_to_pagetemplate);
					
				} else {
					
					/**
					 * Non-default, prioritize the check on child theme first then if its not on child theme
					 * Probably this template can be found in the parent
					 */
					if ( file_exists( $path_to_pagetemplate ) ) {
					
						//Associated this PHP template with the Views Content Templates
						$this->wcviews_save_php_template_settings($path_to_pagetemplate);
					
					} elseif ( ( isset( $path_to_pagetemplate_parent ) ) && ( file_exists( $path_to_pagetemplate_parent ) ) ) {
						//Template is found in parent, use this.
						$this->wcviews_save_php_template_settings( $path_to_pagetemplate_parent );
					}
				}
			}
		}	
	}

	/**
	 * During plugin deactivation, let's clear the functions for conditional evaulations in Views.
	 * @access public
	 * @return void
	 */
		
	public function wcviews_clear_all_func_conditional_eval() {		

	   	//Define WC Views default functions
	   	$wcv_views_default_functions=$this->wcviews_functions;	   			
	   			
	   	//Get Views setting
	   	$views_setting= get_option('wpv_options');
	   	
	   	if ($views_setting) {
	   		
	   		//Views settings exists
	   		//Check if conditional functions are set by user previously
	   		if (isset($views_setting['wpv_custom_conditional_functions'])) {
	   					 
	   			//User has already set this, retrieved existing setting
	   			$existing_conditional_functions_setting=$views_setting['wpv_custom_conditional_functions'];
	   			
				if (is_array($existing_conditional_functions_setting)) {
					//$existing_conditional_functions_setting should be an array
					
					//Now let's loop through $existing_conditional_functions_setting then let's clear all WC Views functions on it
					$unsetted=array();
					foreach ($existing_conditional_functions_setting as $k=>$v) {
						
						if (in_array($v, $wcv_views_default_functions)) {
														
							 //This function is a WC Views function, unset
							 $unsetted[]=$v;
							unset($views_setting['wpv_custom_conditional_functions'][$k]);					     					
						}						
					}

					//Done, looping let's update the settings back to database					
					if (!(empty($unsetted))) {
						update_option('wpv_options',$views_setting);						
					}
					
	   			}
	   		} 
	   	}   		

	}
	
	/**
	 * in WooCommerce Views 2.4+, the admin setting is now included within Views.
	 * We want to make sure its before "settings" section of Views.
	 * 
	 * @access public
	 * @return void
	 */
	
	public function assign_proper_submenu_order_wcviews($menu_ord) {
		
		//Let's check if $menu_ord is TRUE
		if ($menu_ord) {
		   
			//Double check that all dependencies are set, proceed only if meet
			$missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();			
			
			if (empty($missing_required_plugin)) {
				
		   		//Set to true, access $submenu global
				global $submenu;			
			
				//Let's access Views sub-menus
			
				if (isset($submenu['views'])) {
				
					//Views menu set.	
					//We only want to customized menu order if we are sure WooCommerce Views are set.
			 		//Let's check if we can find the WooCommerce Views unordered submenu on it
				 
					$views_menu=$submenu['views'];
					$woocommerce_views_submenu_check=$this->wcviews_recursive_array_search('wpv_wc_views',$views_menu);
					$views_settings_submenu_check=	 $this->wcviews_recursive_array_search('views-settings',$views_menu);
					
					if (($woocommerce_views_submenu_check) && ($views_settings_submenu_check)) {
						
						//All arrays set				    	
						$wc_views_submenu_array=array($views_menu[$woocommerce_views_submenu_check]);
						unset($views_menu[$woocommerce_views_submenu_check]);				    	
						array_splice($views_menu, $views_settings_submenu_check, 0,$wc_views_submenu_array);
						
						unset($submenu['views']);
						
						$submenu['views'] = $views_menu;
						
					}
				
				}
			
			}
			
			
		}
				
		return $menu_ord;		
		
	}
	
	/**
	 * Aux function for recursive array search
	 *
	 * @access public
	 * @return mixed
	 */	
	public function wcviews_recursive_array_search($needle,$haystack) {
		foreach($haystack as $key=>$value) {
			$current_key=$key;
			if($needle===$value OR (is_array($value) && ($this->wcviews_recursive_array_search($needle,$value) !== false))) {
				return $current_key;
			}
		}
		return false;
	}
	
	/**
	 * Remove unnecessary template warnings in edit product page when a layout has been assigned
	 *
	 * @access public
	 * @return void
	 */	
	
	public function remove_template_warning_if_layoutset() {
		
		$screen_output= get_current_screen();
		$current_screen_loaded=$screen_output->id;
		
		global $woocommerce;
		
		if (is_object($woocommerce)) {
			
			//WooCommerce plugin activated
			if (isset($_GET['action']))	{
				
				$action=$_GET['action'];
				
				//Check if we are on product edit and this post type should be under WooCommerce control.
				if (('edit'==$action) && ('product'==$current_screen_loaded)) {
					
					if( defined('WPDDL_VERSION') ) {
							
						//Layouts plugin activated
						//Let's check first if all dependencies are set
						
						$missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();

						if (empty($missing_required_plugin)) {
					
							//All required dependencies are set
							//Get Views setting
							global $wpddlayout;
					
							if (is_object($wpddlayout)) {
								
								//Access Layouts post type object
								$layout_posttype_object=$wpddlayout->post_types_manager;
									
								if (method_exists($layout_posttype_object,'get_layout_to_type_object')) {
									
									//Check if product post type has been assigned with Layouts
									$result=$layout_posttype_object->get_layout_to_type_object( 'product' );
									$check_if_wc_using_layouts=get_option('woocommerce_views_theme_template_file');
									
									if (is_array($check_if_wc_using_layouts)) {
									   //Template file set, let's check if its Layouts template
									   $value=reset($check_if_wc_using_layouts);
									   $template_file=basename($value);	
									   
									   if (('single-product.php' ==  $template_file) && ($result)) {
									   	
									   		//Enqueue
									   		//Product has now layouts assigned
									   		$wc_views_version=WC_VIEWS_VERSION;
									   	  	wp_enqueue_script('wc-views-remove-layout-warnings', plugins_url('res/js/wcviews-removewarnings.js',__FILE__),array('jquery'),$wc_views_version);		    						   	
									   	
									   }  						   
										
									}

								}
							}
						}
					}
					
				}
			}
			
		}
		
		
	}
	
	/**
	 * If client assigns a Layout to shop page, make sure this one is used!
	 * Otherwise fall back to default product archive,etc.
	 *
	 * @access public
	 * @return integer
	 */
	public function use_layouts_shop_if_assigned($the_id,$the_layout) {		
		
		if( defined('WPDDL_VERSION') ) {
				
			//Layouts plugin activated
			//Let's check first if all dependencies are set
			$missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();
			
			if (empty($missing_required_plugin)) {
				
				//All dependencies are set
				//OK, first we need to know if we are on the shop page
				if (is_shop()) {
				   
				   //WooCommerce says this is a shop page, next we need to know if client has Layout assigned to this shop page
					global $wpddlayout;
					if (is_object($wpddlayout)) {
						if ((method_exists($wpddlayout,'get_layout_slug_for_post_object')) && (method_exists($wpddlayout,'get_layout_id_by_slug'))) {
							
							//We need to get the WooCommerce shop page ID
							if (function_exists('wc_get_page_id')) {
								$shop_page_id= wc_get_page_id( 'shop' );
								$shop_page_id = intval($shop_page_id);
								if ($shop_page_id > 0) {
								   //WooCommerce shop page available
								   $layout_slug_for_shop=$wpddlayout->get_layout_slug_for_post_object( $shop_page_id );
								   
								   if ($layout_slug_for_shop) {
								   		//OK we have Layouts assigned to this shop page, get its equivalent Layouts ID
								   		$the_id_shop= $wpddlayout->get_layout_id_by_slug( $layout_slug_for_shop );
								   		$the_id_shop= intval($the_id_shop);
								   		if ($the_id_shop > 0) {
								   			
								   			$the_id = $the_id_shop;
								   		}
								   }									
								}								
							}							
						}

					}
				 
				}				
			}
			
		}	

		return $the_id;
	}
	
	/**
	 * By default, let's not show gallery images in listings.
	 * Since 2.4.1
	 * @access public
	 * @return array
	 */	
	public function remove_gallery_on_main_image_at_listings( $attachment_ids, $postobject ) {		
		$is_listing					= FALSE;		
		$is_listing					= $this->wcviews_is_woocommerce_listing();		

		if ( ( is_array( $attachment_ids ) ) && ( !( empty( $attachment_ids ) ) ) ) {
			//Image with galleries, let's check if we are on listing
			if ( $is_listing ) {
				
				/**
				 * This is a WooCommerce product list page
				 */
				
				//Settings attachment_ids to empty array means we don't want a gallery displayed together with the main image
				//TRUE means an empty array will be set, FALSE not empty.
				$attachment_ids_to_empty_array = false;

			
				/**
				 * We retrieved user settings if it exist
				 * Stored on $wcviews_show_gallery_on_listings global variable
				 * TRUE means display gallery
				 * FALSE means don't display gallery
				 */	
				
				global $wcviews_show_gallery_on_listings;				
				if ( isset( $wcviews_show_gallery_on_listings ) ) {					
					if ( false === $wcviews_show_gallery_on_listings ) {
						//Don't show gallery
						$attachment_ids_to_empty_array = true;						
					}
					
				} 
				
				//Finally we set an empty attachments only if necessary.
				if ( true === $attachment_ids_to_empty_array ) {					
					$attachment_ids = array();
				}	
				
			} else {
				
			  //Catch situations when we are doing a Views loop and we are not showing any of these galleries in the images
				if ( defined('WCVIEWS_DOING_LOOP') ) {
					if (WCVIEWS_DOING_LOOP) {						
						$attachment_ids=array();
					}						
				}				
			}
		}		
		
		return $attachment_ids;
	}
	
	/**
	 * Display selection for PHP template archive in WooCommerce Views
	 * Since 2.4.1
	 * @access public
	 * @return void
	 */	
	
	public function wc_views_display_php_archive_template_html() {
		global $wcviews_edit_help;
		$woocommerce_views_supported_templates= $this->load_correct_archivetemplate_files_for_editing_wc();		
		$layouts_plugin_status=$this->wc_views_check_status_of_layouts_plugin();
		?>
	<div class="wpv-setting-container">
	<div class="wpv-settings-header wcviews_header_views">

		<h3>
		<?php _e('Product Archive Template File','woocommerce_views');?>
		<i class="icon-question-sign js-wcviews-display-tooltip"
				data-header="<?php echo $wcviews_edit_help['archive_template_assignment_section']['title']?>"
				data-content="<?php echo $wcviews_edit_help['archive_template_assignment_section']['content']?>"></i>
		</h3>
	</div>
			<div class="wpv-setting">

				<div id="archivephptemplateassignment_wc_views">
					<p><?php _e('Select the PHP template which will be used for WooCommerce product archive pages:','woocommerce_views');?></p>
					<p>
		<?php 
		if (!(empty($woocommerce_views_supported_templates))) {

			$var_selector='';
			$get_current_settings_wc_template=get_option('woocommerce_views_theme_archivetemplate_file');
			if 	($get_current_settings_wc_template) {
				
			   //Settings initialized	
				$get_key_template=key($get_current_settings_wc_template);
				$get_current_settings_wc_template_path=$get_current_settings_wc_template[$get_key_template];

				//Let's handle if user is originally using non-Layout supported PHP templates
				//Then user activates Layouts plugin
				if ($layouts_plugin_status) {														
					//Layouts activated
					if (!(in_array($get_current_settings_wc_template_path,$woocommerce_views_supported_templates))) {
									
						//User originally selected PHP template is not Layouts supported
						//Automatically use default WooCommerce Templates
						$this->wcviews_save_php_template_settings('Use WooCommerce Default Archive Templates');
						$get_current_settings_wc_template_path='Use WooCommerce Default Archive Templates';									
					}							
				} elseif (!(($layouts_plugin_status))) {
					   //Layouts deactivated
				   	   if (!(in_array($get_current_settings_wc_template_path,$woocommerce_views_supported_templates))) {
											
						//User originally selected PHP template is not Layouts supported
						//Automatically use default WooCommerce Templates
							$this->wcviews_save_php_template_settings('Use WooCommerce Default Archive Templates');
							$get_current_settings_wc_template_path='Use WooCommerce Default Archive Templates';
						}						   										
				}
			
				if ((is_array($woocommerce_views_supported_templates)) && (!(empty($woocommerce_views_supported_templates)))) {
					$counter_p=1;
					foreach ($woocommerce_views_supported_templates as $template_file_name=>$theme_server_path) {			

						$p_id='ptag_archive_'.$counter_p;
	   ?>
						
					
					
					<div class="template_selector_wc_views_div"
						id="<?php echo $p_id;?>">
						<input <?php echo $var_selector;?> type="radio"
							name="woocommerce_views_archivetemplate_to_override"
							value="<?php echo $theme_server_path?>"
							<?php if ($get_current_settings_wc_template_path==$theme_server_path) { echo "CHECKED";} ?>>				
							<?php 
									if ('Use WooCommerce Default Archive Templates' ==$template_file_name) {
									   //Clarity
										if ($layouts_plugin_status) {
											$template_file_name = "WooCommerce Plugin Default Archive Template (doesn't display layouts)";
										} else {
											$template_file_name = 'WooCommerce Plugin Default Archive Templates';
										}
										
									}
									echo $template_file_name;
							?>
							<a class="show_path_link" href="javascript:void(0)"><?php _e('Show template','woocommerce_views');?></a>
						<div class="show_path_wcviews_div" style="display: none;">
							<textarea rows="2" cols="50" class="inputtextpath" readonly />
							</textarea>
						</div>
					</div>
						<?php 
						$counter_p++;
						?>
					 	 <?php
					}
				} else {
							 	//not loaded                  
					 	 ?>
				   <p>
						<input type="radio" name="woocommerce_views_template_to_override"
							value="Use WooCommerce Default Archive Templates">
				   	<?php _e('Use WooCommerce Default Archive Templates','woocommerce_views');?>
				   </p>             			
					<?php
		 		}
		 			?>
					<?php
		 	} else {
							 	
				   //Settings for archive as in dB options, not yet initialized				
				   //Check if no template is saved yet.
					$status_template=$this->wc_views_check_if_using_woocommerce_default_archive_template();
							 	
					if ((is_array($woocommerce_views_supported_templates)) && (!(empty($woocommerce_views_supported_templates)))) {
						 $counter_p=1;
						 foreach ($woocommerce_views_supported_templates as $template_file_name=>$theme_server_path) {
				   			 $file_basename=basename($theme_server_path);  	            
				   			 $p_id='ptag_archive_'.$counter_p;
				   				 
				   		 ?>             
			 			<div class="template_selector_wc_views_div"
						id="<?php echo $p_id;?>">
						<input <?php echo $var_selector;?> type="radio"
							name="woocommerce_views_archivetemplate_to_override"
							value="<?php echo $theme_server_path?>"
							<?php 
							 //At this point, we can consider using default archive if client is not overriding templates
							 if ($file_basename=='Use WooCommerce Default Archive Templates')  {				
					   		   echo "CHECKED";			          	        
					  		  } 
							?>>
							<?php
								 if ('Use WooCommerce Default Archive Templates' ==$template_file_name) {
								 	//Clarity
								 		if ($layouts_plugin_status) {
											$template_file_name = "WooCommerce Plugin Default Archive Template (doesn't display layouts)";
										} else {
											$template_file_name = 'WooCommerce Plugin Default Archive Template';
										}
								 }
								 
								 echo $template_file_name;
								 ?>
								<a class="show_path_link" href="javascript:void(0)"><?php _e('Show template','woocommerce_views');?></a>
						<div class="show_path_wcviews_div" style="display: none;">
							<textarea rows="2" cols="50" class="inputtextpath" readonly />
							</textarea>
						</div>
					</div> 
							<?php $counter_p++;?>           		
						  	<?php
 					   }     
				   }    
			 }
				  		   ?>
		<?php 
		} 
		?>
		</p>
				</div>

			</div>
		</div>
	<?php if (!(empty($wcviews_edit_help['archive_template_assignment_section']['message_for_link']))) {?>
		<div class="toolset-help js-phptemplatesection">
			<div class="toolset-help-content">
				<p><?php echo $wcviews_edit_help['archive_template_assignment_section']['message_for_link']?></p>
			</div>
			<div class="toolset-help-sidebar">
				
			</div>
		</div>
   <?php
		 }
  }
	/**
	 * Method for loading correct archive template files for using with WooCommerce Views
	 * 
	 * @access public
	 * @return array
	 */
	public function load_correct_archivetemplate_files_for_editing_wc() {
		
		// Get all information about the parent and child theme!
		$theme = wp_get_theme ();
		$get_custom_theme_info = $this->theme_name_and_template_path ( $theme );
		$complete_template_files_list = $theme->get_files ( 'php', 1, true );
		$complete_template_files_list = $this->wc_views_filter_only_relevant_wc_templates_innerdir ( $complete_template_files_list );
		//$headers_for_theme_files = $theme->get_page_templates ();
		
		//Retrieve stylesheet directory URI for the current theme/child theme
		$get_stylesheet_directory_data=get_stylesheet_directory();
		
		if ((is_array ( $complete_template_files_list )) && (! (empty ( $complete_template_files_list )))) {
			$correct_templates_list = array ();
			$layouts_plugin_status = $this->wc_views_check_status_of_layouts_plugin ();
			
			foreach ( $complete_template_files_list as $key => $values ) {
				$pos_page = stripos ( $key, 'archive-product' );
								
				if ($pos_page !== false) {
					
					// https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/193344239/comments
					// When Layouts plugin is active, only show templates that have the_ddlayouts integration
					$is_theme_template_has_ddlayout = FALSE;
					$is_theme_template_looped = FALSE;
					
					if ($layouts_plugin_status) {
						//Layouts plugin activated
						//Ensure archive-product.php is checked at correct path
						$template_lower_case= strtolower($key);
						if ((strpos($template_lower_case, 'archive-product.php') !== false)) {
							//This is an archive product template at the user theme directory
							$key = str_replace($get_stylesheet_directory_data, "", $values);
							$key =ltrim($key,'/');
						}
						
						$is_theme_template_has_ddlayout= $this->wcviews_template_have_layout($key);						
						
					} else {
						// Layouts inactive, lets fallback to usual PHP looped templates
						// Emerson: Qualified theme templates should contain WP loops for WC hooks and Views to work
						$is_theme_template_looped = $this->check_if_php_template_contains_wp_loop ( $values );
					}
					
					// Add those qualified PHP templates only once
					if ($is_theme_template_looped) {
						$correct_templates_list [$key] = $values;
					} elseif ($is_theme_template_has_ddlayout) {
						// This has a call to ddlayout
						$correct_templates_list [$key] = $values;
					}
				}
			}
			
			if (! (empty ( $correct_templates_list ))) {
				
				// Has templated loops to return
				$correct_templates_list ['Use WooCommerce Default Archive Templates'] = 'Use WooCommerce Default Archive Templates';
				
				// Append the template name to the file names
				$correct_template_list_final = $this->wcviews_append_archivetemplatename_to_templatefilename ( $correct_templates_list, $get_custom_theme_info );
				
				// Include WooCommerce Views Default archive-product.phpp template
				if (defined ( 'WOOCOMMERCE_VIEWS_PLUGIN_PATH' )) {
					
					$template_path = WOOCOMMERCE_VIEWS_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'archive-product.php';
					
					if (file_exists ( $template_path )) {
						// Template exist
						$correct_template_list_final ['WooCommerce Views plugin default product archive template'] = $template_path;
					}
				}
				
				return $correct_template_list_final;
			} else {
				// In this scenario, no eligible templates are found from the clients theme.
				// Let's provide the defaults from templates inside the WooCommerce Views plugin
				
				$correct_templates_list ['Use WooCommerce Default Archive Templates'] = 'Use WooCommerce Default Archive Templates';
				
				if (defined ( 'WOOCOMMERCE_VIEWS_PLUGIN_PATH' )) {
					
					$template_path = WOOCOMMERCE_VIEWS_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'archive-product.php';
					
					if (file_exists ( $template_path )) {
						// Template exist
						$correct_templates_list ['WooCommerce Views plugin default product archive template'] = $template_path;
					}
				}
				
				return $correct_templates_list;
  			}
  		}
  	}
  	
  	/**
  	 * Append correct archive template name
  	 * Since 2.4.1
  	 * @access public
  	 * @return array
  	 */
  	
  	public function wcviews_append_archivetemplatename_to_templatefilename($correct_template_list,$get_custom_theme_info) {
  	
  		$correct_template_list_final=array();
  	
  		//The defaults array
  		$defaults_name_array=array('woocommerce/archive-product.php'=>__('Theme Custom Product Archive Template','woocommerce_views'));
  		 
  		if (is_array($correct_template_list)) {
  	
  			//Loop through the correct template list
  			foreach ($correct_template_list as $template_file_name=>$template_path) {
  					
			if (isset($defaults_name_array[$template_file_name])) {
  					//not included in default WP core page array
  					//Check if included in basic template name array
  					$template_name_retrieved=$defaults_name_array[$template_file_name];
  	
  					//Append theme name for clarity
  					$theme_name=$this->get_theme_name_based_on_path($template_path,$get_custom_theme_info);
  	
  					//Get correct theme name append
  					$theme_append=$this->theme_append_wcviews_name_correctly($theme_name);
  	
  					if (empty($theme_append)) {
  						//Theme name already contains 'theme' word, remove 'theme' from $template_name_retrieved
  						$template_name_retrieved=str_replace('Theme', '', $template_name_retrieved);
  					}
  					$template_name_appended="$theme_name $template_name_retrieved";
  					$correct_template_list_final[$template_name_appended]=$template_path;
  	
  			} elseif ($template_file_name != 'Use WooCommerce Default Archive Templates') {
  					//No match, dissect the filename
  					 
  					//Append theme name for clarity
  					$theme_name=$this->get_theme_name_based_on_path($template_path,$get_custom_theme_info);
  			
  					$dissected_template_file_name=$this->dissect_file_name_to_convert_to_templatename($template_file_name,$theme_name);
  					$dissected_template_file_name= $theme_name.' '.$dissected_template_file_name;
  					$correct_template_list_final[$dissected_template_file_name]=$template_path;
  			} else {
  					$correct_template_list_final['Use WooCommerce Default Archive Templates']='Use WooCommerce Default Archive Templates';
  			}
  	
  		}
  		  
  			return $correct_template_list_final;
  	
  		}
  	
  	}
  	
  	/**
  	 * Check if using Default WooCommerce core plugin archive templates
  	 * Since 2.4.1
  	 * @access public
  	 * @return boolean
  	 */
  	
  	public function wc_views_check_if_using_woocommerce_default_archive_template() {
  	
  		$the_active_php_template_option_thumbnails=get_option('woocommerce_views_theme_archivetemplate_file');
  	
  		if ((is_array($the_active_php_template_option_thumbnails)) && (!(empty($the_active_php_template_option_thumbnails)))) {
  			$the_active_php_template_thumbnails=reset($the_active_php_template_option_thumbnails);
  				
  			if ($the_active_php_template_thumbnails=='Use WooCommerce Default Archive Templates') {
  	
  				return TRUE;
  	
  			} else {
  	
  				return FALSE;
  			}
  		} else {
  	
  			//If option does not exist, return TRUE since it make sense that it defaults to WooCommerce Templates
  			return TRUE;
  		}
  	
  	}
  	
  	/**
  	 * Check if custom product archive PHP template exists inside theme directory
  	 * Since 2.4.1
  	 * @access public
  	 * @return string
  	 */
  	
  	public function wc_views_check_if_product_archive_template_exists() {
  	
  		$woocommerce_views_supported_templates= $this->load_correct_archivetemplate_files_for_editing_wc();
  	
  		$archive_product_template_found=FALSE;
  	
  		//Loop through the PHP templates array
  		if ((is_array($woocommerce_views_supported_templates)) && (!(empty($woocommerce_views_supported_templates)))) {
  	
  			foreach ($woocommerce_views_supported_templates as $template_name=>$template_path) {
  	
  				$template_file_name= basename($template_path);
  				if ($template_file_name=='archive-product.php') {
  					//Make sure this does not belong to WooCommerce Views
  					if ('WooCommerce Views plugin default product archive template' != $template_name) {
  						//Exist
  						return $template_path;
  					}  					
  					break;
  				}
  			}
  		}
  		 
  		return $archive_product_template_found;
  	
  	}
  	
  	/**
  	 * Save WooCommerce Views archive template settings to the options table.
  	 * @param  string $woocommerce_views_template_to_override
  	 * @access public
  	 * @return void
  	 */
  	
  	public function wcviews_save_php_archivetemplate_settings($woocommerce_views_template_to_override) {
  	
  		//Save template settings to options table
  		$option_name='woocommerce_views_theme_archivetemplate_file';
  	
  		//Template validation according to the status of Layouts plugin
  		$layouts_plugin_status=$this->wc_views_check_status_of_layouts_plugin();
  		$woocommerce_views_supported_templates= $this->load_correct_archivetemplate_files_for_editing_wc();
  		$woocommerce_views_template_to_override_slashed_removed=stripslashes(trim($woocommerce_views_template_to_override));
  	
  		//Let's handle if user is originally using non-Layout supported PHP templates
  		//Then user activates Layouts plugin
  		if ($layouts_plugin_status) {
  				
  			//Layouts activated
  			if (!(in_array($woocommerce_views_template_to_override_slashed_removed,$woocommerce_views_supported_templates))) {
  					
  				//User saved a PHP template which is not Layouts supported
  				//Automatically use default WooCommerce Templates
  				$woocommerce_views_template_to_override = 'Use WooCommerce Default Archive Templates';
  			}
  		} elseif (!(($layouts_plugin_status))) {
  			//Layouts deactivated
  				
  			if (!(in_array($woocommerce_views_template_to_override_slashed_removed,$woocommerce_views_supported_templates))) {
  					
  				//User saved a PHP template which is not Loops supported
  				//Automatically use default WooCommerce Templates
  				$woocommerce_views_template_to_override = 'Use WooCommerce Default Archive Templates';
  			}
  		}
  	
  		$template_associated=get_stylesheet();
  		$settings_value=array();
  		$settings_value[$template_associated]=stripslashes(trim($woocommerce_views_template_to_override));
  		$success=update_option( $option_name, $settings_value);
  	
  		//Reset content templates to none if using Default WooCommerce Template
  		//Template saved
  		$template_saved= stripslashes(trim($woocommerce_views_template_to_override));
  		  	
  		if ($template_saved=='Use WooCommerce Default Archive Templates') {
  				
  			//Reset WP archives to none
  			//All settings
			$this->reset_wp_archives_wcviews_settings();
  	
  		}
  	}

  	/**
  	 * Attempt to reset wp archives settings
  	 * Since 2.4.1
  	 * @access public
  	 * @return void
  	 */
  	
  	public function reset_wp_archives_wcviews_settings() {
  		
  		//Reset WP archives template
  		global $WP_Views;
  		if (!(method_exists($WP_Views,'get_options'))) {
  			return;
  		}
  		$views_settings_options_original = $WP_Views->get_options();
  		$views_settings_options = $views_settings_options_original;
  			
  		//Shop page reset
  		if (isset($views_settings_options['view_cpt_product'])) {
  		
  			//Make sure its not null
  			if (!(empty($views_settings_options['view_cpt_product']))) {
  				//Backup last archive template options table
  					
  				$last_wp_archive_template_used=$views_settings_options['view_cpt_product'];
  				update_option('wc_views_last_archive_template_used',$last_wp_archive_template_used);
  				 
  				//Set archive template to null
  				$views_settings_options['view_cpt_product']='';
  			}
  		}
  			
  		//Product cat reset
  		if (isset($views_settings_options['view_taxonomy_loop_product_cat'])) {
  				
  			//Make sure its not null
  			if (!(empty($views_settings_options['view_taxonomy_loop_product_cat']))) {
  				//Backup last archive template options table
  					
  				$last_wp_archive_cat_template_used=$views_settings_options['view_taxonomy_loop_product_cat'];
  				update_option('wc_views_last_catarchive_template_used',$last_wp_archive_cat_template_used);
  					
  				//Set archive template to null
  				$views_settings_options['view_taxonomy_loop_product_cat']='';
  		
  			}
  		}
  		
  		//Product tag reset
  		if (isset($views_settings_options['view_taxonomy_loop_product_tag'])) {
  				
  			//Make sure its not null
  			if (!(empty($views_settings_options['view_taxonomy_loop_product_tag']))) {
  				//Backup last archive template options table
  					
  				$last_wp_archive_tag_template_used=$views_settings_options['view_taxonomy_loop_product_tag'];
  				update_option('wc_views_last_tagarchive_template_used',$last_wp_archive_tag_template_used);
  					
  				//Set archive template to null
  				$views_settings_options['view_taxonomy_loop_product_tag']='';
  		
  			}
  		}  		
  	}
  	
  	/**
	 * Enforces the WCV archive product PHP template when the settings state so.
	 *
	 * @since 2.4.1	 
	 * @since 2.7.6 Fire the 'template_include' filter without actually applying it, 
	 *     because some third party plugins use it to load extra assets and resources.
	 */
  	public function woocommerce_views_activate_archivetemplate_redirect()
  	{
  	
  		//This affects the front end
  	
  		global $woocommerce;
  		if (is_object($woocommerce)) {
  			//WooCommerce plugin activated
  			if ((is_shop()) ||
  				(is_product_category()) ||
				(is_product_tag()) ||
  				(is_product_taxonomy())) 			 
  			 {
  				//Any WooCommerce product archives!
  				//Get template settings
  		   
  				$get_template_wc_template=get_option('woocommerce_views_theme_archivetemplate_file');
  		   
  				if ((is_array($get_template_wc_template)) && (!(empty($get_template_wc_template)))) {
  					 
  					$live_active_template=get_stylesheet();
  					$template_name_for_redirect=key($get_template_wc_template);
  					$template_path_for_redirect=$get_template_wc_template[$template_name_for_redirect];
  	
  					//Make sure this template change makes sense
  					if ($live_active_template==$template_name_for_redirect) {
  							
  						//Template settings exists, but don't do anything unless specified
  						if (!($template_path_for_redirect=='Use WooCommerce Default Archive Templates')) {
  								
  							//Template file selected, load it
  							if (file_exists($template_path_for_redirect)) {
  								$this->wcviews_support_loading_layouts_integration_plugins();
								$template_path_for_template_include_filter = apply_filters( 'template_include', $template_path_for_redirect );
  								include($template_path_for_redirect);
  								exit();
  							}
  						}
  					}
  				}
  			}
  		}
  	
  	}
  	
  	/**
  	 * Load WooCommerce Views archive template PHP settings (as defined on its backend)
  	 * Since 2.4.1
  	 * @access public
  	 * @return void
  	 */
  	
  	public function wc_views_reset_wc_defaultarchive_after_theme_switching() {

  		//Run the method to use WooCommerce default templates
  		$is_using_wc_default_template=$this->wc_views_check_if_using_woocommerce_default_archive_template();
  		 
  		//Reset this option by deletion
  		delete_option('wc_views_nondefaultarchivetemplate_changed');
  		if (!($is_using_wc_default_template)) {
  		
  			//Using non-default template,
  			update_option('wc_views_nondefaultarchivetemplate_changed','yes');
  			 
  		} else {
  		
  			//Using default WooCommmerce template,
  			update_option('wc_views_nondefaultarchivetemplate_changed','no');
  		}
  		
  		//woocommerceviews-111:  Do not change WooCommerce Views settings on theme change
  		$standard_template	= $this->wc_views_return_standard_archiveproduct_template_path();
  		$this->wcviews_save_php_archivetemplate_settings( $standard_template );
  		
  	}

  	/**
  	 * Return the standard archive product template path inside WCV plugin for products archives.
  	 * Otherwise revert to default WooCommerce core template inside WC core plugin.
  	 *
  	 * since @2.7.2
  	 */
  	public function wc_views_return_standard_archiveproduct_template_path() {
  		
  		$template_path = WOOCOMMERCE_VIEWS_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'archive-product.php';
  		
  		if ( !( file_exists( $template_path ) ) ) {
  			//For some reason, this template does not exist, revert to WooCommerce core default templates
  			$template_path	= 'Use WooCommerce Default Archive Templates';
  		}
  		return $template_path;
  		
  	}
  	
  	/**
  	 * Helper method: Filter function for $template.
  	 * Ensures it returns archive-product.php from the WooCommerce plugin templates.
  	 * Hooked to template_include filter.
  	 * @access public
  	 * @param  string $template
  	 * @return string
  	 */
  	
  	public function wc_views_archivetemplate_loader($template) {
  	
  		global $woocommerce;
  	
  		if (is_object($woocommerce)) {
  			
  			//OK, We have WooCommerce activated
  			//These functions are safe to use
  			if ((is_shop()) ||
  					(is_product_category()) ||
  					(is_product_tag()) ||
  					(is_product_taxonomy())) {
  						
  						/** EMERSON: These are not rendered or read when using the setting 'WooCommerce Views plugin default product archive template' */
  						/** This setting is found in 'Product Archive Template File' section in WooCommerce Views settings.
  						/** This is only read if the user is selecting the setting 'WooCommerce Plugin Default Archive Templates'*/
  						
  						/** However, there are cases beyond WooCommerce Views and WooCommerce plugin controls where there are archive template overrides present in the theme */
  						/** For example using woocommerce.php in the theme root */
  						
  						/** According to WC note: http://docs.woothemes.com/document/template-structure/
  						 *  When creating woocommerce.php in your theme’s folder, you won’t then be able to override the 
  						 *  woocommerce/archive-product.php custom template (in your theme) as woocommerce.php has the priority over all other template files. 
  						 *  This is intended to prevent display issues.
  						 */
  						/** Presence of this wooommerce.php template file assumes user wants to render this and not default WooCommerce core plugin templates */
  						/** So let's checked if the template to be filtered is woocommerce.php and let it pass */
  						
  						$basename_template = basename($template);
  						
  						if ('woocommerce.php' != $basename_template) {
  							
  							//Template is not the woocommerce.php, proceed to loading WC core archive default template
  							$file='archive-product.php';
  							$template = $woocommerce->plugin_path() . '/templates/' . $file;
  						}
  						 
  					}		    				
  		}

  		return $template;
  	}
  	
  	/**
  	 * Ensure that when setting to default WooCommerce, its own templates should be loaded.
  	 * Since 2.4.1
  	 * @access public
  	 * @return void
  	 */
  	
  	public function wc_views_dedicated_archivetemplate_loader() {
  	
  		add_filter( 'template_include',array( $this, 'wc_views_archivetemplate_loader' ) );
  			
  	}
  	
  	/**
  	 * Helper method to fall back to WooCommerce core archive front end rendering or Views archive
  	 * If Layouts plugin is activated but no Layouts has been assigned to an archive
  	 * Since 2.4.1
  	 * @access public
  	 * @return void
  	 */
  	
  	public function wc_views_check_if_anyproductarchive_has_layout() {
  		
  		if( defined('WPDDL_VERSION') ) {
  				
  			//Layouts is activated on this site
  				
  			global $wpddlayout,$woocommerce;
  				
  			if ((is_object($wpddlayout)) & (is_object($woocommerce))) {
  				
  				//Rule below applies only to WooCommerce product archives
  				if ((is_shop()) || (is_product_category()) || (is_product_tag()) || (is_product_taxonomy())) {
  					if (class_exists('WPDD_Layouts_RenderManager')) {
  						$layouts_render_manager_instance=WPDD_Layouts_RenderManager::getInstance();
  						if (method_exists($layouts_render_manager_instance,'get_layout_id_for_render')) {
  								
  							$layouts_id_to_render=$layouts_render_manager_instance->get_layout_id_for_render( false, $args = null );
  							$layouts_id= intval($layouts_id_to_render);
  							if ($layouts_id > 0) {  		
  								//This constant defined only once							
  								define('WC_VIEWS_ARCHIVES_LAYOUTS', true);
  							}  								
  						}  						
  					}
  				}	
  			} 			
  		
  		}  		
  	}
  	
  	/**
  	 * Import archive settings correctly
  	 * Since 2.4.1
  	 * @access public
  	 * @return void
  	 */
  	
  	public function wcviews_fix_theme_archivetemplates_after_import($reference_site_theme_data) {
  	
  		//Set woocommerce_views_theme_template_file
  	
  		//Get currently active theme information
  		$theme_information=wp_get_theme();
  	
  		//Retrieved the currently activated theme name
  		$name_of_template=$theme_information->stylesheet;
  	
  		//Retrieved the reference site theme name
  		if ((is_array($reference_site_theme_data)) && (!(empty($reference_site_theme_data)))) {
  				
  			$imported_site_theme= key($reference_site_theme_data);
  	
  			//Extract PHP template of reference site
  			$refsite_template_path= reset($reference_site_theme_data);
  			
  			$non_default_origin='plugin';
  			//Here we checked if non-default comes from inside the theme or the WooCommerce Views plugin itself
  			if ((strpos($refsite_template_path, '/themes/') !== false)) {
  				$non_default_origin = 'theme';
  			}
  			
  			$reference_site_php_template=basename($refsite_template_path);
  				
  			if ($name_of_template == $imported_site_theme) {
  	
  				//Import only if the activated theme matches with the reference site
  				//Get theme root path
  				$theme_root_template=$theme_information->theme_root;
  	
  				//Define path to new PHP template after import unless its using default WooCommerce Templates
  				if ('Use WooCommerce Default Archive Templates' == $reference_site_php_template) {
  					//Using default WC Templates
  					$path_to_pagetemplate=$reference_site_php_template;
  					 
  				} else {
  					
  					//Non-default
  					//Verify origin
  					if ('theme' == $non_default_origin) {
  						$path_to_pagetemplate=$theme_root_template.DIRECTORY_SEPARATOR.$name_of_template.DIRECTORY_SEPARATOR.$reference_site_php_template;
  					} elseif ('plugin' == $non_default_origin) {
  						$path_to_pagetemplate=WOOCOMMERCE_VIEWS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'archive-product.php';
  					}
  					
  				}
  	
  				if ($path_to_pagetemplate == $reference_site_php_template) {
  					//Using default WC Templates
  					$this->wcviews_save_php_archivetemplate_settings($path_to_pagetemplate);
  	
  				} else {
  					//Non-default
  					if (file_exists($path_to_pagetemplate)) {  	
  						//Associated this PHP template with the Views Content Templates
  						$this->wcviews_save_php_archivetemplate_settings($path_to_pagetemplate);  	
  					}
  				}
  			}
  		}
  	}

  	/**
  	 * Fix new nav menus occurring in WooCommerce 2.3.4
  	 * Since 2.5
  	 * @access public
  	 * @return void
  	 */
  	
  	public function wcviews_remove_filter_for_wc_endpoint_title() {

  		if( defined('WPDDL_VERSION') ) {
  				
  			//Layouts plugin activated

  			//All required dependencies are set
  			//Get Views setting
  			global $wpddlayout;
  		
  			if (is_object($wpddlayout)) {
  				remove_filter( 'the_title', 'wc_page_endpoint_title' );
  			}
  		} 		
  	}

  	/**
  	 * Product meta shortcode
  	 * Since 2.5
  	 * @access public
  	 * @return void
  	 */
  	
  	public function wpv_woo_product_meta_func($atts) {
  		
  		global $post,$woocommerce;
  		
  		ob_start();
  		
  		$product =$this->wcviews_setup_product_data($post);
  		
  		//Check if $product is set
  		if (isset($product)) {
  			//Let's checked if product type is set  			
  			//Let's checked if it contains sensible value
  			$product_type	= $this->wc_views_get_product_type( $product );
  			if (!(empty($product_type))) {
  				//Yes product types exist and set
  					
  				if ( function_exists( 'woocommerce_template_single_meta' ) ) {
  						
  					woocommerce_template_single_meta();
  						
  					/**Let's marked this shortcode execution */
  					$this->wcviews_shortcode_executed();  						
  					return ob_get_clean();  					
  				}	
  			}  			
  		}  		
  	}
  	
  	/**
  	 * Displays the number of items added in WooCommerce Cart
  	 * Please synchronize any changes on this method with 'woocommerce_views_add_to_cart_fragment'
  	 * Original source is derived from this doc: http://docs.woothemes.com/document/show-cart-contents-total/
  	 * Since 2.5.1
  	 * @access public
  	 * @return void
  	 */
  	
  	public function wpv_woo_cart_count_func($atts) {
  		
  		global $woocommerce;
  		
  		ob_start();
  		
  		if (is_object($woocommerce)) {
		   //WooCommerce plugin activated
		   //Let's checked the cart count
		   
			$cart_count=WC()->cart->cart_contents_count;
			$cart_count= intval($cart_count); 
			
			//Add count to class
			$cart_class = $cart_count;

			if ($cart_count < 1) {
				//Nothing is added on cart
				$cart_count='';    
				$cart_class = 0;
			}
	?>	        
			<span class='wcviews_cart_count_output wcviews_cart_count_<?php echo $cart_class;?>'><?php echo $cart_count; ?></span>
	<?php 	    
			/**Let's marked this shortcode execution */
			$this->wcviews_shortcode_executed();	
	  		return ob_get_clean();
  		}
  	}
  	
  	/**
  	 * Ajaxify version-Displays the number of items added in WooCommerce Cart
  	 * Original source is derived from this doc: http://docs.woothemes.com/document/show-cart-contents-total/
  	 * Since 2.5.1
  	 * @access public
  	 * @return void
  	 */
  	public function woocommerce_views_add_to_cart_fragment( $fragments ) {

  		global $woocommerce;
  	
  		ob_start();
  	
	  	if (is_object($woocommerce)) {
	  		//WooCommerce plugin activated
	  		//Let's checked the cart count
	  		 
	  		$cart_count=WC()->cart->cart_contents_count;
	  		$cart_count= intval($cart_count);

	  		//Add count to class
	  		$cart_class = $cart_count;
	  		
	  		if ($cart_count < 1) {
	  			//Nothing is added on cart
	  			$cart_count='';
	  			$cart_class = 0;
	  			 
	  		}
	?>
			<span class='wcviews_cart_count_output wcviews_cart_count_<?php echo $cart_class;?>'><?php echo $cart_count; ?></span>  	
	<?php 		  		
	  		//Doing AJAX	
	  		$fragments['span.wcviews_cart_count_output'] = ob_get_clean();
	  		return $fragments;

	  	}
	  }
	  
  	/**
  	 * WooCommerce Views shortcode to display only the reviews and not in tab.
  	 * Since 2.5.1
  	 * @access public
  	 * @return array
  	 */
  	
  	public function wpv_woo_show_displayreviews_func($atts) {
  		 
  		global $woocommerce,$post;
  		if (is_object($woocommerce)) {
  			if (is_product()) {
  	
  				ob_start();
  				global $product;
  				$product =$this->wcviews_setup_product_data($post);
  				if ((  function_exists( 'woocommerce_default_product_tabs' ) ) && ((isset($product)))) {
  						
  					//Tabs set
  						
  					$tabs_default= woocommerce_default_product_tabs();
  					if (isset($tabs_default['reviews'])) {
  	
  						//Reviews tab
  						$key='reviews';
  						$tab=$tabs_default['reviews'];
  						
  						/**
  						 * WooCommerce Views 2.6.1 +
  						 * Allow wpv-woo-reviews and wpv-woo-display-tabs to be used to together.
  						 * wpv-woo-display-tabs removes the comment template by default
  						 * If wpv-woo-reviews is called along with wpv-woo-display-tabs, it calls to the WooCommere single review comment template
  						 * We remove this filter added on wpv-woo-display-tabs tabs for proper rendering.
  						 */
  						global $wcviews_comment_template_filtered;
  						if ( isset( $wcviews_comment_template_filtered ) ) {
  							if ( true === $wcviews_comment_template_filtered )	{  								
  								remove_filter('comments_template',array(&$this,'wc_views_comments_template_loader'),999);
  							}
  						}  						
  						call_user_func( $tab['callback'], $key, $tab );
  	
  					}
  				}
  	
  				/**Let's marked this shortcode execution */
  				$this->wcviews_shortcode_executed();
  				
  				return ob_get_clean();
  			}
  		}
  	}
  	
  	public function wcviews_before_display_post ($post, $view_id) {
  		
  		if (is_object($post)) {
  			$post_type=$post->post_type;
  			global $woocommerce;
  			if ('product' == $post_type) {
  				if (is_object($woocommerce)) {
  					if ( !defined('WCVIEWS_DOING_LOOP') ) {
  						define('WCVIEWS_DOING_LOOP', true);
  						  						
  						//Disable default WordPress core texturization on WooCommerce Views shortcodes
  						remove_filter( 'the_content', 'wptexturize'        );  						
  					}  					
  				} 				
  			}  			
  		}
  	}
  	
  	public function wcviews_after_display_post ($post, $view_id) {
  	
  		if (is_object($post)) {
  			$post_type=$post->post_type;
  			global $woocommerce;
  			if ('product' == $post_type) {
  				if (is_object($woocommerce)) {
  					if ( !defined('WCVIEWS_DOING_LOOP') ) {
  						define('WCVIEWS_DOING_LOOP', false);
  						
  						//Re-enable default WordPress core texturization on WooCommerce Views shortcodes after Views loop
  						add_filter( 'the_content', 'wptexturize'        );  						
  					}
  				}
  			}
  		}
  	}
  	
  	/** Check if a product category being loaded has a subcategory */
  	/** Example usage:
  	 [wpv-if evaluate="woo_has_product_subcategory() = 1"]
  	 This product category has a subcategory.
  	 [/wpv-if]
  	 [wpv-if evaluate="woo_has_product_subcategory() = 0"]
  	 This product category has does not have a subcategory.
  	 [/wpv-if]
  	 */
  	public function woo_is_product_subcategory_func() {
  	
  		global $woocommerce;
  		$bool=FALSE;
  		if (is_object($woocommerce)) {
  			if (function_exists('is_product_category')) {
  				if ( is_product_category() ) {
  						
  					//This is a product subcategory
  					$term 			= get_queried_object();
  					$parent_id 		= empty( $term->term_id ) ? 0 : $term->term_id;
  						
  						
  					// NOTE: using child_of instead of parent - this is not ideal but due to a WP bug ( http://core.trac.wordpress.org/ticket/15626 ) pad_counts won't work
  					$product_categories = get_categories( apply_filters( 'woocommerce_product_subcategories_args', array(
  							'parent'       => $parent_id,
  							'menu_order'   => 'ASC',
  							'hide_empty'   => 0,
  							'hierarchical' => 1,
  							'taxonomy'     => 'product_cat',
  							'pad_counts'   => 1
  					) ) );
  					
  					if (is_array($product_categories)) {
  						
  						if (!(empty($product_categories))) {
  							
  							$bool=TRUE;
  							return $bool;	
  						}
  						
  					}
  						
  				}	
  			}  			
  		}
  		return $bool;
  	} 

	/** NEW: WooCommerce Views 2.5.5 */
	/** Check if the WooCommerce Shop Page display is set to 'Show categories and subcategories */
	/** This setting is found in WooCommerce -> Settings -> Products -> Display -> Shop page Display */
	/** This is mainly used in the Views -> WordPress archive for Shop page customization mainly to check if this setting is enabled.  */
	/** To use this conditional function, copy woocommerce-views/archive-product.php to your theme/woocommerce/archive-product.php */
	/** Then delete this line in the theme/woocommerce/archive-product.php: */
	/** woocommerce_product_subcategories() 
	/** The purpose is for you to override the display with Toolset Views */
	/** To actvate this custom archive template, go to your WooCommerce Views settings in the backend and select this archive template that is now copied to your theme. */
	/** You should be able to see this copied template under 'Product Archive Template File' */
 
	/** In your Views -> WordPress archive designed to customize WooCommerce shop page, you can then use this conditional function. */
	/** This conditional is designed to work only on WooCommerce shop page, not anywhere else */

	/** Example usage inside WordPress archive Loop output
	 [wpv-if evaluate="woo_shop_display_is_categories() = 1"]
	 	[wpv-view name="Your View to customize categories display in SHOP page"]
	 [/wpv-if]
	 [wpv-if evaluate="woo_shop_display_is_categories() = 0"]
	 	Shop page is set to display product items.
	 [/wpv-if]
	 */
  	
  	public function woo_shop_display_is_categories_func() {
  		 
  		global $woocommerce;
  		$bool=FALSE;
  		if (is_object($woocommerce)) {
  			if (function_exists('is_shop')) {
  				$woocommerce_shop_page_display=get_option( 'woocommerce_shop_page_display');
  				if ( is_shop() && ('subcategories' ==  $woocommerce_shop_page_display)) {
  					$bool=TRUE;
  				}
  			}
  		}
  		return $bool;
  	}
  	 	
  	/** For WooCommerce review system compatibility, we removed this comment_clauses so it will be handled solely by WooCommerce review templates*/
  	public function wcviews_multilingual_reviews_func() {  		
  		global $sitepress,$woocommerce;
  		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {  			
  			if ((is_object($sitepress)) && (is_object($woocommerce))) {							
				if (is_admin()) {			
  					remove_filter( 'comments_clauses', array( $sitepress, 'comments_clauses' ), 10, 2 );  
				} else {
					if (is_product()) {
						remove_filter( 'comments_clauses', array( $sitepress, 'comments_clauses' ), 10, 2 );						
					}					
				}  				  				
  			}  			
  		}  		
  	}
  	
  	public function wcviews_review_templates_handler() {
  		add_action( 'wp',array($this,'wcviews_multilingual_reviews_func'),20);
  		add_action( 'wp_loaded',array($this,'wcviews_multilingual_reviews_func'),20);
  	}
  	//Returns TRUE if using Views 1.10+
  	public function wc_views_modern_views_toolbox_check() {

  		if ( defined( 'WPV_VERSION' )) {
  			if (version_compare(WPV_VERSION, '1.10', '<')) {
  	
  				return FALSE;
  	
  			} else {  	
  				return TRUE;  	
  			}  	
  		} else {
  		  return FALSE;	
  		}  	
  	}
  	
  	public function wcviews_do_other_things_in_init() {
  		add_filter( 'the_content', array( $this, 'wcviews_restore_wptexturize' ), 999, 1 );
  		add_filter( 'get_layout_id_for_render', array( $this, 'wcviews_disable_wptexturize_layouts' ), 50, 2 );

  	}
  	
  	public function wcviews_restore_wptexturize($contentfiltered) {
  		
  		if ( $this->wcviews_disabled_texturize ) {
			add_filter( 'the_content', 'wptexturize'        );
  			$this->wcviews_disabled_texturize = false;
  		} 		

  		return $contentfiltered;
  		
  	}
  	
  	public function wcviews_disable_wptexturize_layouts($processed_id,$layout_object) {  		
  		
  		global $wpddlayout,$woocommerce,$layout_disable_texturize;  		
  		if (is_object($wpddlayout)) {
  			if ((method_exists($wpddlayout,'get_queried_object')) && 
  				(method_exists($wpddlayout,'get_layout_slug_for_post_object')) &&
  				(method_exists($wpddlayout,'get_layout_id_by_slug'))
  				) {
  				//Let's get the queried object , to check if this is product.  				
  				$the_queried_object = $wpddlayout->get_queried_object();	
  				if (is_object($the_queried_object)) {
  					
  					//Get id information
  					if ((isset($the_queried_object->ID)) && (isset($the_queried_object->post_type))) { 
	 					$the_product_id=$the_queried_object->ID;
	 					$the_product_id=intval($the_product_id);
	 					
	 					//Get post type information of the queried object
	 					$the_post_type=$the_queried_object->post_type;
	 					
	 					//Get information of the assigned layout of this product if any
						$layout_assigned_to_product=$wpddlayout->get_layout_slug_for_post_object($the_product_id);
						if (!(empty($layout_assigned_to_product))) {
							//This product has assigned layout
							$the_id_of_this_layout= $wpddlayout->get_layout_id_by_slug( $layout_assigned_to_product );
	
							$the_id_of_this_layout=intval($the_id_of_this_layout);
							$processed_id_int=intval($processed_id);
							
							if ($the_id_of_this_layout ===$processed_id_int) {
							   //About to render this layout, remove wptexturize
								remove_filter( 'the_content', 'wptexturize'        );
								$layout_disable_texturize=TRUE;
							}
						}
  					} 					
  				} 				
  			}  			
  		}  		
  		
  		return $processed_id;
  	}
  	
  	/**
  	 *  When a stock level is reduced, no need to loop all products and use the object being passed
  	 *  Then loop only through the ordered items.
  	 *  This will fix performance issues for sites with massive number of products.
  	 *  since WooCommerce Views 2.5.6
  	 */
  	
  	public function wcviews_reduce_stock_level_field($order_object) {
  		
  		//Define the stock WC Views stock field
  		$views_woo_in_stock = 'views_woo_in_stock';
  		
  		if (method_exists($order_object,'get_items')) {
  			
  			//Get the purchased products associated with this order
  			$purchased_products = $order_object->get_items();
  			
  			if (is_array($purchased_products) && (!(empty($purchased_products)))) {
  				
  				//Loop through the ordered products
  				foreach ($purchased_products as $k=>$product_details) {
  					
  					//Get the product id
  					if (isset($product_details['product_id'])) {
  						$product_id=$product_details['product_id'];
  						$product_id=intval($product_id);
  						if ($product_id > 0) {

  							$post=get_post($product_id);
  							
  							//Get product object from post
  							$product = $this->wcviews_setup_product_data($post);
  							
  							if (isset($product)) {  								
  								 
  								//Check if product is in stock
  								$product_on_stock_boolean=$product->is_in_stock();
  								 
  								//"0" adjustment
  								$product_on_stock_boolean=$this->for_views_null_equals_zero_adjustment($product_on_stock_boolean);  								
  								 
  								//Save to stock WC Views stock field
  								$success_stock=update_post_meta($product_id,$views_woo_in_stock,$product_on_stock_boolean);
  							}
  							
  								
  						}
  					}
  				}
  			}
  		
  		}
  		
  		
  	}
  	
  	/**
  	 * Added compatibility to Dokan Plugin
  	 * Handle edit products on front end and make sure it returns the Dokan Template
  	 * since WooCommerce Views 2.5.6
  	 * Returns TRUE if if its a Dokan edit product page
  	 */
  	
  	protected function wcviews_is_dokan_edit_product() {
  		
  		//This defaults to FALSE
  		$dokan_edit_product=false;
  		
  		//Check if we have Dokan plugin activated on this site  		
  		if ( defined('DOKAN_PLUGIN_VERSION') ) {
  			
  			//Activated, let's check if we are on Dokan edit product page
  			if ( get_query_var( 'edit' ) && is_singular( 'product' ) ) {
  				
  				$dokan_edit_product=true;
  				
  			}
  				
  		}
  		
  		return $dokan_edit_product;
  	}
  	
  	/**
  	 * Helper method to declare that a WooCommerce Views shortcode has been executed on front end.
	 * A call to this method should set the property 'wcviews_shortcode_executed' to TRUE.
  	 * @since 2.5.7
  	 */
  	private function wcviews_shortcode_executed() {  		
  		
  		/**Function is called
  		 * Implies a WC Views shortcode has been executed  		 * 
  		 */
  		$we_have_executed_shortcode=$this->wcviews_shortcode_executed;
  		$default_woocommerce_body_class_loaded =$this->wcviews_woocommerce_default_class_loaded;
  		
  		//Step1 checked if already set to TRUE
  		if (!($we_have_executed_shortcode)) {
  			
			//Not yet, then we should set wcviews_shortcode_executed to TRUE
  			$this->wcviews_shortcode_executed=TRUE;  				
  			 			
  		}
  	}
  	/**
  	 * Helper method to check if woocommerce is activated
  	 * Modern version
  	 * @since 2.5.7
  	 * Derive from here: https://docs.woothemes.com/document/query-whether-woocommerce-is-activated/
  	 */
  	public function is_woocommerce_activated() {
  		if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
  	}
  	
  	/**
  	 * Hook in method for removal of WooCommerce Class in body for pages that does not involved WC at all
  	 * @since 2.5.7
  	 */
  	public function wcviews_remove_woocommerce_class() {
  		$we_have_executed_shortcode=$this->wcviews_shortcode_executed;
  		if (!($we_have_executed_shortcode)) {
  			
  			//False implies, this page does not execute any WC Views shortcodes
  			//'woocommerce' class is not needed in the body
  			//But let's not removed in cases where WooCommerce Views is activated
  			//And using default WooCommerce core template , can be checked if we are loading
  			//Any WC pages using conditional tag is_woocommerce()
  			
  			$is_using_wc_default_template=$this->wc_views_check_if_using_woocommerce_default_template();
  			$wc_is_activated= $this->is_woocommerce_activated();
  			$is_woocommerce=false;
  			
  			if ($wc_is_activated) {
  				if (is_woocommerce()) {
  					$is_woocommerce=true;  					
  				}
  			}  			
  			
	  			if (!($is_woocommerce)) {
	  				/** Code to remove class below */
	  			?>
	  					<script>
	  						if ( undefined !== window.jQuery ) {
	  							(function($) {
	  								$("body").removeClass("woocommerce");
	  							})(jQuery);
	  						}
	  					</script>
	  					
	  			<?php
	  			}
  			}  		
  	}
  	/**
  	 * Added compatibility of WooCommerce Views settings with Toolset packager.
  	 * @since 2.5.7
  	 */
  	public function wcviews_embedded_support_basic($data_passed) {
  		
  		//Check if we have 	Toolset packager activated  		
  		if ( defined('WPOTGTP_TOOLSET_PACKAGER_VERSION') ) {
  			
  			//Activated, we check if it's a Toolset packager export event
  			if ( is_admin()) {  				
  				if ( defined('DOING_AJAX') && DOING_AJAX ) {
  					
  					//AJAX event, validate action
  					if (isset($_POST['action'])) {
  						$views_export_action= $_POST['action'];
  						if ('export_views' == $views_export_action) {  							
  							
  							//We are exporting Views via Toolset packager  							
  							if (class_exists('WPOTGTP_Toolset_Packager_Main')) {
  								$wpotgtp_wcviews_instance = new WPOTGTP_Toolset_Packager_Main();
  								if (is_object($wpotgtp_wcviews_instance)) {
  									if (isset($wpotgtp_wcviews_instance->_wptpexp)) {
  										$toolsetpackagerexporter_instance=$wpotgtp_wcviews_instance->_wptpexp;
  										if (isset($toolsetpackagerexporter_instance->current_template_toolset_import_dir)) {
  											$current_template_export_dir=$toolsetpackagerexporter_instance->current_template_toolset_import_dir;
  											if ((!(empty($current_template_export_dir))) && (file_exists($current_template_export_dir))) {
  												$wcviews_slug_for_packager=$this->wcviews_toolset_packager_slug;
  												$wcviews_core_settings_slug= $this->wcviews_wc_core_slug;  												
  												if ((!(empty($wcviews_slug_for_packager))) && (!(empty($wcviews_core_settings_slug)))) {
  													$complete_import_path = $current_template_export_dir .DIRECTORY_SEPARATOR. $wcviews_slug_for_packager;
  													$complete_import_path_wccore = $current_template_export_dir .DIRECTORY_SEPARATOR. $wcviews_core_settings_slug;
  													
  													/** Export WooCommerce Views settings */ 												
  													$xmlString = $this->wcviews_export_settings();  	
  													if (is_writable($current_template_export_dir)) {
  														
  														//Folder writable 
  														$dir=false;  														
  														if (!file_exists($complete_import_path)) {
  															//Path does not exist,create
  															$dir = mkdir($complete_import_path);
  														} else {
  															//Path exist
  															$dir=true;
  														}

		  												if ($dir) {
		  													//Directory exist
		  													$filename = "settings.xml";  		
		  													$path_to_write=$complete_import_path .DIRECTORY_SEPARATOR. $filename;
		  													if (is_writable($complete_import_path)) {
		  														//Writable
		  														$res = file_put_contents($path_to_write, $xmlString);
		  													}		  													
		  												}
  													}
  													
  													/** Export WooCommerce core settings */
  													$xmlString_core = $this->wcviews_export_core_wcsettings();
  													if (is_writable($current_template_export_dir)) {
  													
  														//Folder writable
  														$dir_core=false;
  														if (!file_exists($complete_import_path_wccore)) {
  															//Path does not exist,create
  															$dir_core = mkdir($complete_import_path_wccore);
  														} else {
  															//Path exist
  															$dir_core=true;
  														}
  													
  														if ($dir_core) {
  															//Directory exist
  															$filename_core = "settings.xml";
  															$path_to_write_core=$complete_import_path_wccore .DIRECTORY_SEPARATOR. $filename_core;
  															if (is_writable($complete_import_path_wccore)) {
  																//Writable
  																$res_core = file_put_contents($path_to_write_core, $xmlString_core);
  															}
  														}
  													} 													
  												}  												
  											}  											
  										}  										
  									}  									
  								}  								
  							}  							
  						}  						
  					}  					
  				}
  				
  			}
  		}
  		
  		return $data_passed;
  		
  	}
  	/**
  	 * Added WooCommerce Views settings import in Toolset packager import.
  	 * @since 2.5.7
  	 */  	
  	public function wcviews_toolset_packager_import() {
  		
  		 if ( is_admin()) {  				
  				if ( defined('DOING_AJAX') && DOING_AJAX ) {
  					
  					//AJAX event, validate action
  					if (isset($_POST['action'])) {
  						$import_action= $_POST['action'];
  						if ('installer_ep_run' == $import_action) { 
  							
  							//Installer initiated import  							
  							$requisite_check=$this->wcviews_check_requisite_toolset_packager_import();
  							if ($requisite_check) {
  								//Requisites meet
  								//Get XML path
  								$wcviews_slug_for_packager=$this->wcviews_toolset_packager_slug;
  								$wcviews_core_settings_slug= $this->wcviews_wc_core_slug;
  								
  								$wcviews_theme_import_xml = get_stylesheet_directory(). DIRECTORY_SEPARATOR.'toolset_import'.DIRECTORY_SEPARATOR.$wcviews_slug_for_packager.DIRECTORY_SEPARATOR.'settings.xml';
  								$wccore_theme_import_xml = get_stylesheet_directory(). DIRECTORY_SEPARATOR.'toolset_import'.DIRECTORY_SEPARATOR.$wcviews_core_settings_slug.DIRECTORY_SEPARATOR.'settings.xml';
  								
  								//Retrieved
  								/** Import WooCommerce Views settings */
  								$data = file_get_contents($wcviews_theme_import_xml);
  								if ($data) {								
  								  									
	  								// Parse to XML
	  								$xml = simplexml_load_string ( $data );
	  								
	  								//Import
	  								if ($xml) {
	  									$this->wcviews_import_settings ( $xml );	  									
	  								}	  								
  								}
  								
  								/** Import WooCommerce core settings */
  								$data_core = file_get_contents($wccore_theme_import_xml);
  								if ($data_core) {
  									 
  									// Parse to XML
  									$xml_core = simplexml_load_string ( $data_core );
  										
  									//Import
  									if ($xml_core ) {
  										$this->wcviews_import_core_wcsettings ( $xml_core );
  									}
  								} 								
  							}  							
  						}
  					}
  				}
  		 }  		
  	}
  	
  	/**
  	 * Basic requisite checking for Toolset installer import
  	 * @since 2.5.7
  	 */
  	private function wcviews_check_requisite_toolset_packager_import() {
  		
  		/** Embedded Views*/
  		$embedded_views=false;
  		
  		/** WooCommerce activated*/
  		$woocommerce_activated=false;
  		
  		/** WooCommerce Views settings xml file */
  		$wcviews_settings_xml_exist= false;
  		
  		/** WooCommerce core settings xml file */
  		$wccore_settings_xml_exist= false;
  		
  		$requirements_meet=false;
  		
  		/** Check for embedded Views */
  		global $WP_Views;
  		if (( is_object( $WP_Views ) ) && (method_exists($WP_Views,'is_embedded'))) {
  			// Views is installed
  			
  			if ( $WP_Views->is_embedded() ) {
  				
  				//Running embedded
	  			if (!(defined('WPVDEMO_VERSION'))) {	  				
	  				//Not together with Framework installer
	  				$embedded_views=true;
				}  				
  			} 
  		}
  		
  		/** Check for WooCommerce plugin*/
  		$woocommerce_activated=$this->is_woocommerce_activated();
  		
  		/** Check for WooCommerce Views settings.xml file inside themes directory */  
  		$wcviews_slug_for_packager=$this->wcviews_toolset_packager_slug;
  		$wcviews_theme_import_xml = get_stylesheet_directory(). DIRECTORY_SEPARATOR.'toolset_import'.DIRECTORY_SEPARATOR.$wcviews_slug_for_packager.DIRECTORY_SEPARATOR.'settings.xml';
  		if ( file_exists($wcviews_theme_import_xml) ){
  			$wcviews_settings_xml_exist=true;  			
  		}
  		
  		/** Check for WooCommerce core settings.xml file inside themes directory */  		
  		$wccore_slug_for_packager=$this->wcviews_wc_core_slug;
  		$wccore_theme_import_xml = get_stylesheet_directory(). DIRECTORY_SEPARATOR.'toolset_import'.DIRECTORY_SEPARATOR.$wccore_slug_for_packager.DIRECTORY_SEPARATOR.'settings.xml';
  		if ( file_exists($wccore_theme_import_xml) ){
  			$wccore_settings_xml_exist=true;
  		}

  		if (($embedded_views) && ($woocommerce_activated) && ($wcviews_settings_xml_exist) && ($wccore_settings_xml_exist)) {  			
  			$requirements_meet=true;
  		}
  		
  		return $requirements_meet;
  	}
  	
  	/**
  	 * Returns TRUE if Installer embedded instance comes from the Theme
  	 * And setup is incomplete
  	 * @since 2.5.7
  	 */
  	private function wcviews_check_installer_origin() {
  		
  		$ret=false;
  		
  		if (function_exists('WP_Installer')) {
  			$installer_instance=WP_Installer();
  			if (isset($installer_instance->installer_embedded_plugins)) {
  				$embedded_instance=$installer_instance->installer_embedded_plugins;
  				if (is_object($embedded_instance)) {  					
  					if (isset($embedded_instance->settings)) {  					
  						$embedded_settings=$embedded_instance->settings;  						
  						$installed=true;
  						$activated=true;  
  						$wcviews_settings_xml_exist=false;  						
  						if (isset($embedded_settings['completed_items']['install'])) {
  							$installed=$embedded_settings['completed_items']['install'];  							
  						}
  						if (isset($embedded_settings['completed_items']['activate'])) {
  							$activated=$embedded_settings['completed_items']['activate'];
  						}
  						$wcviews_slug_for_packager=$this->wcviews_toolset_packager_slug;
  						$wcviews_theme_import_xml = get_stylesheet_directory(). DIRECTORY_SEPARATOR.'toolset_import'.DIRECTORY_SEPARATOR.$wcviews_slug_for_packager.DIRECTORY_SEPARATOR.'settings.xml';
  						if ( file_exists($wcviews_theme_import_xml) ){
  							$wcviews_settings_xml_exist=true;
  						} 						
  						
  						if ((false === $installed) || (false === $activated)) {
  							if (($wcviews_settings_xml_exist) & (!(defined('WPVDEMO_VERSION')))) {
  								//Incomplete setup
  								$ret=true;
  							}
  						}
  					}  					
  				}  				
  			}
  		}
  		
  		return $ret;
  		
  		
  	}
  	
  	/**
  	 * Export WooCommerce core settings (important ones)
  	 * @since 2.5.7
  	 */
  	public function wcviews_export_core_wcsettings() { 		
  		
  		$woocommerce_core_export_xml =FALSE;
  		
  		if(defined('WOOCOMMERCE_VIEWS_PLUGIN_PATH')) {
  				
  			//Define the parser path
  			$array_xml_parser=WOOCOMMERCE_VIEWS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'array2xml.php';
  		
  			if (file_exists($array_xml_parser)) {
 
  				//Define array of important WooCommerce settings for exporting.
  				$woocommerce_options_for_exporting=array(
  						'woocommerce_bacs_settings',
  						'woocommerce_calc_shipping',
  						'woocommerce_cart_page_id',
  						'woocommerce_change_password_page_id',
  						'woocommerce_checkout_page_id',
  						'woocommerce_cheque_settings',
  						'woocommerce_clear_cart_on_logout',
  						'woocommerce_currency',
  						'woocommerce_customer_completed_order_settings',
  						'woocommerce_customer_processing_order_settings',
  						'woocommerce_default_country',
  						'woocommerce_display_cart_prices_excluding_tax',
  						'woocommerce_display_cart_taxes',
  						'woocommerce_display_cart_taxes_if_zero',
  						'woocommerce_display_totals_excluding_tax',
  						'woocommerce_edit_address_page_id',
  						'woocommerce_email_base_color',
  						'woocommerce_price_num_decimals',
  						'woocommerce_email_footer_text',
  						'woocommerce_email_header_image',
  						'woocommerce_enable_coupons',
  						'woocommerce_enable_coupon_form_on_cart',
  						'woocommerce_enable_coupon_form_on_checkout',
  						'woocommerce_enable_guest_checkout',
  						'woocommerce_enable_shipping_calc',
  						'woocommerce_frontend_css',
  						'woocommerce_frontend_css_colors',
  						'woocommerce_hide_products_when_showing_subcategories',
  						'woocommerce_informal_localisation_type',
  						'woocommerce_installed',
  						'woocommerce_limit_downloadable_product_qty',
  						'woocommerce_lock_down_admin',
  						'woocommerce_logout_page_id',
  						'woocommerce_lost_password_page_id',
  						'woocommerce_myaccount_page_id',
  						'woocommerce_new_order_email_recipient',
  						'woocommerce_new_order_settings',
  						'woocommerce_order_tracking_page_id',
  						'woocommerce_paypal_settings',
  						'woocommerce_pay_page_id',
  						'woocommerce_prepend_category_to_products',
  						'woocommerce_prepend_shop_page_to_urls',
  						'woocommerce_redirect_on_single_search_result',
  						'woocommerce_shipping_method_order',
  						'woocommerce_ship_to_billing_address_only',
  						'woocommerce_ship_to_same_address',
  						'woocommerce_shop_show_subcategories',
  						'woocommerce_shop_slug',
  						'woocommerce_show_subcategories',
  						'woocommerce_specific_allowed_countries',
  						'woocommerce_stock_email_recipient',
  						'woocommerce_tax_classes',
  						'woocommerce_thanks_page_id',
  						'woocommerce_theme_support_check',
  						'woocommerce_view_order_page_id',
  						'woocommerce_default_gateway',
  						'woocommerce_permalinks',
  						'shop_catalog_image_size',
  						'shop_single_image_size',
  						'shop_thumbnail_image_size'
  				
  				);
  		
  				$woocommerce_core_settings=array();
  		
  				//Loop through the settings and assign to array
  				foreach ($woocommerce_options_for_exporting as $key=>$value) {
  		
  					$the_value=get_option($value);
  					if ($the_value) {
  						$woocommerce_core_settings[$value]=$the_value;
  					}
  				}
  		
  				//Parser exists, require once
  				require_once $array_xml_parser;
  		
  				//Instantiate
  				$xml = new ICL_Array2XML();
  		
  				//Define anchor name
  				$anchor_name='woocommerce_core_export_settings';
  		
  				//Get XML only if array is not empty
  				if (!(empty($woocommerce_core_settings))) {
  					$woocommerce_core_export_xml = $xml->array2xml($woocommerce_core_settings, $anchor_name);
  				}  					
  			}
  		}
  		
  		return $woocommerce_core_export_xml;
  		
  	}
  	
  	/**
  	 * Import WooCommerce core settings (important ones)
  	 * @since 2.5.7
  	 */
  	public function wcviews_import_core_wcsettings($xml) {

  		if (function_exists('wpv_admin_import_export_simplexml2array')) {
  			
  			global $wpdb;
	  		$import_data = wpv_admin_import_export_simplexml2array ( $xml );
	  		
	  		// Loop through the settings and update WooCommerce options
	  		foreach ( $import_data as $key => $value ) {
	  			update_option ( $key, $value );
	  		}
	  		
	  		// Define WooCommerce shop page ID
	  		$existing_shop_page = get_option ( 'woocommerce_shop_page_id' );
	  		if ((! ($existing_shop_page)) || (empty ( $existing_shop_page ))) {
	  					
	  			// Shop page not yet defined
	  			$posttable = $wpdb->posts;
	  			$shop_page_id = $wpdb->get_var ( "SELECT ID FROM $posttable WHERE post_name='shop' AND post_type='page'" );
	  			if (! (empty ( $shop_page_id ))) {
	  				update_option ( 'woocommerce_shop_page_id', $shop_page_id );
	  			}
	  		}
  		}  	
  	}
  	
  	/**  	 
  	 * In the shortcode GUI, only display loop-related shortcodes
  	 * Don't display WooCommerce Views shortcodes that are not meant for loops
  	 * @deprecated
  	 * @since 2.5.9
  	 */
  	public function wcviews_allow_loop_related_shortcodes_only($the_items) {
  		
  		_deprecated_function( 'wcviews_allow_loop_related_shortcodes_only() is deprecated.', '2.7.4' );
  		
  		return $the_items;
  	}
  	
  	/**
  	 * Ensure these filter fields will display on for WooCommerce Products
  	 */
  	
  	public function wcviews_set_groups_to_product() {
  		
  		if ( $this->check_if_types_group_exist('WooCommerce Views filter fields') ) {
  			//WooCommerce Views filter groups fields exist.  			
  			$wc_views_group_id	= $this->wcv_get_groups_id();
  			if ( $wc_views_group_id > 0 ) {  				
  				$correct_types_group = get_post_meta( $wc_views_group_id , '_wp_types_group_post_types' , TRUE);  				
  				if ( $correct_types_group != 'product') {  					
  					update_post_meta( $wc_views_group_id, '_wp_types_group_post_types', 'product');
  				}
  			}  			
  		}  		
  	}
  	
  	/**
  	 * Helper function for retrieving WCV filter groups ID
  	 * @return number
  	 */
  	private function wcv_get_groups_id() {  		
  		global $wpdb;
  		$wc_views_group_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title='WooCommerce Views filter fields' && post_status = 'publish' && post_type = 'wp-types-group'");  		
  		$wc_views_group_id = intval( $wc_views_group_id );
  		return $wc_views_group_id;  		
  	}
  	/** Quick way to check if release notes exist in the site
  	 *
  	 * @param string $url
  	 * @return boolean
  	 * @since 2.5.9
  	 */
  	private function wcviews_release_notes_exist( $url ) {
  	
  		$ch = curl_init($url);
  		curl_setopt( $ch, CURLOPT_NOBODY, true); // set to HEAD request
  		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true); // don't output the response
  		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
  		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
  		curl_exec( $ch );
  		$valid = curl_getinfo( $ch, CURLINFO_HTTP_CODE ) == 200;
  		curl_close( $ch );
  	
  		return $valid;
  	
  	}  	
  	/**
  	 * Automatic release notes link
  	 * @since 2.5.9
  	 */
  	public function wcviews_plugin_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
  		
  		if ( ( defined('WOOCOMMERCE_VIEWS_PLUGIN_PATH') ) && ( defined('WC_VIEWS_VERSION') ) && ( is_callable('curl_init') ) ) {
  			if ( ( WOOCOMMERCE_VIEWS_PLUGIN_PATH ) && ( WC_VIEWS_VERSION ) ) {
  				$this_plugin = basename( WOOCOMMERCE_VIEWS_PLUGIN_PATH ) . '/views-woocommerce.php';
  				if ( $plugin_file == $this_plugin ) {
  					//This is WooCommerce Views
  					$version_slug = 'woocommerceviews-';
  					$current_plugin_version = WC_VIEWS_VERSION;
  					$current_plugin_version_simplified = str_replace( '.', '-', $current_plugin_version );
  		
  					//When releasing WooCommerce Views, slug of version content should match with $article_slug
  					$article_slug = $version_slug.$current_plugin_version_simplified;
  					$linktitle = 'WooCommerce Views'.' '.$current_plugin_version.' '.'release notes';
  		
  					//Raw URL
  					//Override with Toolset domain constant if set
  					if ( defined('WPVDEMO_TOOLSET_DOMAIN') ) {
  						if (WPVDEMO_TOOLSET_DOMAIN) {
  							$raw_url = 'https://'.WPVDEMO_TOOLSET_DOMAIN.'/version/'.$article_slug.'/';
  		
  							$wcviews_release_link = get_option( 'wcviews_release_link' );
  		
  							//We don't need to check if release notes exist anytime a user accesses a plugin page
  							//Once the release note is proven to exist, we display
  							$exists = false;
  							if ( false === $wcviews_release_link) {
  								//Option value not yet defined, we need to check- one time event only
  								if ( $this->wcviews_release_notes_exist( $raw_url ) ) {
  									//Now exists
  									$exists = true;
  								}
  							} elseif ( 'released' == $wcviews_release_link ) {
  								$exists = true;
  							}
  		
  							if ( $exists ) {
  									
  								//Now released, we append this link.
  								$url_with_ga = $raw_url.'?utm_source=woocommerceviewsplugin&utm_campaign=woocommerceviews&utm_medium=release-notes-link&utm_term='.$linktitle;
  								$plugin_meta[] = sprintf(
  										'<a href="%s" target="_blank">%s</a>',
  										$url_with_ga,
  										$linktitle
  										);
  								if ( !($wcviews_release_link) ) {
  									//We update to set this, one time event only.
  									update_option( 'wcviews_release_link', 'released');
  								}
  									
  							}
  						}
  					}
  				}
  			}
  		}
  		
  		return $plugin_meta; 		
  		
  	}
  	/**
  	 * Added support for Toolset unified menu checks.
  	 * Returns TRUE if existing Toolset common library can support unified menu
  	 * @return boolean
  	 */
  	public function wcviews_can_implement_unified_menu() {  		
  		$unified_menu = false;
  		$is_available = apply_filters( 'toolset_is_toolset_common_available', false );
  		if ( TRUE === $is_available ) {
  				$unified_menu = true;   			  			
  		}
  		
  		return $unified_menu;
  	}
  	
  	/**
  	 * Register unified menu for WooCommerce Views
  	 * @param array $pages
  	 * @return array
  	 */
  	
  	public function wcviews_unified_menu( $pages ) {
  		
  		//Retrieved missing plugins information
  		$missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();  
  		
  		//Add admin screen only when all required plugins are activated
  		if ( empty( $missing_required_plugin ) ) {
	  		$pages[] = array(
	  				'slug'          => 'wpv_wc_views',
	  				'menu_title'    => __('WooCommerce Views', 'woocommerce_views'),
	  				'page_title'    => __('WooCommerce Views', 'woocommerce_views'),
	  				'callback'      => array(&$this, 'woocommerce_views_admin_screen')
	  		);
  		}
  		return $pages;
  	}
  	
  	/**
  	 * Added forward/backward compatibility on current screen usage in WC Views
  	 * For Toolset Unified Menu Implementation
  	 * Returns the correct canonical screen ID
  	 */
  	private function wcviews_unified_current_screen() {
  		
  		//Backward compatible screen ID
  		$canonical_screen_id = 'views_page_wpv_wc_views';
  		
  		//Check if this can support unified menu
  		$can_support_unified_menu = $this->wcviews_can_implement_unified_menu();
  		
  		if ( $can_support_unified_menu ) {
  			//Yes, use an updated ID
  			$canonical_screen_id = 'toolset_page_wpv_wc_views' ;
  		} 		
  		
  		return $canonical_screen_id;
  		
  	}
  	
  	/**
  	 * Upgrade WooCommerce Views price field to 'numeric'
  	 * @since 2.6.1
  	 */
  	public function wcviews_updated_price_field_to_numeric() {  		
  		
  		//Check if the Types field utilties exists
  		if ( class_exists( 'Types_Field_Utils') ) {
  			
  			//Double check if the change_field_type method exist
  			if ( method_exists( 'Types_Field_Utils', 'change_field_type' ) ) {
  				
  				$wcviews_price_field_converted = get_option( 'wcviews_price_field_converted' );  				
  				if ( false === $wcviews_price_field_converted ) {  					
  					//Not yet converted
  					//Read to use..
  					//Define parameters
  					$field_slug = 'views_woo_price';
  					$domain 	= 'posts';
  					$arguments 	= array( 'field_type' => 'numeric' );
  					
  					//Convert..
  					$result 	= Types_Field_Utils::change_field_type( $field_slug, $domain, $arguments );
  					$is_error	= is_wp_error( $result );
  					
  					if ( ( $result ) && ( false === $is_error ) ) {
  						//Success numeric conversion
  						update_option( 'wcviews_price_field_converted', 'converted' );
  							
  					}  					
  				}  			
  			}  			
  		} 		
  		
  	}
  	
  	/**
  	 * Execute 'woocommerce_before_single_product_summary' WooCommerce hook for compatibility with third party WooCommerce Extensions that is using this.
  	 * We establish relevance of hook execution with respect to WooCommerce single product image which is executed with '20' as the priority
  	 * @since 2.6.2
  	 * 
  	 */
  	public function wcviews_woocommerce_before_single_product_summary( $image_content, $the_post, $the_atts ) {
  	
  		if ( !defined('WCVIEWS_DOING_LOOP') ) {
  			//Single product hooked execution , excludes shortcode executed within Views loop
	  		$using_default_wc_template=$this->wc_views_check_if_using_woocommerce_default_template();
	  		$woocommerce_before_single_product_summary_content = '';
	  		if ( (false === $using_default_wc_template ) && ( is_product() ) )  {
	  			//Override by WooCommerce Views	  			 			
	  			global $wp_filter;
	  			//Let's checked if we woocommerce_before_single_product_summary overrides by third party plugins or themes
	  			
	  			$html_output_hooks_before 	= '';
	  			$html_output_hooks_after	= '';
	  			//WooThumbs compatibility
	  			$html_output_hooks_in		= '';
	  			
	  			if ( isset( $wp_filter['woocommerce_before_single_product_summary'] ) ) {
	  				//Looks there is..
	  				$hooked_actions = $wp_filter['woocommerce_before_single_product_summary'];	  				
	  				
	  				 // Let's break these hooks into two, those executed before the main image hook (WooCommerce core uses '20' as priority)
	  				 // And those executed after '20'
	  				 
	  				$before_main_image_hook 	= array();
	  				$after_main_image_hook		= array();
	  				
	  				//WooThumbs compatibility
	  				$in_main_image_hook			= array();
	  				
	  				/**
	  				 * WooCommerce Views 2.6.4
	  				 * Sales badge and WooCommerce image are handled by WooCommerce Views, exclude these from
	  				 * hook execution
	  				 */
	  				$excluded_func = array ( 'woocommerce_show_product_sale_flash', 'woocommerce_show_product_images' );
	  				
	  				//Let's looped and analyzed their priorities
	  				foreach ( $hooked_actions as $priority => $hooked_action ) {
	  					$priority = intval( $priority );
	  					if ( $priority < 20 ) {
	  						//Fires before the main WooCommerce image
	  						foreach ( $hooked_action as $k_hooked_action => $v_hooked_action ) {
	  							if ( isset( $v_hooked_action['function'] ) ) {
	  								if ( !( in_array( $k_hooked_action, $excluded_func ) ) ) {
	  									//Not an excluded function, add	
	  									$before_main_image_hook[ $k_hooked_action ] = $v_hooked_action['function'];
	  								}
	  									
	  									  								
	  							}
	  						}
	  					} elseif ($priority > 20 ) {
	  						//Fires after the main WooCommerce image
	  						  foreach ( $hooked_action as $k_hooked_action => $v_hooked_action ) {
	  							if ( isset( $v_hooked_action['function'] ) ) {
	  								if ( !( in_array( $k_hooked_action, $excluded_func ) ) ) {
	  									//Not an excluded function, add	
	  									$after_main_image_hook[ $k_hooked_action ] = $v_hooked_action['function'];
	  								}	  								
	  							}
	  						}
	  					} elseif ( 20 === $priority ) {
	  						//WooThumbs compatibility
	  						foreach ( $hooked_action as $k_hooked_action => $v_hooked_action ) {
	  							if ( isset( $v_hooked_action['function'] ) ) {
	  								if ( !( in_array( $k_hooked_action, $excluded_func ) ) ) {
	  									//Not an excluded function, add
	  									$in_main_image_hook[ $k_hooked_action ] = $v_hooked_action['function'];
	  								}
	  							}
	  						}	  						
	  						
	  					}
	  				}
	  				
	  				//Parse hooks before main image
	  				ob_start();
	  				foreach ( $before_main_image_hook as $k_before => $v_before ) {
	  					call_user_func_array( $v_before, array() );
	  				}
	  				$html_output_hooks_before = ob_get_contents();
	  				ob_end_clean();
	  				
	  				//Parse hooks after main image
	  				ob_start();
	  				foreach ( $after_main_image_hook as $k_after => $v_after ) {
	  					call_user_func_array( $v_after, array() );
	  				}
	  				$html_output_hooks_after = ob_get_contents();
	  				ob_end_clean();
	  				
	  				//WooThumbs compatibility
	  				//Parse hooks in main image
	  				ob_start();
	  				foreach ( $in_main_image_hook as $k_in => $v_in ) {
	  					call_user_func_array( $v_in, array() );
	  				}
	  				$html_output_hooks_in = ob_get_contents();
	  				ob_end_clean();	  				
	  			}
	  			
	  			//Concatenate HTML output of third party plugin/theme hooks
	  			$html_output_hooks_before 	= trim( preg_replace( '/\s\s+/', ' ', $html_output_hooks_before ) );
	  			$html_output_hooks_after 	= trim( preg_replace( '/\s\s+/', ' ', $html_output_hooks_after ) );
	  			
	  			//WooThumbs compatibility
	  			$html_output_hooks_in 		= trim( preg_replace( '/\s\s+/', ' ', $html_output_hooks_in ) );
	  			if ( !(empty( $html_output_hooks_in ) ) ) {
	  				//If WooThumbs plugin activated, replace WooCommerce shortcode output with their own output for compatibility
	  				$woothumbs_activated = $this->is_woothumbs_plugin_activated();
	  				if ( true === $woothumbs_activated ) {
	  					$image_content = $html_output_hooks_in;
	  				}	  				
	  			}
	  			$image_content 				= $html_output_hooks_before. $image_content. $html_output_hooks_after;	  			
	  			
	  		}
  		}
  		
  		return $image_content;
  	
  	}
 
  	/**
  	 * Execute 'woocommerce_before_shop_loop_item' WooCommerce hook for compatibility with third party WooCommerce Extensions that is using this.
  	 * This always FIRES before the product image inside the product loop
  	 * @since 2.6.2
  	 */
  	public function wcviews_woocommerce_before_shop_loop_item( $image_content, $the_post, $the_atts ) {
  		
  		//Check if this is a product listing page
  		$is_product_listing = $this->wcviews_is_woocommerce_listing();
  		$doing_views_loop = false;
  		if ( defined('WCVIEWS_DOING_LOOP') ) {
  			if ( true === WCVIEWS_DOING_LOOP ) {
  				$doing_views_loop = true;
  			}
  		}
  		if ( ( true === $is_product_listing ) || ( true === $doing_views_loop ) ) {
  			//This is a product listing

  			/**NEW METHOD -START */
  			//Override by WooCommerce Views
  			global $wp_filter;
  			//Let's checked if woocommerce_before_shop_loop_item overrides by third party plugins or themes
  			$html_output_hooks_before 	= '';
  			
  			if ( isset( $wp_filter['woocommerce_before_shop_loop_item'] ) ) {
  				//Looks there is..
  				$hooked_actions = $wp_filter['woocommerce_before_shop_loop_item'];
  					
  						$before_main_image_hook 	= array();  						
  						$html_output_hooks_before 	= ''; 					
  				  	
  						/**
  						 * WooCommerce Views 2.6.4
  						 * woocommerce_template_loop_product_link_open will be removed since user can customize wrappings with hyperlink inside CT
  						 * hook execution
  						 */
  						$excluded_func = array ( 'woocommerce_template_loop_product_link_open' );
  				  	
  						//Let's looped and analyzed their priorities
  						foreach ( $hooked_actions as $priority => $hooked_action ) {

  								//Fires before the main WooCommerce thumbnail image in listings
  								foreach ( $hooked_action as $k_hooked_action => $v_hooked_action ) {
  									if ( isset( $v_hooked_action['function'] ) ) {
  										if ( !( in_array( $k_hooked_action, $excluded_func ) ) ) {
  											//Not an excluded function, add
  											$before_main_image_hook[ $k_hooked_action ] = $v_hooked_action['function'];
  										}  			
  									}
  								}  							
  						}
  				  	
  						//Parse hooks before main image
  						ob_start();
  						foreach ( $before_main_image_hook as $k_before => $v_before ) {
  							call_user_func_array( $v_before, array() );
  						}
  						$html_output_hooks_before = ob_get_contents();
  						ob_end_clean();  				  	

  			}
  			
  			//Concatenate HTML output of third party plugin/theme hooks
  			$html_output_hooks_before 	= trim( preg_replace( '/\s\s+/', ' ', $html_output_hooks_before ) );  			
  			$image_content 				= $html_output_hooks_before. $image_content;  			
  			
  			/** NEW METHOD END */
  			
  		}
  		
  		return $image_content;		
  		
  	}
  	
  	/**
  	 * Since version 2.6.2
  	 * Implement stock inventory control on product listing pages for add to cart button with quantity selector.
  	 * @param array $args
  	 * @param object $product
  	 */
  	public function wcviews_woocommerce_quantity_input_args( $args, $product ) {
		global $wcviews_impose_stock_inventory, $product;
		
		//Let's checked if this is Loop Add to Cart template override for quantity selectors
		if ( isset( $wcviews_impose_stock_inventory ) ) {
			if ( TRUE === $wcviews_impose_stock_inventory ) {
				//Yes it is.
				//Check if product is stock managed
				if ( is_object( $product ) ) {
					if ( ( method_exists( $product, 'get_stock_quantity'  ) ) && (method_exists( $product, 'managing_stock'  ) ) ) {
						$is_stock_manage = $product->managing_stock();
						if ( true === $is_stock_manage ) {
						
							$stock_quantity = $product->get_stock_quantity();
							$stock_quantity = intval( $stock_quantity );
								
							if ( $stock_quantity > 0 ) {
								//Filter only if we have sensible stock quantity
								$args['max_value'] 	= $stock_quantity; 	// Maximum value
								$args['min_value'] 	= 1;
							}
						}							
					}
				}							
			}
		}
		
		return $args;
  	}
  	
  	/**
  	 * Compatibility with WooCommerce Woothumbs plugin
  	 * http://codecanyon.net/item/woothumbs-awesome-product-imagery/2867927
  	 * @since version 2.6.5
	 * @since 2.7.8 Remove also the hook callback for printing all notices, since WooCommerce 3.5
  	 * @return string
  	 */
  	protected function wcviews_execute_woocommerce_before_single_product() {
		ob_start();
	  remove_action( 'woocommerce_before_single_product', 'wc_print_notices', 10 );
	  if ( function_exists( 'woocommerce_output_all_notices' ) ) {
		  remove_action( 'woocommerce_before_single_product', 'woocommerce_output_all_notices' );
	  }
		do_action( 'woocommerce_before_single_product' );
		
		return ob_get_clean(); 			
	}
  	
  	/**
  	 * Check if WooThumbs plugin is activated
  	 * @since 2.6.5
  	 * @return boolean
  	 */
  	public function is_woothumbs_plugin_activated() {
  		
  		$activated = false;
  		if ( class_exists( 'JCKWooThumbs') ) {
  			global $jck_woothumbs_class;
  			if ( is_object( $jck_woothumbs_class ) ) {
  				//	WooThumbs plugin activated
  				$activated = true;
  			}
  		} elseif ( class_exists( 'Iconic_WooThumbs') ) {
  			/**
  			 * woocommerceviews-127:
  			 * New version of WooThumbs plugin that is using a different class name
  			 */
  			global $iconic_woothumbs_class;
  			if ( is_object( $iconic_woothumbs_class) ) {
  				//	WooThumbs plugin activated
  				$activated = true;
  			}
  		}
  		return $activated;  		
  	}
  	
  	/**
  	 * Simplified GUI WooCommerce Views export/import screen
  	 * @since 2.6.6
  	 * @param array $sections
  	 */
  	public function wcviews_register_export_import_section( $sections ) {
  		 
  		$sections['wcviews_export_import'] = array(
  				'slug'      => 'wcviews_export_import',
  				'title'     => __( 'WooCommerce Views','woocommerce_views' ),
  				'icon'      => '<i class="icon-views-logo ont-icon-16"></i>',
  				'items'		=> array(
  							
  					'export'	=> array(
  								'title'		=> __( 'Export WooCommerce Views','woocommerce_views' ),
  								'callback'	=> array( $this, 'wcviews_export_template' ),
  					),
  					'import'	=> array(
  								'title'		=> __( 'Import WooCommerce Views','woocommerce_views' ),
  								'callback'	=> array( $this, 'wcviews_import_template'),
  					),
  				),
  		);  	
  		return $sections;  		 
  	}
  	
  	/**
  	 * Export GUI template
  	 * @since 2.6.6
  	 */
  	public function wcviews_export_template() {
  		$action_url = admin_url('admin.php') . '?page=toolset-export-import&tab=wcviews_export_import';
  	?>
  		<form id="woocommerce_views_export" name="woocommerce_views_export" method="post" action="<?php echo $action_url;?>">
	  		<div>
	  			<p><?php echo __( 'Download and export this site WooCommerce Views settings.','woocommerce_views' ); ?></p>
	  			<p class="toolset-update-button-wrap">
	  			<?php wp_nonce_field( 'wcviews-export-nonce', 'wcviews-export-nonce', false, true ); ?>
				<input type="submit" class="button-primary wcviews-export-import-form-submit form-submit submit" value="Export" name="wcviews_export_gui_button" id="wcviews-export-form-submit">
				</p>	
	   	</div>
		</form>		
  	<?php   		
  	}  	
  	
  	/**
  	 * Import GUI template
  	 * @since 2.6.6
  	 */
  	public function wcviews_import_template() {
  		$action_url = admin_url('admin.php') . '?page=toolset-export-import&tab=wcviews_export_import';
  	?>
  		<form enctype="multipart/form-data" id="woocommerce_views_import" name="woocommerce_views_import" method="post" action="<?php echo $action_url;?>">
	  		<div>
	  			<p><?php echo __( 'Import WooCommerce Views settings for this site. Take note this import works only if you are importing WooCommerce Views settings that match with the theme used in the origin site.','woocommerce_views' ); ?></p>
	  			<p><?php echo __( 'The origin site is where the export for WooCommerce Views settings are generated. It also exports the theme information like the product template name/paths being used with WooCommerce Views.')?>
	  			<p><?php echo __( 'If this is not the case, please switch to this compatible theme first before importing the settings. This is for compatibility purposes.','woocommerce_views' ); ?></p>
	  			<p><input type="file" name="import-file" id="upload-wcviews-file"></p>
	  			<p class="toolset-update-button-wrap">
	  			<?php wp_nonce_field( 'wcviews-import-nonce', 'wcviews-import-nonce', false, true ); ?>				
				<input type="submit" class="button-primary wcviews-export-import-form-submit form-submit submit" value="Import" name="wcviews_import_gui_button" id="wcviews-import-form-submit">
				</p>	  		 	
	  		</div>
	  	</form>		
  	<?php  			
  	}
  	
  	/**
  	 * Export handler for WooCommerce Views GUI
  	 * @since 2.6.6
  	 */
  	public function wcviews_export_handler() {  		
  		//Check for export events
  		if ( ( isset( $_POST['wcviews_export_gui_button'] ) ) && ( isset( $_POST['wcviews-export-nonce'] ) ) ) {
  			$wcviews_export_gui_button = $_POST['wcviews_export_gui_button'];
  			if ( ( 'Export' === $wcviews_export_gui_button ) && ( wp_verify_nonce( $_POST['wcviews-export-nonce'], 'wcviews-export-nonce' ) ) ) {  				
  				/**
  				 * Export event 
  				 * Retrieved WooCommerce Views settings of this site as xml
  				 */
  				$data	=	$this->wcviews_export_settings();  				
  				if ( $data ) {
  					$sitename = sanitize_key( get_bloginfo( 'name' ) );
  					if ( ! empty( $sitename ) ) {
  						$sitename .= '.';
  					}
  					$filename = $sitename . 'woocommerce_views.' . date( 'Y-m-d' ) . '.xml';
  					if ( class_exists( 'ZipArchive' ) ) {
  						$zipname = $sitename . 'woocommerce_views.' . date( 'Y-m-d' ) . '.zip';
  						$zip = new ZipArchive();
  						$file = tempnam( sys_get_temp_dir(), "zip" );
  						$zip->open( $file, ZipArchive::OVERWRITE );
  						$res = $zip->addFromString( 'settings.xml', $data );  						
  						$zip->close();
  						$data = file_get_contents( $file );
  						header( "Content-Description: File Transfer" );
  						header( "Content-Disposition: attachment; filename=" . $zipname );
  						header( "Content-Type: application/zip" );
  						header( "Content-length: " . strlen($data) . "\n\n" );
  						header( "Content-Transfer-Encoding: binary" );
  						echo $data;
  						unlink( $file );
  						die();
  					} else {
  						// download the xml.
  						header( "Content-Description: File Transfer" );
  						header( "Content-Disposition: attachment; filename=" . $filename );
  						header( "Content-Type: application/xml" );
  						header( "Content-length: " . strlen( $data ) . "\n\n" );
  						echo $data;
  						die();
  					}  					
  					die();
  				}  				
  			} 			
  		}  		
  	}
  	
  	/**
  	 * Import handler for WooCommerce Views GUI
  	 * @since 2.6.6
  	 */
  	public function wcviews_import_handler() {
  		//Check for import events
  		if ( ( isset( $_POST['wcviews_import_gui_button'] ) ) && ( current_user_can( 'manage_options' ) ) && ( isset( $_POST['wcviews-import-nonce'] ) ) ) {
  			$wcviews_import_gui_button = $_POST['wcviews_import_gui_button'];
  			if ( ( 'Import' === $wcviews_import_gui_button ) && ( wp_verify_nonce( $_POST['wcviews-import-nonce'], 'wcviews-import-nonce' ) ) ) {
  				/**
  				 * Import event
  				 * Check existence of imported file
  				 */
  				$file = false;
  				if ( isset( $_FILES['import-file'] ) ) {  					
  					$file = $_FILES['import-file'];
  				}
  				if (
  						! $file
  						|| ! isset( $file['name'] )
  						|| empty( $file['name'] )
  						) {
  							$this->wcviews_import_errors	=	new WP_Error(' could_not_open_file', __( 'Could not read the WooCommerce Views import file.', 'woocommerce_views' ) );
  							return $this->wcviews_import_errors;
  				}
  				//File exists at this point, validate
  				$data = array();
  				$info = pathinfo( $file['name'] );
  				$is_zip = $info['extension'] == 'zip' ? true : false;
  				if ( $is_zip ) {
  					$zip = zip_open( urldecode( $file['tmp_name'] ) );
  					if ( is_resource( $zip ) ) {
  						while ( ( $zip_entry = zip_read( $zip ) ) !== false ) {
  							if ( zip_entry_name( $zip_entry ) == 'settings.xml' ) {
  								$data = @zip_entry_read( $zip_entry, zip_entry_filesize( $zip_entry ) );
  							}
  						}
  					} else {
  						$this->wcviews_import_errors		=	new WP_Error( 'could_not_open_file', __( 'Unable to open zip file', 'woocommerce_views' ) );
  						return $this->wcviews_import_errors;
  					}
  				} else {
  					$fh = fopen( $file['tmp_name'], 'r' );
  					if ( $fh ) {
  						$data = fread( $fh, $file['size'] );
  						fclose( $fh );
  					}
  				} 				
				//Check if we have data to import
  				if ( ! empty( $data ) ) {
  					//Data exists at this point
  					if ( ! function_exists( 'simplexml_load_string' ) ) {
  						$this->wcviews_import_errors		= 	new WP_Error( 'xml_missing', __( 'The Simple XML library is missing.', 'woocommerce_views' ) );
  						return $this->wcviews_import_errors;
  					}
  					$xml = simplexml_load_string( $data );
  					if ( ! $xml ) {
  						$this->wcviews_import_errors		=	new WP_Error( 'not_xml_file', sprintf( __( 'The XML file (%s) could not be read.', 'woocommerce_views' ), $file['name'] ) );
  						return $this->wcviews_import_errors;
  					}
  					$this->wcviews_import_settings ( $xml );
  					$this->wcviews_import_messages			=	__('WooCommerce Views setting import is completed', 'woocommerce_views'); 
  				
  				} else {
  					$this->wcviews_import_errors			=	new WP_Error( 'could_not_open_file', __( 'Could not read the WooCommerce Views import file.', 'woocommerce_views' ) );;
  					return $this->wcviews_import_errors;
  				}  				
  			}
  		}
  	}
  	/** 
  	 *
  	 * Display admin notices related to WooCommerce Views import errors
  	 *
  	 * @since 2.6.6
  	 */
  	
  	public function wcviews_import_notices_errors() {
  		if ( ! is_null( $this->wcviews_import_errors ) && is_wp_error( $this->wcviews_import_errors ) ) {
  			?>
  			<div class="message error"><p><?php echo $this->wcviews_import_errors->get_error_message() ?></p></div>
  			<?php
  		}
  	}
  	/**
  	 *
  	 * Display admin notices related to WooCommerce Views import errors
  	 *
  	 * @since 2.6.6
  	 */
  	 
  	public function wcviews_import_messages() {
  		if ( ! is_null( $this->wcviews_import_messages ) ) {
  					?>
  	  			<div class="message updated"><p><?php echo $this->wcviews_import_messages; ?></p></div>
  	  			<?php
  	 	}
  	 }
  	 
  	 /**
  	  * Check if we are using WooCommerce 2.7.0+ 
  	  * Returns TRUE if using the major release version 2.7.0.
  	  */ 
  	 public function wc_views_two_point_seven_above() {
  	 
  	 	global $woocommerce;
  	 	if (is_object($woocommerce)) {
  	 
  	 		$woocommerce_version_running=$woocommerce->version;
  	 
  	 		if (version_compare($woocommerce_version_running, '2.7.0', '<')) {
  	 
  	 			return FALSE;
  	 
  	 		} else {
  	 
  	 			return TRUE;
  	 
  	 		}
  	 	}
  	 
  	 }
  	 
  	 /**
  	  * A modern way of identifying WooCommerce product types
  	  * Returns the product type being queried (e.g. 'simple', 'variable', etc.)
  	  * If none identified returns empty string.
  	  * @param object $product
  	  * @return string $ret
  	  * @since 2.7.0
  	  */
  	 public function wc_views_get_product_type( $product ) {
  	 	
  	 	$ret = '';
  	 	if ( ( is_object( $product ) ) && ( method_exists( $product, 'get_type' ) ) ) {
  	 		/**
  	 		 * WooCommerce 2.7.0+ made some changes the way product types are identified
  	 		 * From this version onwards, use get_type() method
  	 		 */
  	 		if ( true === $this->wc_views_two_point_seven_above() ) {
  	 			//Call WC get_type()
  	 			$product_type 	= $product->get_type();    	 		
  	 		} else {
  	 			/** Backwards compatibility */
  	 			if ( isset( $product->product_type ) ) {
  	 				$product_type	= $product->product_type;
  	 			}  	 			
  	 		}
  	 		/**
  	 		 * Force empty return if product type is not string
  	 		 */
  	 		if ( ( is_string( $product_type ) ) && ( !empty( $product_type ) ) ) {
  	 			$ret	= $product_type;
  	 		}
  	 	}
  	 	return $ret; 	 	
  	 }
  	 
  	 /**
  	  * woocommerceviews-70: Toolset WPA Order By is winning over WooCommerce Sort by
  	  * @since 2.7.1
  	  */
  	 public function wcviews_analyzed_sorting( $query, $archive_settings, $archive_id ) {
  	 	
  	 	/**
  	 	 * Check if we are only on WooCommerce loop pages:
  	 	 * shop, product category and product tag pages.
  	 	 * This method is HOOKED to 'wpv_action_apply_archive_query_settings'
  	 	 * which will fire in archives only
  	 	 * and is_woocommerce() returns TRUE on WC related pages only
  	 	 */
  	 	$woocommerce_looped_pages	= false;
  	 	if ( ( function_exists( 'is_woocommerce' ) ) && ( is_woocommerce() ) ) {
  	 		$woocommerce_looped_pages	= true;
  	 	}
  	 	
  	 	//Layouts plugin status
  	 	$layouts_plugin_status = $this->wc_views_check_status_of_layouts_plugin();
  	 	
  	 	if ( ( true === $woocommerce_looped_pages ) && ( false === $layouts_plugin_status ) ) {
  	 		/**
  	 		 * This block only applies to Toolset WPA customization of WooCommerce by Views
  	 		 */
  	 		/**
  	 		 * Check that this archives are overriden by Toolset
  	 		 */
  	 		$toolset_implemented	= false;
  	 		
  	 		//We are on a standard WooCommerce looped pages.
  	 		//Check for WPA archive only implementation
  	 		if ( ( function_exists( 'is_wpv_wp_archive_assigned' ) ) && ( true === is_wpv_wp_archive_assigned() ) ) {
  	 			//This WooCommerce loop hage has Toolset WPA implementation
  	 			$toolset_implemented	= true;
  	 		}
  	 		
  	 		/**
  	 		 * If yes, check that sorting comes from WooCommerce or from Toolset
  	 		 */
  	 		$toolset_override	= false;
  	 		if ( true === $toolset_implemented ) {
  	 			//We need to check if user is aiming to sort WC products via the built-in sort
  	 			$toolset_override	= $this->wcviews_check_if_toolset_overrides_wc_sort();
  	 		}
  	 		
  	 		/**
  	 		 * If the sorting queries are initiated by WooCommerce front end controls, then we need to unhook the Views order mechanisms
  	 		 * Then it will let the products to be sorted by WooCommerce itself as what the user wanted.
  	 		 */
  	 		if ( false === $toolset_override ) {
  	 			//No Toolset override, defer to default WooCommerce sorting
  	 			//And we need to remove the WPA sorting hook to make WC sorting work.
  	 			
  	 			global $WPV_view_archive_loop;
  	 			if ( is_object( $WPV_view_archive_loop ) ) {
  	 				remove_action( 'wpv_action_apply_archive_query_settings', array( $WPV_view_archive_loop, 'archive_apply_order_settings' ), 20, 3 );
  	 			}
  	 		} else {
  	 			//Toolset override, implies use should be using Toolset own front end sorting controls
  	 			//No need to show default WC sorting
  	 			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
  	 		}
  	 	}
  	 }
  	 
  	 /**
  	  * Helper function to check if Toolset overrides WooCommerce sorting mechanism through
  	  * its own front-end ordering controls.
  	  * @return boolean
  	  */
  	 private function wcviews_check_if_toolset_overrides_wc_sort() {
  	 	
  	 	//Default to false
  	 	$override	= false;
  	 	/**
  	 	 * Here check the WC Views setting
  	 	 */
  	 	$woocommerce_views_frontend_sorting_setting	= get_option( 'woocommerce_views_frontend_sorting_setting' );
  	 	if ( 'no' ===  $woocommerce_views_frontend_sorting_setting ) {
  	 		//"No" means user is overriding default WC sorting with Toolset
  	 		$override	= true;
  	 	}
  	 	
  	 	return $override;
  	 	
  	 }
  	 
  	 /**
  	  * In WooCommerce 3.0, it loads new js that breaks images and gallery functionalities in listings.
  	  * We load them here.
  	  * This runs only in sites updated with WC 3.0 core.
  	  * @since 2.7.1
  	  */
  	 public function wcviews_load_new_wcthree_scripts() {  	 	
  	 	$is_listing		=	$this->wcviews_is_woocommerce_listing();
  	 	$new_wc_three	= $this->wc_views_two_point_seven_above();
  	 	if ( ( true === $is_listing ) && ( true === $new_wc_three ) ) { 	 		 	 			
  	 		wp_enqueue_script( 'zoom' );  	 			
  	 		wp_enqueue_script( 'flexslider' );  	 			
  	 		wp_enqueue_script( 'photoswipe-ui-default' );
  	 		wp_enqueue_style( 'photoswipe-default-skin' );
  	 		add_action( 'wp_footer', 'woocommerce_photoswipe' );
  	 		wp_enqueue_script( 'wc-single-product' );
  	 	} 	 	
  	 }
  	 /**
  	  * Add the resizing filter only when processing gallery thumbnails
  	  * @since 2.7.1
  	  */
  	 public function wcviews_inject_resizing_gallery_filter() {
  	 	if ( ( defined( 'WCVIEWS_DOING_LOOP') ) && ( WCVIEWS_DOING_LOOP ) ) {
  	 		//Loop only
  	 		add_filter( 'wp_get_attachment_image_src', array( $this,'wcviews_resize_galleries'), 10, 4 ); 
  	 	}  	 	
  	 }

  	 /**
  	  * Auto-resize galleries based on user image size settings.
  	  * @param array $image
  	  * @param number $attachment_id
  	  * @param string $size
  	  * @param string $icon
  	  * @return number
  	  * @since 2.7.1
  	  */
  	 public function wcviews_resize_galleries( $image = array(), $attachment_id = 0, $size = '', $icon = false ) {
  	 	
  	 	global $wcviews_show_gallery_on_listings, $attribute_image_size;   	 	
  	 	if ( ( isset( $wcviews_show_gallery_on_listings ) ) 
  	 			&& ( true === $wcviews_show_gallery_on_listings ) && 
  	 			( $image ) &&
  	 			( ( defined( 'WCVIEWS_DOING_LOOP') ) && ( WCVIEWS_DOING_LOOP ) ) &&
  	 			( $this->wc_views_two_point_seven_above() )
  	 			) {  	 		
  	 		
  	 		//Get available image sizes
  	 		$image_sizes_available	=	$this->wc_views_list_image_sizes();
  	 		
  	 		//Get image size for user settings
  	 		if ( isset( $image_sizes_available[ $attribute_image_size ] ) ) {
  	 			//Dimensions set
  	 			$image_dimensions_set	= $image_sizes_available[ $attribute_image_size ];
				
  	 			//Retrieved specific dimensions and filter $image if set  	 			
  	 			if ( ( ( isset( $image_dimensions_set[0] ) ) && ( !empty( $image_dimensions_set[0]) ) ) && 
  	 			( ( isset( $image_dimensions_set[1] ) ) && ( !empty( $image_dimensions_set[1]) ) ) ) {
  	 				$dim_1		= ( int )$image_dimensions_set[0];
  	 				$dim_2		= ( int )$image_dimensions_set[1];
  	 				if ( ( $dim_1 > 0 ) && ( $dim_2 > 0 ) ) {
  	 					$image[1]	= $dim_1;
  	 					$image[2]	= $dim_2;
  	 				}
  	 			}  	 			
  	 		}  	 		
  	 	}
  	 	
  	 	return $image;
  	 }
  	 
  	 /**
  	  * Put WCV field meta box at low priority so it won't interfere usability
  	  * @since 2.7.2
  	  */
  	 public function wcviews_filter_fields_improvement_metabox() {
  	 	
  	 	//Types put this always at high priority. Put this low.
  	 	global $wp_meta_boxes;
  	 	$group_value = array();
  	 	if ( isset( $wp_meta_boxes['product']['normal']['high']['wpcf-group-woocommerce-views-filter-fields'] ) ) {
  	 		$group_value	= $wp_meta_boxes['product']['normal']['high']['wpcf-group-woocommerce-views-filter-fields'];  
  	 		unset( $wp_meta_boxes['product']['normal']['high']['wpcf-group-woocommerce-views-filter-fields'] );
  	 	}
  	 	if ( !empty( $group_value) ) {
  	 		$wp_meta_boxes['product']['normal']['low']['wpcf-group-woocommerce-views-filter-fields'] = $group_value;
  	 		add_filter( 'postbox_classes_product_wpcf-group-woocommerce-views-filter-fields', array( $this, 'wcviews_minify_my_metabox' ) );
  	 	}  	 		 	
  	 }
  	 
  	 /**
  	  * Let's clse the WCV filter fields screen by default
  	  * @param array $classes
  	  * @return array
  	  * @since 2.7.2
  	  */
  	 public function wcviews_minify_my_metabox( $classes = array() ) {  	 	
  	 	
  	 	if ( is_array( $classes ) ) {
  	 		array_push( $classes, 'closed' ); 
  	 	}
  	 	return $classes;
  	 }
  	 
  	 /**
  	  * Rating structure misses a classname when performing Views AJAX
  	  * We add 'wc_views_star_rating' custom class name so it will work with anywhere else including AJAX.
  	  * @param string $rating_html
  	  * @param number $rating
  	  * @return string
  	  * @since 2.7.2
  	  */
  	 public function wcviews_product_get_rating_html( $rating_html ='' , $rating = 0 ) {
  	 	if ( $rating > 0 ) {
  	 		$rating_html  = '<div class="star-rating wc_views_star_rating" title="' . sprintf( esc_attr__( 'Rated %s out of 5', 'woocommerce' ), $rating ) . '">';
  	 		$rating_html .= '<span style="width:' . ( ( $rating / 5 ) * 100 ) . '%"><strong class="rating">' . $rating . '</strong> ' . esc_html__( 'out of 5', 'woocommerce' ) . '</span>';
  	 		$rating_html .= '</div>';
  	 	} else {
  	 		$rating_html  = '';
  	 	}
  	 	
  	 	return $rating_html;
  	 	
  	 }
  	 
	 /**
	  * Layouts archive overwrites default WooCommerce taxonomy queries that handles the display of product visibiilty and display of out of stock.
	  * Restore this important WooCommerce taxonomy query. Ticket: woocommerceviews-116
	  * @param object $query
	  * @param array $archive_settings
	  * @param number $archive_id
	  * @since 2.7.2
	  */
  	 public function wcviews_restore_wc_taxonomy_queries( $query = null, $archive_settings = array() , $archive_id = 0 ) {  	 	
  	 	/**
  	 	 * We alter only the main query
  	 	 */
  	 	if ( ! $query->is_main_query() ) {
  	 		return;
  	 	}  
  	 	/**
  	 	 * We want to implement this in WC 3.0+ only
  	 	 */
  	 	/**
  	 	 * Check if we are using WooCommerce 3.0+
  	 	 */
  	 	$new_wc_three	= $this->wc_views_two_point_seven_above();
  	 	if ( false === $new_wc_three ) {
  	 		return;	
  	 	}
  	 	/**
  	 	 * Check if we are only on WooCommerce loop pages:
  	 	 * shop, product category and product tag pages.
  	 	 * This method is HOOKED to 'wpv_action_apply_archive_query_settings'
  	 	 * which will fire in archives only
  	 	 * and is_woocommerce() returns TRUE on WC related pages only
  	 	 */
  	 	
  	 	$woocommerce_looped_pages	= false;
  	 	if ( ( function_exists( 'is_woocommerce' ) ) && ( is_woocommerce() ) ) {
  	 		$woocommerce_looped_pages	= true;
  	 	}
  	 	
  	 	//Layouts plugin status
  	 	$toolset_implemented			= false;
  	 	$layouts_plugin_status = $this->wc_views_check_status_of_layouts_plugin();  	 	
  	 	if ( ( true === $woocommerce_looped_pages ) && ( true === $layouts_plugin_status ) ) {
  	 		
  	 		/**
  	 		 * This block only applies to WooCommerce listing pages and Toolset WPA customization with Layouts
  	 		 */
  	 		/**
  	 		 * Check that this archives are overriden by Toolset
  	 		 * 3 WC archives we need to check
  	 		 * WooCommerce Shop, Product categories and Product tags
  	 		 * 
  	 		 */ 	 		
			$woocommerce_archive_type		= '';
  	 		if ( is_shop() ) {
  	 			$woocommerce_archive_type	= 'shop';
  	 		}
  	 		if ( is_product_category() ) {
  	 			$woocommerce_archive_type	= 'product_category';
  	 		}
  	 		if ( is_product_tag() ) {
  	 			$woocommerce_archive_type	= 'product_tag';
  	 			
  	 		}
  	 		if ( empty( $woocommerce_archive_type ) ) {
  	 			//No archives analyzed, bail out
  	 			return;
  	 		} 	 		
  	 		$toolset_implemented	= $this->wcviews_this_wc_archive_is_layouts_implemented( $woocommerce_archive_type );
  	 	} elseif ( ( true === $woocommerce_looped_pages ) && ( false === $layouts_plugin_status ) ) {  	 	
  	 		/**
  	 		 * This block applies to  WooCommerce listing pages and Toolset WPA customization without Layouts
  	 		 */
  	 		//We are on a standard WooCommerce looped pages.
  	 		//Check for WPA archive only implementation
  	 		if ( ( function_exists( 'is_wpv_wp_archive_assigned' ) ) && ( true === is_wpv_wp_archive_assigned() ) ) {
  	 			//This WooCommerce loop hage has Toolset WPA implementation
  	 			$toolset_implemented	= true;
  	 		}
  	 	}  	 	
  	 	//For both Layouts and non-Layouts implementation
  	 	if ( true === $toolset_implemented ) {
  	 		/**
  	 		 * We only inject the missing query if Toolset implemented
  	 		 */ 
  	 		global $woocommerce;
  	 		if ( ( is_object( $woocommerce ) ) && ( isset( $woocommerce->query ) ) ) {
  	 			//All set
  	 			$wc_query_object	= $woocommerce->query;
  	 			if ( method_exists( $wc_query_object, 'get_tax_query' ) ) {
  	 				$query->set( 'tax_query', $wc_query_object->get_tax_query( $query->get( 'tax_query' ), true ) );
  	 			}
  	 		}  	 		
  	 	} 	 	
  	 }
  	 /**
  	  * Anayze if this WooCommerce archives is Toolset layouts assigned.
  	  * @param string $archive_type
  	  * @return boolean
  	  * @since 2.7.2
  	  */
  	 public function wcviews_this_wc_archive_is_layouts_implemented( $archive_type = null ) {
  	 	$implemented	= false;
  	 	$archive_type_to_layouts_equivalence	= array(
  	 			'shop'				=>	'layouts_cpt_product',
  	 			'product_category'	=>	'layouts_taxonomy_loop_product_cat',
  	 			'product_tag'		=>	'layouts_taxonomy_loop_product_tag'
  	 	);
  	 	if ( ( class_exists( 'WPDDL_Options_Manager') ) && ( defined( 'WPDDL_GENERAL_OPTIONS') ) ) {
  	 		//Instantiate
  	 		$layouts_options_object	= new WPDDL_Options_Manager(WPDDL_GENERAL_OPTIONS);
  	 		//Get options
  	 		if ( method_exists( $layouts_options_object, 'get_options' ) ) {
  	 			$options = $layouts_options_object->get_options();
  	 			//Get layouts archive loop for this archive type
  	 			if ( isset( $archive_type_to_layouts_equivalence[ $archive_type ] ) ) {
  	 				$layouts_archive_loop	= $archive_type_to_layouts_equivalence[ $archive_type ];
  	 				if ( isset( $options[ $layouts_archive_loop ] ) ) {
  	 					$layouts_id	= $options[ $layouts_archive_loop ];
  	 					$layouts_id	= (int) $layouts_id;
  	 					if ( $layouts_id > 0 ) {
  	 						//OK we have layouts assigned to this WC archive
  	 						$implemented	= true;
  	 					}
  	 				}
  	 			}  	 			
  	 		}  	 		
  	 	}
  	 	return $implemented;  	 	
  	 }
  	 /**
  	  * Include WooCommerce product visibility in filters
  	  * For querying non-archive implementations.
  	  * @param array $filters
  	  * @param array $post_type
  	  * @return array
  	  * @since 2.7.2
  	  */
  	 public function wcviews_include_product_visibility_filters( $filters = array(), $post_type = array() ) { 
  	 	/**
  	 	 * Check if WC is active as this code might run if WooCommerce is inactive
  	 	 */
  	 	if ( false === $this->is_woocommerce_activated() ) {
  	 		//WC inactive skip this filter
  	 		return $filters;
  	 	}
  	 	/**
  	 	 * Check if we are using WooCommerce 3.0+
  	 	 */
  	 	$new_wc_three	= $this->wc_views_two_point_seven_above();
  	 	if ( false === $new_wc_three ) {
  	 		//Not using latest WooCommerce 3.0, skip this filter
  	 		return $filters;
  	 	}
  	 	if ( ( is_array( $post_type ) ) && ( in_array( 'product', $post_type ) ) ) {
  	 		//Define key
  	 		$key	= 'tax_input[product_visibility]';
  	 		//Define requisites
  	 		$name		= 'Product Visibility';
  	 		$present	= 'tax_product_visibility_relationship';
  	 		$callback	= array(
  	 				'WPV_Taxonomy_Filter',
  	 				'wpv_add_new_filter_taxonomy_list_item'
  	 		);
  	 		$args		= get_taxonomy( 'product_visibility' );
  	 		$group		= 'Taxonomies';  	 		
  	 		$filters[ $key ]['name']		= $name;
  	 		$filters[ $key ]['present']		= $present;
  	 		$filters[ $key ]['callback']	= $callback;
  	 		$filters[ $key ]['args']		= $args;
  	 		$filters[ $key ]['group']		= $group;  	 		
  	 	}
  	 	return $filters;
  	 	
  	 }
  	 
  	 /**
  	  * woocommerceviews-115: Hooked to 'woocommerce_register_post_type_shop_order' filter to make WooCommerce post type queryable by Views.
  	  * @since 2.7.4
  	  * @param array $post_type_param
  	  * @return array
  	  */
  	 public function wcviews_make_shop_order_views_queryable( $post_type_param = array() ) {  	 	
  	 	
  	 	//Make it public
  	 	if ( isset( $post_type_param['public'] ) ) {
  	 		$post_type_param['public']	= true;
  	 	}
  	 	//Make it publicly queryable
  	 	if ( isset( $post_type_param['publicly_queryable'] ) ) {
  	 		$post_type_param['publicly_queryable'] = true;
  	 	}
  	 	//Enable query vars
  	 	if ( isset( $post_type_param['query_var'] ) ) {
  	 		$post_type_param['query_var'] = true;
  	 	}
  	 	//Enable archives
  	 	if ( isset( $post_type_param['has_archive'] ) ) {
  	 		$post_type_param['has_archive'] = true;
  	 	}
  	 	//Enable rewrite
  	 	if ( isset( $post_type_param['rewrite'] ) ) {
  	 		$post_type_param['rewrite'] = true;
  	 	}
  	 	return $post_type_param;
  	 	
  	 }
	 
	/**
	 * Include the WooCommerce orders valid post status values in the list of supported
	 * post statuses that we can generate frontend edit links to, based on the permalink.
	 *
	 * @param array $statuses
	 * @param int $form_id
	 *
	 * @return array
	 *
	 * @since 2.7.8
	 */
	public function toolset_edit_post_link_publish_statuses_allowed( $statuses, $form_id ) {

		if ( ! $this->is_woocommerce_activated() ) {
			return $statuses;
		}
		
		$woocommerce_order_statuses = wc_get_order_statuses();
		
		$statuses = array_merge( array_keys( $woocommerce_order_statuses ), $statuses );
		
		return $statuses;
	}	
  	 
  	 /**
  	  * make the order to visible on front end.
  	  * @param string $ret
  	  * @param object $post_object
  	  * @return string
  	  */
  	 public function wcviews_allow_frontend_visibility_order_post_type( $ret = false, $post_object = null ) {
  	 	
  	 	if ( $this->is_woocommerce_activated() ) {
  	 		//WooCommerce active
  	 		if ( isset( $post_object->post_type ) ) {
  	 			$processed_post_type	= $post_object->post_type;
  	 			if ( 'shop_order' === $processed_post_type ) {
  	 				$ret = false;	
  	 			}
  	 		}  	 		
  	 	} 
  	 	
  	 	return $ret;
  	 	
  	 }
  	 
  	 /**
  	  * Allow shop order post statuses posts to appear in front end
  	  * @param array $post_status_args
  	  * @return array $post_status_args
  	  */
  	 public function wcviews_allow_frontend_visibility_order_post_type_statuses( $post_status_args = array() ) {  	 	
  	 	if ( ( is_array( $post_status_args ) ) && ( !empty( $post_status_args ) ) ) {
  	 		foreach ( $post_status_args as $k => $post_status_details ) {
  	 			if ( isset( $post_status_details['public'] ) ) {
  	 				//Make publicly available
  	 				$post_status_args[ $k ]['public'] = true;
  	 			}  	 			
  	 		}  	 		
  	 	} 	 	
  	 	
  	 	return $post_status_args;
  	 }
  	 
  	 /**
  	  * Callback of [wpv-ordered-product-ids] shortcode
  	  * @since 2.7.4
  	  * Shortcode to output ordered products in comma separated format for use with View shortcode attributes 
  	  * This shortcode should be used only within an WooCommerce shop order loop that is outputted by Toolset Views.
  	  * This shortcode is not meant to be added on single products or product listings.
  	  */
  	 public function wpv_show_ordered_product_ids_func( $output_format = 'comma_separated' ) {
  	 	if ( empty( $output_format ) ) {
  	 		//Defaults to comma separated output format
  	 		$output_format = 'comma_separated';
  	 	}
  	 	$output	= '';
  	 	$temp	= array();
  	 	global $post;
  	 	if ( ( $this->is_woocommerce_activated() ) && ( true === $this->wc_views_two_point_seven_above() ) ) {
  	 		//require WC 3.0+
  	 		//WooCommerce core plugin activated, so we are sure this 'shop_order' post type is under WC control
	  	 	//We check if the global $post is an WC Order object
  	 		if ( ( is_object( $post ) ) && ( isset( $post->post_type ) ) && ( isset( $post->ID ) ) ) {
	  	 		$post_type_parsed	= $post->post_type;
	  	 		if ( 'shop_order' === $post_type_parsed ) {
	  	 			//$post is a WooCommerce order
	  	 			$order_id	= $post->ID;
	  	 			$order_id	= (int) $order_id;
	  	 			if ( $order_id > 0 ) {
	  	 				//Get WC order object
	  	 				$order = wc_get_order( $order_id );  
	  	 				if ( ( is_object( $order ) ) && ( method_exists( $order, 'get_items') ) ) {
	  	 					$ordered_items	= $order->get_items();
	  	 					if ( ( is_array( $ordered_items ) ) && ( !empty( $ordered_items ) ) ) {
	  	 						//Loop over ordered items and get products
	  	 						foreach ( $ordered_items as $item_id => $item ) {
	  	 							if ( ( is_object( $item ) ) && ( method_exists( $item, 'get_product') ) ) {
	  	 								$product = $item->get_product();  
	  	 								if ( ( is_object( $product ) ) && 
	  	 								 ( method_exists( $product, 'get_id' ) ) && 
	  	 								 ( method_exists( $product, 'get_type' ) ) ) {
	  	 								 	//Retrieved product type
	  	 								 	if ( 'variation' === $product->get_type() ) {	  	 								 		
	  	 								 		//Variation product
	  	 								 		$variation_id	= $product->get_id();
	  	 								 		
	  	 								 		//Get parent variable product
	  	 								 		$temp[]	= wp_get_post_parent_id( $variation_id);
	  	 								 		
	  	 								 	} else {
	  	 								 		//Other product types
	  	 								 		//Retrieved product ID
	  	 								 		$temp[]	= $product->get_id();
	  	 								 	}
	  	 									
	  	 								}
	  	 							}	  	 							
	  	 						}
	  	 						//We are done looping over ordered items
	  	 						if ( ( !empty( $temp ) ) && ( 'comma_separated' === $output_format ) ) {
	  	 							$output = implode(",", $temp );	  	 							
	  	 						} else {
	  	 							//Array output
	  	 							$output	= $temp;
	  	 						}
	  	 					}	  	 					
	  	 				}
	  	 			}	  	 			
	  	 		}	  	 		
	  	 	}
  	 	} 
  	 	return $output;
  	 }
  	 
  	 /**
  	  * Registered wpv-ordered-product-ids automatically to Toolset Views third party shortcode arguments.
  	  * @param array $shortcodes
  	  * @return array
  	  */
  	 public function wcviews_add_ordered_products_ids_shortcode( $shortcodes = array() ) {  	 	
  	 	if ( is_array( $shortcodes ) ) {
  	 		$shortcodes[] = 'wpv-ordered-product-ids';  	 		
  	 	}
  	 	return $shortcodes;
  	 }
	 
	/**
	 * Callback for the addon compatibility shortcode wpv-storefront-product-sharing
	 *
	 * @since 2.7.6
	 */
	public function wpv_storefront_product_sharing_func( $atts = array(), $content = null ) {
		if ( ! is_callable( array( 'Storefront_Product_Sharing', 'instance' ) ) ) {
			return '';
		}
		
		if ( ! is_singular( 'product' ) ) {
			return;
		}
		
		$instance = Storefront_Product_Sharing::instance();
		
		ob_start();
		$instance->sps_product_sharing();
		return ob_get_clean();
	}
	
	/**
	 * Callback for the addon compatibility shortcode wpv-storefront-product-pagination
	 *
	 * @since 2.7.6
	 */
	public function wpv_storefront_product_pagination_func( $atts = array(), $content = null ) {
		if ( ! is_callable( array( 'Storefront_Product_Pagination', 'instance' ) ) ) {
			return '';
		}
		
		if ( ! is_singular( 'product' ) ) {
			return;
		}
		
		$instance = Storefront_Product_Pagination::instance();
		
		ob_start();
		$instance->spp_single_product_pagination();
		return ob_get_clean();
	}
  	 
  	 /**
  	  * Returns TRUE if the loaded product belongs to a current order under an order loop.
  	  * For use with wpv conditional statements.
  	  */
  	 public function wpv_woo_product_belongs_to_this_order() {
  	 	//Initialize to default false
  	 	$belongs_to_order	= false;
  	 	$current_page_id	= 0;
  	 	if ( ( $this->is_woocommerce_activated() ) && ( true === $this->wc_views_two_point_seven_above() ) ) {
  	 		global $post;
  	 		if ( ( is_object( $post ) ) && ( isset( $post->post_type ) ) && ( isset( $post->ID ) ) ) {
  	 			$post_type_parsed	= $post->post_type;
  	 			if ( 'shop_order' === $post_type_parsed ) {
  	 				//Current loaded post in a Views loop is a WooCommerce order, proceed..
  	 				$order_id	= (int) $post->ID;  	 				
  	 				if ( $order_id > 0 ) {  	 					
  	 					global $WP_Views;
  	 					if ( ( is_object( $WP_Views ) ) && ( method_exists( $WP_Views, 'get_current_page' ) ) ) {
  	 						$current_page	= $WP_Views->get_current_page();
  	 						if ( ( is_object( $current_page ) ) && ( isset( $current_page->ID ) ) ) {
  	 							$current_page_id	= (int) $current_page->ID;
  	 							if ( $current_page_id > 0 ) {
  	 								//Check if this is a product
  	 								if ( 'product' === get_post_type( $current_page_id ) ) {
  	 									//Current page is a product, proceed.
  	 									//Get associated products with this order
  	 									$associated_products	= $this->wpv_show_ordered_product_ids_func( 'array' );
  	 									//We check if this current product is in the order
  	 									if  ( ( is_array( $associated_products ) ) && ( in_array( $current_page_id, $associated_products) ) ) {
  	 										//Yes this product belongs to this order
  	 										$belongs_to_order	= true;  	 											
  	 									}
  	 								}
  	 							}
  	 						}
  	 					}  	 					
  	 				}
  	 			}
  	 		}  	 		
  	 	} 
  	 	return $belongs_to_order;
  	 	
  	 }
  	 
  	 /**
  	  * Added handler method where we can add the filter to remove users ability to edit WCV filter fields.
  	  * This is basically removing the 'edit' link in the WCV filter group link in Types field group edit page.
  	  * @since 2.7.4
  	  * @param string $good_protocol_url
  	  * @param string $original_url
  	  * @param string $_context
  	  * @return string
  	  */  	 
  	 public function wcviews_add_readonly_filter_wcvfieldgroup( $good_protocol_url = '', $original_url = '', $_context = '' ) {  	 	
  	 	//Establish filtering conditions - backend only
  	 	//Check if original URL is valid
  	 	$implement	= false;
  	 	//By default, always remove the filter
  	 	remove_filter( 'user_has_cap', array( $this,'wcviews_remove_wcv_filtergroup_edit_cap'), 10, 4 );
  	 	if ( ( !empty( $original_url ) ) && ( filter_var( $original_url, FILTER_VALIDATE_URL ) !== false ) && ( is_admin() ) ) {  	 		
  	 		//Original URL is valid
  	 		//Check the current screen
  	 		global $current_screen;
  	 		if ( ( is_object( $current_screen ) ) &&
  	 		( isset( $current_screen->id ) ) && 
  	 		( 'toolset_page_wpcf-cf' === $current_screen->id ) ) {
  	 			//We are on Types custom fields group admin page
  	 			//Parse URL
  	 			$parsed_url	= parse_url( $original_url );
  	 			if ( ( isset( $parsed_url['query'] ) ) && 
  	 				( isset( $parsed_url['path'] ) ) &&
  	 				( !empty( $parsed_url['query'] ) ) && 
  	 				( !empty( $parsed_url['path'] ) ) ) {
  	 				$exploded_section	= explode( '&', $parsed_url['query'] );
  	 				if ( is_array( $exploded_section ) ) {
  	 					$found	= array();
  	 					foreach ( $exploded_section as $k => $v ) {
  	 						if ( 'page=wpcf-edit' === $v ) {
  	 							$found[]	= $v;
  	 						} elseif ( ( strpos( $v, 'group_id=') !== false ) ) {
  	 							$found[]	= $v;
  	 						}
  	 					}
  	 					//Done check..
  	 					$counted	= count( $found );
  	 					if ( 2 === $counted ) {   	 		
  	 						$query	= array();
  	 						parse_str($parsed_url['query'], $query );
  	 						if ( isset( $query['group_id'] ) ) {
  	 							$group_id		= $query['group_id'];
  	 							$group_id		= (int) $group_id;
  	 							$wcv_group_id	= $this->wcv_get_groups_id();
  	 							$wcv_group_id	= (int) $wcv_group_id;
  	 							if ( $wcv_group_id === $group_id ) {
  	 								$implement	= true;
  	 							}
  	 						}
  	 					}
  	 				}  	 				
  	 			}  	 			
  	 		}  	 		
  	 	}  	 	
  	 	if ( true === $implement ) {
  	 		add_filter( 'user_has_cap', array( $this,'wcviews_remove_wcv_filtergroup_edit_cap'), 10, 4 );  	 		
  	 	} 
  	 	
  	 	//Return unfiltered
  	 	return $good_protocol_url;  	 	
  	 }
  	 
  	 /**
  	  * [woocommerceviews-124] Removes the capability of the user to edit WCV filter field groups.
  	  * @param array $all_caps
  	  * @param array $caps
  	  * @param array $args
  	  * @param object $the_object
  	  * @return array
  	  */
  	 public function wcviews_remove_wcv_filtergroup_edit_cap( $all_caps = array(), $caps = array(), $args = array(), $the_object = null ) {
  	 	
  	 	//Basic input data validation
  	 	$valid	= false;
  	 	if ( ( is_array( $caps ) ) && ( is_array( $all_caps ) ) ) {
  	 		$valid	= true;
  	 	}  	 	
  	 	if ( true === $valid ) {
  	 		//wpcf_custom_field_edit_others
  	 		if ( isset( $all_caps['wpcf_custom_field_edit_others'] ) ) {
  	 			if ( in_array( 'wpcf_custom_field_edit_others', $caps ) ) {
  	 				//Remove cap
  	 				unset( $all_caps['wpcf_custom_field_edit_others'] );
  	 			}
  	 		}
  	 		if ( isset( $all_caps['wpcf_custom_field_edit'] ) ) {
  	 			if ( in_array( 'wpcf_custom_field_edit', $caps ) ) {
  	 				//Remove cap
  	 				unset( $all_caps['wpcf_custom_field_edit'] );
  	 			}
  	 		}
  	 	}
  	 	return $all_caps;
  	 	
  	 }
  	 
	 /**
	  * Check if we want to add the filter to remove WCV filter groups edit capability.
	  * @param boolean $implement
	  * @return boolean
	  */
  	 public function wcviews_remove_wcv_filtergroup_edit_cap_filter_func() {  	 	
  	 	global $current_screen;  	 	
  	 	//By default, always remove the filter
  	 	remove_filter( 'user_has_cap', array( $this,'wcviews_remove_wcv_filtergroup_edit_cap'), 10, 4 );
  	 	if ( true === $this->wcviews_editing_wcv_filter_field_groups() ) {
  	 		//We are editing, add this filter
  	 		add_filter( 'user_has_cap', array( $this,'wcviews_remove_wcv_filtergroup_edit_cap'), 10, 4 );  	 		
  	 	}  	 	
  	 }
  	 
  	 /**
  	  * Don't show loop related shortcodes in a Layout assigned to single products
  	  * @since 2.7.4
  	  * @param array $group_data
  	  * @return array
  	  */
  	 public function wcviews_filter_loop_related_shortcodes_layouts( $group_data	= array(), $group_id = '' ) {   	 	
  	 	if ( ( 'woocomerce-views' === $group_id ) && ( is_array( $group_data ) ) && ( !empty( $group_data ) ) ) {
  	 		//Yes, now we need to know what is Layout is assigned to
  	 		if ( $this->wcviews_get_loaded_layout_edit_id() > 0 ) {
  	 			//Get loaded layouts ID
  	 			$layout_id	= $this->wcviews_get_loaded_layout_edit_id();
  	 			//Given the ID check if its assigned to 'products'
  	 			if ( ( true === $this->wcviews_get_loaded_layout_assignment( $layout_id, 'product' ) ) ||
  	 			( true === $this->wcviews_get_loaded_layout_assignment_specific( $layout_id, 'product' ) ) )
  	 			{
  	 				//This layout is assigned to single products;
  	 				//We don't want loop related shortcodes to appear here
  	 				//Loop over group data
  	 				foreach ( $group_data as $k => $v ) {
  	 					if ( 'fields' === $k ) {
  	 						foreach ( $v as $wcv_shortcode_name => $wcv_shortcode_details ) {
  	 							if ( isset( $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['single_product_usage'] ) ) {
  	 								//Check if this used only single products
  	 								if ( false === $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['single_product_usage'] ) {
  	 									//None single product usage, don't display
  	 									unset( $group_data[ $k ][ $wcv_shortcode_name ] );  	 												
  	 								}  	 											
  	 							}  	 										
  	 						}
  	 					}  	 								
  	 				}  	 							
  	 			} 	 			
  	 		} 	 		  	 		
  	 	}  	 	
  	 	return $group_data;  	 	
  	 }

  	 /**
  	  * Don't show non-loop related shortcodes in Views assigned to output products
  	  * @since 2.7.4
  	  * @param array $group_data
  	  * @return array
  	  */
  	 public function wcviews_filter_nonloop_related_shortcodes_views( $group_data	= array(), $group_id = '' ) {  	 	
  	 	$pop_up_mode	= false;
  	 	$key_match		= 'fields';
  	 	if ( empty( $group_id ) ) {
  	 		$pop_up_mode	= true;
  	 		$key_match		= 'WooCommerce';
  	 	}
  	 	if ( ( is_array( $group_data ) ) && ( !empty( $group_data ) ) ) {
  	 		$view_id = $this->wcviews_get_loaded_views_edit_id( $pop_up_mode );
  	 		if ( $view_id > 0 ) {
  	 			if ( true === $this->wcviews_get_loaded_views_assignment( $view_id, 'product' ) ) {
  	 				//This View is set to display 'Products'.
  	 				//We don't want non-loop related shortcodes to appear here
  	 				//Loop over group data
  	 				foreach ( $group_data as $k => $v ) {
  	 					if ( $key_match === $k ) {
  	 						foreach ( $v as $wcv_shortcode_name => $wcv_shortcode_details ) {
  	 							if ( isset( $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['listings_usage'] ) ) {
  	 								//Check if this used in listings.
  	 								if ( false === $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['listings_usage'] ) {
  	 									//single product usage, don't display
  	 									unset( $group_data[ $k ][ $wcv_shortcode_name ] );
  	 								}
  	 							}
  	 						}
  	 					}
  	 				}  	 				
  	 			} 	 			  	 			
  	 		} 	 		
  	 	}  	 	
  	 	return $group_data;  	 	
  	 }
  	 
  	 /**
  	  * Don't show non-order related shortcodes in a Layout assigned to WooCommerce orders
  	  * @since 2.7.4
  	  * @param array $group_data
  	  * @return array
  	  */
  	 public function wcviews_filter_nonorder_related_shortcodes_layouts( $group_data	= array(), $group_id = '' ) {
  	 	if ( ( 'woocomerce-views' === $group_id ) && ( is_array( $group_data ) ) && ( !empty( $group_data ) ) ) {
  	 		//Yes, now we need to know what is Layout is assigned to
  	 		if ( $this->wcviews_get_loaded_layout_edit_id() > 0 ) {
  	 			//Get loaded layouts ID
  	 			$layout_id	= $this->wcviews_get_loaded_layout_edit_id();
  	 			//Given the ID check if its assigned to 'shop_order'
  	 			if ( true === $this->wcviews_get_loaded_layout_assignment( $layout_id, 'shop_order' ) ) {
  	 				//This layout is assigned to WooCommerce orders post type;
  	 				//We don't want non-order related shortcodes to appear here
  	 				//Loop over group data
  	 				foreach ( $group_data as $k => $v ) {
  	 					if ( 'fields' === $k ) {
  	 						foreach ( $v as $wcv_shortcode_name => $wcv_shortcode_details ) {
  	 							if ( isset( $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['order_usage'] ) ) {
  	 								//Check if this covers order usage
  	 								if ( false === $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['order_usage'] ) {
  	 									//Not covered for order usage, removed.
  	 									unset( $group_data[ $k ][ $wcv_shortcode_name ] );
  	 								}
  	 							}
  	 						}
  	 					}
  	 				}
  	 			}
  	 		}
  	 	}
  	 	return $group_data;  	 	
  	 }
  	 
  	 /**
  	  * Don't show non-order related shortcodes in a Views which is set to display WooCommerce orders
  	  * @since 2.7.4
  	  * @param array $group_data
  	  * @param string $group_id
  	  */
  	 public function wcviews_filter_nonorder_related_shortcodes_views( $group_data	= array(), $group_id = '' ) {  	 	
  	 	$pop_up_mode	= false;
  	 	$key_match		= 'fields';
  	 	if ( empty( $group_id ) ) {
  	 		$pop_up_mode	= true;
  	 		$key_match		= 'WooCommerce';
  	 	}
  	 	if ( ( is_array( $group_data ) ) && ( !empty( $group_data ) ) ) {
  	 		$view_id = $this->wcviews_get_loaded_views_edit_id( $pop_up_mode );
  	 		if ( $view_id > 0 ) {
  	 			if ( true === $this->wcviews_get_loaded_views_assignment( $view_id, 'shop_order' ) ) {
  	 				//This View is set to display WooCommerce shop orders.
  	 				//We don't want non-order related shortcodes to appear here
  	 				//Loop over group data
  	 				foreach ( $group_data as $k => $v ) {
  	 					if ( $key_match === $k ) {
  	 						foreach ( $v as $wcv_shortcode_name => $wcv_shortcode_details ) {
  	 							if ( isset( $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['order_usage'] ) ) {
  	 								//Check if this used in listings.
  	 								if ( false === $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['order_usage'] ) {
  	 									//single product usage, don't display
  	 									unset( $group_data[ $k ][ $wcv_shortcode_name ] );
  	 								}
  	 							}
  	 						}
  	 					}
  	 				}
  	 			}
  	 		}
  	 	}
  	 	return $group_data;
  	 	
  	 	
  	 }
  	 
  	 /**
  	  * Usability - dont display loop related shortcodes on CT which are not for loops
  	  * @param array $group_data
  	  * @param string $group_id
  	  * @return array
  	  */
  	 public function wcviews_filter_loop_related_shortcodes_ct( $group_data	= array(), $group_id = '' ) {
  	 	if ( ( 'woocomerce-views' === $group_id ) && ( is_array( $group_data ) ) && ( !empty( $group_data ) ) ) {
  	 		$ct_id = $this->wcviews_get_loaded_ct_edit_id();
  	 		if ( $ct_id > 0 ) {
  	 			//Determine the CT ID assigned to products
  	 			$ct_assigned	= $this->check_if_content_template_has_assigned_to_products_wcviews( 'ID' );
  	 			if ( $ct_assigned === $ct_id ) {  	 			
  	 				//This CT is set to display 'Products'.
  	 				//We don't want loop related shortcodes to appear here
  	 				//Loop over group data
  	 				foreach ( $group_data as $k => $v ) {
  	 					if ( 'fields' === $k ) {
  	 						foreach ( $v as $wcv_shortcode_name => $wcv_shortcode_details ) {
  	 							if ( isset( $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['single_product_usage'] ) ) {
  	 								//Check if this used in single products
  	 								if ( false === $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['single_product_usage'] ) {
  	 									//Not a single product usage, don't display
  	 									unset( $group_data[ $k ][ $wcv_shortcode_name ] );
  	 								}
  	 							}
  	 						}
  	 					}
  	 				}
  	 			}
  	 		}
  	 	}
  	 	return $group_data; 
  	 }
  	 
  	 /**
  	  * Quick check if all prerequisite Layouts methods exists.
  	  * @return boolean
  	  */
  	 private function wcviews_check_prerequisite_layouts_methods() {  	 	
  	 	$method_exist_all	= false;
  	 	//Main Layouts object
  	 	global $wpddlayout;
  	 	$layouts_post_type_manager	= null;
  	 		 	
  	 	if ( is_object( $wpddlayout ) ) {
  	 		//Post types layouts object  
  	 		if ( isset( $wpddlayout->post_types_manager ) ) {
  	 			$layouts_post_type_manager	= $wpddlayout->post_types_manager;  	 			
  	 		}
  	 		if ( ( is_object( $layouts_post_type_manager ) ) && 
  	 		( method_exists( $wpddlayout, 'get_where_used' ) ) &&
  	 		( method_exists( $layouts_post_type_manager, 'get_layout_to_type_object' ) ) )
  	 		{
  	 			$method_exist_all	= true;  	 			
  	 		}
  	 	} 	 	
  	 	return $method_exist_all;  	 	
  	 }
  	 
  	 /**
  	  * Quick check if all prerequisite Views methods exists.
  	  * @return boolean
  	  */
  	 private function wcviews_check_prerequisite_views_methods() {
  	 	$method_exist_all	= false;
  	 	//Main Views object
  	 	global $WP_Views;  	  	 	
  	 	if ( is_object( $WP_Views ) ) {
  	 		if ( method_exists( $WP_Views, 'get_view_settings' ) ) {
  	 			$method_exist_all	= true;
  	 		}
  	 	}
  	 	return $method_exist_all;  	 	
  	 }
  	 
  	 /**
  	  * Returns layouts ID being edited
  	  * @return number
  	  */
  	 private function wcviews_get_loaded_layout_edit_id() {
  	 	$layout_id	= 0;
  	 	$args		= array(
  	 			'page'		=> FILTER_SANITIZE_STRING,
  	 			'layout_id'	=> FILTER_SANITIZE_NUMBER_INT,
  	 			'action'	=> FILTER_SANITIZE_STRING
  	 	);
  	 	$filter_input = filter_input_array( INPUT_GET, $args );
  	 	//Satisfy conditions that user is editing Layout
  	 	if ( ( isset( $filter_input['page'] ) ) && ( isset( $filter_input['layout_id'] ) )  ) {
  	 		if ( 'dd_layouts_edit' === $filter_input['page'] ) {
  	 			$layout_id	= (int) $filter_input['layout_id'];
  	 		}  	 		
  	 	}
  	 	return $layout_id;  	 	
  	 }
  	 
  	 /**
  	  * Returns Views ID being edited
  	  * @return number
  	  */
  	 private function wcviews_get_loaded_views_edit_id( $pop_up_mode = false ) {  
  	 	$view_id	= 0;
  	 	$args		= array(
  	 			'page'		=> FILTER_SANITIZE_STRING,
  	 			'view_id'	=> FILTER_SANITIZE_NUMBER_INT,
  	 			'action'	=> FILTER_SANITIZE_STRING
  	 	);
  	 	if ( true === $pop_up_mode ) {
  	 		$source	= INPUT_POST;
  	 	} else {
  	 		$source	= INPUT_GET;
  	 	}
  	 	$filter_input = filter_input_array( $source, $args );
  	 	
  	 	//Satisfy conditions that user is editing Views
  	 	if ( isset( $filter_input['view_id'] )  ) { 
  	 		//Check sources
  	 		$source_page	= '';
  	 		$source_action	= '';
  	 		$valid_source	= false;
  	 		$valid_actions	= array( 'wpv_loop_wizard_add_field', 'wpv_loop_wizard_load_saved_fields' );
  	 		if ( isset( $filter_input['page'] )  ) {
  	 			$source_page	= $filter_input['page'];
  	 			if ( ( 'view-archives-editor' === $source_page ) || ( 'views-editor' === $source_page ) ) {
  	 				$valid_source	= true;
  	 			} 
  	 		} elseif ( $filter_input['action'] ) {
  	 			$source_action	= $filter_input['action'];
  	 			if ( in_array( $source_action, $valid_actions ) ) {
  	 				$valid_source	= true;
  	 			}
  	 		}
  	 		if ( true === $valid_source) {
  	 			$view_id	= (int) $filter_input['view_id'];
  	 		}  	 		  	 		
  	 	}  	 	
  	 	return $view_id;  	 	
  	 }
  	 
  	 /**
  	  * Returns CT ID being edited.
  	  * @return number
  	  */
  	 private function wcviews_get_loaded_ct_edit_id() {
  	 	$ct_id	= 0;
  	 	$args	= array(
  	 			'page'		=> FILTER_SANITIZE_STRING,
  	 			'ct_id'		=> FILTER_SANITIZE_NUMBER_INT,
  	 			'action'	=> FILTER_SANITIZE_STRING
  	 	);
  	 	$filter_input = filter_input_array( INPUT_GET, $args );
  	 	
  	 	//Satisfy conditions that user is editing CT
  	 	if ( ( isset( $filter_input['page'] ) ) && 
  	 	   ( isset( $filter_input['ct_id'] ) ) )  	 	   
  	 	   {
  	 	   	if ( 'ct-editor' === $filter_input['page'] ) {
  	 	   		///Get CT id
  	 	   		$ct_id= (int) $filter_input['ct_id'];  	 	   		
  	 	   	}
  	 	}
  	 	return $ct_id;
  	 }
  	 
	 /**
	  * Check if $layout_id is assigned to given $post_type
	  * @param number $layout_id
	  * @param string $post_type
	  * @return boolean
	  */
  	 private function wcviews_get_loaded_layout_assignment( $layout_id = 0, $post_type = '' ) {
  	 	$assigned_matched	= false;
  	 	if ( ( true === $this->wcviews_check_prerequisite_layouts_methods() ) &&
  	 		 ( $this->is_woocommerce_activated() ) && 
  	 		 ( $layout_id > 0 ) &&
  	 		 ( !empty( $post_type ) ) ) {
  	 		//Meet all pre-requisites Start with checking if $layout_id is assigned to $post_type
  	 		global $wpddlayout;
  	 		$layouts_post_type_manager	= $wpddlayout->post_types_manager;
  	 		$layout_assigned_to_products_id	= 0;
  	 		$layout_assigned_to_products	= $layouts_post_type_manager->get_layout_to_type_object( $post_type );
  	 		if ( isset( $layout_assigned_to_products->layout_id ) ) {
  	 			$layout_assigned_to_products_id = $layout_assigned_to_products->layout_id;
  	 			$layout_assigned_to_products_id	= (int) $layout_assigned_to_products_id;
  	 			if ( $layout_assigned_to_products_id === $layout_id ) {
  	 				$assigned_matched	= true;  	 				
  	 			}
  	 		}
  	 	}
  	 	
  	 	return $assigned_matched;
  	 }
  	 
  	 /**
  	  * Check if $views_id is assigned to given $post_type
  	  * @param number $views_id
  	  * @param string $post_type
  	  * @return boolean
  	  */
  	 private function wcviews_get_loaded_views_assignment( $views_id = 0, $post_type = '', $archives_query = false, $tax_query	= false ) {
  	 	$assigned_matched	= false; 	 	
  	 	if ( ( true === $this->wcviews_check_prerequisite_views_methods() ) && 
  	 			( $this->is_woocommerce_activated() )  &&
  	 			( $views_id > 0 ) &&
  	 			( !empty( $post_type ) )	
  	 			) {
  	 				//Start with checking if this View is assigned to products  
  	 				if ( ( false === $archives_query ) ) {
  	 					global $WP_Views;
  	 					$view_settings = $WP_Views->get_view_settings( $views_id );
  	 					if ( ( ( isset( $view_settings['post_type'] ) ) && ( is_array( $view_settings['post_type'] ) ) ) &&
  	 					( false === $tax_query ) )
  	 					{
  	 						if ( in_array( $post_type , $view_settings['post_type'] ) ) {
  	 							//Matched
  	 							$assigned_matched	= true;
  	 						}
  	 					} elseif ( ( isset( $view_settings['taxonomy_type'] ) ) && 
  	 							( is_array( $view_settings['taxonomy_type'] ) ) &&
  	 							( true === $tax_query )	) {
  	 							//Taxonomy query
  	 							if ( in_array( $post_type , $view_settings['taxonomy_type'] ) ) {
  	 								//Matched
  	 								$assigned_matched	= true;
  	 							}  	 						
  	 					}
  	 				} elseif ( true === $archives_query ) {
  	 					//Check if this archive is products assigned	 					
  	 					$is_looping_products	= $this->wcviews_check_if_archive_is_assign_given_pt( $views_id, $post_type ); 
  	 					if ( true === $is_looping_products ) {
  	 						$assigned_matched = true;
  	 					}
  	 				}
  	 	}  	 	
  	 	return $assigned_matched;
  	 }
  	 
  	 /**
  	  * Check if this archive is products assigned
  	  * @param number $views_id
  	  * @return boolean
  	  */
  	 private function wcviews_check_if_archive_is_assign_given_pt( $views_id = 0, $given_pt	= '' ) { 
  	 	$is_looping_products	= false;
  	 	$views_id				= (int)$views_id;
  	 	$looped_assigned		= array();
  	 	if ( 'product' === $given_pt ) {
  	 		$default_product_loops	= array(
  	 				//Custom Product archive
  	 				'view_cpt_product',
  	 				//Product cateory
  	 				'view_taxonomy_loop_product_cat',
  	 				//Product tag
  	 				'view_taxonomy_loop_product_tag'
  	 		);  	 		
  	 	} elseif ( 'shop_order' === $given_pt ) {
  	 		$default_product_loops	= array(
  	 				//Custom Shop order archive
  	 				'view_cpt_shop_order'  	 				
  	 		);   	 		
  	 	}

  	 	//Get Views setting and loops that is using this archive
  	 	$views_setting			= get_option('wpv_options');
  	 	if ( ( is_array( $views_setting ) ) && ( $views_id > 0 ) ) {
  	 		foreach ( $views_setting as $k => $v ) {
  	 			$v	= intval( $v  );
  	 			if ( $v === $views_id ) {
  	 				$looped_assigned[]	= $k;  	 				
  	 			} 	 			
  	 		}
  	 		//Done looping, now we checked if these are product loops  	 		
  	 		if ( is_array( $looped_assigned ) ) {
  	 			foreach ( $looped_assigned as $loop_key => $loop ) {
  	 				if ( in_array( $loop, $default_product_loops ) ) {
  	 					//Yes, looping products
  	 					$is_looping_products	= true;
  	 					return $is_looping_products;
  	 				}
  	 			}  	 			
  	 		}
  	 		//When it reach this part, no default product loops are used.
  	 		//Check for custom taxonomy outputting a product loop
  	 		foreach ( $looped_assigned as $loop_key => $loop ) {
  	 			//Get custom taxonomy slug
  	 			$custom_taxonomy_slug	= str_replace('view_taxonomy_loop_', '', $loop );
  	 			if ( ( !empty( $custom_taxonomy_slug ) ) && ( is_string( $custom_taxonomy_slug ) ) ) {
  	 				$taxonomy_details	= get_taxonomy( $custom_taxonomy_slug );  
  	 				if ( ( is_object( $taxonomy_details ) ) && ( isset( $taxonomy_details->object_type ) ) ) {
  	 					$object_type	= $taxonomy_details->object_type;
  	 					if ( is_array( $object_type ) ) {
  	 						if ( in_array( $given_pt, $object_type ) ) {
  	 							$is_looping_products	= true;
  	 							return $is_looping_products;
  	 						}
  	 					}
  	 				}	 				
  	 			}
  	 		}  	 		
  	 	}  	 	
  	 	return $is_looping_products;
  	 }
  	 
  	 /**
  	  * Sample some layout assigned post if matched with the analyzed post type
  	  * @param number $layout_id
  	  * @param string $post_type
  	  * @return boolean
  	  */
  	 private function wcviews_get_loaded_layout_assignment_specific( $layout_id = 0, $post_type = '' ) {
  	 	$assigned_matched	= false;
  	 	if ( ( true === $this->wcviews_check_prerequisite_layouts_methods() ) &&
  	 			( $this->is_woocommerce_activated() ) &&
  	 			( $layout_id > 0 ) &&
  	 			( !empty( $post_type ) ) ) {
  	 		//Meet all pre-requisites Start with checking if $layout_id is assigned to $post_type
  	 		global $wpddlayout;
  	 		$where_used	= $wpddlayout->get_where_used( $layout_id );
  	 		//Sample just one post
  	 		if ( isset( $where_used[0]->post_type ) ) {
  	 			$post_type_used	= $where_used[0]->post_type;
  	 			if ( $post_type === $post_type_used ) {
  	 				$assigned_matched	= true;  	 						
  	 			}  	 					
  	 		}  	 				
  	 	}  	 			
  	 	return $assigned_matched;  	 	
  	 }
  	 
	/**
	 * Remove non-loop related shortcodes on WordPress archives edit that displays product loops
	 * @since 2.7.4
	 * @param array $group_data
	 * @param string $group_id
	 * @return array
	 */
  	 public function wcviews_filter_nonloop_related_shortcodes_wparchives( $group_data	= array(), $group_id = '' ) { 
  	 	$pop_up_mode	= false;
  	 	$key_match		= 'fields';
  	 	if ( empty( $group_id ) ) {
  	 		$pop_up_mode	= true;
  	 		$key_match		= 'WooCommerce';
  	 	}
  	 	if ( ( is_array( $group_data ) ) && ( !empty( $group_data ) ) ) {
  	 		$view_id = $this->wcviews_get_loaded_views_edit_id( $pop_up_mode );
  	 		if ( $view_id > 0 ) {
  	 			if ( true === $this->wcviews_get_loaded_views_assignment( $view_id, 'product', true ) ) {
  	 				//This WordPress archive View is set to display a loop of WooCommerce products
  	 				//We don't want non-loop related shortcodes on WordPress archives edit
  	 				//Loop over group data
  	 				foreach ( $group_data as $k => $v ) {
  	 					if ( $key_match === $k ) {
  	 						foreach ( $v as $wcv_shortcode_name => $wcv_shortcode_details ) {
  	 							if ( isset( $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['listings_usage']) ) {
  	 								//Check if this used in listings.
  	 								if ( false === $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['listings_usage'] ) {
  	 									//single product usage, don't display
  	 									unset( $group_data[ $k ][ $wcv_shortcode_name ] );
  	 								}
  	 							}
  	 						}
  	 					}
  	 				}
  	 			}
  	 		}
  	 	}  	 	
  	 	return $group_data;
  	 	
  	 }
  	 
  	 /**
  	  * Remove non-order related shortcodes on WordPress archives edit that displays order loops.
  	  * @since 2.7.4
  	  * @param array $group_data
  	  * @param string $group_id
  	  * @return array
  	  */
  	 public function wcviews_filter_nonorder_related_shortcodes_wparchives( $group_data	= array(), $group_id = '' ) {
  	 	$pop_up_mode	= false;
  	 	$key_match		= 'fields';
  	 	if ( empty( $group_id ) ) {
  	 		$pop_up_mode	= true;
  	 		$key_match		= 'WooCommerce';
  	 	}
  	 	if ( ( is_array( $group_data ) ) && ( !empty( $group_data ) ) ) {
  	 		$view_id = $this->wcviews_get_loaded_views_edit_id( $pop_up_mode );
  	 		if ( $view_id > 0 ) {
  	 			if ( true === $this->wcviews_get_loaded_views_assignment( $view_id, 'shop_order', true ) ) {
  	 				//This WordPress archive View is set to display a loop of WooCommerce products
  	 				//We don't want non-loop related shortcodes on WordPress archives edit
  	 				//Loop over group data  	 				
  	 				foreach ( $group_data as $k => $v ) {
  	 					if ( $key_match === $k ) {
  	 						foreach ( $v as $wcv_shortcode_name => $wcv_shortcode_details ) {
  	 							if ( isset( $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['order_usage']) ) {
  	 								//Check if this used in listings.
  	 								if ( false === $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['order_usage'] ) {
  	 									//single product usage, don't display
  	 									unset( $group_data[ $k ][ $wcv_shortcode_name ] );
  	 								}
  	 							}
  	 						}
  	 					}
  	 				}  	 				
  	 			}
  	 		}
  	 	}
  	 	return $group_data;  	 	
  	 }
  	 
  	 /**
  	  * Don't show non-product category related shortcode on Views editing product category.
  	  * @param array $group_data
  	  * @param string $group_id
  	  * @return array
  	  */
  	 public function wcviews_filter_nonprodcat_related_shortcodes( $group_data	= array(), $group_id = '' ) {
  	 	$pop_up_mode	= false;
  	 	$key_match		= 'fields';
  	 	if ( empty( $group_id ) ) {
  	 		$pop_up_mode	= true;
  	 		$key_match		= 'WooCommerce';
  	 	}
  	 	if ( ( is_array( $group_data ) ) && ( !empty( $group_data ) ) ) {
  	 		$view_id = $this->wcviews_get_loaded_views_edit_id( $pop_up_mode );
  	 		if ( $view_id > 0 ) {
  	 			if ( true === $this->wcviews_get_loaded_views_assignment( $view_id, 'product_cat', false, true ) ) {
  	 				//This WordPress archive View is set to display a loop of WooCommerce products
  	 				//We don't want non-loop related shortcodes on WordPress archives edit
  	 				//Loop over group data
  	 				foreach ( $group_data as $k => $v ) {
  	 					if ( $key_match === $k ) {
  	 						foreach ( $v as $wcv_shortcode_name => $wcv_shortcode_details ) {
  	 							if ( isset( $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['product_cat_usage']) ) {
  	 								//Check if this used in listings.
  	 								if ( false === $this->wcviews_final_shortcodes[ $wcv_shortcode_name ]['product_cat_usage'] ) {
  	 									//single product usage, don't display
  	 									unset( $group_data[ $k ][ $wcv_shortcode_name ] );
  	 								}
  	 							}
  	 						}
  	 					}
  	 				}
  	 			}
  	 		}
  	 	}
  	 	return $group_data;   	 	
  	 }
  	 
  	 /**
  	  * Enable preview for WooCommerce orders post type in Layouts
  	  * @since 2.7.4
  	  * @param object $posts_query
  	  */
  	 public function wcviews_enable_preview_order_post_type_layouts( $posts_query = null ) {  	 	
  	 	if ( ! is_object( $posts_query ) || ! is_a( $posts_query, 'WP_Query' ) ) {
  	 		return;
  	 	}
  	 	if ( ( isset( $posts_query->query_vars['post_status'] ) ) && 
  	 	 ( isset( $posts_query->query_vars['post_type'] ) ) && 
  	 	 ( $this->is_woocommerce_activated() ) &&
  	 	 ( defined('DOING_AJAX') ) && 
  	 	 ( DOING_AJAX ) && 
  	 	 ( is_admin() ) ) {  	 			 
  	 	 	$post_type_parse	= $posts_query->query_vars['post_type'];
  	 	 	if ( 'shop_order' === $post_type_parse ) {
  	 	 		//Shop order is under WooCommerce control
  	 	 		//Get post status passed by reference
  	 	 		if ( is_array( $posts_query->query_vars['post_status'] ) ) {
  	 	 			foreach ( $posts_query->query_vars['post_status'] as $k => $v ) {
  	 	 				if ( 'publish'	=== $v ) {
  	 	 					//Change to 'wc-completed'
  	 	 					$posts_query->query_vars['post_status'][$k]	= 'wc-completed';
  	 	 				}
  	 	 			}
  	 	 		}
  	 	 	}  	 		
  	 	}  	 	
  	 }
}