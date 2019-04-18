/**
 * @since 2.5.2
 * @author Riccardo Strobbia
 * A wrapper for a button to create a private layout or re-connect an existing one and redirect to its editor
 */
import { IconButton } from '@wordpress/components';
const { Component } = wp.element;
const { __ } = wp.i18n;

class PrivateLayoutCreateButton extends Component {
	/**
	 *
	 * @param props
	 */
	constructor( props ) {
		super( props );

		this.state = {
			hasPrivateLayout: this.props.hasPrivateLayout ? true : false,
			isPrivateLayoutInUse: this.props.isPrivateLayoutInUse ? true : false,
			editorUrl: this.props.editorUrl,
			postId: this.props.postId,
			postType: this.props.postType,
			userCanEditPrivate: this.props.userCanEditPrivate === '1' ? true : false,
		};

		this.handleClick.bind( this );
		Toolset.hooks.addAction( 'ddl_private_layout_usage_stopped', this.stopUsingPrivateLayoutCallback );
		Toolset.hooks.addAction( 'ddl_private_layout_created', this.updatePrivateLayoutStates, 10, 1 );
	}

	/**
	 * @return void
	 */
	stopUsingPrivateLayoutCallback = () => {
		this.setState( { isPrivateLayoutInUse: false } );
	}
	/**
	 *
	 * @param event
	 * @return {boolean}
	 */
	handleClick = ( event ) => {
		event.preventDefault();
		if ( ! this.state.userCanEditPrivate ) {
			return false;
		}
		if ( this.state.hasPrivateLayout && ! this.state.isPrivateLayoutInUse ) {
			this.setState( { isPrivateLayoutInUse: true } );
			this.updatePrivateLayout( event );
		} else if ( ! this.state.hasPrivateLayout && ! this.state.isPrivateLayoutInUse ) {
			this.createPrivateLayout( event );
		}
	}
	/**
	 *
	 * @param event
	 */
	createPrivateLayout = (event ) => {
		if ( _.isObject( DDLayout ) && DDLayout.new_layout_dialog instanceof DDLayout.NewLayoutDialog ) {
			DDLayout.new_layout_dialog.privateLayoutNewTop( event );
		} else {
			const createLayout = new DDLayout.NewLayoutDialog();
			createLayout.privateLayoutNewTop( event );
		}
	}
	/**
	 *
	 * @param layoutId
	 */
	updatePrivateLayoutStates = ( layoutId ) => {
		if (typeof layoutId !== 'undefined' && layoutId !== 0){
			this.setState( { hasPrivateLayout: true, isPrivateLayoutInUse: true } );
		}
	}
	/**
	 *
	 * @param event
	 */
	updatePrivateLayout = (event ) => {
		const updateManager = new DDLayout.UseLayoutsAsPageBuilderManager( jQuery, jQuery( event.target ) );
		updateManager.update_status( event );
	}
	/**
	 *
	 * @return {string}
	 */
	getClassNames = () => {
		const classes = 'layouts-create-private-layout-button-wrap';

		return this.state.isPrivateLayoutInUse ? classes + ' hidden' : classes;
	}
	/**
	 *
	 * @return {*}
	 */
	getIconButton = () => {
		if ( this.state.userCanEditPrivate ) {
			return ( <IconButton
				data-layout_type="private"
				data-layout_id={ this.state.postId }
				data-post_type={ this.state.postType }
				data-content_id={ this.state.postId }
				data-editor="editor"
				isDefault
				isLarge
				onClick={ this.handleClick }
				className="button-primary-toolset js-layout-private-use"
				icon="<i class='icon-layouts-logo fa fa-wpv-custom ont-icon-18 ont-color-white'></i>"
			>{ __( 'Content Layout Editor', 'ddl-layouts' ) }</IconButton> );
		} else {
			return ( <IconButton
				isDefault
				isLarge
				onClick={ this.doNothing }
				className="button-primary-toolset js-layout-private-use"
				icon="<i class='icon-layouts-logo fa fa-wpv-custom ont-icon-18 ont-color-white'></i>"
				disabled
			>{ __( 'Content Layout Editor', 'ddl-layouts' ) }</IconButton> );
		}
	}
	/**
	 *
	 * @param event
	 * @return {boolean}
	 */
	doNothing = ( event ) => {
		event.preventDefault();
		console.log( __( 'User doesn\'t have the rights to edit layouts', 'ddl-layouts' ) );
		return false;
	}

	/**
	 *
	 * @return {*}
	 */
	render() {
		return (
			<div className={ this.getClassNames() }>
				{ this.getIconButton() }</div>
		);
	}
}

export default PrivateLayoutCreateButton;
