import classnames from 'classnames';
import ServerSideRenderMap from './render/server-side-render-map';
import MapInspectorControls from './inspector/inspector';
import './styles/editor.scss';

// Internal libraries
const {
	__,
	setLocaleData,
} = wp.i18n;

const {
	registerBlockType,
} = wp.blocks;

const {
	Notice,
	Dashicon,
} = wp.components;

const {
	toolset_map_block_strings: i18n,
} = window;

if ( i18n.locale ) {
	setLocaleData( i18n.locale, 'toolset-maps' );
}

const {
	forEach,
	clone,
} = window.lodash;

// Properties

const name = i18n.blockName;

const counters = {
	map: i18n.mapCounter,
	marker: i18n.markerCounter,
};

const markerAttributes = [
	'markerId', 'markerAddress', 'markerSource', 'currentVisitorLocationRenderTime', 'markerPostField', 'markerLat',
	'markerLon', 'markerTitle', 'popupContent', 'markerUseMapIcon', 'markerIcon', 'markerIconUseDifferentForHover',
	'markerIconHover',
];

// Functions

const buildMapShortcodeAttributes = ( attributes ) => {
	// Boolean attributes to be added to shortcode when they are truthy
	const booleanAttributesTrue = {
		mapMarkerClustering: 'cluster="on"',
		mapBackgroundColor: `background_color="${ attributes.mapBackgroundColor }"`,
		mapForceCenterSettingForSingleMarker: 'single_center="off"',
		mapMarkerSpiderfying: 'spiderfy="on"',
		mapStreetView: 'street_view="on" location="first"',
		mapMarkerIcon: 'marker_icon="' + attributes.mapMarkerIcon + '"',
		mapMarkerIconUseDifferentForHover: `marker_icon_hover="${ attributes.mapMarkerIconHover }"`,
	};

	// Boolean attributes to be added to shortcode when they are falsy
	const booleanAttributesFalse = {
		mapDraggable: 'draggable="off"',
		mapScrollable: 'scrollwheel="off"',
		mapDoubleClickZoom: 'double_click_zoom="off"',
		mapZoomAutomatic: 'fitbounds="off"',
		mapMarkerClusteringClickZoom: 'cluster_click_zoom="off"',
		mapTypeControl: 'map_type_control="off"',
		mapZoomControls: 'zoom_control="off"',
		mapStreetViewControl: 'street_view_control="off"',
	};

	// Attributes with default value (to be added when not at default value) of type string
	const valueAttributesString = {
		mapWidth: { default: i18n.mapDefaultSettings.map_width, output: `map_width="${ attributes.mapWidth }"` },
		mapHeight: { default: i18n.mapDefaultSettings.map_height, output: `map_height="${ attributes.mapHeight }"` },
		mapType: { default: i18n.mapDefaultSettings.map_type, output: `map_type="${ attributes.mapType }"` },
		mapStyle: { default: i18n.mapDefaultSettings.style_json, output: `style_json="${ attributes.mapStyle }"` },
	};

	// Attributes with default value of type number
	const valueAttributesNumber = {
		mapZoomLevelForMultipleMarkers: {
			default: i18n.mapDefaultSettings.general_zoom,
			output: `general_zoom="${ attributes.mapZoomLevelForMultipleMarkers }"`,
		},
		mapZoomLevelForSingleMarker: {
			default: i18n.mapDefaultSettings.single_zoom,
			output: `single_zoom="${ attributes.mapZoomLevelForSingleMarker }"`,
		},
		mapCenterLat: {
			default: i18n.mapDefaultSettings.general_center_lat,
			output: `general_center_lat="${ attributes.mapCenterLat }"`,
		},
		mapCenterLon: {
			default: i18n.mapDefaultSettings.general_center_lon,
			output: `general_center_lon="${ attributes.mapCenterLon }"`,
		},
		mapMarkerClusteringMinimalNumber: {
			default: i18n.mapDefaultSettings.cluster_min_size,
			output: `cluster_min_size="${ attributes.mapMarkerClusteringMinimalNumber }"`,
		},
		mapMarkerClusteringMinimalDistance: {
			default: i18n.mapDefaultSettings.cluster_grid_size,
			output: `cluster_grid_size="${ attributes.mapMarkerClusteringMinimalDistance }"`,
		},
		mapMarkerClusteringMaximalZoomLevel: {
			default: 14,
			output: `cluster_max_zoom="${ attributes.mapMarkerClusteringMaximalZoomLevel }"`,
		},
	};

	const shortcodeAttributes = [ `map_id="${ attributes.mapId }"` ];

	forEach( booleanAttributesTrue, function( output, attribute ) {
		if ( attributes[ attribute ] ) {
			shortcodeAttributes.push( output );
		}
	} );

	forEach( booleanAttributesFalse, function( output, attribute ) {
		if ( ! attributes[ attribute ] ) {
			shortcodeAttributes.push( output );
		}
	} );

	forEach( valueAttributesString, function( rules, attribute ) {
		if ( attributes[ attribute ] !== rules.default ) {
			shortcodeAttributes.push( rules.output );
		}
	} );

	forEach( valueAttributesNumber, function( rules, attribute ) {
		if ( Number( attributes[ attribute ] ) !== rules.default ) {
			shortcodeAttributes.push( rules.output );
		}
	} );

	return shortcodeAttributes;
};

