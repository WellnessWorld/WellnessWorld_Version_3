<?php

/**
 * Class CRED_Commerce_Post_Form_Handler
 *
 * @since 1.7
 * //TODO: split event methods in more command files
 */
class CRED_Commerce_Notification_Post_Handler extends CRED_Commerce_Notification_Form_Handler_Base {

	/**
	 * Change the post status according to the cred commerce form settings
	 *
	 * @param int $post_id
	 * @param WP_Post $form
	 *
	 * @return int|WP_Error
	 */
	protected function change_post_status( $post_id, $form ) {
		$new_status = $this->data_order[ 'new_status' ];
		$previous_status = $this->data_order[ 'previous_status' ];
		$default_status = $previous_status;
		/*
		 * Status like 'on_hold' is not used in Cred Form Cred Commerce Box Settings so the post status doesn't change
		 */
		$new_post_status = isset( $form->commerce[ 'order_' . $new_status ] ) ? $form->commerce[ 'order_' . $new_status ][ 'post_status' ] : $default_status;
		$processed_post = array(
			'ID' => $post_id,
			'post_status' => $new_post_status,
		);

		// Update the post into the database
		return wp_update_post( $processed_post );
	}

	/**
	 * On Order Change Event
	 */
	public function on_order_change_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$referred_object_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();

		$this->change_post_status( $referred_object_id, $form );

		$this->_data = array(
			'order_id' => $data_order[ 'order_id' ],
			'previous_status' => $data_order[ 'previous_status' ],
			'new_status' => $data_order[ 'new_status' ],
			'cred_meta' => $cred_meta,
		);

		$this->trigger_notification_event( $form_id, $form, $referred_object_id, 'order_modified', 'notification_order_event' );

		do_action( 'cred_commerce_after_send_notifications_form_' . $form_slug, $this->data_order );
		do_action( 'cred_commerce_after_send_notifications', $this->data_order );

