<?php

/**
 * Class CRED_Commerce_User_Form_Handler
 *
 * @since 1.7
 * //TODO: split event methods in more command files
 */
class CRED_Commerce_Notification_User_Handler extends CRED_Commerce_Notification_Form_Handler_Base {

	/**
	 * On Order Change Event
	 */
	public function on_order_change_event() {
		$this->try_to_elaborate_premium_user_feature();
	}

	/**
	 * User premium feature triggered order changed event
	 */
	protected function try_to_elaborate_premium_user_feature() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$user_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();

		$is_notification_order_modified = false;
		$to_delete = - 1;
		//Order status has changed
		if ( $data_order[ 'previous_status' ] != $data_order[ 'new_status' ] ) {
			if ( isset( $form->fields[ 'notification' ]->notifications ) ) {
				foreach ( $form->fields[ 'notification' ]->notifications as $index => $notification ) {
					if ( $notification[ 'event' ][ 'type' ] == 'order_modified' ) {
						$is_notification_order_modified = ( $notification[ 'event' ][ 'order_status' ] == $data_order[ 'new_status' ] );
						if ( $is_notification_order_modified ) {
							break;
						}
					}
				}
			}
			if ( $is_notification_order_modified ) {
				//If Order is completed we can publish the user from draft
				if ( $data_order[ 'new_status' ] == 'completed' ) {
					$order_id = isset( $data_order[ 'order_id' ] ) ? $data_order[ 'order_id' ] : $data_order[ 'transaction_id' ];

					$new_user_id = $this->publish_temporary_user( $user_id, $order_id );

					$user_id = $new_user_id;
					$cred_meta[ 'cred_post_id' ] = $user_id;

					//If Order is different by completed
				} else {
					$to_delete = $this->create_draft_temporary_user( $user_id, $data_order[ 'order_id' ] );

					if ( $to_delete != - 1 ) {
						$user_id = $to_delete;
						$cred_meta[ 'cred_post_id' ] = $user_id;

						$this->execute_add_actions_cred_after_send_notifications();
					}
				}
			}

			if (
				$data_order[ 'previous_status' ] != $data_order[ 'new_status' ]
				&& $data_order[ 'new_status' ] == 'cancelled'
				&& $form->commerce[ 'order_cancelled' ][ 'post_status' ] == 'delete'
			) {
				$this->delete_draft_temporary_user( $user_id );
			}
		}

		$this->_data = array(
			'order_id' => $data_order[ 'order_id' ],
			'previous_status' => $data_order[ 'previous_status' ],
			'new_status' => $data_order[ 'new_status' ],
			'cred_meta' => $cred_meta,
		);

		$this->trigger_notification_event( $form_id, $form, $user_id, 'order_modified', 'notification_order_event' );

		do_action( 'cred_commerce_after_send_notifications_form_' . $form_slug, $this->data_order );
		do_action( 'cred_commerce_after_send_notifications', $this->data_order );

		if (
			$is_notification_order_modified
			&& $to_delete != - 1
			&& $data_order[ 'new_status' ] != 'completed'
		) {
			$this->delete_db_temporary_user( $user_id );
		}

