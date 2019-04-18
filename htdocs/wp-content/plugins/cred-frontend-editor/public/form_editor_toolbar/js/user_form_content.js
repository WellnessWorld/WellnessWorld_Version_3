/**
 * Manage the user form editor toolbar.
 *
 * @see Toolset.CRED.EditorToolbarPrototype
 *
 * @since m2m
 * @package CRED
 */

var Toolset = Toolset || {};

Toolset.CRED = Toolset.CRED || {};

Toolset.CRED.UserFormsContentEditorToolbar = function( $ ) {
	Toolset.CRED.EditorToolbarPrototype.call( this );

	var self = this;

	/**
	 * Initialize localization strings.
	 *
	 * @since 2.1
	 */
	self.initI18n = function() {
		self.i18n = cred_user_form_content_editor_toolbar_i18n;
		return self;
	};

	/**
	 * Init cache. Maybe populate it with fields for the currenty selected object key.
	 *
	 * @since 2.3.1
	 */
	self.initCache = function() {
		self.fieldsCache = _.has( self.i18n, 'initialCache' ) ? self.i18n.initialCache : {};
		return self;
	};

	/**
	 * Init Toolset hooks.
	 *
	 * @uses Toolset.hooks
	 * @since 2.1
	 */
	self.initHooks = function() {
		self.constructor.prototype.initHooks.call( self );

		Toolset.hooks.addFilter( 'toolset-filter-shortcode-gui-cred_field-computed-attribute-values', self.adjustAttributes, 10 );
		Toolset.hooks.addFilter( 'toolset-filter-shortcode-gui-cred_field-crafted-shortcode', self.adjustCraftedShortcode, 10 );

		return self;
	};

	/**
	 * Init Toolset hooks.
	 *
	 * @uses Toolset.hooks
	 * @since 2.1
	 */
	self.initEvents = function() {
		self.constructor.prototype.initEvents.call( self );

		$( document ).on( 'change', '.js-cred-editor-scaffold-options-autogeneratedUsername', function() {
			var checked = $( this ).prop( 'checked' ),
				$itemRow = $( '.js-cred-toolbar-scaffold-item-autogeneratedUsername' );

			if ( checked ) {
				if ( $itemRow.hasClass( 'js-cred-editor-scaffold-item-container-options-opened' ) ) {
					var $itemOptions = $itemRow.closest( '.cred-editor-scaffold-item-wrapper-row' ).next();
					if ( $itemOptions.hasClass( 'js-cred-editor-scaffold-item-options' ) ) {
						$itemOptions.find('.js-cred-editor-scaffold-options-close' ).trigger( 'click' );
					}
				}
				$itemRow
					.addClass( 'cred-editor-scaffold-item-container-disabled js-cred-editor-scaffold-item-container-disabled' )
					.data( 'include', false );
			} else {
				$itemRow
					.removeClass( 'cred-editor-scaffold-item-container-disabled js-cred-editor-scaffold-item-container-disabled' )
					.data( 'include', true );
			}
		});

		$( document ).on( 'change', '.js-cred-editor-scaffold-options-autogeneratedNickname', function() {
			var checked = $( this ).prop( 'checked' ),
				$itemRow = $( '.js-cred-toolbar-scaffold-item-autogeneratedNickname' );

			if ( checked ) {
				if ( $itemRow.hasClass( 'js-cred-editor-scaffold-item-container-options-opened' ) ) {
					var $itemOptions = $itemRow.closest( '.cred-editor-scaffold-item-wrapper-row' ).next();
					if ( $itemOptions.hasClass( 'js-cred-editor-scaffold-item-options' ) ) {
						$itemOptions.find('.js-cred-editor-scaffold-options-close' ).trigger( 'click' );
					}
				}
				$itemRow
					.addClass( 'cred-editor-scaffold-item-container-disabled js-cred-editor-scaffold-item-container-disabled' )
					.data( 'include', false );
			} else {
				$itemRow
					.removeClass( 'cred-editor-scaffold-item-container-disabled js-cred-editor-scaffold-item-container-disabled' )
					.data( 'include', true );
			}
		});

		$( document ).on( 'change', '.js-cred-editor-scaffold-options-autogeneratedPassword', function() {
			var checked = $( this ).prop( 'checked' ),
				$itemRow = $( '.js-cred-toolbar-scaffold-item-autogeneratedPassword' );

			if ( checked ) {
				if ( $itemRow.hasClass( 'js-cred-editor-scaffold-item-container-options-opened' ) ) {
					var $itemOptions = $itemRow.closest( '.cred-editor-scaffold-item-wrapper-row' ).next();
					if ( $itemOptions.hasClass( 'js-cred-editor-scaffold-item-options' ) ) {
						$itemOptions.find('.js-cred-editor-scaffold-options-close' ).trigger( 'click' );
					}
				}
				$itemRow
					.addClass( 'cred-editor-scaffold-item-container-disabled js-cred-editor-scaffold-item-container-disabled' )
					.data( 'include', false );
			} else {
				$itemRow
					.removeClass( 'cred-editor-scaffold-item-container-disabled js-cred-editor-scaffold-item-container-disabled' )
					.data( 'include', true );
			}
		});

		return self;
	};

	/**
	 * Get the current form slug.
	 *
	 * @return string
	 * @since 2.2.1
	 */
	self.getFormSlug = function() {
		return $( '#post_name' ).val();
	};

	/**
	 * Get the object key to manipulate fields for.
	 *
	 * @return string|array
	 * @since 2.1
	 */
	self.getObjectKey = function() {
		var formType = $( 'input[name="_cred[form][type]"]:checked' ).val();

		switch( formType ) {
			case 'new':
				return $( '#cred_form_user_role' ).val();
			case 'edit':
				var selectedRoles = [];
				$( 'input[name="_cred[form][user_role][]"]:checked' ).each( function() {
					selectedRoles.push( $( this ).val() );
				});
				return selectedRoles;
		}

		return '';
	};

	/**
	 * Adjust the attributes for the password fields.
	 *
	 * @param {object} attributes
	 *
	 * @return {object}
	 *
	 * @since 2.1
	 */
	self.adjustPasswordAtributes = function( attributes ) {
		attributes.confirm_pass = false;
		return attributes;
	};

	/**
	 * Adjust the form shortcode attributes when generated as an individual field.
	 *
	 * @param {object} attributes
	 * @param {object} data
	 *
	 * @return {object}
	 *
	 * @since 2.1
	 */
	self.adjustAttributes = function( attributes, data ) {
		if ( 'user_pass' == attributes.field ) {
			attributes = self.adjustPasswordAtributes( attributes );
		}

		attributes.scaffold_field_id = false;

		return attributes;
	};

	/**
	 * Maybe extend the generated shortcode for the password fields.
	 *
	 * @param {string} shortcodeString
	 * @param {object} data
	 *
	 * @return {string}
	 *
	 * @since 2.1
	 */
	self.maybeExtendPasswordCraftedShortcode = function( shortcodeString, data ) {
		var rawAttributes = data.rawAttributes,
			outcome = '';

		// Manage "confirm password" for password fields
		if (
			_.has( rawAttributes, 'confirm_pass' )
			&& 'yes' == rawAttributes.confirm_pass
		) {
			outcome += shortcodeString.replace( 'user_pass', 'user_pass2' );
		}

		return outcome;
	};

	/**
	 * Adjust the crafted string in some cases for special shortcodes when generated as an individual field.
	 *
	 * @param {string} shortcodeString
	 * @param {object} data
	 *
	 * @return {string}
	 *
	 * @since 2.1
	 */
	self.adjustCraftedShortcode = function( shortcodeString, data ) {
		if ( 'user_pass' == data.rawAttributes.field ) {
			var maybeExtended = self.maybeExtendPasswordCraftedShortcode( shortcodeString, data );
			if ( '' != maybeExtended ) {
				shortcodeString += "\n" + maybeExtended;
			}
		}

		return shortcodeString;
	};

	self.init();

};

Toolset.CRED.UserFormsContentEditorToolbar.prototype = Object.create( Toolset.CRED.EditorToolbarPrototype.prototype );

jQuery( document ).ready( function( $ ) {
	new Toolset.CRED.UserFormsContentEditorToolbar( $ );
});