const buildMapShortcode = ( attributes ) => {
	return buildShortcode(
		'wpv-map-render',
		buildMapShortcodeAttributes( attributes ),
		attributes.mapLoadingText
	);
};

/**
 * Very simple WP shortcode blueprint
 *
 * @param {string} shortcodeName Shortcode name
 * @param {array} attributes Shortcode attributes
 * @param {string} content Inside content
 * @return {string} Shortcode string
 */
const buildShortcode = ( shortcodeName, attributes, content ) => {
	return '[' + shortcodeName + ' ' + attributes.join( ' ' ) + ']' + content + '[/' + shortcodeName + ']';
};

const buildMarkerShortcodes = attributes => {
	let markerShortcodes = '';
	let markerSource = '';
	let markerTitle = '';

	forEach( attributes.markerId, function( markerId, key ) {
		// Marker source
		switch ( attributes.markerSource[ key ] ) {
			case 'address':
				if ( attributes.markerAddress[ key ] ) {
					markerSource = ' address="' + attributes.markerAddress[ key ] + '"';
				}
				break;
			case 'postmeta':
				if ( attributes.markerPostField[ key ] ) {
					markerSource = ' marker_field="' + attributes.markerPostField[ key ] + '"';
				}
				break;
			case 'browser_geolocation':
				markerSource = ' map_render="' + attributes.currentVisitorLocationRenderTime[ key ] +
					'" current_visitor_location="true"';
				break;
			case 'latlon':
				markerSource = ' lat="' + attributes.markerLat[ key ] + '" lon="' + attributes.markerLon[ key ] + '"';
		}

		// Marker title
		if ( attributes.markerTitle[ key ] ) {
			markerTitle = ' marker_title="' + attributes.markerTitle[ key ] + '"';
		}

		// Icons
		const markerIcon = attributes.markerUseMapIcon[ key ] ?
			'' :
			' marker_icon="' + attributes.markerIcon[ key ] + '"';
		const markerIconHover = ( ! attributes.markerUseMapIcon[ key ] && attributes.markerIconUseDifferentForHover[ key ] ) ?
			' marker_icon_hover="' + attributes.markerIconHover[ key ] + '"' :
			'';

		// Filtered popup content
		const filteredPopupContent = attributes.popupContent[ key ].replace( /(?:\r\n|\r|\n)/g, '<br>' );

		// Final marker shortcode
		markerShortcodes += '\n[wpv-map-marker map_id="' + attributes.mapId + '" marker_id="' +
			markerId + '"' + markerSource + markerTitle + markerIcon + markerIconHover + ']' +
			filteredPopupContent + '[/wpv-map-marker]';
	} );

	return markerShortcodes;
};