		$this->_data = false;
	}

	/**
	 * Function to handle the cred commerce order created event
	 */
	public function on_order_created_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$user_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();

		$is_notification_order_created = false;
		$to_delete = - 1;
		if ( isset( $form->fields[ 'notification' ]->notifications ) ) {
			foreach ( $form->fields[ 'notification' ]->notifications as $index => $notification ) {
				if ( $notification[ 'event' ][ 'type' ] == 'order_created' ) {
					$is_notification_order_created = true;
					break;
				}
			}
		}
		if ( $is_notification_order_created ) {
			$new_user_id = $this->create_draft_temporary_user( $user_id, $data_order[ 'order_id' ] );
			if ( $new_user_id != - 1 ) {
				$to_delete = $user_id;
				$user_id = $new_user_id;
				$cred_meta[ 'cred_post_id' ] = $user_id;

				$this->execute_add_actions_cred_after_send_notifications();
			}
		}

		$this->_data = array(
			'order_id' => $data_order[ 'order_id' ],
			'cred_meta' => $cred_meta,
		);
		$this->trigger_notification_event( $form_id, $form, $user_id, 'order_created', 'notification_order_created_event', $this->customer );

		if (
			$is_notification_order_created
			&& $to_delete != - 1
		) {
			$this->delete_db_temporary_user( $user_id );
		}

		$this->_data = false;
	}

	/**
	 * Function to handle the cred commerce order completed event
	 */
	public function on_order_completed_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$user_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();

		//Move user from wp_options to wp_user $post_id is the counter
		$order_id = isset( $data_order[ 'order_id' ] ) ? $data_order[ 'order_id' ] : $data_order[ 'transaction_id' ];

		$new_user_id = $this->publish_temporary_user( $user_id, $order_id );

		$user_id = $new_user_id;
		$cred_meta[ 'cred_post_id' ] = $user_id;

		$this->_data = array(
			'order_id' => $order_id,
			'cred_meta' => $cred_meta,
		);

		$this->trigger_notification_event( $form_id, $form, $user_id, 'order_completed', 'notification_order_complete_event', $this->customer );

		$this->_data = false;

		update_post_meta( $order_id, '_cred_meta', serialize( $cred_meta ) );
	}

	/**
	 * On Order Received Event
	 */
	public function on_order_received_event() {
	}

	/**
	 * On Order Payment Failed Event
	 */
	public function on_payment_failed_event() {
	}

	/**
	 * On Order Payment Complete
	 */
	public function on_payment_complete_event() {
	}

	/**
	 * On Order Hold Event
	 */
	public function on_hold_event() {
	}

	/**
	 * On Order Refund Event
	 */
	public function on_refund_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$user_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();

		if ( $form->commerce[ 'order_refunded' ][ 'post_status' ] == 'delete' ) {
			wp_delete_user( $user_id );
			$this->delete_draft_temporary_user( $user_id );
		}

		if ( $form->commerce[ 'order_refunded' ][ 'post_status' ] == 'draft' ) {
			wp_delete_user( $user_id );
		}
	}

	/**
	 * On Order Cancel Event
	 */
	public function on_cancel_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$user_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();

		if ( $form->commerce[ 'order_cancelled' ][ 'post_status' ] == 'delete' ) {
			wp_delete_user( $user_id );
			$this->delete_draft_temporary_user( $user_id );
		}
		if ( $form->commerce[ 'order_cancelled' ][ 'post_status' ] == 'draft' ) {
			wp_delete_user( $user_id );
		}
	}

	/**
	 * Execute CRED Notification action 'cred_after_send_notifications'
	 */
	protected function execute_add_actions_cred_after_send_notifications() {
		if ( class_exists( "CRED_User_Premium_Feature" ) ) {
			$cred_user_premium_feature = CRED_User_Premium_Feature::get_instance();
			add_action( 'cred_after_send_notifications', array(
				$cred_user_premium_feature,
				'delete_db_temporary_user',
			), 10, 1 );
		} else {
			add_action( 'cred_after_send_notifications', 'CRED_StaticClass::delete_temporary_user', 10, 1 );
		}
	}

	/**
	 * @param int $user_id
	 */
	protected function delete_db_temporary_user( $user_id ) {
		if ( class_exists( "CRED_User_Premium_Feature" ) ) {
			CRED_User_Premium_Feature::get_instance()->delete_db_temporary_user( $user_id );
		} else {
			CRED_StaticClass::delete_temporary_user( $user_id );
		}
	}

	/**
	 * @param int $user_id
	 * @param int $order_id
	 *
	 * @return bool|int|WP_Error
	 */
	protected function publish_temporary_user( $user_id, $order_id ) {
		if ( class_exists( "CRED_User_Premium_Feature" ) ) {
			$new_user_id = CRED_User_Premium_Feature::get_instance()->publish_temporary_user( $user_id, $order_id );
		} else {
			$new_user_id = $this->user_forms_model->publishTemporaryUser( $user_id, $order_id );
		}

		return $new_user_id;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool
	 */
	protected function delete_draft_temporary_user( $user_id ) {
		if ( class_exists( 'CRED_User_Premium_Feature' ) ) {
			$new_user_id = CRED_User_Premium_Feature::get_instance()->delete_draft_temporary_user( $user_id );
		} else {
			$new_user_id = $this->user_forms_model->deleteTemporaryUser( $user_id );
		}

		return $new_user_id;
	}

	/**
	 * @param int $user_id
	 * @param int $data_order_id
	 *
	 * @return int
	 */
	protected function create_draft_temporary_user( $user_id, $data_order_id ) {
		$new_user_id = ( class_exists( "CRED_User_Premium_Feature" ) ) ? CRED_User_Premium_Feature::get_instance()->create_draft_temporary_user( $user_id, $data_order_id[ 'order_id' ] ) : CRED_StaticClass::create_temporary_user_from_draft( $user_id, $data_order_id );

		return $new_user_id;
	}

	/**
	 * Triggering CRED form Notification Event based to order_event and notification event
	 *
	 * @param int $form_id
	 * @param WP_Post $form
	 * @param int $user_id
	 * @param string $order_event
	 * @param string $cred_custom_notification_event
	 * @param array|null $customer
	 */
	public function trigger_notification_event( $form_id, $form, $user_id, $order_event, $cred_custom_notification_event, $customer = null ) {
		add_filter( 'cred_custom_notification_event', array( $this, $cred_custom_notification_event ), 1, 4 );

		$args = array(
			'event' => $order_event,
			'form_id' => $form_id,
			'notification' => $form->fields[ 'notification' ],
		);

		//Adding data order if is set
		if ( isset( $this->_data ) ) {
			$args[ 'data_order' ] = $this->_data;
		}

		if ( null !== $customer ) {
			$args[ 'customer' ] = $customer;
		}

		if ( method_exists( 'CRED_Notification_Manager_User', 'get_instance' ) ) {
			CRED_Notification_Manager_User::get_instance()->trigger_notifications( $user_id, $args );
		} elseif ( method_exists( 'CRED_Notification_Manager', 'get_instance' ) ) {
			CRED_Notification_Manager::get_instance()->triggerNotifications( $user_id, $args );
		} else {
			CRED_Notification_Manager::triggerNotifications( $user_id, $args );
		}

		remove_filter( 'cred_custom_notification_event', array( $this, $cred_custom_notification_event ), 1, 4 );
	}
}