<?php

/**
 * Class responsible to register the entry point when an AJAXed Form is submitted
 *
 * @since 1.9.4
 */
class CRED_Form_Ajax_Init {

	/**
	 * Check whether an entry point is needed for frontend AJAX forms.
	 *
	 * @return bool
	 * @since 2.1.2
	 */
	public function condition_is_met() {
		return (
			isset( $_POST )
			&& 'cred_ajax_form' === toolset_getpost( 'action' )
			&& array_key_exists( CRED_StaticClass::PREFIX . 'form_id', $_POST )
			&& array_key_exists( CRED_StaticClass::PREFIX . 'form_count', $_POST )
		);
	}

	/**
	 * Include the form initialization as early as possible.
	 * Note that this is nto a proper, real AJAX call.
	 *
	 * @todo Turn this into a real AJAX call, please!
	 * @return void
	 */
	public function initialize() {
		add_action( 'template_redirect', array( $this, 'register_entry_point' ), 10 );
	}

	/**
	 * When Forms are AJAX-submitted we need to register a dedicated entry point in order to
	 * re-create the saved form. We need to have at least a Form submition.
	 *
	 * @return bool
	 */
	public function register_entry_point() {
		if ( ! is_admin() ) {
			$form_id = false;
			$post_id = false;
			$form_count = 1;
			$preview = false;
			$this->try_to_update_by_post( $form_id, $post_id, $form_count, $preview );
			return CRED_Form_Builder::initialize()->get_form( $form_id, $post_id, $form_count, $preview );
		}
	}

	/**
	 * Try to set the right form and post from the POSTed data, when processing an AJAX form.
	 *
	 * @param int|bool $form_id
	 * @param int|bool $post_id
	 * @param int $form_count
	 * @param bool $preview
	 * @since unknown
	 */
	public function try_to_update_by_post( &$form_id, &$post_id, &$form_count, &$preview ) {
		if (
			'cred_ajax_form' === toolset_getpost( 'action' )
			&& array_key_exists( CRED_StaticClass::PREFIX . 'form_id', $_POST )
			&& array_key_exists( CRED_StaticClass::PREFIX . 'form_count', $_POST )
		) {
			$form_id = intval( toolset_getpost( CRED_StaticClass::PREFIX . 'form_id' ) );
			$form_count = intval( toolset_getpost( CRED_StaticClass::PREFIX . 'form_count' ) );
			$post_id = ( array_key_exists( CRED_StaticClass::PREFIX . 'post_id', $_POST ) ) ? intval( toolset_getpost( CRED_StaticClass::PREFIX . 'post_id' ) ) : false;
			$preview = ( array_key_exists( CRED_StaticClass::PREFIX . 'form_preview_content', $_POST ) ) ? true : false;
		}
	}
}