const renderShortcodes = ( props ) => {
	const attributes = parseAttributes( props.attributes );

	return buildMapShortcode( attributes ) + buildMarkerShortcodes( attributes );
};

/**
 * Perform AJAX call to save the new values - both of them.
 *
 * @since 1.7.1
 */
const updateCounters = () => {
	global.$.ajax( {
		type: 'POST',
		url: window.ajaxurl,
		data: {
			action: 'wpv_toolset_maps_addon_update_counters',
			map_counter: counters.map,
			marker_counter: counters.marker,
			wpnonce: window.wpv_addon_maps_dialogs_local.nonce,
		},
		dataType: 'json',
	} );
};

/**
 * Provides the next map Id, and optionally (by default) updates the backend.
 *
 * (Callers might want to skip updating the backend and call update themselves, for example to group updating map &
 * marker id together.)
 *
 * @since 1.7.1
 * @param {Boolean} updateBackend Should the backend be updated with new value immediately.
 * @return {string} Next Map ID.
 */
const getNextMapId = ( updateBackend = true ) => {
	counters.map++;

	if ( updateBackend ) {
		updateCounters();
	}

	return 'map-' + counters.map;
};

/**
 * Provides the next marker Id, and optionally (by default) updates the backend.
 *
 * @since 1.7.1
 * @param {Boolean} updateBackend Should the backend be updated with new value immediately.
 * @return {string} Next Marker ID.
 */
const getNextMarkerId = ( updateBackend = true ) => {
	counters.marker++;

	if ( updateBackend ) {
		updateCounters();
	}

	return 'marker-' + counters.marker;
};

const parseAttributes = ( attributes ) => {
	const parsedAttributes = clone( attributes );

	markerAttributes.forEach( ( value ) => {
		parsedAttributes[ value ] = JSON.parse( parsedAttributes[ value ] );
	} );

	return parsedAttributes;
};

// Block settings

