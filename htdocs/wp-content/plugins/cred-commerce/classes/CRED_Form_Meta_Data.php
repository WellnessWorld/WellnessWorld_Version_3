<?php

/**
 * Class CRED_Form_Meta_Data that is the form meta object
 */
class CRED_Form_Meta_Data {

	/**
	 * @var int
	 */
	private $form_id;
	/**
	 * @var string
	 */
	private $form_slug;
	/**
	 * @var WP_Post CRED Post/User Form
	 */
	private $form;
	/**
	 * @var array of notifications setting
	 */
	private $notifications;
	/**
	 * @var string {CRED_FORMS_CUSTOM_POST_NAME|CRED_USER_FORMS_CUSTOM_POST_NAME}
	 */
	private $type_form;
	/**
	 * Is referred to post_id for Cred Post Forms or user_id if Cred User Forms (cred_meta['cred_post_id'])
	 *
	 * @var int|string is string a the case of user premium feature (draft_x)
	 */
	private $referred_object_id;
	/**
	 * @var array
	 */
	private $meta;

	/**
	 * @var int
	 */
	private $meta_index;
	/**
	 * @var string
	 */
	private $meta_name;
	/**
	 * @var array
	 */
	private $data_order;

	/**
	 * CRED_Form_Meta_Data constructor.
	 *
	 * @param $form_meta_array
	 *
	 * @throws Exception
	 */
	public function __construct( $form_meta_array ) {
		if ( ! isset( $form_meta_array[ 'form_id' ] ) ) {
			throw new Exception( __( 'form_id is missing.', 'wp-cred' ) );
		}
		if ( ! isset( $form_meta_array[ 'form_slug' ] ) ) {
			throw new Exception( __( 'form_slug is missing.', 'wp-cred' ) );
		}
		if ( ! isset( $form_meta_array[ 'type_form' ] ) ) {
			throw new Exception( __( 'type_form is missing.', 'wp-cred' ) );
		}
		$this->form_id = $form_meta_array[ 'form_id' ];
		$this->form_slug = $form_meta_array[ 'form_slug' ];
		$this->form = isset( $form_meta_array[ 'form' ] ) ? $form_meta_array[ 'form' ] : null;
		$this->notifications = isset( $form_meta_array[ 'notifications' ] ) ? $form_meta_array[ 'notifications' ] : array();
		$this->type_form = $form_meta_array[ 'type_form' ];
		$this->referred_object_id = isset( $form_meta_array[ 'referred_object_id' ] ) ? $form_meta_array[ 'referred_object_id' ] : '';
		$this->meta = $form_meta_array[ 'meta' ];
		$this->meta_index = $form_meta_array[ 'meta_index' ];
		$this->meta_name = $form_meta_array[ 'meta_name' ];
		$this->data_order = $form_meta_array[ 'data_order' ];
	}

	/**
	 * @return int
	 */
	public function get_form_id() {
		return $this->form_id;
	}

	/**
	 * @return string
	 */
	public function get_type_form() {
		return $this->type_form;
	}

	/**
	 * @return int
	 */
	public function get_referred_object_id() {
		return $this->referred_object_id;
	}

	/**
	 * @return null|WP_Post
	 */
	public function get_form() {
		return $this->form;
	}

	/**
	 * @return array
	 */
	public function get_notifications() {
		return $this->notifications;
	}

	/**
	 * @return string
	 */
	public function get_form_slug() {
		return $this->form_slug;
	}

	/**
	 * @return array
	 */
	public function get_meta() {
		return $this->meta;
	}

	/**
	 * @param $meta
	 */
	public function set_meta( $meta ) {
		$this->meta = $meta;
	}

	/**
	 * @return int
	 */
	public function get_meta_index() {
		return $this->meta_index;
	}

	/**
	 * @return string
	 */
	public function get_meta_name() {
		return $this->meta_name;
	}

	/**
	 * @return array
	 */
	public function get_data_order() {
		return $this->data_order;
	}

	/**
	 * @param array $data_order
	 */
	public function set_data_order( $data_order ) {
		$this->data_order = $data_order;
	}

}