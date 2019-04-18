<?php

/**
 * Base Event Commands Class
 *
 * @since 1.7
 */
abstract class CRED_Commerce_Event_Base implements ICRED_Commerce_Form_Command {

	/** @var array */
	protected $data_order;
	/** @var ICRED_Commerce_Form_Handler */
	protected $plugin;
	/** @var CRED_Form_Meta_Data */
	protected $cred_forms_meta;
	/** @var string */
	protected $meta_name;

	/**
	 * CRED_Commerce_Base_Event constructor.
	 *
	 * @param array $data_order
	 * @param ICRED_Commerce_Form_Handler $form_handler
	 * @param string $meta_name
	 */
	public function __construct( $data_order, ICRED_Commerce_Form_Handler $form_handler, $meta_name = 'cred_meta' ) {
		$this->data_order = $data_order;
		$this->meta_name = $meta_name;
		$this->plugin = $form_handler;
	}

	public function execute() {
		//Collect Forms Data
		$cred_notification_handler = new CRED_Commerce_Forms_Meta_Handler( $this->data_order );
		//Get cred forms meta info
		$this->cred_forms_meta = $cred_notification_handler->get_forms_meta_data( $this->meta_name );
	}

	/**
	 * Get Notification class name by form type {cred-form|cred-user-form}
	 *
	 * @param string $form_type
	 *
	 * @return string
	 */
	public function get_notification_class_by_form_type( $form_type ) {
		switch ( $form_type ) {
			case CRED_USER_FORMS_CUSTOM_POST_NAME:
				$class = 'CRED_Commerce_Notification_User_Handler';
				break;

			default:
			case CRED_FORMS_CUSTOM_POST_NAME:
				$class = 'CRED_Commerce_Notification_Post_Handler';
				break;
		}

		return $class;
	}
}