const settings = {
	title: __( 'Map', 'toolset-maps' ),
	description: __( 'Add a map and markers to the editor.', 'toolset-maps' ),
	category: i18n.blockCategory,
	icon: (
		<span>
			<span className={ classnames( 'icon-toolset-map-logo' ) } />
		</span>
	),
	keywords: [
		__( 'Toolset', 'toolset-maps' ),
		__( 'map', 'toolset-maps' ),
		__( 'shortcode', 'toolset-maps' ),
	],

	edit: ( props ) => {
		const onChangeMapId = ( value ) => {
			props.setAttributes( { mapId: value } );
		};

		const onChangeMapWidth = value => {
			props.setAttributes( { mapWidth: value } );
		};

		const onChangeMapHeight = value => {
			props.setAttributes( { mapHeight: value } );
		};

		const onChangeMapZoomAutomatic = () => {
			props.setAttributes( { mapZoomAutomatic: ! props.attributes.mapZoomAutomatic } );
		};

		const onChangeMapZoomLevelForMultipleMarkers = value => {
			props.setAttributes( { mapZoomLevelForMultipleMarkers: value } );
		};

		const onChangeMapZoomLevelForSingleMarker = value => {
			props.setAttributes( { mapZoomLevelForSingleMarker: value } );
		};

		const onChangeMapCenterLat = value => {
			props.setAttributes( { mapCenterLat: value } );
		};

		const onChangeMapCenterLon = value => {
			props.setAttributes( { mapCenterLon: value } );
		};

		const onChangeMapForceCenterSettingForSingleMarker = () => {
			props.setAttributes(
				{ mapForceCenterSettingForSingleMarker: ! props.attributes.mapForceCenterSettingForSingleMarker }
			);
		};

		const onChangeMapMarkerClustering = () => {
			props.setAttributes( { mapMarkerClustering: ! props.attributes.mapMarkerClustering } );
		};

		const onChangeMapMarkerClusteringMinimalNumber = value => {
			props.setAttributes( { mapMarkerClusteringMinimalNumber: value } );
		};

		const onChangeMapMarkerClusteringMinimalDistance = value => {
			props.setAttributes( { mapMarkerClusteringMinimalDistance: value } );
		};

		const onChangeMapMarkerClusteringMaximalZoomLevel = value => {
			props.setAttributes( { mapMarkerClusteringMaximalZoomLevel: value } );
		};

		const onChangeMapMarkerClusteringClickZoom = () => {
			props.setAttributes( { mapMarkerClusteringClickZoom: ! props.attributes.mapMarkerClusteringClickZoom } );
		};

		const onChangeMapMarkerSpiderfying = () => {
			props.setAttributes( { mapMarkerSpiderfying: ! props.attributes.mapMarkerSpiderfying } );
		};

		const onChangeMapDraggable = () => {
			props.setAttributes( { mapDraggable: ! props.attributes.mapDraggable } );
		};

		const onChangeMapScrollable = () => {
			props.setAttributes( { mapScrollable: ! props.attributes.mapScrollable } );
		};

		const onChangeMapDoubleClickZoom = () => {
			props.setAttributes( { mapDoubleClickZoom: ! props.attributes.mapDoubleClickZoom } );
		};

		const onChangeMapType = ( value ) => {
			props.setAttributes( { mapType: value } );
		};

		const onChangeMapTypeControl = () => {
			props.setAttributes( { mapTypeControl: ! props.attributes.mapTypeControl } );
		};

		const onChangeMapZoomControls = () => {
			props.setAttributes( { mapZoomControls: ! props.attributes.mapZoomControls } );
		};

		const onChangeMapStreetViewControl = () => {
			props.setAttributes( { mapStreetViewControl: ! props.attributes.mapStreetViewControl } );
		};

		const onChangeMapBackgroundColor = value => {
			props.setAttributes( { mapBackgroundColor: value } );
		};

		const onChangeMapLoadingText = ( value ) => {
			props.setAttributes( { mapLoadingText: value } );
		};

		const onChangeMapMarkerIcon = value => {
			props.setAttributes( { mapMarkerIcon: value } );
		};

		const onChangeMapMarkerIconUseDifferentForHover = () => {
			props.setAttributes(
				{ mapMarkerIconUseDifferentForHover: ! props.attributes.mapMarkerIconUseDifferentForHover }
			);
		};

		const onChangeMapMarkerIconHover = value => {
			props.setAttributes( { mapMarkerIconHover: value } );
		};

		const onChangeMapStyle = value => {
			props.setAttributes( { mapStyle: value } );
		};

		const onChangeMapStreetView = () => {
			props.setAttributes( { mapStreetView: ! props.attributes.mapStreetView } );
		};

		const updateAttributeInArray = ( attributeName, value, key ) => {
			const attribute = JSON.parse( props.attributes[ attributeName ] );
			const attributeObject = {};

			attribute[ key ] = value;
			attributeObject[ attributeName ] = JSON.stringify( attribute );

			props.setAttributes( attributeObject );
		};

		const toggleAttributeInArray = ( attributeName, key ) => {
			const attribute = JSON.parse( props.attributes[ attributeName ] );
			const attributeObject = {};

			attribute[ key ] = ! attribute[ key ];
			attributeObject[ attributeName ] = JSON.stringify( attribute );

			props.setAttributes( attributeObject );
		};

		const onChangeMarkerId = ( value, key ) => {
			updateAttributeInArray( 'markerId', value, key );
		};

		const onChangeMarkerAddress = ( value, key ) => {
			if ( value ) {
				updateAttributeInArray( 'markerAddress', value.label, key );
			}
		};

		const onChangeMarkerPostField = ( value, key ) => {
			updateAttributeInArray( 'markerPostField', value, key );
		};

		const onChangeLatitude = ( value, key ) => {
			updateAttributeInArray( 'markerLat', value, key );
		};

		const onChangeLongitude = ( value, key ) => {
			updateAttributeInArray( 'markerLon', value, key );
		};

		const onChangeMarkerTitle = ( value, key ) => {
			updateAttributeInArray( 'markerTitle', value, key );
		};

		const onChangeMarkerSource = ( value, key ) => {
			updateAttributeInArray( 'markerSource', value, key );
		};

		const onChangeCurrentVisitorLocationRenderTime = ( value, key ) => {
			updateAttributeInArray( 'currentVisitorLocationRenderTime', value, key );
		};

		const onChangePopupContent = ( value, key ) => {
			updateAttributeInArray( 'popupContent', value, key );
		};

		const onChangeMarkerUseMapIcon = ( key ) => {
			toggleAttributeInArray( 'markerUseMapIcon', key );
		};

		const onChangeMarkerIcon = ( value, key ) => {
			updateAttributeInArray( 'markerIcon', value, key );
		};

		const onChangeMarkerIconUseDifferentForHover = ( key ) => {
			toggleAttributeInArray( 'markerIconUseDifferentForHover', key );
		};

		const onChangeMarkerIconHover = ( value, key ) => {
			updateAttributeInArray( 'markerIconHover', value, key );
		};

		const onAddAnotherMarker = () => {
			const parsedAttributes = parseAttributes( props.attributes );

			parsedAttributes.markerId.push( getNextMarkerId() );
			parsedAttributes.markerAddress.push( '' );
			parsedAttributes.markerSource.push( 'address' );
			parsedAttributes.currentVisitorLocationRenderTime.push( 'immediate' );
			parsedAttributes.markerLat.push( '' );
			parsedAttributes.markerLon.push( '' );
			parsedAttributes.markerTitle.push( '' );
			parsedAttributes.popupContent.push( '' );
			parsedAttributes.markerPostField.push( '' );
			parsedAttributes.markerUseMapIcon.push( true );
			parsedAttributes.markerIcon.push( '' );
			parsedAttributes.markerIconUseDifferentForHover.push( false );
			parsedAttributes.markerIconHover.push( '' );

			updateMarkers( parsedAttributes );
		};

		const onRemoveMarker = ( key ) => {
			const parsedAttributes = parseAttributes( props.attributes );

			markerAttributes.forEach( ( attribute ) => {
				parsedAttributes[ attribute ].splice( key, 1 );
			} );

			updateMarkers( parsedAttributes );
		};

		const updateMarkers = ( parsedAttributes ) => {
			const attributesToSet = {};

			markerAttributes.forEach( ( attribute ) => {
				attributesToSet[ attribute ] = JSON.stringify( parsedAttributes[ attribute ] );
			} );

			props.setAttributes( attributesToSet );
		};

		/**
		 * Adds the rendered shortcodes to attributes, so server side can turn them directly to HTML
		 * @return {Object} Attributes
		 */
		const attributesPlusShortcodes = () => {
			const attributes = props.attributes;

			attributes.shortcodes = renderShortcodes( props );

			return attributes;
		};

		/**
		 * Checks and sets mapId and markerId if they are not yet set.
		 * @return {Object} Attributes
		 */
		const getAttributes = () => {
			if ( ! props.attributes.mapId ) {
				const markerId = JSON.parse( props.attributes.markerId );
				markerId[ 0 ] = getNextMarkerId( false );

				props.setAttributes( {
					mapId: getNextMapId( false ),
					markerId: JSON.stringify( markerId ),
				} );

				updateCounters();
			}

			return props.attributes;
		};

		return [
			!! (
				(
					props.focus ||
					props.isSelected
				) &&
				i18n.apiKey
			) && (
				<MapInspectorControls
					attributes={ parseAttributes( getAttributes() ) }
					onChangeMapId={ onChangeMapId }
					onChangeMapWidth={ onChangeMapWidth }
					onChangeMapHeight={ onChangeMapHeight }
					onChangeMapZoomAutomatic={ onChangeMapZoomAutomatic }
					onChangeMapZoomLevelForMultipleMarkers={ onChangeMapZoomLevelForMultipleMarkers }
					onChangeMapZoomLevelForSingleMarker={ onChangeMapZoomLevelForSingleMarker }
					onChangeMapCenterLat={ onChangeMapCenterLat }
					onChangeMapCenterLon={ onChangeMapCenterLon }
					onChangeMapForceCenterSettingForSingleMarker={ onChangeMapForceCenterSettingForSingleMarker }
					onChangeMapMarkerClustering={ onChangeMapMarkerClustering }
					onChangeMapMarkerClusteringMinimalNumber={ onChangeMapMarkerClusteringMinimalNumber }
					onChangeMapMarkerClusteringMinimalDistance={ onChangeMapMarkerClusteringMinimalDistance }
					onChangeMapMarkerClusteringMaximalZoomLevel={ onChangeMapMarkerClusteringMaximalZoomLevel }
					onChangeMapMarkerClusteringClickZoom={ onChangeMapMarkerClusteringClickZoom }
					onChangeMapMarkerSpiderfying={ onChangeMapMarkerSpiderfying }
					onChangeMapDraggable={ onChangeMapDraggable }
					onChangeMapScrollable={ onChangeMapScrollable }
					onChangeMapDoubleClickZoom={ onChangeMapDoubleClickZoom }
					onChangeMapType={ onChangeMapType }
					onChangeMapTypeControl={ onChangeMapTypeControl }
					onChangeMapZoomControls={ onChangeMapZoomControls }
					onChangeMapStreetViewControl={ onChangeMapStreetViewControl }
					onChangeMapBackgroundColor={ onChangeMapBackgroundColor }
					onChangeMapLoadingText={ onChangeMapLoadingText }
					onChangeMapMarkerIcon={ onChangeMapMarkerIcon }
					onChangeMapMarkerIconUseDifferentForHover={ onChangeMapMarkerIconUseDifferentForHover }
					onChangeMapMarkerIconHover={ onChangeMapMarkerIconHover }
					onChangeMapStyle={ onChangeMapStyle }
					onChangeMapStreetView={ onChangeMapStreetView }
					onChangeMarkerId={ onChangeMarkerId }
					onChangeMarkerAddress={ onChangeMarkerAddress }
					onChangeMarkerPostField={ onChangeMarkerPostField }
					onChangeMarkerSource={ onChangeMarkerSource }
					onChangeCurrentVisitorLocationRenderTime={ onChangeCurrentVisitorLocationRenderTime }
					onAddAnotherMarker={ onAddAnotherMarker }
					onRemoveMarker={ onRemoveMarker }
					onChangeLatitude={ onChangeLatitude }
					onChangeLongitude={ onChangeLongitude }
					onChangeMarkerTitle={ onChangeMarkerTitle }
					onChangePopupContent={ onChangePopupContent }
					onChangeMarkerUseMapIcon={ onChangeMarkerUseMapIcon }
					onChangeMarkerIcon={ onChangeMarkerIcon }
					onChangeMarkerIconUseDifferentForHover={ onChangeMarkerIconUseDifferentForHover }
					onChangeMarkerIconHover={ onChangeMarkerIconHover }
				/>
			),
			!! ( i18n.apiKey ) && (
				<ServerSideRenderMap
					key="toolset-gutenberg-map-block-render-inspector"
					block={ i18n.blockName }
					attributes={ attributesPlusShortcodes() }
				/>
			),
			! ( i18n.apiKey ) && (
				<Notice
					status="error"
					isDismissible={ false }
					actions={ [
						{
							label: __( 'Enter an API key on the Toolset Settings page.', 'toolset-maps' ),
							url: i18n.settingsLink,
						},
					] }
				>
					<Dashicon icon="warning" />
					{ __( ' You need an API key to use Toolset Map block.', 'toolset-maps' ) }
				</Notice>
			),
		];
	},

	save: () => {},

	supports: {
		html: false,
	},
};

registerBlockType( name, settings );
