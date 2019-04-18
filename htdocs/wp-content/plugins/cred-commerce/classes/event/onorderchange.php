<?php

/**
 * Class CRED_Commerce_OnOrderChange_Event
 *
 * @since 1.7
 */
class CRED_Commerce_Event_OnOrderChange extends CRED_Commerce_Event_Base {

	public function execute() {
		parent::execute();

		// send notifications
		if ( ! isset( $this->data_order[ 'new_status' ] )
			|| ! in_array(
				$this->data_order[ 'new_status' ], array(
					'pending',
					'failed',
					'processing',
					'completed',
					'on-hold',
					'cancelled',
					'refunded',
				)
			)
		) {
			return;
		} // not spam with useless notifications ;)

		$cred_forms_meta = $this->cred_forms_meta;
		if ( ! empty( $cred_forms_meta ) ) {
			foreach ( $cred_forms_meta as $form_id => $cred_form_meta_object ) {
				$form = $cred_form_meta_object->get_form();
				$is_commerce = $form->isCommerce;
				$form_id = $cred_form_meta_object->get_form_id();
				$form_slug = $cred_form_meta_object->get_form_slug();
				$customer = $this->plugin->getCustomer( $cred_form_meta_object->get_referred_object_id(), $form_id );
				if ( $is_commerce ) {
					$form_type = $cred_form_meta_object->get_type_form();
					$notification_class = $this->get_notification_class_by_form_type( $form_type );
					$notification_handler = new $notification_class( $customer, $this->data_order, $cred_form_meta_object );
					$notification_handler->on_order_change_event();
				}
			}

			// HOOKS API
			do_action( 'cred_commerce_after_on_order_change', $this->data_order );
		}
	}
}