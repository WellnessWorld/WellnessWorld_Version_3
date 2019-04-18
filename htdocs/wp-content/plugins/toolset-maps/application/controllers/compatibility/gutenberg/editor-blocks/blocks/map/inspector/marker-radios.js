import classnames from 'classnames';

const {
	Component,
} = wp.element;

const {
	BaseControl,
} = wp.components;

/**
 * Maps-specific component for choosing a marker icon. Shows a group of radios with icon previews instead of labels.
 */
export default class MarkerRadios extends Component {
	render() {
		const {
			label,
			className,
			selected,
			help,
			instanceId,
			onChange,
			options,
		} = this.props;

		const id = `marker-radio-control-${ instanceId }`;
		const onChangeValue = ( event ) => onChange( event.target.value );

		return !! options.length && (
			<BaseControl
				label={ label }
				id={ id }
				help={ help }
				className={ classnames( className, 'marker-radio-control' ) }
			>
				<ul>
					{ options.map( ( option, index ) =>
						<li
							key={ `${ id }-${ index }` }
							className="marker-radio-control__option"
						>
							<span
								className="wpv-icon-img js-wpv-icon-img"
								data-img={ option.value ? option.value : '//maps.loc/wp-content/plugins/views-addon-maps/resources/images/markers/default.png' }
								style={ {
									backgroundImage: 'url(' + ( option.value ? option.value : '//maps.loc/wp-content/plugins/views-addon-maps/resources/images/markers/default.png' ) + ')',
									backgroundPositionX: '50%',
									backgroundRepeatX: 'no-repeat',
									backgroundPositionY: 'center',
									backgroundRepeatY: 'no-repeat',
									backgroundSize: 'contain',
									width: '32px',
									height: '46px',
									display: 'inline-block',
								} }
							/>
							<br />
							<input
								id={ `${ id }-${ index }` }
								className="marker-radio-control__input"
								type="radio"
								name={ id }
								value={ option.value }
								onChange={ onChangeValue }
								checked={ option.value === selected }
								aria-describedby={ !! help ? `${ id }__help` : undefined }
							/>
						</li>
					) }
				</ul>
			</BaseControl>
		);
	}
}