		$this->_data = false;
	}

	/**
	 * On Order Created Event
	 */
	public function on_order_created_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$cred_meta = $this->cred_form_meta_data->get_meta();
		$user_id = $cred_meta[ 'cred_post_id' ];

		$this->_data = array(
			'order_id' => $data_order[ 'order_id' ],
			'cred_meta' => $cred_meta,
		);

		$this->trigger_notification_event( $form_id, $form, $user_id, 'order_created', 'notification_order_created_event', $this->customer );

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
		$post_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();
		$user_id = isset( $data_order[ 'user_id' ] ) ? (int) $data_order[ 'user_id' ] : false;

		if ( $post_id ) {
			//Check if post actually exists !!
			$current_post = get_post( $post_id );
			if ( $current_post ) {
				$post_data = array();
				if ( $form->fixAuthor && $user_id ) {
					$post_data[ 'post_author' ] = $user_id;
				}
				if (
					isset( $form->commerce[ 'order_completed' ] )
					&& isset( $form->commerce[ 'order_completed' ][ 'post_status' ] )
					&& in_array(
						$form->commerce[ 'order_completed' ][ 'post_status' ], array(
							'draft',
							'pending',
							'private',
							'publish',
						)
					)
				) {
					$post_data[ 'post_status' ] = $form->commerce[ 'order_completed' ][ 'post_status' ];
				}
				if ( ! empty( $post_data ) ) {
					$post_data[ 'ID' ] = $post_id;
					wp_update_post( $post_data );
				}
			}
		}
	}

	/**
	 * On Order Received Event
	 */
	public function on_order_received_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$post_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();
		$user_id = isset( $data_order[ 'user_id' ] ) ? (int) $data_order[ 'user_id' ] : false;

		if ( $post_id ) {
			// check if post actually exists !!
			$_post = get_post( $post_id );
			if ( $_post ) {
				$post_data = array();
				if ( $form->fixAuthor && $user_id ) {
					$post_data[ 'post_author' ] = $user_id;
				}
				if (
					isset( $form->commerce[ 'order_pending' ] ) &&
					isset( $form->commerce[ 'order_pending' ][ 'post_status' ] ) &&
					in_array(
						$form->commerce[ 'order_pending' ][ 'post_status' ], array(
							'draft',
							'pending',
							'private',
							'publish',
						)
					)
				) {
					$post_data[ 'post_status' ] = $form->commerce[ 'order_pending' ][ 'post_status' ];
				}
				if ( ! empty( $post_data ) ) {
					$post_data[ 'ID' ] = $post_id;
					wp_update_post( $post_data );
				}
			}
		}
	}

	/**
	 * On Payment Failed Event
	 */
	public function on_payment_failed_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$post_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();
		$user_id = isset( $data_order[ 'user_id' ] ) ? (int) $data_order[ 'user_id' ] : false;

		if ( $post_id ) {
			// check if post actually exists !!
			$_post = get_post( $post_id );
			if ( $_post ) {
				$post_data = array();
				if ( $form->fixAuthor && $user_id ) {
					$post_data[ 'post_author' ] = $user_id;
				}
				if (
					isset( $form->commerce[ 'order_failed' ] ) &&
					isset( $form->commerce[ 'order_failed' ][ 'post_status' ] ) &&
					in_array(
						$form->commerce[ 'order_failed' ][ 'post_status' ], array(
							'draft',
							'pending',
							'private',
							'publish',
						)
					)
				) {
					$post_data[ 'post_status' ] = $form->commerce[ 'order_failed' ][ 'post_status' ];
				}
				if ( ! empty( $post_data ) ) {
					$post_data[ 'ID' ] = $post_id;
					wp_update_post( $post_data );
				}
			}
		}
	}

	/**
	 * On Payment Complete Event
	 */
	public function on_payment_complete_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$post_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();
		$user_id = isset( $data_order[ 'user_id' ] ) ? (int) $data_order[ 'user_id' ] : false;

		if ( $post_id ) {
			// check if post actually exists !!
			$_post = get_post( $post_id );
			if ( $_post ) {
				$post_data = array();
				if ( $form->fixAuthor && $user_id ) {
					$post_data[ 'post_author' ] = $user_id;
				}
				if (
					isset( $form->commerce[ 'order_processing' ] ) &&
					isset( $form->commerce[ 'order_processing' ][ 'post_status' ] ) &&
					in_array(
						$form->commerce[ 'order_processing' ][ 'post_status' ], array(
							'draft',
							'pending',
							'private',
							'publish',
						)
					)
				) {
					$post_data[ 'post_status' ] = $form->commerce[ 'order_processing' ][ 'post_status' ];
				}
				if ( ! empty( $post_data ) ) {
					$post_data[ 'ID' ] = $post_id;
					wp_update_post( $post_data );
				}
			}
		}
	}

	/**
	 * On Hold Event
	 */
	public function on_hold_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$post_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();
		$user_id = isset( $data_order[ 'user_id' ] ) ? (int) $data_order[ 'user_id' ] : false;

		if ( $post_id ) {
			// check if post actually exists !!
			$_post = get_post( $post_id );
			if ( $_post ) {
				$post_data = array();
				if ( $form->fixAuthor && $user_id ) {
					$post_data[ 'post_author' ] = $user_id;
				}
				if (
					isset( $form->commerce[ 'order_on_hold' ] ) &&
					isset( $form->commerce[ 'order_on_hold' ][ 'post_status' ] ) &&
					in_array(
						$form->commerce[ 'order_on_hold' ][ 'post_status' ], array(
							'draft',
							'pending',
							'private',
							'publish',
						)
					)
				) {
					$post_data[ 'post_status' ] = $form->commerce[ 'order_on_hold' ][ 'post_status' ];
				}
				if ( ! empty( $post_data ) ) {
					$post_data[ 'ID' ] = $post_id;
					wp_update_post( $post_data );
				}
			}
		}
	}

	/**
	 * On Order Refund Event
	 */
	public function on_refund_event() {
		$data_order = $this->data_order;
		$form = $this->cred_form_meta_data->get_form();
		$form_id = $this->cred_form_meta_data->get_form_id();
		$form_slug = $this->cred_form_meta_data->get_form_slug();
		$post_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();

		if ( $post_id ) {
			// check if post actually exists !!
			$_post = get_post( $post_id );

			if ( $_post ) {
				$post_data = array();
				if (
					isset( $form->commerce[ 'order_refunded' ] ) &&
					isset( $form->commerce[ 'order_refunded' ][ 'post_status' ] ) &&
					in_array(
						$form->commerce[ 'order_refunded' ][ 'post_status' ], array(
							'draft',
							'pending',
							'private',
							'publish',
						)
					)
				) {
					$post_data[ 'post_status' ] = $form->commerce[ 'order_refunded' ][ 'post_status' ];
				}
				if ( ! empty( $post_data ) ) {
					$post_data[ 'ID' ] = $post_id;
					wp_update_post( $post_data );
				}

				if (
					isset( $form->commerce[ 'order_refunded' ] ) &&
					isset( $form->commerce[ 'order_refunded' ][ 'post_status' ] ) &&
					'trash' == $form->commerce[ 'order_refunded' ][ 'post_status' ]
				) {
					// move to trash
					wp_delete_post( $post_id, false );
				} elseif (
					isset( $form->commerce[ 'order_refunded' ] ) &&
					isset( $form->commerce[ 'order_refunded' ][ 'post_status' ] ) &&
					'delete' == $form->commerce[ 'order_refunded' ][ 'post_status' ]
				) {
					// delete
					wp_delete_post( $post_id, true );
				}
			}
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
		$post_id = $this->cred_form_meta_data->get_referred_object_id();
		$cred_meta = $this->cred_form_meta_data->get_meta();

		if ( $post_id ) {
			// check if post actually exists !!
			$_post = get_post( $post_id );

			if ( $_post ) {
				$post_data = array();
				if (
					isset( $form->commerce[ 'order_cancelled' ] )
					&& isset( $form->commerce[ 'order_cancelled' ][ 'post_status' ] )
					&& in_array(
						$form->commerce[ 'order_cancelled' ][ 'post_status' ], array(
							'draft',
							'pending',
							'private',
							'publish',
						)
					)
				) {
					$post_data[ 'post_status' ] = $form->commerce[ 'order_cancelled' ][ 'post_status' ];
				}
				if ( ! empty( $post_data ) ) {
					$post_data[ 'ID' ] = $post_id;
					wp_update_post( $post_data );
				}

				if (
					isset( $form->commerce[ 'order_cancelled' ] )
					&& isset( $form->commerce[ 'order_cancelled' ][ 'post_status' ] )
					&& 'trash' == $form->commerce[ 'order_cancelled' ][ 'post_status' ]
				) {
					// move to trash
					wp_delete_post( $post_id, false );
				} elseif (
					isset( $form->commerce[ 'order_cancelled' ] )
					&& isset( $form->commerce[ 'order_cancelled' ][ 'post_status' ] )
					&& 'delete' == $form->commerce[ 'order_cancelled' ][ 'post_status' ]
				) {
					// delete
					wp_delete_post( $post_id, true );
				}
			}
		}
	}

	/**
	 * Triggering CRED form Notification Event based to order_event and notification event
	 *
	 * @param int $form_id
	 * @param WP_Post $form
	 * @param int $post_id
	 * @param string $order_event
	 * @param string $cred_custom_notification_event
	 * @param array|null $customer
	 */
	public function trigger_notification_event( $form_id, $form, $post_id, $order_event, $cred_custom_notification_event, $customer = null ) {
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

		if ( method_exists( 'CRED_Notification_Manager_Post', 'get_instance' ) ) {
			CRED_Notification_Manager_Post::get_instance()->trigger_notifications( $post_id, $args );
		} elseif ( method_exists( 'CRED_Notification_Manager', 'get_instance' ) ) {
			CRED_Notification_Manager::get_instance()->triggerNotifications( $post_id, $args );
		} else {
			CRED_Notification_Manager::triggerNotifications( $post_id, $args );
		}

		remove_filter( 'cred_custom_notification_event', array( $this, $cred_custom_notification_event ), 1, 4 );
	}
}