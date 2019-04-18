<?php

class WCViews_shortcodes_gui {
	
	private $wpv_shortcodes_api_version = 0;
	
	public function initialize() {
		
		/** {ENCRYPTION PATCH HERE} **/
		
		// Needs to run after after_setup_theme::999
		add_action( 'after_setup_theme', array( $this, 'register_shortcodes_within_views' ), 9999 );
		
		add_action( 'init', array( $this, 'initialize_hooks' ) );
	}
	
	/**
	 * Register the WCV shortcodes within Views.
	 *
	 * @since m2m
	 */
	public function register_shortcodes_within_views() {
		$this->wpv_shortcodes_api_version = apply_filters( 'wpv_filter_wpv_get_shortcodes_api_version', 0 );
		
		if ( $this->wpv_shortcodes_api_version < 260000 ) {
			// Shortcodes in the Fields and Views dialog, legacy pre-2.6
			add_action( 'init', array( $this, 'register_shortcodes_dialog_groups' ), 10 );
		} else {
			// Since Views 2.6 (Shortcodes API version 260000) 
			// we use a dedicated action and better priorities management
			// TODO new action callback with the proper registration action and elements
			// TODO move legacy to a dedicated compatibility class
			add_action( 'wpv_action_collect_shortcode_groups', array( $this, 'register_shortcodes_dialog_groups' ), 5 );
		}
	}
	
	/**
	 *
	 * @since xxx
	 */
	public function initialize_hooks() {
		add_filter( 'wpv_filter_wpv_shortcodes_gui_data', array( $this, 'register_shortcodes_data' ) );
	}
	
	public function get_images_sizes() {
		global $_wp_additional_image_sizes;
		$sizes = array();
		foreach( get_intermediate_image_sizes() as $s ){
			$sizes[ $s ] = array( 0, 0 );
			if ( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ){
				$sizes[ $s ][0] = get_option( $s . '_size_w' );
				$sizes[ $s ][1] = get_option( $s . '_size_h' );
			} else {
				if ( 
					isset( $_wp_additional_image_sizes ) 
					&& isset( $_wp_additional_image_sizes[ $s ] ) 
				) {
					$sizes[ $s ] = array( 
						$_wp_additional_image_sizes[ $s ]['width'], 
						$_wp_additional_image_sizes[ $s ]['height'], 
					);
				}
			}
		}
	
		return $sizes;
	}
	
	/**
	 * Maybe add brackets to the shortcode as passed to the Views GUI API.
	 *
	 * Required for backwards compatibility, as in legacy Views shortcodes were getting brackets automatically,
	 * and since v.260000 they need to be passed completed.
	 *
	 * @param string $shortcode
	 *
	 * @return string
	 *
	 * @since m2m
	 */
	private function maybe_add_backets_to_shortcode( $shortcode ) {
		if ( $this->wpv_shortcodes_api_version < 260000 ) {
			return $shortcode;
		}
		$shortcode = '[' . $shortcode . ']';
		return $shortcode;
	}
	
