<?php

/**
 * Class CRED_Commerce_Base_Form_Notification_Handler
 *
 * @since 1.7
 */
abstract class CRED_Commerce_Notification_Form_Handler_Base {

	/** @var array */
	protected $data_order;
	/** @var CRED_Form_Meta_Data */
	protected $cred_form_meta_data;
	/** @var array */
	protected $customer;
	/** @var CRED_User_Forms_Model */
	protected $user_forms_model;
	/** @var array */
	public $_data;

	/**
	 * CRED_Commerce_User_Form_Notification_Handler constructor.
	 *
	 * array(
	 * 'form_id' => $form_id,
	 * 'form_slug' => $form_slug,
	 * 'type_form' => $cred_form_post->post_type,
	 * 'post_id' => isset( $meta['cred_post_id'] ) ? $meta['cred_post_id'] : '',
	 * 'form' => $form,
	 * 'notifications' => isset( $form->fields['notification']->notifications ) ?
	 * $form->fields['notification']->notifications : array(),
	 * )
	 *
	 * CRED_Commerce_User_Form_Notification_Handler constructor.
	 *
	 * @param array $customer
	 * @param array $data_order
	 * @param CRED_Form_Meta_Data $cred_form_meta_data
	 */
	public function __construct( $customer, $data_order, CRED_Form_Meta_Data $cred_form_meta_data, $user_forms_model = null ) {
		$this->data_order = $data_order;
		$this->cred_form_meta_data = $cred_form_meta_data;
		$this->customer = $customer;
		if ( null === $user_forms_model ) {
			$this->user_forms_model = CRED_Loader::get( 'MODEL/UserForms' );
		}
	}

	/**
	 * @param array $cred_meta
	 */
	public function update_cred_meta( $cred_meta ) {
		$order_id = isset( $this->data_order[ 'order_id' ] ) ? $this->data_order[ 'order_id' ] : $this->data_order[ 'transaction_id' ];
		update_post_meta( $order_id, '_cred_meta', serialize( $cred_meta ) );
	}

	/**
	 * @param bool $result
	 * @param array $notification
	 * @param int $form_id
	 * @param int $referred_object_id
	 *
	 * @return bool
	 */
	public function notification_order_event( $result, $notification, $form_id, $referred_object_id ) {
		if ( $this->_data ) {
			if (
				'order_modified' == $notification[ 'event' ][ 'type' ]
				&& $form_id == $this->_data[ 'cred_meta' ][ 'cred_form_id' ]
				&& $referred_object_id == $this->_data[ 'cred_meta' ][ 'cred_post_id' ]
				&& isset( $notification[ 'event' ][ 'order_status' ] )
				&& isset( $this->_data[ 'new_status' ] )
				&& $this->_data[ 'new_status' ] == $notification[ 'event' ][ 'order_status' ]
			) {
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * @param bool $result
	 * @param array $notification
	 * @param int $form_id
	 * @param int $referred_object_id
	 *
	 * @return bool
	 */
	public function notification_order_created_event( $result, $notification, $form_id, $referred_object_id ) {
		if ( $this->_data ) {
			if (
				'order_created' == $notification[ 'event' ][ 'type' ]
				&& $form_id == $this->_data[ 'cred_meta' ][ 'cred_form_id' ]
				&& $referred_object_id == $this->_data[ 'cred_meta' ][ 'cred_post_id' ]
			) {
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * @param bool $result
	 * @param array $notification
	 * @param int $form_id
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function notification_order_complete_event( $result, $notification, $form_id, $user_id ) {
		if ( $this->_data ) {
			if (
				'order_completed' == $notification[ 'event' ][ 'type' ]
				&& $form_id == $this->_data[ 'cred_meta' ][ 'cred_form_id' ]
				&& $user_id == $this->_data[ 'cred_meta' ][ 'cred_post_id' ]
			) {
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function get_form_configurations() {
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$user_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();
		$referred_object_id = $this->cred_form_meta_object->get_referred_object_id();

		return array( $form, $form_id, $form_slug, $user_id, $cred_meta, $referred_object_id );
	}

	abstract public function trigger_notification_event( $form_id, $form, $post_id, $order_event, $cred_custom_notification_event, $customer = null );
}