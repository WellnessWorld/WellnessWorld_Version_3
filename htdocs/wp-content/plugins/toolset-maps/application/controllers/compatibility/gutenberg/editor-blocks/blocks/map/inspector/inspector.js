import classnames from 'classnames';
import AddressAutocomplete from './address-autocomplete';
import MarkerRadios from './marker-radios';

const { InspectorControls } = wp.editor;
const { Component } = wp.element;
const { __ } = wp.i18n;
const { toolset_map_block_strings: i18n } = window;

const {
	PanelBody,
	PanelRow,
	TextControl,
	RadioControl,
	TextareaControl,
	ToggleControl,
	ColorPalette,
	ColorIndicator,
	Disabled,
	Button,
	SelectControl,
} = wp.components;

const {
	forEach,
	map,
} = window.lodash;

/**
 * Inspector component for Map block. Quite big, but that's how many options we have for that block...
 */
export default class MapInspectorControls extends Component {
	render() {
		const {
			attributes,
			onChangeMapId,
			onChangeMapWidth,
			onChangeMapHeight,
			onChangeMapZoomAutomatic,
			onChangeMapZoomLevelForMultipleMarkers,
			onChangeMapZoomLevelForSingleMarker,
			onChangeMapCenterLat,
			onChangeMapCenterLon,
			onChangeMapForceCenterSettingForSingleMarker,
			onChangeMapMarkerClustering,
			onChangeMapMarkerClusteringMinimalNumber,
			onChangeMapMarkerClusteringMinimalDistance,
			onChangeMapMarkerClusteringMaximalZoomLevel,
			onChangeMapMarkerClusteringClickZoom,
			onChangeMapMarkerSpiderfying,
			onChangeMapDraggable,
			onChangeMapScrollable,
			onChangeMapDoubleClickZoom,
			onChangeMapType,
			onChangeMapTypeControl,
			onChangeMapZoomControls,
			onChangeMapStreetViewControl,
			onChangeMapBackgroundColor,
			onChangeMapStyle,
			onChangeMapLoadingText,
			onChangeMapMarkerIcon,
			onChangeMapMarkerIconUseDifferentForHover,
			onChangeMapMarkerIconHover,
			onChangeMapStreetView,
			onChangeMarkerId,
			onChangeMarkerAddress,
			onChangeMarkerPostField,
			onChangeMarkerSource,
			onChangeCurrentVisitorLocationRenderTime,
			onAddAnotherMarker,
			onRemoveMarker,
			onChangeLatitude,
			onChangeLongitude,
			onChangeMarkerTitle,
			onChangePopupContent,
			onChangeMarkerUseMapIcon,
			onChangeMarkerIcon,
			onChangeMarkerIconUseDifferentForHover,
			onChangeMarkerIconHover,
		} = this.props;

		const googleAPI = ( i18n.api === 'google' );
		const draggableOffAndGoogleAPI = ( ! attributes.mapDraggable && googleAPI );
		const oneMarker = ( attributes.markerId.length === 1 );

		let dependentMapInteractionOptions = [
			<PanelRow
				key={ 'map-interaction-option-scroll' }
			>
				<ToggleControl
					label={ __( 'Scroll inside the map to zoom', 'toolset-maps' ) }
					checked={ !! attributes.mapScrollable }
					onChange={ onChangeMapScrollable }
				/>
			</PanelRow>,
			<PanelRow
				key={ 'map-interaction-option-double-click-zoom' }
			>
				<ToggleControl
					label={ __( 'Double-click to zoom', 'toolset-maps' ) }
					checked={ !! attributes.mapDoubleClickZoom }
					onChange={ onChangeMapDoubleClickZoom }
				/>
			</PanelRow>,
		];

		if ( draggableOffAndGoogleAPI ) {
			dependentMapInteractionOptions = [
				<Disabled
					key={ 'map-interaction-options-disabled' }
				>
					{ dependentMapInteractionOptions }
				</Disabled>,
			];
		}

		const mapInteractionOptions = [
			<PanelRow
				key={ 'map-interaction-option-draggable' }
			>
				<ToggleControl
					label={ __( 'Move the map by dragging', 'toolset-maps' ) }
					help={ draggableOffAndGoogleAPI ?
						__(
							'When this is disabled with Google API, other map interaction options don\'t work either.',
							'toolset-maps'
						) :
						''
					}
					checked={ !! attributes.mapDraggable }
					onChange={ onChangeMapDraggable }
				/>
			</PanelRow>,
		];
		mapInteractionOptions.push( dependentMapInteractionOptions );

		const markers = [];
		const lastMarkerKey = attributes.markerId.length - 1;
		let openMarkerPanel = false;

		/**
		 * Maps a simple key-value object to array of objects formatted for React radios, select, etc.
		 *
		 * @param {Object} keyValue Simple key-value object.
		 * @return {array} Format: [ { label: value, value: key }, ... ]
		 */
		const keyValue2Options = keyValue => {
			return map( keyValue, ( value, key ) => {
				return { label: value, value: key };
			} );
		};

		const mapStyleOptions = keyValue2Options( i18n.mapStyleOptions );
		const markerOptions = keyValue2Options( i18n.markerOptions );
		const postmetaFields = keyValue2Options( i18n.postmetaFields );

		const markerSourceOptions = [
			{ value: 'address', label: __( 'Address', 'toolset-maps' ) },
			//{ value: 'postmeta', label: __( 'A post field storing an address', 'toolset-maps' ) },
			{
				value: 'latlon',
				label: __( 'GPS Coordinates', 'toolset-maps' ),
			},
		];
		if ( i18n.isFrontendServerOverHttps ) {
			markerSourceOptions.push( {
				value: 'browser_geolocation',
				label: __( 'Location of the current visitor', 'toolset-maps' ),
			} );
		}

		forEach( attributes.markerId, function( markerId, key ) {
			openMarkerPanel = ( key === lastMarkerKey );

			markers.push(
				<PanelBody
					title={ __( 'Marker' + ' ' + attributes.markerId[ key ], 'toolset-maps' ) }
					initialOpen={ openMarkerPanel }
				>
					<PanelRow>
						<TextControl
							label={ __( 'Marker ID', 'toolset-maps' ) }
							value={ attributes.markerId[ key ] }
							onChange={ ( value ) => onChangeMarkerId( value, key ) }
						/>
					</PanelRow>
					<PanelRow>
						<RadioControl
							label={ __( 'Source for the marker', 'toolset-maps' ) }
							selected={ attributes.markerSource[ key ] ? attributes.markerSource[ key ] : 'address' }
							onChange={ ( value ) => onChangeMarkerSource( value, key ) }
							options={ markerSourceOptions }
						/>
					</PanelRow>
					{ attributes.markerSource[ key ] === 'address' ?
						<PanelRow>
							<AddressAutocomplete
								markerAddress={ attributes.markerAddress }
								addressKey={ key }
								onChangeMarkerAddress={ onChangeMarkerAddress }
							/>
						</PanelRow> :
						null
					}
					{ attributes.markerSource[ key ] === 'postmeta' ?
						<PanelRow>
							<SelectControl
								label={ __( 'Post field name', 'toolset-maps' ) }
								value={ attributes.markerPostField[ key ] }
								options={ postmetaFields }
								onChange={ ( value ) => onChangeMarkerPostField( value, key ) }
							/>
						</PanelRow> :
						null
					}
					{ attributes.markerSource[ key ] === 'latlon' ?
						<PanelRow>
							<TextControl
								label={ __( 'Latitude', 'toolset-maps' ) }
								onChange={ ( value ) => onChangeLatitude( value, key ) }
								value={ attributes.markerLat[ key ] }
							/>
						</PanelRow> :
						null
					}
					{ attributes.markerSource[ key ] === 'latlon' ?
						<PanelRow>
							<TextControl
								label={ __( 'Longitude', 'toolset-maps' ) }
								onChange={ ( value ) => onChangeLongitude( value, key ) }
								value={ attributes.markerLon[ key ] }
							/>
						</PanelRow>	:
						null
					}
					{ attributes.markerSource[ key ] === 'browser_geolocation' ?
						<PanelRow>
							<RadioControl
								label={ __( 'Geolocation options', 'toolset-maps' ) }
								selected={
									attributes.currentVisitorLocationRenderTime[ key ] ?
										attributes.currentVisitorLocationRenderTime[ key ] :
										'immediate'
								}
								onChange={ ( value ) => onChangeCurrentVisitorLocationRenderTime( value, key ) }
								options={ [
									{
										value: 'immediate',
										label: __(
											'Render the map immediately and then add visitor location',
											'toolset-maps'
										),
									},
									{
										value: 'wait',
										label: __(
											'Wait until visitors share their location and only then render the map',
											'toolset-maps'
										),
									},
								] }
							/>
						</PanelRow> :
						null
					}
					<PanelRow>
						<TextControl
							label={ __( 'Hint content (displayed on hover)', 'toolset-maps' ) }
							value={ attributes.markerTitle[ key ] }
							onChange={ ( value ) => onChangeMarkerTitle( value, key ) }
						/>
					</PanelRow>
					<PanelRow>
						<TextareaControl
							label={ __( 'Pop-up content', 'toolset-maps' ) }
							value={ attributes.popupContent[ key ] }
							onChange={ ( value ) => onChangePopupContent( value, key ) }
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Use the map\'s icon settings', 'toolset-maps' ) }
							checked={ !! attributes.markerUseMapIcon[ key ] }
							onChange={ () => onChangeMarkerUseMapIcon( key ) }
						/>
					</PanelRow>
					{ ! attributes.markerUseMapIcon[ key ] &&
						<PanelRow>
							<MarkerRadios
								label={ __( 'Marker icon', 'toolset-maps' ) }
								options={ markerOptions }
								selected={ attributes.markerIcon[ key ] }
								onChange={ ( value ) => onChangeMarkerIcon( value, key ) }
								instanceId={ 'marker-icon' }
							/>
						</PanelRow>
					}
					{ ! attributes.markerUseMapIcon[ key ] &&
						<PanelRow>
							<ToggleControl
								label={ __( 'Use a different marker icon on hover', 'toolset-maps' ) }
								checked={ !! attributes.markerIconUseDifferentForHover[ key ] }
								onChange={ () => onChangeMarkerIconUseDifferentForHover( key ) }
							/>
						</PanelRow>
					}
					{ ! attributes.markerUseMapIcon[ key ] && !! attributes.markerIconUseDifferentForHover[ key ] &&
						<PanelRow>
							<MarkerRadios
								label={ __( 'Icon when hovering this marker', 'toolset-maps' ) }
								options={ markerOptions }
								selected={ attributes.markerIconHover[ key ] }
								onChange={ ( value ) => onChangeMarkerIconHover( value, key ) }
								instanceId={ 'marker-icon-hover' }
							/>
						</PanelRow>
					}
					<Button
						isLink
						id={ 'remove-marker-' + key }
						className={ 'remove-marker-button' }
						onClick={ ( e ) => onRemoveMarker( key, e ) }
					>
						{ __( 'Remove this marker', 'toolset-maps' ) }
					</Button>
				</PanelBody>
			);
		} );

		return (
			<InspectorControls>
				<div className={ classnames( 'wp-block-toolset-map-inspector' ) }>
					<PanelBody title={ __( 'Map', 'toolset-maps' ) + ' ' + attributes.mapId }>
						<PanelRow>
							<TextControl
								label={ __( 'Map ID', 'toolset-maps' ) }
								value={ attributes.mapId }
								onChange={ onChangeMapId }
							/>
						</PanelRow>
						<PanelRow>
							<TextControl
								label={ __( 'Width', 'toolset-maps' ) }
								value={ attributes.mapWidth }
								onChange={ onChangeMapWidth }
								help={ __(
									'Use percentages or units (defaults to pixels).',
									'toolset-maps'
								) }
							/>
						</PanelRow>
						<PanelRow>
							<TextControl
								label={ __( 'Height', 'toolset-maps' ) }
								value={ attributes.mapHeight }
								onChange={ onChangeMapHeight }
								help={ __(
									'Use percentages or units (defaults to pixels).',
									'toolset-maps'
								) }
							/>
						</PanelRow>
						<PanelRow>
							<ToggleControl
								label={ __( 'Adjust zoom and center to show all markers at once', 'toolset-maps' ) }
								checked={ !! attributes.mapZoomAutomatic }
								onChange={ onChangeMapZoomAutomatic }
							/>
						</PanelRow>
						{ ! attributes.mapZoomAutomatic &&
							<PanelRow>
								<TextControl
									type={ 'number' }
									label={
										__( 'Zoom level for multiple markers (0-18)', 'toolset-maps' )
									}
									value={ attributes.mapZoomLevelForMultipleMarkers }
									onChange={ onChangeMapZoomLevelForMultipleMarkers }
								/>
							</PanelRow>
						}
						<PanelRow>
							<TextControl
								type={ 'number' }
								label={ __( 'Zoom level for one marker (0-18)', 'toolset-maps' ) }
								value={ attributes.mapZoomLevelForSingleMarker }
								onChange={ onChangeMapZoomLevelForSingleMarker }
							/>
						</PanelRow>
						{ ! attributes.mapZoomAutomatic &&
							<PanelRow>
								<TextControl
									type={ 'number' }
									label={ __( 'Center latitude', 'toolset-maps' ) }
									value={ attributes.mapCenterLat }
									onChange={ onChangeMapCenterLat }
								/>
							</PanelRow>
						}
						{ ! attributes.mapZoomAutomatic &&
							<PanelRow>
								<TextControl
									type={ 'number' }
									label={ __( 'Center longitude', 'toolset-maps' ) }
									value={ attributes.mapCenterLon }
									onChange={ onChangeMapCenterLon }
								/>
							</PanelRow>
						}
						{ ! attributes.mapZoomAutomatic && oneMarker &&
							<PanelRow>
								<ToggleControl
									label={
										__(
											'Force the map center setting even when only one marker is used',
											'toolset-maps'
										)
									}
									checked={ !! attributes.mapForceCenterSettingForSingleMarker }
									onChange={ onChangeMapForceCenterSettingForSingleMarker }
								/>
							</PanelRow>
						}
						<PanelRow>
							<ToggleControl
								label={ __( 'Cluster markers', 'toolset-maps' ) }
								checked={ !! attributes.mapMarkerClustering }
								onChange={ onChangeMapMarkerClustering }
							/>
						</PanelRow>
						{ !! googleAPI && attributes.mapMarkerClustering &&
							<PanelRow>
								<TextControl
									type={ 'number' }
									label={ __( 'Minimal number of markers in a cluster', 'toolset-maps' ) }
									value={ attributes.mapMarkerClusteringMinimalNumber }
									onChange={ onChangeMapMarkerClusteringMinimalNumber }
								/>
							</PanelRow>
						}
						{ !! googleAPI && attributes.mapMarkerClustering &&
							<PanelRow>
								<TextControl
									type={ 'number' }
									label={ __( 'Minimal distance, in pixels, between markers', 'toolset-maps' ) }
									value={ attributes.mapMarkerClusteringMinimalDistance }
									onChange={ onChangeMapMarkerClusteringMinimalDistance }
								/>
							</PanelRow>
						}
						{ !! googleAPI && attributes.mapMarkerClustering &&
							<PanelRow>
								<TextControl
									type={ 'number' }
									label={ __( 'Maximal map zoom level that allows clustering', 'toolset-maps' ) }
									value={ attributes.mapMarkerClusteringMaximalZoomLevel }
									onChange={ onChangeMapMarkerClusteringMaximalZoomLevel }
								/>
							</PanelRow>
						}
						{ !! googleAPI && attributes.mapMarkerClustering &&
							<PanelRow>
								<ToggleControl
									label={ __( 'Zoom the map when clicking on a cluster icon', 'toolset-maps' ) }
									checked={ !! attributes.mapMarkerClusteringClickZoom }
									onChange={ onChangeMapMarkerClusteringClickZoom }
								/>
							</PanelRow>
						}
						{ !! googleAPI &&
							<PanelRow>
								<ToggleControl
									label={ __( 'Spiderfy overlapping markers', 'toolset-maps' ) }
									help={ __(
										'Overlapping markers spring apart gracefully (on click).',
										'toolset-maps'
									) }
									checked={ !! attributes.mapMarkerSpiderfying }
									onChange={ onChangeMapMarkerSpiderfying }
								/>
							</PanelRow>
						}
						{ mapInteractionOptions }
						{ !! googleAPI &&
							<PanelRow>
								<RadioControl
									label={ __( 'Map type', 'toolset-maps' ) }
									selected={ attributes.mapType ? attributes.mapType : 'roadmap' }
									onChange={ onChangeMapType }
									options={
										[
											{
												value: 'roadmap',
												label: __( 'Map (default view)', 'toolset-maps' ),
											},
											{
												value: 'satellite',
												label: __( 'Satellite', 'toolset-maps' ),
											},
											{
												value: 'hybrid',
												label: __( 'Mix of Map & Satellite', 'toolset-maps' ),
											},
											{
												value: 'terrain',
												label: __( 'Terrain', 'toolset-maps' ),
											},
										]
									}
								/>
							</PanelRow>
						}
						{ !! googleAPI &&
							<PanelRow>
								<ToggleControl
									label={ __( 'Map type controls', 'toolset-maps' ) }
									checked={ !! attributes.mapTypeControl }
									onChange={ onChangeMapTypeControl }
								/>
							</PanelRow>
						}
						{ !! googleAPI &&
							<PanelRow>
								<ToggleControl
									label={ __( 'Zoom controls', 'toolset-maps' ) }
									checked={ !! attributes.mapZoomControls }
									onChange={ onChangeMapZoomControls }
								/>
							</PanelRow>
						}
						{ !! googleAPI &&
							<PanelRow>
								<ToggleControl
									label={ __( 'Street View control', 'toolset-maps' ) }
									checked={ !! attributes.mapStreetViewControl }
									onChange={ onChangeMapStreetViewControl }
								/>
							</PanelRow>
						}
						{ !! googleAPI &&
							<PanelRow>
								<SelectControl
									label={ __( 'Map style', 'toolset-maps' ) }
									value={ attributes.mapStyle }
									options={ mapStyleOptions }
									onChange={ onChangeMapStyle }
								/>
							</PanelRow>
						}
						{ !! googleAPI &&
							<PanelRow>
								<ToggleControl
									label={ __( 'Open Street View when this map loads', 'toolset-maps' ) }
									help={ __(
										'First marker added to this map will be used as location.',
										'toolset-maps'
									) }
									checked={ !! attributes.mapStreetView }
									onChange={ onChangeMapStreetView }
								/>
							</PanelRow>
						}
						<PanelRow>
							<TextareaControl
								label={ __( 'Text to show while the map is loading', 'toolset-maps' ) }
								value={ attributes.mapLoadingText }
								onChange={ onChangeMapLoadingText }
							/>
						</PanelRow>
						<PanelRow>
							<label
								htmlFor="map-background-color"
							>
								{ __( 'Background color', 'toolset-maps' ) }
							</label>
							<ColorIndicator
								id="map-background-color"
								colorValue={ attributes.mapBackgroundColor }
							/>
						</PanelRow>
						<PanelRow>
							<ColorPalette
								colors={ i18n.themeColors[ 0 ] }
								value={ attributes.mapBackgroundColor }
								onChange={ onChangeMapBackgroundColor }
							/>
						</PanelRow>
						<PanelRow>
							<MarkerRadios
								label={ __( 'Marker icon', 'toolset-maps' ) }
								options={ markerOptions }
								selected={ attributes.mapMarkerIcon }
								onChange={ onChangeMapMarkerIcon }
								instanceId={ 'map-marker-icon' }
							/>
						</PanelRow>
						<PanelRow>
							<ToggleControl
								label={ __( 'Use a different marker icon on hover', 'toolset-maps' ) }
								checked={ !! attributes.mapMarkerIconUseDifferentForHover }
								onChange={ onChangeMapMarkerIconUseDifferentForHover }
							/>
						</PanelRow>
						{ !! attributes.mapMarkerIconUseDifferentForHover &&
							<PanelRow>
								<MarkerRadios
									label={ __( 'Marker icon on hover', 'toolset-maps' ) }
									options={ markerOptions }
									selected={ attributes.mapMarkerIconHover }
									onChange={ onChangeMapMarkerIconHover }
									instanceId={ 'map-marker-icon-hover' }
								/>
							</PanelRow>
						}
					</PanelBody>
					{ markers }
					<PanelBody>
						<Button
							isPrimary
							id={ 'add-another-marker' }
							onClick={ onAddAnotherMarker }
						>
							{ __( 'Add marker', 'toolset-maps' ) }
						</Button>
					</PanelBody>
				</div>
			</InspectorControls>
		);
	}
}
