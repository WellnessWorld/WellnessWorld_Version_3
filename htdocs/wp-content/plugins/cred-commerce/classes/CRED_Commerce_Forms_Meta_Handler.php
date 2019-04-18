<?php

/**
 * Handler of cred forms meta info by cred commerce data order
 *
 * @since 1.7
 */
class CRED_Commerce_Forms_Meta_Handler {

	/** @var array */
	protected $data_order;
	/** @var CRED_COMMERCE_Main_Model  */
	protected $main_model;

	/**
	 * CRED_Commerce_Forms_Meta_Handler constructor.
	 *
	 * @param array $data_order
	 * @param CRED_COMMERCE_Main_Model|null $main_model
	 */
	public function __construct( $data_order, CRED_COMMERCE_Main_Model $main_model = null ) {
		$this->data_order = $data_order;
		if ( null === $main_model ) {
			$this->main_model = CREDC_Loader::get( 'MODEL/Main' );
		}
	}

	/**
	 * @param array $form_meta
	 *
	 * @return bool
	 */
	public static function is_cred_user_form_by_form_meta( $form_meta ) {
		return ( isset( $form_meta[ 'form_type' ] ) && $form_meta[ 'form_type' ] == CRED_USER_FORMS_CUSTOM_POST_NAME );
	}

	/**
	 * @param string $meta_name
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_forms_meta_data( $meta_name = 'cred_meta' ) {
		if ( ! isset( $this->data_order[ $meta_name ] )
			|| empty( $this->data_order[ $meta_name ] )
		) {
			return array();
		}

		$cred_forms_meta = array();
		foreach ( $this->data_order[ $meta_name ] as $index => $meta ) {
			if ( ! isset( $meta[ 'cred_form_id' ] )
				|| empty( $meta[ 'cred_form_id' ] )
			) {
				continue;
			}

			$form_slug = '';
			$form_id = $meta[ 'cred_form_id' ];
			$cred_form_post = get_post( $form_id );
			if ( $cred_form_post ) {
				$form_slug = $cred_form_post->post_name;
			}
			$form = $this->main_model->getForm( $form_id, true );

			$cred_form_meta_array = array(
				'form_id' => $form_id,
				'form_slug' => $form_slug,
				'type_form' => $cred_form_post->post_type,
				'referred_object_id' => isset( $meta[ 'cred_post_id' ] ) ? $meta[ 'cred_post_id' ] : false,
				'form' => $form,
				'notifications' => isset( $form->fields[ 'notification' ]->notifications ) ? $form->fields[ 'notification' ]->notifications : array(),
				'meta' => $meta,
				'meta_index' => $index,
				'meta_name' => $meta_name,
				'data_order' => $this->data_order,
			);
			$cred_forms_meta[ $form_id ] = new CRED_Form_Meta_Data( $cred_form_meta_array );
		}

		return $cred_forms_meta;
	}
}