	public function register_shortcodes_dialog_groups() {
		
		$group_id	= 'woocomerce-views';
		$group_data	= array(
			'name'		=> __( 'WooCommerce', 'woocommerce_views' ),
			'fields'	=> array(
				'wpv-woo-buy-or-select' => array(
					'name'		=> __( 'Add to cart button - product listing pages', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-buy-or-select' ),
					'handle'	=> 'wpv-woo-buy-or-select',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-buy-or-select', title: '" . esc_js( __( 'Add to cart button - product listing pages', 'woocommerce_views' ) ). "' })"
				),
				'wpv-woo-product-price' => array(
					'name'		=> __( 'Product price', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-product-price' ),
					'handle'	=> 'wpv-woo-product-price',
					'callback'	=> ""
				),
				'wpv-woo-buy-options' => array(
					'name'		=> __( 'Add to cart button - single product page', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-buy-options' ),
					'handle'	=> 'wpv-woo-buy-options',
					'callback' => "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-buy-options', title: '" . esc_js( __( 'Add to cart button - single product page', 'woocommerce_views' ) ). "' })"
				),
				'wpv-woo-product-image' => array(
					'name'		=> __( 'Product image', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-product-image' ),
					'handle'	=> 'wpv-woo-product-image',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-product-image', title: '" . esc_js( __( 'Product image', 'woocommerce_views' ) ). "' })"
				),
				'wpv-add-to-cart-message' => array(
					'name'		=> __( 'Add to cart message', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-add-to-cart-message' ),
					'handle'	=> 'wpv-add-to-cart-message',
					'callback'	=> ""
				),
				'wpv-woo-display-tabs' => array(
					'name'		=> __( 'Product tabs - single product page', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-display-tabs' ),
					'handle'	=> 'wpv-woo-display-tabs',
					'callback' => "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-display-tabs', title: '" . esc_js( __( 'Product tabs - single product page', 'woocommerce_views' ) ). "' })"
				),
				'wpv-woo-onsale' => array(
					'name'		=> __( 'Onsale badge', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-onsale' ),
					'handle'	=> 'wpv-woo-onsale',
					'callback'	=> ""
				),
				'wpv-woo-list_attributes' => array(
					'name'		=> __( 'Product attributes', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-list_attributes' ),
					'handle'	=> 'wpv-woo-list_attributes',
					'callback'	=> ""
				),
				'wpv-woo-related_products' => array(
					'name'		=> __( 'Related Products', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-related_products' ),
					'handle'	=> 'wpv-woo-related_products',
					'callback'	=> ""
				),
				'wpv-woo-single-products-rating' => array(
					'name'		=> __( 'Product Rating - single product page', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-single-products-rating' ),
					'handle'	=> 'wpv-woo-single-products-rating',
					'callback'	=> ""
				),
				'wpv-woo-products-rating-listing' => array(
					'name'		=> __( 'Product Rating - product listing pages', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-products-rating-listing' ),
					'handle'	=> 'wpv-woo-products-rating-listing',
					'callback'	=> ""
				),
				'wpv-woo-productcategory-images' => array(
					'name'		=> __( 'Product Category Image', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-productcategory-images' ),
					'handle'	=> 'wpv-woo-productcategory-images',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-woo-productcategory-images', title: '" . esc_js( __( 'Product category image', 'woocommerce_views' ) ). "' })"
				),
				'wpv-woo-show-upsell-items' => array(
					'name'		=> __( 'Product Upsell', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-show-upsell-items' ),
					'handle'	=> 'wpv-woo-show-upsell-items',
					'callback'	=> ""
				),
				'wpv-woo-breadcrumb' => array(
					'name'		=> __( 'Breadcrumb', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-breadcrumb' ),
					'handle'	=> 'wpv-woo-breadcrumb',
					'callback'	=> ""
				),
				'wpv-woo-product-meta' => array(
					'name'		=> __( 'Product meta', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-product-meta' ),
					'handle'	=> 'wpv-woo-product-meta',
					'callback'	=> ""
				),
				'wpv-woo-cart-count' => array(
					'name'		=> __( 'Cart Count', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-cart-count' ),
					'handle'	=> 'wpv-woo-cart-count',
					'callback'	=> ""
				),
				'wpv-woo-reviews' => array(
					'name'		=> __( 'Reviews', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-woo-reviews' ),
					'handle'	=> 'wpv-woo-reviews',
					'callback'	=> ""
				),
				'wpv-ordered-product-ids' => array(
					'name'		=> __( 'Ordered products', 'woocommerce_views' ),
					'shortcode'	=> $this->maybe_add_backets_to_shortcode( 'wpv-ordered-product-ids' ),
					'handle'	=> 'wpv-ordered-product-ids',
					'callback'	=> ""
					)
			)
		);
		
		//Filter for refine control
		$group_data	= apply_filters( 'wcviews_filter_group_data_shortcodes', $group_data, $group_id );
		
		do_action( 'wpv_action_wpv_register_dialog_group', $group_id, $group_data );
		
		$this->maybe_register_addon_shortcodes_dialog_groups();
		
	}
	
	public function register_shortcodes_data( $views_shortcodes ) {
		$views_shortcodes['wpv-woo-buy-or-select'] = array(
			'callback' => array( $this, 'get_wpv_woo_buy_or_select_data' )
		);
		
		$views_shortcodes['wpv-woo-buy-options'] = array(
			'callback' => array( $this, 'get_wpv_woo_buy_options_data' )
		);
		
		$views_shortcodes['wpv-woo-product-image'] = array(
			'callback' => array( $this, 'get_wpv_woo_product_image_data' )
		);
		
		$views_shortcodes['wpv-woo-display-tabs'] = array(
			'callback' => array( $this, 'get_wpv_woo_display_tabs_data' )
		);
		
		$views_shortcodes['wpv-woo-productcategory-images'] = array(
			'callback' => array( $this, 'get_wpv_woo_productcategory_images_data' )
		);
		
		return $views_shortcodes;
	}
	
	public function get_wpv_woo_buy_or_select_data( $parameters = array(), $overrides = array() ) {
		$data = array(
			'name' => __( 'Add to cart button - product listing pages', 'woocommerce_views' ),
			'label' => __( 'Add to cart button - product listing pages', 'woocommerce_views' ),
			'attributes' => array(
				'display-options' => array(
					'label' => __('Display options', 'woocommerce_views'),
					'header' => __('Display options', 'woocommerce_views'),
					'fields' => array(
						'information'	=> array(
							'type'		=> 'info',
							'content'	=> __( 'Displays "Add to cart" or "Select option" button in product listing pages.', 'woocommerce_views' )
						),
						'add_to_cart_text' => array(
							'label' => __( 'Simple products: Add to Cart label', 'woocommerce_views'),
							'type' => 'text',
							'placeholder' => __( 'Add to cart', 'woocommerce_views' ),
							'description' => __( '' , 'woocommerce_views' ),
						),
						'show_quantity_in_button' => array(
							'label'			=> __( 'Simple products: quantity', 'woocommerce_views'),
							'type'			=> 'radio',
							'options'		=> array(
								'no'		=> __( 'Do not show quantities selector next to the Add to Cart button', 'woocommerce_views' ),
								'yes'		=> __( 'Show quantities selector next to the Add to Cart button', 'woocommerce_views' ),
							),
							'default'		=> 'no',
							'description'	=> '',
						),
						'link_to_product_text' => array(
							'label' => __( 'Variations: Select Options label', 'woocommerce_views'),
							'type' => 'text',
							'placeholder' => __( 'Select options', 'woocommerce_views' ),
							'description' => __( '' , 'woocommerce_views' ),
						),
						'show_variation_options' => array(
							'label'			=> __( 'Variations: options in listing page', 'woocommerce_views'),
							'type'			=> 'radio',
							'options'		=> array(
								'no'		=> __( 'Do not show variation options in products listing pages', 'woocommerce_views' ),
								'yes'		=> __( 'Show variation options in products listing page', 'woocommerce_views' ),
							),
							'default'		=> 'no',
							'description'	=> '',
						),
						'group_add_to_cart_text' => array(
							'label' => __( 'Grouped products: Add to Cart label', 'woocommerce_views'),
							'type' => 'text',
							'placeholder' => __( 'Select options', 'woocommerce_views' ),
							'description' => __( '' , 'woocommerce_views' ),
						),
						'external_add_to_cart_text' => array(
							'label' => __( 'External products: Add to Cart label', 'woocommerce_views'),
							'type' => 'text',
							'placeholder' => __( 'Select options', 'woocommerce_views' ),
							'description' => __( '' , 'woocommerce_views' ),
						),
					),
				),
			),
		);

		return $data;
	}
	
	public function get_wpv_woo_buy_options_data( $parameters = array(), $overrides = array() ) {
		$data = array(
			'name' => __( 'Add to cart button - single product page', 'woocommerce_views' ),
			'label' => __( 'Add to cart button - single product page', 'woocommerce_views' ),
			'attributes' => array(
				'display-options' => array(
					'label' => __('Display options', 'woocommerce_views'),
					'header' => __('Display options', 'woocommerce_views'),
					'fields' => array(
						'information'	=> array(
							'type'		=> 'info',
							'content'	=> __( 'Displays "Add to cart" button or "Select option" box for variations in single product pages.', 'woocommerce_views' )
						),
						'add_to_cart_text' => array(
							'label' => __( 'Simple products: Add to Cart label', 'woocommerce_views'),
							'type' => 'text',
							'placeholder' => __( 'Add to cart', 'woocommerce_views' ),
							'description' => __( '' , 'woocommerce_views' ),
						),
					),
				),
			),
		);

		return $data;
	}
	
	public function get_wpv_woo_product_image_data( $parameters = array(), $overrides = array() ) {
		$available_images_for_wcviews = $this->get_images_sizes();
		$clean_image_name_array = array(
			'thumbnail' => __( 'WordPress thumbnail size', 'woocommerce_views' ),
			'medium' => __( 'WordPress medium image size', 'woocommerce_views' ),
			'large' => __( 'WordPress large image size', 'woocommerce_views' ),
			'shop_thumbnail' => __( 'WooCommerce product thumbnail size', 'woocommerce_views' ),
			'shop_catalog' => __( 'WooCommerce shop catalog image size', 'woocommerce_views' ),
			'shop_single' => __( 'WooCommerce single product image size', 'woocommerce_views' ) 
		);
		$size_options = array();
		foreach ( $available_images_for_wcviews as $key => $size_data ) {
			if ( isset( $clean_image_name_array[ $key ] ) ) {
				$image_name_set = $clean_image_name_array[ $key ];
			} else {
				$image_name_set = '[' . __( 'Custom size', 'woocommerce_views' ) . ']-' . $key;
			}
			$size_options[ $key ] = $image_name_set 
				. ' (' 
				.  sprintf(
					'%1$s x %2$s px',
					$size_data[0],
					$size_data[1]
				)
				. ')';

		}
		
		$data = array(
			'name' => __( 'Product image', 'woocommerce_views' ),
			'label' => __( 'Product image', 'woocommerce_views' ),
			'attributes' => array(
				'display-options' => array(
					'label' => __( 'Display options', 'woocommerce_views '),
					'header' => __( 'Display options', 'woocommerce_views' ),
					'fields' => array(
						'information'	=> array(
							'type'		=> 'info',
							'content'	=> __( 'Displays the product image on single product and product listing pages. It will use the product featured image if set or output a placeholder if empty. This will also display variation images.', 'woocommerce_views' )
						),
						'size' => array(
							'label' => __( 'Select image size', 'woocommerce_views'),
							'type' => 'select',
							'options' => $size_options,
							'default' => 'shop_single',
							'description' => __( '' , 'woocommerce_views' ),
						),
						'output' => array(
							'label' => __( 'Select output format', 'woocommerce_views'),
							'type' => 'select',
							'options' => array(
								'woocommerce' => __( 'WooCommerce default', 'woocommerce_views' ),
								'img_tag' => __( 'Output image tag only', 'woocommerce_views' ),
								'raw' => __( 'Output image URL only', 'woocommerce_views' )
							),
							'default' => 'woocommerce',
							'description' => __( 'WooCommerce default will display an image tag with a popup effect on click.' , 'woocommerce_views' ),
						),
						'gallery_on_listings' => array(
							'label'			=> __( 'Product listing: show gallery', 'woocommerce_views'),
							'type'			=> 'radio',
							'options'		=> array(
								'no'		=> __( 'Do not show thumbnails gallery in product listings', 'woocommerce_views' ),
								'yes'		=> __( 'Show thumbnails gallery in product listings', 'woocommerce_views' ),
							),
							'default'		=> 'no',
							'description'	=> __( 'Only affects product listings and WooCommerce default output format', 'woocommerce_views' ),
						),
					),
				),
				'third-party-options' => array(
					'label' => __( 'Filter options', 'woocommerce_views '),
					'header' => __( 'Filter options', 'woocommerce_views' ),
					'fields' => array(
						'enable_third_party_filters' => array(
							'label'			=> __( 'Third party filters', 'woocommerce_views'),
							'type'			=> 'radio',
							'options'		=> array(
								'no'		=> __( 'Do not apply third party filters to the WooCommerce image', 'woocommerce_views' ),
								'yes'		=> __( 'Apply third party filters to the WooCommerce image', 'woocommerce_views' ),
							),
							'default'		=> 'no',
							'description'	=> __( 'Option to maybe apply third party filters hooked on "woocommerce_before_single_product_summary" and "woocommerce_before_shop_loop_item"', 'woocommerce_views' ),
						),
					),
				),
			),
		);

		return $data;
	}
	
	public function get_wpv_woo_display_tabs_data( $parameters = array(), $overrides = array() ) {
		$data = array(
			'name' => __( 'Product tabs - single product page', 'woocommerce_views' ),
			'label' => __( 'Product tabs - single product page', 'woocommerce_views' ),
			'attributes' => array(
				'display-options' => array(
					'label' => __( 'Display options', 'woocommerce_views' ),
					'header' => __( 'Display options', 'woocommerce_views' ),
					'fields' => array(
						'information'	=> array(
							'type'		=> 'info',
							'content'	=> __( 'Displays the WooCommerce product tabs. By default this will display product reviews and product attributes.', 'woocommerce_views' )
						),
						'disable_reviews_tab' => array(
							'label' => __( 'Reviews tab', 'woocommerce_views'),
							'type'			=> 'radio',
							'options'		=> array(
								'no'		=> __( 'Include the reviews tab', 'woocommerce_views' ),
								'yes'		=> __( 'Do not include the reviews tab', 'woocommerce_views' ),
							),
							'default'		=> 'no',
							'description'	=> __( 'You can use the wpv-woo-reviews shortcode to show the reviews separatedly', 'woocommerce_views' ),
						),
					),
				),
			),
		);

		return $data;
	}
	
	public function get_wpv_woo_productcategory_images_data( $parameters = array(), $overrides = array() ) {
		$available_images_for_wcviews = $this->get_images_sizes();
		$clean_image_name_array = array(
			'thumbnail' => __( 'WordPress thumbnail size', 'woocommerce_views' ),
			'medium' => __( 'WordPress medium image size', 'woocommerce_views' ),
			'large' => __( 'WordPress large image size', 'woocommerce_views' ),
			'shop_thumbnail' => __( 'WooCommerce product thumbnail size', 'woocommerce_views' ),
			'shop_catalog' => __( 'WooCommerce shop catalog image size', 'woocommerce_views' ),
			'shop_single' => __( 'WooCommerce single product image size', 'woocommerce_views' ) 
		);
		$size_options = array();
		foreach ( $available_images_for_wcviews as $key => $size_data ) {
			if ( isset( $clean_image_name_array[ $key ] ) ) {
				$image_name_set = $clean_image_name_array[ $key ];
			} else {
				$image_name_set = '[' . __( 'Custom size', 'woocommerce_views' ) . ']-' . $key;
			}
			$size_options[ $key ] = $image_name_set 
				. ' (' 
				.  sprintf(
					'%1$s x %2$s px',
					$size_data[0],
					$size_data[1]
				)
				. ')';

		}
		
		$data = array(
			'name' => __( 'Product category image', 'woocommerce_views' ),
			'label' => __( 'Product category image', 'woocommerce_views' ),
			'attributes' => array(
				'display-options' => array(
					'label' => __( 'Display options', 'woocommerce_views '),
					'header' => __( 'Display options', 'woocommerce_views' ),
					'fields' => array(
						'information'	=> array(
							'type'		=> 'info',
							'content'	=> __( 'Displays the product category image on product listing pages. It will use the product category image set on the backend. If it is not set, it will show no image.', 'woocommerce_views' )
						),
						'size' => array(
							'label' => __( 'Select image size', 'woocommerce_views'),
							'type' => 'select',
							'options' => $size_options,
							'default' => 'shop_single',
							'description' => __( '' , 'woocommerce_views' ),
						),
						'output' => array(
							'label' => __( 'Select output format', 'woocommerce_views'),
							'type' => 'select',
							'options' => array(
								'img_tag' => __( 'Output image tag only', 'woocommerce_views' ),
								'raw' => __( 'Output image URL only', 'woocommerce_views' )
							),
							'default' => 'raw',
							'description' => '',
						),
					),
				),
			),
		);

		return $data;
	}
	
	/**
	 * Bring GUI support to shortcodes we created for third party plugins compatibility:
	 * - Storefront Product Sharing
	 * - Storefront Product Pagination
	 *
	 * @since 2.7.6
	 */
	public function maybe_register_addon_shortcodes_dialog_groups() {
		
		$group_id = 'woocommerce-views-addons';
		$group_data = array(
			'name' => __( 'WooCommerce Addons', 'woocommerce_views' ),
			'fields' => array()
		);
		
		
		if ( is_callable( array( 'Storefront_Product_Sharing', 'instance' ) ) ) {
			$group_data['fields']['wpv-storefront-product-sharing'] = array(
				'name'		=> __( 'Storefront Product Sharing', 'woocommerce_views' ),
				'shortcode'	=> 'wpv-storefront-product-sharing',
				'callback'	=> ""
			);
		}
		
		if ( is_callable( array( 'Storefront_Product_Pagination', 'instance' ) ) ) {
			$group_data['fields']['wpv-storefront-product-pagination'] = array(
				'name'		=> __( 'Storefront Product Pagination', 'woocommerce_views' ),
				'shortcode'	=> 'wpv-storefront-product-pagination',
				'callback'	=> ""
			);
		}
		
		if ( ! empty( $group_data['fields'] ) ) {
			do_action( 'wpv_action_wpv_register_dialog_group', $group_id, $group_data );
		}
	}
	
}
