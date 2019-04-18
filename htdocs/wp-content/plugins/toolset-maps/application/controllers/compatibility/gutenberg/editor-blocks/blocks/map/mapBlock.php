<?php
namespace OTGS\Toolset\Maps\Controller\Compatibility;

use Toolset_Addon_Maps_Common;

class MapBlock extends \Toolset_Gutenberg_Block {

	const BLOCK_NAME = 'toolset/map';

	/**
	 * Block initialization.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'register_block_editor_assets' ) );
		add_action( 'init', array( $this, 'register_block_type' ) );
	}

	/**
	 * @return array Key-name list of all postmeta fields
	 */
	protected function get_postmeta_fields_key_name_list() {
		$all_types_fields = get_option( 'wpcf-fields', array() );
		$postmeta_keys = apply_filters( 'wpv_filter_wpv_get_postmeta_keys', array() );
		$postmeta_fields = array();

		foreach ( $postmeta_keys as $postmeta_key ) {
			foreach ( $all_types_fields as $types_field ) {
				if ( $types_field['meta_key'] === $postmeta_key ) {
					$postmeta_fields[ $postmeta_key ] = $types_field['name'];
				}
			}
		}

		return $postmeta_fields;
	}

	/**
	 * @return string First key of all available postmeta keys. '' if none available. Useful for defaults.
	 */
	protected function get_first_postmeta_field_key() {
		$postmeta_keys = apply_filters( 'wpv_filter_wpv_get_postmeta_keys', array() );

		return (string) reset( $postmeta_keys );
	}

	/**
	 * Block editor asset registration.
	 *
	 * @return void
	 */
	public function register_block_editor_assets() {
		$editor_script_dependencies = array( 'wp-editor', 'lodash', 'jquery', 'views-addon-maps-script' );
		$api_used = apply_filters( 'toolset_maps_get_api_used', '' );

		if ( Toolset_Addon_Maps_Common::API_GOOGLE === $api_used ) {
			array_push(
				$editor_script_dependencies,
				'marker-clusterer-script',
				'overlapping-marker-spiderfier'
			);
		};

		$this->toolset_assets_manager->register_script(
			'toolset-map-block-js',
			TOOLSET_ADDON_MAPS_URL . MapsEditorBlocks::TOOLSET_MAPS_BLOCKS_ASSETS_RELATIVE_PATH . '/js/map.block.editor.js',
			$editor_script_dependencies,
			TOOLSET_ADDON_MAPS_VERSION
		);

		if ( function_exists( 'wp_get_jed_locale_data' ) ) {
			$locale = wp_get_jed_locale_data( 'toolset-maps' );
		} elseif ( function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale = gutenberg_get_jed_locale_data( 'toolset-maps' );
		} else {
			$locale = array(
				array(
					'domain' => 'toolset-maps',
					'lang' => 'en_US',
				),
			);
		}

		wp_localize_script(
			'toolset-map-block-js',
			'toolset_map_block_strings',
			array(
				'blockName' => self::BLOCK_NAME,
				'blockCategory' => \Toolset_Blocks::TOOLSET_GUTENBERG_BLOCKS_CATEGORY_SLUG,
				'mapCounter' => $this->get_map_counter(),
				'markerCounter' => $this->get_marker_counter(),
				'locale' => $locale,
				'api' => apply_filters( 'toolset_maps_get_api_used', '' ),
				'apiKey' => $this->is_the_right_api_key_entered(),
				'settingsLink' => Toolset_Addon_Maps_Common::get_settings_link(),
				'themeColors' => get_theme_support( 'editor-color-palette' ),
				'mapDefaultSettings' => Toolset_Addon_Maps_Common::$map_defaults,
				'mapStyleOptions' => Toolset_Addon_Maps_Common::get_style_options(),
				'markerOptions' => apply_filters( 'toolset_maps_views_get_marker_options', array() ),
				'postmetaFields' => $this->get_postmeta_fields_key_name_list(),
				'isFrontendServerOverHttps' => $this->is_frontend_served_over_https(),
			)
		);

		$this->toolset_assets_manager->register_style(
			'toolset-map-block-editor-css',
			TOOLSET_ADDON_MAPS_URL . MapsEditorBlocks::TOOLSET_MAPS_BLOCKS_ASSETS_RELATIVE_PATH . '/css/map.block.editor.css',
			array(),
			TOOLSET_ADDON_MAPS_VERSION
		);
	}

	/**
	 * Server side block registration.
	 *
	 * @return void
	 */
	public function register_block_type() {
		register_block_type(
			self::BLOCK_NAME,
			array(
				'attributes' => array(
					'mapId' => array(
						'type' => 'string',
						'default' => '',
					),
					'mapWidth' => array(
						'type' => 'string',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['map_width'],
					),
					'mapHeight' => array(
						'type' => 'string',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['map_height'],
					),
					'mapZoomAutomatic' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapZoomLevelForMultipleMarkers' => array(
						'type' => 'integer',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['general_zoom'],
					),
					'mapZoomLevelForSingleMarker' => array(
						'type' => 'integer',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['single_zoom'],
					),
					'mapCenterLat' => array(
						'type' => 'float',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['general_center_lat'],
					),
					'mapCenterLon' => array(
						'type' => 'float',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['general_center_lon'],
					),
					'mapForceCenterSettingForSingleMarker' => array(
						'type' => 'boolean',
						'default' => false,
					),
					'mapMarkerClustering' => array(
						'type' => 'boolean',
						'default' => false,
					),
					'mapMarkerClusteringMinimalNumber' => array(
						'type' => 'integer',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['cluster_min_size'],
					),
					'mapMarkerClusteringMinimalDistance' => array(
						'type' => 'integer',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['cluster_grid_size'],
					),
					'mapMarkerClusteringMaximalZoomLevel' => array(
						'type' => 'integer',
						'default' => 14,
					),
					'mapMarkerClusteringClickZoom' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapMarkerSpiderfying' => array(
						'type' => 'boolean',
						'default' => false,
					),
					'mapDraggable' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapScrollable' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapDoubleClickZoom' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapType' => array(
						'type' => 'string',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['map_type'],
					),
					'mapTypeControl' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapZoomControls' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapStreetViewControl' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapBackgroundColor' => array(
						'type' => 'string',
						'default' => '',
					),
					'mapStyle' => array(
						'type' => 'string',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['style_json'],
					),
					'mapLoadingText' => array(
						'type' => 'string',
						'default' => '',
					),
					'mapMarkerIcon' => array(
						'type' => 'string',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['marker_icon'],
					),
					'mapMarkerIconUseDifferentForHover' => array(
						'type' => 'boolean',
						'default' => false,
					),
					'mapMarkerIconHover' => array(
						'type' => 'string',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['marker_icon_hover'],
					),
					'mapStreetView' => array(
						'type' => 'boolean',
						'default' => false,
					),
					'shortcodes' => array(
						'type' => 'string',
					),
					// There is array type, but then Gutenberg goes crazy validating. Instead, we have to serialize
					// arrays ourselves, and everything needs to be of type string...
					'markerId' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerAddress' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerSource' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( 'address' ) ),
					),
					'currentVisitorLocationRenderTime' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( 'immediate' ) ),
					),
					'markerPostField' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( $this->get_first_postmeta_field_key() ) ),
					),
					'markerLat' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerLon' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerTitle' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'popupContent' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerUseMapIcon' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( true ) ),
					),
					'markerIcon' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerIconUseDifferentForHover' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( false ) ),
					),
					'markerIconHover' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
				),
				'editor_script' => 'toolset-map-block-js',
				'editor_style' => 'toolset-map-block-editor-css',
				'render_callback' => array( $this, 'render_preview' ),
			)
		);
	}

	/**
	 * Renders map & marker shortcodes to HTML.
	 *
	 * @param array $attributes Contains attributes + added shortcodes which are the only thing used.
	 * @param string $content Previous version of rendered HTML. Unused.
	 *
	 * @return string
	 */
	public function render_preview( array $attributes, $content ) {
		// If no shortcodes are provided (some edge cases), do nothing, better than to crash or emit warnings.
		if ( ! array_key_exists( 'shortcodes', $attributes ) ) {
			return '';
		};

		// If Views are not active, we need to init shortcode rendering methods...
		if ( ! $this->views_active->is_met() ) {
			require_once TOOLSET_ADDON_MAPS_PATH . '/includes/toolset-maps-views.class.php';
			$views = new \Toolset_Addon_Maps_Views();
			$views->init();
		}

		// First shortcode in the array is the the map shortcode, all others are markers
		$shortcodes = explode("\n", $attributes['shortcodes'] );
		$map_shortcode = array_shift( $shortcodes );

		$output = do_shortcode( $map_shortcode );

		foreach ( $shortcodes as $marker_shortcode ) {
			$output .= do_shortcode( $marker_shortcode );
		}

		return $output;
	}

	/**
	 * @param string $option
	 *
	 * @return mixed
	 */
	private function get_saved_option( $option ) {
		$saved_options = apply_filters( 'toolset_filter_toolset_maps_get_options', array() );

		return $saved_options[$option];
	}

	/**
	 * @return int
	 */
	private function get_map_counter() {
		return $this->get_saved_option( 'map_counter' );
	}

	/**
	 * @return int
	 */
	private function get_marker_counter() {
		return $this->get_saved_option( 'marker_counter' );
	}

	/**
	 * Multi-API aware check for API keys.
	 * @return bool
	 */
	private function is_the_right_api_key_entered() {
		$api_used = apply_filters( 'toolset_maps_get_api_used', '' );

		if ( Toolset_Addon_Maps_Common::API_GOOGLE === $api_used ) {
			$key = apply_filters( 'toolset_filter_toolset_maps_get_api_key', '' );
		} else {
			$key = apply_filters( 'toolset_filter_toolset_maps_get_azure_api_key', '' );
		}
		return !empty( $key );
	}

	/**
	 * Under assumption that site settings are not wrong, answers if frontend is served over https
	 *
	 * @return bool
	 */
	private function is_frontend_served_over_https(){
		return ( wp_parse_url( get_home_url(), PHP_URL_SCHEME ) === 'https' );
	}
}
