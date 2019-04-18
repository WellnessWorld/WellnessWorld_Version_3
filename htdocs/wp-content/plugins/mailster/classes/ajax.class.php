<?php

class MailsterAjax {

	private $methods = array(

		'remove_notice',

		// edit screen
		'get_template',
		'get_plaintext',
		'create_new_template',
		'set_preview',
		'get_preview',
		'toggle_codeview',
		'send_test',
		'check_spam_score',
		'get_totals',
		'save_color_schema',
		'delete_color_schema',
		'delete_color_schema_all',
		'get_recipients',
		'get_recipients_page',
		'get_recipient_detail',
		'get_clicks',
		'get_errors',
		'get_environment',
		'get_geolocation',
		'get_post_term_dropdown',
		'check_for_posts',
		'create_image',
		'get_post_list',
		'get_post',

		'get_file_list',
		'get_template_html',
		'set_template_html',
		'remove_template',

		'notice_dismiss',
		'notice_dismiss_all',

		// settings
		'load_geo_data',
		'get_fallback_images',
		'bounce_test',
		'bounce_test_check',
		'get_system_info',
		'get_gravatar',
		'check_email',

		'sync_all_subscriber',
		'sync_all_wp_user',

		'create_list',
		'get_create_list_count',

		'get_subscriber_count',

		'editor_image_upload_handler',
		'template_upload_handler',

		// dashboard
		'get_dashboard_data',
		'get_dashboard_chart',

		'register',
		'envato_verify',
		'check_for_update',
		'check_language',
		'load_language',
		'quick_install',
		'wizard_save',

		'test',

	);

	private $methods_no_priv = array(
		'image_placeholder',
		'forward_message',
		'subscribe',
		'update',
		'unsubscribe',
		'form_submit',
		'profile_submit',
		'form_unsubscribe',
		'form_css',
	);

	public function __construct() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'plugins_loaded', array( &$this, 'init' ) );
		}

	}


	public function add_ajax_nonce() {
		wp_nonce_field( 'mailster_nonce', 'mailster_nonce', false );
	}


	public function init() {

		foreach ( $this->methods as $method ) {

			add_action( 'wp_ajax_mailster_' . $method, array( &$this, 'call_method' ) );

		}

		foreach ( $this->methods_no_priv as $method ) {

			add_action( 'wp_ajax_mailster_' . $method, array( &$this, 'call_method' ) );
			add_action( 'wp_ajax_nopriv_mailster_' . $method, array( &$this, 'call_method' ) );

		}

	}


	public function call_method() {

		$method_name = str_replace( array( 'wp_ajax_mailster_', 'wp_ajax_nopriv_mailster_' ), '', current_filter() );
		$args = func_get_args();

		if ( method_exists( $this, $method_name ) ) {
			call_user_func_array( array( $this, $method_name ), $args );
		} else {
			die( "Method $method does not exist!" );
		}

	}


	/**
	 *
	 *
	 * @param unknown $return
	 */
	private function json_return( $return ) {

		@header( 'Content-type: application/json' );
		echo json_encode( $return );
		exit;

	}


	public function form_css() {
		_deprecated_function( __FUNCTION__, '2.2' );
	}


	private function submit() {

		global $wp;

		$wp->query_vars['_mailster'] = 'subscribe';

		mailster( 'form' )->submit();

	}


	private function update() {

		mailster( 'form' )->update();

	}


	private function unsubscribe() {

		mailster( 'form' )->unsubscribe();

	}


	private function form_submit() {

		$this->submit();
	}


	private function profile_submit() {

		$this->update();
	}


	private function form_unsubscribe() {

		$this->unsubscribe();

	}


	private function get_plaintext() {

		$this->ajax_nonce( );

		$html = isset( $_POST['html'] ) ? stripslashes( $_POST['html'] ) : '';

		$html = mailster()->sanitize_content( $html );

		$html = mailster( 'helper' )->plain_text( $html );

		echo $html;

		exit;

	}


	private function get_template() {

		$this->ajax_nonce( );

		@error_reporting( 0 );

		$id = (int) $_GET['id'];
		$template = basename( $_GET['template'] );
		$file = isset( $_GET['templatefile'] ) ? basename( $_GET['templatefile'] ) : 'index.html';
		$editorstyle = isset( $_GET['editorstyle'] ) && '1' == $_GET['editorstyle'];

		$meta = mailster( 'campaigns' )->meta( $id );
		$head = isset( $meta['head'] ) ? $meta['head'] : null;

		if ( ! isset( $meta['file'] ) ) {
			$meta['file'] = 'index.html';
		}

		// template has been changed
		if ( ! isset( $meta['template'] ) || $template != $meta['template'] || $file != $meta['file'] ) {
			$html = mailster( 'campaigns' )->get_template_by_slug( $template, $file, false, $editorstyle );
		} else {
			$html = mailster( 'campaigns' )->get_template_by_id( $id, $file, false, $editorstyle );
		}

		if ( ! $editorstyle ) {
			$revision = isset( $_REQUEST['revision'] ) ? (int) $_REQUEST['revision'] : false;
			$campaign = get_post( $id );
			$subject = isset( $_REQUEST['subject'] ) ? esc_attr( $_REQUEST['subject'] ) : isset( $meta['subject'] ) ? esc_attr( $meta['subject'] ) : '';

			$current_user = wp_get_current_user();

			if ( $revision ) {
				$revision = get_post( $revision );
				$html = mailster()->sanitize_content( $revision->post_content, null, $head );
			}

			$placeholder = mailster( 'placeholder', $html );

			$placeholder->do_conditions( false );

			$placeholder->set_campaign( $campaign->ID );

			$placeholder->remove_last_post_args();

			$placeholder->add_defaults( $campaign->ID, array(
				'subject' => $subject,
			) );
			$placeholder->add_custom( $campaign->ID, array(
				'emailaddress' => $current_user->user_email,
			) );

			if ( 0 != $current_user->ID ) {
				$firstname = ( $current_user->user_firstname ) ? $current_user->user_firstname : $current_user->display_name;

				// $placeholder->add( array(
				// 'firstname' => $firstname,
				// 'lastname' => $current_user->user_lastname,
				// 'fullname' => mailster_option( 'name_order' ) ? trim( $current_user->user_lastname . ' ' . $firstname ) : trim( $firstname . ' ' . $current_user->user_lastname ),
				// ) );
			}

			$suffix = SCRIPT_DEBUG ? '' : '.min';
			$html = $placeholder->get_content( false );
			$html = str_replace( '</head>', '<link rel="stylesheet" id="template-style" href="' . MAILSTER_URI . 'assets/css/template-style' . $suffix . '.css?ver=' . MAILSTER_VERSION . '" type="text/css" media="all"></head>', $html );
		}

		$replace = array(
			'http://dummy.newsletter-plugin.com' => 'https://dummy.newsletter-plugin.com',
		);
		$replace = apply_filters( 'mymail_get_template_replace', apply_filters( 'mailster_get_template_replace', $replace ) );

		$html = strtr( $html, $replace );
		echo $html;

		exit;

	}


	private function create_new_template() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$this->ajax_filesystem();

		$content = mailster()->sanitize_content( stripslashes( $_POST['content'] ), null, ( isset( $_POST['head'] ) ? stripslashes( $_POST['head'] ) : null ) );

		$name = esc_attr( $_POST['name'] );
		$template = esc_attr( $_POST['template'] );
		$modules = ! ! ( $_POST['modules'] === 'true' );
		$activemodules = ! ! ( $_POST['activemodules'] === 'true' );
		$overwrite = $_POST['overwrite'] === 'false' ? false : $_POST['overwrite'];

		$t = mailster( 'template', $template );
		$filename = $t->create_new( $name, $content, $modules, $activemodules, $overwrite );

		if ( $return['success'] = $filename !== false ) {
			$return['url'] = add_query_arg( array( 'template' => $template, 'file' => $filename, 'message' => 3 ), mailster_get_referer() );
		}

		if ( ! $return['success'] ) {
			$return['msg'] = __( 'Unable to save template!', 'mailster' );
		}

		$this->json_return( $return );

	}


	private function toggle_codeview() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$head = isset( $_POST['head'] ) ? stripslashes( $_POST['head'] ) : null;
		$bodyattributes = isset( $_POST['bodyattributes'] ) ? stripslashes( $_POST['bodyattributes'] ) : '';
		$content = isset( $_POST['content'] ) ? '<body' . $bodyattributes . '>' . stripslashes( $_POST['content'] ) . '</body>' : null;

		$return['content'] = mailster()->sanitize_content( $content, null, $head );

		$this->json_return( $return );

	}


	private function set_preview() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$content = isset( $_POST['content'] ) ? stripslashes( $_POST['content'] ) : '';
		$ID = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$subject = isset( $_POST['subject'] ) ? stripslashes( $_POST['subject'] ) : '';
		$preheader = isset( $_POST['preheader'] ) ? stripslashes( $_POST['preheader'] ) : '';
		$issue = isset( $_POST['issue'] ) ? (int) $_POST['issue'] : 1;
		$head = isset( $_POST['head'] ) ? stripslashes( $_POST['head'] ) : null;
		$userid = isset( $_POST['userid'] ) ? (int) $_POST['userid'] : null;

		$html = mailster()->sanitize_content( $content, true, $head );

		$placeholder = mailster( 'placeholder', $html );

		$placeholder->set_campaign( $ID );

		$current_user = wp_get_current_user();

		if ( ! $userid ) {
			if ( $subscriber = mailster( 'subscribers' )->get_by_wpid( $current_user->ID, true ) ) {
				$userid = $subscriber->ID;
			}
		}

		if ( $userid ) {

			if ( $subscriber = mailster( 'subscribers' )->get( $userid, true ) ) {

				$userdata = mailster( 'subscribers' )->get_custom_fields( $subscriber->ID );

				$placeholder->set_subscriber( $subscriber->ID );
				$placeholder->add( $userdata );

				$names = array(
					'firstname' => $subscriber->firstname,
					'lastname' => $subscriber->lastname,
					'fullname' => $subscriber->fullname,
				);

			} else {

				$firstname = ( $current_user->user_firstname ) ? $current_user->user_firstname : $current_user->display_name;
				$names = array(
					'firstname' => $firstname,
					'lastname' => $current_user->user_lastname,
					'fullname' => mailster_option( 'name_order' ) ? trim( $current_user->user_lastname . ' ' . $firstname ) : trim( $firstname . ' ' . $current_user->user_lastname ),
				);
			}

			$placeholder->add( $names );

		}

		$placeholder->add_defaults( $ID, array(
			'issue' => $issue,
			'subject' => $subject,
			'preheader' => $preheader,
		) );

		$placeholder->add_custom( $ID, array(
			'emailaddress' => $current_user->user_email,
		) );

		$content = $placeholder->get_content();

		$content = str_replace( '@media only screen and (max-device-width:', '@media only screen and (max-width:', $content );

		$hash = md5( $content );

		// cache preview for 60 seconds
		set_transient( 'mailster_p_' . $hash, $content, 60 );

		$placeholder->set_content( $subject );
		$return['subject'] = $placeholder->get_content();
		$return['hash'] = $hash;
		$return['nonce'] = wp_create_nonce( 'mailster_nonce' );
		$return['success'] = true;

		$this->json_return( $return );

	}


	private function get_preview() {

		$this->ajax_nonce( );

		$hash = $_GET['hash'];

		$content = get_transient( 'mailster_p_' . $hash );

		if ( empty( $content ) ) {
			$content = 'error';
		}

		echo $content;
		exit;
	}


	private function send_test() {

		$this->ajax_nonce( json_encode( array(
			'success' => false,
			'msg' => __( 'Nonce invalid! Please reload site.', 'mailster' ),
		) ) );

		if ( isset( $_POST['test'] ) ) {

			if ( isset( $_POST['formdata'] ) ) {
				parse_str( $_POST['formdata'], $formdata );
				mailster_update_option( $formdata['mailster_options'], true );
			}

			$basic = ! ! ( $_POST['basic'] === 'true' );

			$to = stripslashes( $_POST['to'] );

			$n = mailster( 'notification' );
			$n->debug();
			$n->to( $to );
			$n->template( 'test' );
			$n->requeue( false );

			$return['success'] = $n->add();

			$mail = $n->mail;

			$return['log'] = $mail->get_error_log();

		} else {

			$return['success'] = true;
			parse_str( $_POST['formdata'], $formdata );

			$spam_test = isset( $_POST['spamtest'] );
			if ( $spam_test ) {
				$spam_check_id = uniqid();
				$receivers = apply_filters( 'mymail_spam_score_mail', apply_filters( 'mailster_spam_score_mail', 'mailster-' . $spam_check_id . '@check.newsletter-plugin.com' ) );
				if ( ! is_array( $receivers ) ) {
					$receivers = array( $receivers );
				}
			} else {
				$receivers = explode( ',', stripslashes( $_POST['to'] ) );
			}

			$subject = stripslashes( $formdata['mailster_data']['subject'] );
			$from = $formdata['mailster_data']['from_email'];
			$from_name = stripslashes( $formdata['mailster_data']['from_name'] );
			$reply_to = $formdata['mailster_data']['reply_to'];
			$embed_images = isset( $formdata['mailster_data']['embed_images'] );
			$track_opens = isset( $formdata['mailster_data']['track_opens'] );
			$track_clicks = isset( $formdata['mailster_data']['track_clicks'] );
			$head = stripslashes( $_POST['head'] );
			$content = stripslashes( $_POST['content'] );
			$preheader = stripslashes( $formdata['mailster_data']['preheader'] );
			$bouncemail = mailster_option( 'bounce' );
			$attachments = isset( $formdata['mailster_data']['attachments'] ) ? $formdata['mailster_data']['attachments'] : array();
			$max_size = apply_filters( 'mymail_attachments_max_filesize', apply_filters( 'mailster_attachments_max_filesize', 1024 * 1024 ) );

			$autoplain = isset( $formdata['mailster_data']['autoplaintext'] );
			$plaintext = stripslashes( $_POST['plaintext'] );
			// if ( function_exists( 'wp_encode_emoji' ) ) {
			// $subject = wp_decode_emoji( $subject );
			// $preheader = wp_decode_emoji( $preheader );
			// $from_name = wp_decode_emoji( $from_name );
			// }
			$MID = mailster_option( 'ID' );

			$ID = (int) $formdata['post_ID'];
			$issue = $formdata['mailster_data']['autoresponder']['issue'];

			$campaign_permalink = get_permalink( $ID );

			$attach = array();

			if ( ! empty( $attachments ) ) {
				$total_size = 0;
				foreach ( (array) $attachments as $attachment_id ) {
					if ( ! $attachment_id ) {
						continue;
					}
					$file = get_attached_file( $attachment_id );
					if ( ! @is_file( $file ) ) {
						continue;
					}
					$total_size += filesize( $file );
					if ( $total_size <= $max_size ) {
						$attach[ basename( $file ) ] = $file;
					} else {
						$receivers = array();
						$return['success'] = false;
						$return['msg'] = sprintf( __( 'Attachments must not exceed the file size limit of %s!', 'mailster' ), '<strong>' . esc_html( size_format( $max_size ) ) . '</strong>' );
					}
				}
			}

			foreach ( $receivers as $to ) {

				$current_user = null;
				$names = null;

				$mail = mailster( 'mail' );

				$mail->to = $to;
				$mail->subject = $subject;
				$mail->from = $from;
				$mail->from_name = $from_name;
				$mail->reply_to = $reply_to;
				$mail->bouncemail = $bouncemail;
				$mail->embed_images = $embed_images;
				$mail->hash = str_repeat( '0', 32 );

				$content = mailster()->sanitize_content( $content, null, $head );

				$placeholder = mailster( 'placeholder', $content );

				$mail->set_campaign( $ID );
				$placeholder->set_campaign( $ID );

				$unsubscribelink = mailster()->get_unsubscribe_link( $ID );

				$listunsubscribe = '';
				if ( $mail->bouncemail ) {
					$listunsubscribe_mail = $mail->bouncemail;
					$listunsubscribe_subject = 'Unsubscribe from ' . $mail->from;
					$listunsubscribe_body = "X-Mailster: $mail->hash\nX-Mailster-Campaign: $ID\nX-Mailster-ID: $MID\n\n";
					$listunsubscribe .= "<mailto:$listunsubscribe_mail?subject=$listunsubscribe_subject&body=$listunsubscribe_body>,";
				}
				$listunsubscribe .= '<' . $unsubscribelink . '>';

				$headers = array(
					'X-Mailster-Campaign' => $ID,
					'X-Mailster-ID' => $MID,
					'List-Unsubscribe' => $listunsubscribe,
					'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
				);

				if ( 'autoresponder' != get_post_status( $ID ) ) {
					$headers['Precedence'] = 'bulk';
				}

				$mail->add_header( apply_filters( 'mailster_mail_headers', $headers, $ID, null ) );

				// check for subscriber by mail
				$subscriber = mailster( 'subscribers' )->get_by_mail( $to, true );

				if ( $subscriber ) {

					$profilelink = mailster()->get_profile_link( $ID, $subscriber->hash );

					$userdata = mailster( 'subscribers' )->get_custom_fields( $subscriber->ID );

					$placeholder->set_subscriber( $subscriber->ID );
					$placeholder->add( $userdata );

					$names = array(
						'firstname' => $subscriber->firstname,
						'lastname' => $subscriber->lastname,
						'fullname' => $subscriber->fullname,
					);

					$mail->set_subscriber( $subscriber->ID );
					$placeholder->set_subscriber( $subscriber->ID );

				} elseif ( $current_user = wp_get_current_user() ) {

					$profilelink = mailster()->get_profile_link( $ID, '' );

					$firstname = ( $current_user->user_firstname ) ? $current_user->user_firstname : $current_user->display_name;
					$names = array(
						'firstname' => $firstname,
						'lastname' => $current_user->user_lastname,
						'fullname' => mailster_option( 'name_order' ) ? trim( $current_user->user_lastname . ' ' . $firstname ) : trim( $firstname . ' ' . $current_user->user_lastname ),
					);
				} else {
					// no subscriber found for data
					$names = null;
				}

				if ( $names ) {
					$placeholder->add( $names );
				}

				if ( ! empty( $attach ) ) {
					$mail->attachments = $attach;
				}

				$placeholder->add_defaults( $ID, array(
					'issue' => $issue,
					'subject' => $subject,
					'preheader' => $preheader,
				) );

				$placeholder->add_custom( $ID, array(
					'emailaddress' => $to,
				) );

				$content = $placeholder->get_content();
				$content = mailster( 'helper' )->prepare_content( $content );

				// replace links with fake hash to prevent tracking
				if ( $track_clicks ) {
					$content = mailster()->replace_links( $content, $mail->hash, $ID );
				}

				$mail->content = apply_filters( 'mailster_campaign_content', $content, get_post( $ID ), $subscriber );

				if ( ! $autoplain ) {
					$placeholder->set_content( esc_textarea( $plaintext ) );
					$mail->plaintext = mailster( 'helper' )->plain_text( $placeholder->get_content(), true );
				}

				$placeholder->set_content( $mail->subject );
				$mail->subject = $placeholder->get_content();

				$mail->add_tracking_image = $track_opens;

				if ( $spam_test ) {

					if ( false === ( $count = get_transient( '_mailster_spam_score_count' ) ) ) {

						$count = 0;
						set_transient( '_mailster_spam_score_count', $count, 3600 );
					}

					if ( $count < 10 ) {

						$return['success'] = $return['success'] && $mail->send();
						$return['id'] = $spam_check_id;
						update_option( '_transient__mailster_spam_score_count', ++$count );

					} else {

						$return['success'] = false;
						$return['msg'] = __( 'You can only perform 10 test within an hour. Please try again later!', 'mailster' );

					}
				} else {

					$return['success'] = $return['success'] && $mail->send();
				}

				$mail->close();
			}
		}

		if ( ! isset( $return['msg'] ) ) {
			$return['msg'] = ( $return['success'] )
				? __( 'Message sent. Check your inbox!', 'mailster' )
				: __( 'Couldn\'t send message. Check your settings!', 'mailster' ) . '<strong>' . $mail->get_errors( 'br' ) . '</strong>';
		}

		if ( isset( $return['log'] ) ) {
			$return['msg'] .= '<br>' . __( 'Check your console for more info.', 'mailster' );
		}

		$this->json_return( $return );

	}


	private function check_spam_score() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$id = isset( $_POST['ID'] ) ? $_POST['ID'] : false;

		if ( $id ) {

			$return = apply_filters( 'mymail_check_spam_score', apply_filters( 'mailster_check_spam_score', false, $id ), $id );

			if ( false === $return ) {

				$response = wp_remote_get( 'http://check.newsletter-plugin.com/' . $id, array(
					'sslverify' => false,
					'timeout' => 20,
				) );

				$code = wp_remote_retrieve_response_code( $response );

				if ( is_wp_error( $response ) ) {
					$return['msg'] = $response->get_error_message();
				} elseif ( 200 == $code ) {
					$body = json_decode( wp_remote_retrieve_body( $response ) );
					$return['score'] = $body->score;
				} elseif ( 503 == $code ) {
					$return['abort'] = true;
					$body = json_decode( wp_remote_retrieve_body( $response ) );
					$return['msg'] = $body->msg;
				} else {
					$return['abort'] = false;
					$body = json_decode( wp_remote_retrieve_body( $response ) );
					$return['msg'] = $body->msg;
				}
			}
		}

		$this->json_return( $return );

	}


	private function get_totals() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$campaign_ID = (int) $_POST['id'];
		$lists = ( $_POST['ignore_lists'] == 'true' ) ? false : ( isset( $_POST['lists'] ) ? $_POST['lists'] : array() );
		$conditions = isset( $_POST['conditions'] ) ? stripslashes_deep( array_values( array_filter( $_POST['conditions'] ) ) ) : false;
		$statuses = null;

		$return['success'] = true;
		$return['total'] = mailster( 'campaigns' )->get_totals_by_lists( $lists, $conditions, $statuses, $campaign_ID );
		$return['conditions'] = mailster( 'conditions' )->render( $conditions, false );
		$return['totalformatted'] = number_format_i18n( $return['total'] );

		$this->json_return( $return );

	}


	private function save_color_schema() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$colors = get_option( 'mailster_colors' );
		$hash = md5( implode( '', $_POST['colors'] ) );

		if ( ! isset( $colors[ $_POST['template'] ] ) ) {
			$colors[ $_POST['template'] ] = array();
		}

		$colors[ $_POST['template'] ][ $hash ] = $_POST['colors'];

		$return['html'] = '<ul class="colorschema custom" data-hash="' . $hash . '">';
		foreach ( $_POST['colors'] as $color ) {
			$return['html'] .= '<li class="colorschema-field" data-hex="' . $color . '" style="background-color:' . $color . '"></li>';
		}
		$return['html'] .= '<li class="colorschema-delete-field"><a class="colorschema-delete">&#10005;</a></li></ul>';

		$return['success'] = update_option( 'mailster_colors', $colors );

		$this->json_return( $return );

	}


	private function delete_color_schema() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$colors = get_option( 'mailster_colors' );

		$template = esc_attr( $_POST['template'] );

		if ( ! isset( $colors[ $template ] ) ) {
			$colors[ $template ] = array();
		}

		if ( isset( $colors[ $template ][ $_POST['hash'] ] ) ) {
			unset( $colors[ $template ][ $_POST['hash'] ] );
		}

		if ( empty( $colors[ $template ] ) ) {
			unset( $colors[ $template ] );
		}

		$return['success'] = update_option( 'mailster_colors', $colors );

		$this->json_return( $return );

	}


	private function delete_color_schema_all() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$colors = get_option( 'mailster_colors' );

		$template = esc_attr( $_POST['template'] );

		if ( isset( $colors[ $template ] ) ) {
			unset( $colors[ $template ] );
		}

		$return['success'] = update_option( 'mailster_colors', $colors );

		$this->json_return( $return );

	}


	private function get_clicks() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$campaign_ID = (int) $_POST['id'];

		$clicked_links = mailster( 'campaigns' )->get_clicked_links( $campaign_ID );
		$clicks_total = mailster( 'campaigns' )->get_clicks( $campaign_ID, true );

		$return['html'] = '<table class="wp-list-table widefat"><tbody>';

		$i = 1;
		foreach ( $clicked_links as $link => $indexes ) {
			foreach ( $indexes as $index => $counts ) {
				$return['html'] .= '<tr ' . ( ! ( $i % 2 ) ? ' class="alternate"' : '' ) . '><td>' . sprintf( _n( '%s click', '%s clicks', $counts['total'], 'mailster' ), $counts['total'] ) . ' ' . ( $counts['total'] != $counts['clicks'] ? '<span class="count">(' . sprintf( __( '%s unique', 'mailster' ), $counts['clicks'] ) . ')</span>' : '' ) . '</td><td>' . round( ( $counts['total'] / $clicks_total * 100 ), 2 ) . '%</td><td><a href="' . $link . '" class="external clicked-link">' . $link . '</a></td></tr>';
				$i++;
			}
		}

		$return['html'] .= '</tbody>';
		$return['html'] .= '</table>';

		$this->json_return( $return );

	}


	private function get_errors() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$timeformat = mailster( 'helper' )->timeformat();
		$timeoffset = mailster( 'helper' )->gmt_offset( true );

		$campaign_ID = (int) $_POST['id'];

		$errors = mailster( 'campaigns' )->get_error_list( $campaign_ID );

		$return['html'] = '<table class="wp-list-table widefat"><tbody>';

		foreach ( $errors as $i => $data ) {
			$return['html'] .= '<tr ' . ( ! ( $i % 2 ) ? ' class="alternate"' : '' ) . '><td class="textright">' . ( $i + 1 ) . '</td><td><a href="edit.php?post_type=newsletter&page=mailster_subscribers&ID=' . $data->ID . '">' . $data->email . '</a></td><td><span class="red">' . $data->errormsg . '</span></td><td>' . date( $timeformat, $data->timestamp + $timeoffset ) . '</td></tr>';
		}

		$return['html'] .= '</tbody>';
		$return['html'] .= '</table>';

		$this->json_return( $return );

	}


	private function get_environment() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$campaign_ID = (int) $_POST['id'];

		$clients = mailster( 'campaigns' )->get_clients( $campaign_ID );

		$return['html'] = '<table class="wp-list-table widefat"><tbody>';

		$i = 1;
		foreach ( $clients as $client ) {
			$return['html'] .= '<tr ' . ( ! ( $i % 2 ) ? ' class="alternate"' : '' ) . '><td class="client-type"><span class="mailster-icon client-' . $client['type'] . '"></span></td><td>' . $client['name'] . ' ' . $client['version'] . '</td><td>' . round( $client['percentage'] * 100, 2 ) . ' % <span class="count">(' . $client['count'] . ' ' . _n( 'opened', 'opens', $client['count'], 'mailster' ) . ')</span></td></tr>';
			$i++;
		}

		$return['html'] .= '</tbody>';
		$return['html'] .= '</table>';

		$this->json_return( $return );

	}


	private function get_geolocation() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$campaign_ID = (int) $_POST['id'];

		$geo_data = mailster( 'campaigns' )->get_geo_data( $campaign_ID );
		$totalopens = mailster( 'campaigns' )->get_opens( $campaign_ID );

		$unknown_cities = array();
		$countrycodes = array();

		foreach ( $geo_data as $countrycode => $data ) {
			$x = wp_list_pluck( $data, 3 );
			if ( $x ) {
				$countrycodes[ $countrycode ] = array_sum( $x );
			}

			if ( $data[0][3] ) {
				$unknown_cities[ $countrycode ] = $data[0][3];
			}
		}

		arsort( $countrycodes );
		$total = array_sum( $countrycodes );

		$return['geodata'] = $geo_data;
		$return['unknown_cities'] = $unknown_cities;
		$return['countrydata'] = array( array( 'code', __( 'Country', 'mailster' ), __( 'opens', 'mailster' ) ) );

		foreach ( $geo_data as $country => $cities ) {
			$opens = 0;
			foreach ( $cities as $city ) {
				$opens += $city[3];
			}
			$return['countrydata'][] = array( $country, mailster( 'geo' )->code2Country( $country ), $opens );
		}

		$return['html'] = '<div id="countries_wrap"><a class="zoomout button mailster-icon" title="' . __( 'back to world view', 'mailster' ) . '">&nbsp;</a><div id="countries_map"></div><div id="mapinfo"></div><div id="countries_table"><table class="wp-list-table widefat">
			<tbody>';

		$i = 0;
		$unknown = $totalopens - $total;

		foreach ( $countrycodes as $countrycode => $count ) {
			$data = $geo_data[ $countrycode ];
			$return['html'] .= '<tr data-code="' . $countrycode . '" id="country-row-' . $countrycode . '" class="' . ( ( ! ( $i % 2 ) ) ? ' alternate' : '' ) . '"><td width="20"><span class="mailster-flag-24 flag-' . strtolower( $countrycode ) . '"></span></td><td width="100%"><span class="country">' . mailster( 'geo' )->code2Country( $countrycode ) . '</span> <span class="count">(' . round( $count / $totalopens * 100, 2 ) . '%)</span></td><td class="textright">' . number_format_i18n( $count ) . '</td></tr>';
			$i++;
		}

		if ( $unknown ) :
			$return['html'] .= '<tr data-code="-" id="country-row-unknown" class="' . ( ( ! ( $i % 2 ) ) ? ' alternate' : '' ) . '"><td width="20"><span class="mailster-flag-24 flag-unknown"></span></td><td width="100%">' . __( 'unknown', 'mailster' ) . ' <span class="count">(' . round( $unknown / $totalopens * 100, 2 ) . '%)</span></td><td class="textright">' . number_format_i18n( $unknown ) . '</td></tr>';
		endif;

		$return['html'] .= '</tbody></table></div>';

		$this->json_return( $return );

	}


	private function get_recipients() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$campaign_ID = (int) $_POST['id'];

		$parts = ! empty( $_POST['types'] ) ? explode( ',', $_POST['types'] ) : array( 'unopen', 'opens', 'clicks', 'unsubs', 'bounces' );
		$orderby = ! empty( $_POST['orderby'] ) ? $_POST['orderby'] : 'sent';
		$order = ! isset( $_POST['order'] ) || $_POST['order'] == 'ASC' ? 'ASC' : 'DESC';

		$return['html'] = '<table class="wp-list-table widefat"><tbody>';

		$return['html'] = mailster( 'campaigns' )->get_recipients_part( $campaign_ID, $parts, 0, $orderby, $order );

		$return['html'] .= '</tbody>';
		$return['html'] .= '</table>';

		$this->json_return( $return );

	}


	private function get_recipients_page() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$campaign_ID = (int) $_POST['id'];
		$page = (int) $_POST['page'];

		$parts = ! empty( $_POST['types'] ) ? explode( ',', $_POST['types'] ) : array( 'unopen', 'opens', 'clicks', 'unsubs', 'bounces' );
		$orderby = ! empty( $_POST['orderby'] ) ? $_POST['orderby'] : 'sent';
		$order = ! isset( $_POST['order'] ) || $_POST['order'] == 'ASC' ? 'ASC' : 'DESC';

		$return['html'] = mailster( 'campaigns' )->get_recipients_part( $campaign_ID, $parts, $page, $orderby, $order );
		$return['success'] = true;

		$this->json_return( $return );

	}


	private function get_recipient_detail() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$subscriber_id = (int) $_POST['id'];
		$campaign_id = (int) $_POST['campaignid'];

		$return['html'] = mailster( 'subscribers' )->get_recipient_detail( $subscriber_id, $campaign_id );
		$return['success'] = ! ! $return['html'];

		$this->json_return( $return );

	}


	private function create_image() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		if ( isset( $_POST['id'] ) ) {

			$id = (int) $_POST['id'];
			$src = isset( $_POST['src'] ) ? ( $_POST['src'] ) : null;
			$crop = isset( $_POST['crop'] ) ? ( $_POST['crop'] == 'true' ) : false;
			$width = isset( $_POST['width'] ) ? (int) $_POST['width'] : null;
			$height = isset( $_POST['height'] ) && $crop ? (int) $_POST['height'] : null;

			$return['success'] = ! ! ( $return['image'] = mailster( 'helper' )->create_image( $id, $src, $width, $height, $crop ) );
		}

		$this->json_return( $return );

	}


	private function image_placeholder() {

		$factor = ! empty( $_GET['f'] ) ? (int) $_GET['f'] : 1;
		$width = $factor * ( ! empty( $_GET['w'] ) ? (int) $_GET['w'] : 600);
		$height = $factor * ( ! empty( $_GET['h'] ) ? (int) $_GET['h'] : round( $width / 1.6 ));
		$tag = isset( $_GET['tag'] ) ? '' . esc_attr( $_GET['tag'] ) . '' : '';

		$text = '{' . $tag . '}';
		$font_size = max( 11, round( $width / strlen( $text ) ) );
		// $font = MAILSTER_DIR . 'assets/font/OpenSans-Regular.ttf';
		$font = MAILSTER_DIR . 'assets/font/FredokaOne-Regular.ttf';

		$im = imagecreatetruecolor( $width, $height );

		$bg = imagecolorallocate( $im, 43, 179, 231 );
		$font_color = imagecolorallocate( $im, 255, 255, 255 );

		imagefilledrectangle( $im, 0, 0, $width, $height, $bg );

		if ( function_exists( 'imagettftext' ) ) {

			$bbox = imagettfbbox( $font_size, 0, $font, $text );

			$center_x = $width / 2 - ( abs( $bbox[4] - $bbox[0] ) / 2 );
			$center_y = $height / 2;

			imagettftext( $im, $font_size, 0, $center_x, $center_y, $font_color, $font, $text );

		} else {

			$font_size = 5;

			$fw = imagefontwidth( $font_size );
			$fh = imagefontheight( $font_size );
			$l = strlen( $text );
			$tw = $l * $fw;

			$center_x = ( $width - $tw ) / 2;
			$center_y = ( $height - $font_size ) / 2;

			imagestring( $im, $font_size, $center_x, $center_y, $text, $font_color );

		}

		header( 'Expires: Thu, 31 Dec 2050 23:59:59 GMT' );
		header( 'Cache-Control: max-age=3600, must-revalidate' );
		header( 'Pragma: cache' );
		header( 'Content-Type: image/gif' );

		imagegif( $im );

		imagedestroy( $im );

	}


	private function get_post_list() {

		$return['success'] = false;

		global $wp_post_statuses;
		$this->ajax_nonce( json_encode( $return ) );

		$offset = (int) $_POST['offset'];
		$post_count = mailster_option( 'post_count', 30 );
		$search = $_POST['search'];

		if ( in_array( $_POST['type'], array( 'post', 'attachment' ) ) ) {

			$post_type = esc_attr( $_POST['type'] );
			$current_id = isset( $_POST['id'] ) ? (int) $_POST['id'] : null;

			$defaults = array(
				'post_type' => $post_type,
				'numberposts' => $post_count,
				'suppress_filters' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'offset' => $offset,
				'orderby' => 'post_date',
				'order' => 'DESC',
				'exclude' => $current_id,
			);

			if ( $search ) {
				$defaults['s'] = $search;
			}

			if ( $post_type == 'post' ) {
				parse_str( $_POST['posttypes'], $pt );
				if ( isset( $pt['post_types'] ) ) {
					$post_types = (array) $pt['post_types'];
				} else {
					$post_types = array( -1 );
				}

				$args = wp_parse_args( array(
					'post_type' => $post_types,
					'post_status' => array( 'publish', 'future', 'draft' ),
				), $defaults );

				$post_counts = 0;
				foreach ( $post_types as $type ) {
					if ( $type == -1 ) {
						continue;
					}
					$counts = wp_count_posts( $type );
					$post_counts += $counts->publish + $counts->future;
				}
			} else {

				$args = wp_parse_args( array(
					'post_status' => 'inherit',
					'post_mime_type' => ( $post_type == 'attachment' ) ? array( 'image/jpeg', 'image/gif', 'image/png', 'image/tiff', 'image/bmp' ) : null,
				), $defaults );

				$post_counts = wp_count_posts( $post_type );
				$post_counts = $post_counts->inherit;

			}

			$return['success'] = true;
			$return['itemcount'] = isset( $_POST['itemcount'] ) ? $_POST['itemcount'] : array();

			$args = apply_filters( 'mailster_get_post_list_args', $args );

			$posts = get_posts( $args );

			if ( $current_id && ( $current = get_post( $current_id ) ) ) {

				array_unshift( $posts, $current );
				$post_counts++;

			} else {

				$args['exclude'] = null;

			}

			$relativenames = array(
				-1 => __( 'last %s', 'mailster' ),
				-2 => __( 'second last %s', 'mailster' ),
				-3 => __( 'third last %s', 'mailster' ),
				-4 => __( 'fourth last %s', 'mailster' ),
				-5 => __( 'fifth last %s', 'mailster' ),
				-6 => __( 'sixth last %s', 'mailster' ),
				-7 => __( 'seventh last %s', 'mailster' ),
				-8 => __( 'eighth last %s', 'mailster' ),
				-9 => __( 'ninth last %s', 'mailster' ),
				-10 => __( 'tenth last %s', 'mailster' ),
				-11 => __( 'eleventh last %s', 'mailster' ),
				-12 => __( 'twelfth last %s', 'mailster' ),
			);

			$posts_lefts = max( 0, $post_counts - $offset - $post_count );

			if ( $post_counts ) {
				$html = '';
				if ( $_POST['type'] == 'post' ) {

					$pts = mailster( 'helper' )->get_post_types( true, 'objects' );

					foreach ( $posts as $post ) {
						if ( ! isset( $return['itemcount'][ $post->post_type ] ) ) {
							$return['itemcount'][ $post->post_type ] = 0;
						}

						$relative = ( --$return['itemcount'][ $post->post_type ] );
						$hasthumb = ! ! ( $thumbid = get_post_thumbnail_id( $post->ID ) );
						$html .= '<li data-id="' . $post->ID . '" data-name="' . esc_attr( $post->post_title ) . '" class="status-' . $post->post_status . '';
						if ( $current_id == $post->ID ) {
							$html .= ' selected';
						}

						( $hasthumb )
							? $html .= ' has-thumb" data-thumbid="' . $thumbid . '"'
							: $html .= '"';
						$html .= ' data-link="' . get_permalink( $post->ID ) . '" data-type="' . $post->post_type . '" data-relative="' . $relative . '">';
						( $hasthumb )
							? $html .= get_the_post_thumbnail( $post->ID, array( 48, 48 ) )
							: $html .= '<div class="no-feature"></div>';
						$html .= '<span class="post-type">' . $pts[ $post->post_type ]->labels->singular_name . '</span>';
						$html .= '<strong>' . $post->post_title . '' . ( $post->post_status != 'publish' ? ' <em class="post-status wp-ui-highlight">' . $wp_post_statuses[ $post->post_status ]->label . '</em>' : '' ) . '</strong>';
						$html .= '<span class="excerpt">' . trim( wp_trim_words( strip_shortcodes( $post->post_content ), 25 ) ) . '</span>';
						$html .= '<span class="date">' . date_i18n( mailster( 'helper' )->dateformat(), strtotime( $post->post_date ) ) . '</span>';
						$html .= '</li>';
					}
				} elseif ( $_POST['type'] == 'attachment' ) {

					foreach ( $posts as $post ) {
						$image = wp_get_attachment_image_src( $post->ID, 'large' );
						$title = $post->post_title ? $post->post_title : ( $post->post_excerpt ? $post->post_excerpt : basename( $image[0] ) );
						$html .= '<li data-id="' . $post->ID . '" data-name="' . esc_attr( $title ) . '" data-src="' . esc_attr( $image[0] ) . '" data-asp="' . ( $image[2] ? str_replace( ',', '.', $image[1] / $image[2] ) : '' ) . '"';
						if ( $current_id == $post->ID ) {
							$html .= ' class="selected"';
						}

						$html .= '>';
						$image = wp_get_attachment_image_src( $post->ID, 'medium' );
						$html .= '<a style="background-image:url(' . $image[0] . ')"><span class="caption" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</span></a>';
						$html .= '</li>';
					}
				}

				if ( $posts_lefts ) {
					$html .= '<li><a class="load-more-posts" data-offset="' . ( $offset + $post_count ) . '" data-type="' . $_POST['type'] . '"><span>' . sprintf( __( 'load more entries (%s left)', 'mailster' ), number_format_i18n( $posts_lefts ) ) . '</span></a></li>';
				}

				$return['html'] = $html;
			} else {
				$return['html'] = '<li><span class="norows">' . __( 'no entries found', 'mailster' ) . '</span></li>';
			}
		} elseif ( $_POST['type'] == 'link' ) {

				$post_type = esc_attr( $_POST['type'] );

				$args = array();

				$post_counts = mailster( 'helper' )->link_query( array(
						'post_status' => array( 'publish', 'finished', 'queued', 'paused' ),
				), true );

				$posts_lefts = max( 0, $post_counts - $offset - $post_count );

				$results = mailster( 'helper' )->link_query( array(
						'offset' => $offset,
						'posts_per_page' => $post_count,
						'post_status' => array( 'publish', 'finished', 'queued', 'paused' ),
				) );

				$return['success'] = true;

			if ( isset( $results ) ) {
				$html = '';
				foreach ( $results as $entry ) {
					$hasthumb = ! ! ( $thumbid = get_post_thumbnail_id( $entry['ID'] ) );
					$html .= '<li data-id="' . $entry['ID'] . '" data-name="' . $entry['title'] . '"';
					if ( $hasthumb ) {
						$html .= ' data-thumbid="' . $thumbid . '" class="has-thumb"';
					}

					$html .= ' data-link="' . $entry['permalink'] . '">';
					( $hasthumb )
						? $html .= get_the_post_thumbnail( $entry['ID'], array( 48, 48 ) )
						: $html .= '<div class="no-feature"></div>';
					$html .= '<strong>' . $entry['title'] . '</strong>';
					$html .= '<span class="link">' . $entry['permalink'] . '</span>';
					$html .= '<span class="info">' . $entry['info'] . '</span>';
					$html .= '</li>';
				}
				if ( $posts_lefts ) {
					$html .= '<li><a class="load-more-posts" data-offset="' . ( $offset + $post_count ) . '" data-type="' . $post_type . '"><span>' . sprintf( __( 'load more entries (%s left)', 'mailster' ), number_format_i18n( $posts_lefts ) ) . '</span></a></li>';
				}

				$return['html'] = $html;

			} else {
				$return['html'] = '<li><span class="norows">' . __( 'no entries found', 'mailster' ) . '</span></li>';
			}
		} elseif ( $_POST['type'] == '_rss' ) {

			$url = esc_url( $_POST['url'] );

			include_once ABSPATH . WPINC . '/feed.php';

			$rss = fetch_feed( $url );

			if ( ! is_wp_error( $rss ) ) {

				$return['success'] = true;

				$maxitems = $rss->get_item_quantity( $post_count );

				$rss_items = $rss->get_items( $offset, $maxitems );
				$post_counts = count( $rss->get_items( $offset ) );

				$posts_lefts = max( 0, $post_counts - $offset - $post_count );

				$html = '';

				foreach ( $rss_items as $i => $item ) {
					$relative = 0;
					preg_match_all( '/<img[^>]*src="(.*?(?:\.png|\.jpg|\.gif))"[^>]*>/i', $item->get_content(), $images );
					$hasthumb = false;
					if ( ! empty( $images[0] ) ) {
						$hasthumb = $images[1][0];
					}
					$html .= '<li data-id="' . $url . '#' . ( $i + $offset ) . '" data-name="' . $item->get_title() . '"';
					if ( $hasthumb ) {
						$html .= ' data-thumbid="' . $hasthumb . '" class="has-thumb"';
					}

					$html .= ' data-link="' . $item->get_permalink() . '" data-type="rss-item" data-relative="' . $relative . '">';
					( $hasthumb )
						? $html .= '<div class="feature" style="background-image:url(' . $hasthumb . ')"></div>'
						: $html .= '<div class="no-feature"></div>';
					$html .= '<strong>' . $item->get_title() . '</strong>';
					$html .= '<span>' . trim( wp_trim_words( strip_shortcodes( $item->get_description() ), 18 ) ) . '</span>';
					$html .= '<span>' . date_i18n( mailster( 'helper' )->dateformat(), strtotime( $item->get_date() ) ) . '</span>';
					$html .= '</li>';
				}

				if ( $posts_lefts ) {
					$html .= '<li><a class="load-more-posts" data-offset="' . ( $offset + $post_count ) . '" data-type="_rss"><span>' . sprintf( __( 'load more entries (%s left)', 'mailster' ), number_format_i18n( $posts_lefts ) ) . '</span></a></li>';
				}

				$return['html'] = $html;

				$return['rssinfo'] = array(
					'copyright' => $rss->get_copyright() ? $rss->get_copyright() : sprintf( __( 'All rights reserved %s', 'mailster' ), '<a href="' . $rss->get_link() . '" class="external">' . $rss->get_title() . '</a>' ),
					'title' => $rss->get_title(),
					'description' => $rss->get_description(),
				);

				$recent_feeds = get_option( 'mailster_recent_feeds', array() );
				$recent_feeds = array_reverse( $recent_feeds );
				$recent_feeds[ $rss->get_title() ] = $url;
				$recent_feeds = array_reverse( $recent_feeds );
				$recent_feeds = array_slice( $recent_feeds, 0, 5 );
				update_option( 'mailster_recent_feeds', $recent_feeds );

			} else {

				$return['success'] = true;
				$return['html'] = '<li><span class="norows">' . $rss->get_error_message() . '</span></li>';
			}
		}

		$this->json_return( $return );

	}


	private function get_post() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$strip_shortcodes = apply_filters( 'mymail_strip_shortcodes', apply_filters( 'mailster_strip_shortcodes', true ) );

		if ( is_numeric( $_POST['id'] ) ) {
			$post = get_post( (int) $_POST['id'] );
			$expects = isset( $_POST['expect'] ) ? (array) $_POST['expect'] : array();

			if ( $post ) {
				if ( ! $post->post_excerpt ) {
					if ( preg_match( '/<!--more(.*?)?-->/', $post->post_content, $matches ) ) {
						$content = explode( $matches[0], $post->post_content, 2 );
						$post->post_excerpt = trim( $content[0] );
					}
					if ( ! $post->post_excerpt ) {
						$post->post_excerpt = mailster( 'helper' )->get_excerpt( $post->post_content );
					}
				}

				if ( $length = apply_filters( 'mailster_excerpt_length', false ) ) {
					$post->post_excerpt = wp_trim_words( $post->post_excerpt, $length );
				}
				$post->post_excerpt = apply_filters( 'the_excerpt', $post->post_excerpt );
				$link = get_permalink( $post->ID );

				$content = wpautop( $post->post_content );

				if ( ! empty( $post->post_excerpt ) ) {
					$excerpt = wpautop( $post->post_excerpt );
				} else {
					$excerpt = mailster( 'helper' )->get_excerpt( $content );
				}

				$image = null;
				if ( has_post_thumbnail( $post->ID ) ) {
					$image = array(
						'id' => get_post_thumbnail_id( $post->ID ),
						'name' => $post->post_title,
					);
				}

				$content = str_replace( '<img ', '<img editable ', $content );
				$content = ( $strip_shortcodes ) ? strip_shortcodes( $content ) : do_shortcode( $content );

				$excerpt = ( $strip_shortcodes ) ? strip_shortcodes( $excerpt ) : do_shortcode( $excerpt );

				$data = array(
					'title' => $post->post_title,
					'alt' => $post->post_title,
					'content' => $content,
					'excerpt' => $excerpt,
					'link' => get_permalink( $post->ID ),
					'image' => $image,
				);

				foreach ( $expects as $expect ) {
					if ( isset( $data[ $expect ] ) ) {
						continue;
					}
					$data[ $expect ] = mailster( 'placeholder' )->get_replace( $post, $expect );
				}

				$return['pattern'] = apply_filters( 'mymail_auto_post', apply_filters( 'mailster_auto_post', $data, $post ), $post );
				$return['success'] = true;

			}
		} else {

			$url = explode( '#', esc_url( $_POST['id'] ) );
			$id = (int) array_pop( $url );
			$url = implode( '#', $url );
			$rss = fetch_feed( $url );
			$expects = isset( $_POST['expect'] ) ? (array) $_POST['expect'] : array();

			if ( ! is_wp_error( $rss ) ) {

				$item = $rss->get_item( $id );

				$content = mailster()->sanitize_content( $item->get_content() );
				$excerpt = mailster()->sanitize_content( $item->get_description() );

				$content = strip_shortcodes( wpautop( $content, false ) );
				$excerpt = strip_shortcodes( wpautop( $excerpt ? $excerpt : substr( $content, 0, strpos( $content, '<!--more-->' ) ), false ) );

				$image = null;
				preg_match_all( '/<img[^>]*src="(.*?(?:\.png|\.jpg|\.gif))"[^>]*>/i', $content, $images );
				if ( ! empty( $images[0] ) ) {
					$image = array(
						'src' => $images[1][0],
						'name' => $item->get_title(),
					);
					// remove that image
					$content = str_replace( $images[0][0], '', $content );
					$excerpt = str_replace( $images[0][0], '', $excerpt );
				}

				preg_match_all( '/<img[^>]*src="[^"]+(\.feedsportal|\.feedburner)[^"]*"[^>]*>/i', $content, $remove_images );
				if ( ! empty( $remove_images[0] ) ) {
					foreach ( $remove_images as $i => $remove_image ) {
						$content = str_replace( $remove_images[0][ $i ], '', $content );
					}
				}

				$content = preg_replace( array( '/class=".*?"/', '/id=".*?"/', '/style=".*?"/' ), '', $content );
				$content = str_replace( '<img ', '<img editable ', $content );
				$excerpt = preg_replace( array( '/class=".*?"/', '/id=".*?"/', '/style=".*?"/' ), '', $excerpt );

				$data = array(
					'title' => $item->get_title(),
					'alt' => $item->get_title(),
					'content' => $content,
					'excerpt' => $excerpt,
					'link' => $item->get_permalink(),
					'image' => $image,
				);

				foreach ( $expects as $expect ) {
					if ( isset( $data[ $expect ] ) ) {
						continue;
					}
					$data[ $expect ] = method_exists( $item, 'get_' . $expect ) ? $item->{'get_' . $expect}() : '';
				}

				$return['pattern'] = apply_filters( 'mymail_auto_rss', apply_filters( 'mailster_auto_rss', $data, $url ), $url );
				$return['success'] = true;

			}
		}

		$this->json_return( $return );

	}


	private function check_for_posts() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$campaign_id = (int) $_POST['id'];
		$post_type = sanitize_key( $_POST['post_type'] );
		$relative = (int) $_POST['relative'];
		$offset = $relative + 1;
		$term_ids = isset( $_POST['extra'] ) ? (array) $_POST['extra'] : array();
		$modulename = isset( $_POST['modulename'] ) ? $_POST['modulename'] : null;
		$expects = isset( $_POST['expect'] ) ? (array) $_POST['expect'] : array();
		$args = array();

		$post = mailster()->get_last_post( $offset, $post_type, $term_ids, $args, $campaign_id );
		$is_post = ! ! $post;

		$return['title'] = $is_post
			? '<a href="post.php?post=' . $post->ID . '&action=edit" class="external">#' . $post->ID . ' &ndash; ' . ( $post->post_title ? $post->post_title : __( 'no title', 'mailster' ) ) . '</a>'
			: __( 'no match for your selection!', 'mailster' ) . ' <a href="post-new.php?post_type=' . $post_type . '" class="external">' . __( 'create a new one', 'mailster' ) . '</a>?';

		$options = $relative . ( ! empty( $term_ids ) ? ';' . implode( ';', $term_ids ) : '' );

		$pattern = array(
			'title' => '{' . $post_type . '_title:' . $options . '}',
			'alt' => '{' . $post_type . '_title:' . $options . '}',
			'content' => '{' . $post_type . '_content:' . $options . '}',
			'excerpt' => '{' . $post_type . '_excerpt:' . $options . '}',
			'link' => '{' . $post_type . '_link:' . $options . '}',
			'image' => '{' . $post_type . '_image:' . $options . '}',
		);

		foreach ( $expects as $expect ) {
			if ( isset( $pattern[ $expect ] ) ) {
				continue;
			}
			$pattern[ $expect ] = '{' . $post_type . '_' . $expect . ':' . $options . '}';
		}

		$return['pattern'] = apply_filters( 'mymail_auto_tag', apply_filters( 'mailster_auto_tag', $pattern, $post_type, $options, $post, $modulename ), $post_type, $options, $post, $modulename );

		$return['pattern']['tag'] = '{' . $post_type . ':' . $options . '}';

		$return['success'] = true;

		$this->json_return( $return );

	}


	private function get_post_term_dropdown() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$post_type = $_POST['posttype'];
		$labels = isset( $_POST['labels'] ) ? ( $_POST['labels'] == 'true' ) : false;
		$names = isset( $_POST['names'] ) ? $_POST['names'] : false;

		$return['html'] = '<div class="dynamic_embed_options_taxonomies">' . mailster( 'helper' )->get_post_term_dropdown( $post_type, $labels, $names ) . '</div>';
		$return['success'] = true;

		$this->json_return( $return );

	}


	private function forward_message() {
		$return['success'] = false;

		parse_str( $_POST['data'], $data );

		if ( ! wp_verify_nonce( $data['_wpnonce'], $data['url'] ) ) {
			die( json_encode( $return ) );
		}

		if ( empty( $data['message'] ) || ! mailster_is_email( $data['receiver'] ) || ! mailster_is_email( $data['sender'] ) || empty( $data['sendername'] ) ) {

			$return['msg'] = __( 'Please fill out all fields correctly!', 'mailster' );

			$this->json_return( $return );

		}

		$mail = mailster( 'mail' );
		$mail->to = esc_attr( $data['receiver'] );
		$mail->subject = esc_attr( '[' . get_bloginfo( 'name' ) . '] ' . sprintf( __( '%s is forwarding an email to you!', 'mailster' ), $data['sendername'] ) );
		// $mail->from = esc_attr($data['sender']);
		$mail->from = mailster_option( 'from' );
		$mail->from_name = sprintf( _x( '%1$s via %2$s', 'user forwarded via website', 'mailster' ), $data['sendername'], get_bloginfo( 'name' ) );

		$message = nl2br( $data['message'] ) . '<br><br>' . $data['url'];

		$replace = array(
			'notification' => sprintf( __( '%1$s is forwarding this mail to you via %2$s', 'mailster' ), $data['sendername'] . ' (<a href="mailto:' . esc_attr( $data['sender'] ) . '">' . esc_attr( $data['sender'] ) . '</a>)', '<a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'name' ) . '</a>' ),
		);

		$return['success'] = $mail->send_notification( $message, $mail->subject, $replace );

		$return['msg'] = ( $return['success'] ) ? __( 'Your message was sent successfully!', 'mailster' ) : __( 'Sorry, we couldn\'t deliver your message. Please try again later!', 'mailster' );

		$this->json_return( $return );

	}


	private function remove_notice() {

		$return['success'] = false;

		global $mailster_notices;

		if ( $mailster_notices = get_option( 'mailster_notices' ) ) {

			if ( isset( $_GET['id'] ) && isset( $mailster_notices[ $_GET['id'] ] ) ) {

				unset( $mailster_notices[ $_GET['id'] ] );

				update_option( 'mailster_notices', $mailster_notices );

			}

			$return['success'] = true;

		}

		$this->json_return( $return );

	}


	/**
	 *
	 *
	 * @param unknown $return (optional)
	 * @param unknown $nonce  (optional)
	 */
	private function ajax_nonce( $return = null, $nonce = 'mailster_nonce' ) {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $nonce ) ) {
			if ( is_null( $return ) ) {
				$return = __( 'Your nonce is expired! Please reload the site.', 'mailster' );
			}
			if ( is_string( $return ) ) {
				wp_die( $return );
			} else {
				die( $return );
			}
		}

	}


	private function get_file_list() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$return['slug'] = $_POST['slug'];

		$return['files'] = mailster( 'templates' )->get_files( $return['slug'] );

		if ( count( $return['files'] ) ) {
			$return['success'] = true;
			$return['base'] = trailingslashit( mailster( 'templates' )->get_url() ) . $return['slug'];
			foreach ( $return['files'] as $file => $data ) {
				$return['files'][ $file ]['screenshot'] = mailster( 'templates' )->get_screenshot( $return['slug'], $file );
			}
		}

		$this->json_return( $return );
	}


	private function get_template_html() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$return['slug'] = dirname( $_POST['href'] );
		$return['file'] = basename( $_POST['href'] );
		$file = mailster( 'templates' )->get_path() . '/' . $return['slug'] . '/' . $return['file'];

		$return['files'] = mailster( 'templates' )->get_files( $return['slug'], true );

		if ( file_exists( $file ) ) {
			$return['success'] = ! ! $return['html'] = @file_get_contents( $file );
		}

		$this->json_return( $return );
	}


	private function set_template_html() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$this->ajax_filesystem();

		$return['slug'] = esc_attr( $_POST['slug'] );
		$return['file'] = esc_attr( $_POST['file'] );
		$new = ! empty( $_POST['name'] );

		$name = $new ? esc_attr( $_POST['name'] ) : $return['file'];
		$content = stripslashes( $_POST['content'] );
		$filename = false;

		if ( $new ) {
			$data = mailster( 'templates' )->get_template_data( $content );

			$content = preg_replace( '#^(\s)?<!--(.*)-->\n(\s)?#sUm', '', $content );

			$filename = mailster( 'template', $return['slug'] )->create_new( $name, $content );

		} else {

			global $wp_filesystem;
			mailster_require_filesystem();
			$path = mailster( 'templates', $return['slug'] )->get_path();
			$file = $path . '/' . $return['slug'] . '/' . $return['file'];

			$content = mailster()->sanitize_content( $content, false, null, true );

			if ( $wp_filesystem->put_contents( $file, $content, FS_CHMOD_FILE ) ) {
				$filename = $file;
			}
		}

		if ( $filename ) {
			$file = basename( $filename );
			if ( $new ) {
				$return['newfile'] = $file;
			}

			$return['msg'] = __( 'File has been saved!', 'mailster' );
			$return['success'] = true;
			wp_remote_get( mailster( 'templates' )->get_screenshot( $return['slug'], $file ) );
		} else {
			$return['msg'] = __( 'Not able to save file!', 'mailster' );
		}

		$this->json_return( $return );
	}


	private function remove_template() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$path = mailster( 'templates' )->get_path();

		$file = $path . '/' . esc_attr( $_POST['file'] );

		if ( file_exists( $file ) && current_user_can( 'mailster_delete_templates' ) ) {
			mailster_require_filesystem();

			global $wp_filesystem;

			$return['success'] = $wp_filesystem->delete( $file );
		}

		$this->json_return( $return );
	}


	private function notice_dismiss() {
		$return['success'] = true;

		if ( isset( $_POST['id'] ) ) {
			mailster_remove_notice( $_POST['id'] );
		}

		$this->json_return( $return );
	}


	private function notice_dismiss_all() {
		$return['success'] = true;

		update_option( 'mailster_notices', array() );

		$this->json_return( $return );
	}


	private function ajax_filesystem() {
		if ( 'ftpext' == get_filesystem_method() && ! defined( 'FTP_HOST' ) && ! defined( 'FTP_USER' ) && ! defined( 'FTP_PASS' ) ) {
			$return['msg'] = __( 'WordPress is not able to access to your filesystem!', 'mailster' );
			$return['msg'] .= "\n" . sprintf( __( 'Please add following lines to the wp-config.php %s', 'mailster' ), "\n\ndefine('FTP_HOST', 'your-ftp-host');\ndefine('FTP_USER', 'your-ftp-user');\ndefine('FTP_PASS', 'your-ftp-password');\n" );
			$return['success'] = false;

			$this->json_return( $return );
		}

	}


	private function load_geo_data() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		if ( mailster( 'geo' )->update( true ) ) {
			$return['success'] = true;
			$return['update'] = esc_html__( 'Last update', 'mailster' ) . ': ' . esc_html__( 'right now', 'mailster' );
			$return['msg'] = __( 'Location Database success loaded!', 'mailster' );
		} else {
			$return['msg'] = __( 'Couldn\'t load Location Database', 'mailster' );
		}

		$this->json_return( $return );

	}


	private function sync_all_subscriber() {

		$return['success'] = false;
		$limit = 100;
		$offset = isset( $_POST['offset'] ) ? (int) $_POST['offset'] : 0;

		$return['count'] = mailster( 'subscribers' )->sync_all_subscriber( $limit, $offset );
		$return['success'] = true;
		$return['offset'] = $limit + $offset;

		$this->json_return( $return );

	}


	private function sync_all_wp_user() {

		$return['success'] = false;
		$limit = 100;
		$offset = isset( $_POST['offset'] ) ? (int) $_POST['offset'] : 0;

		$return['count'] = mailster( 'subscribers' )->sync_all_wp_user( $limit, $offset );
		$return['success'] = true;
		$return['offset'] = $limit + $offset;

		$this->json_return( $return );

	}


	private function bounce_test() {

		$return['success'] = false;

		if ( isset( $_POST['formdata'] ) ) {
			parse_str( $_POST['formdata'], $formdata );
			mailster_update_option( $formdata['mailster_options'], true );
		}

		$identifier = 'mailster_bonuce_test_' . md5( uniqid() );

		$return['identifier'] = $identifier;

		$mail = mailster( 'mail' );
		$mail->to = mailster_option( 'bounce' );
		$mail->subject = 'Mailster Bounce Test Mail';
		$mail->add_header( 'X-Mailster-Bounce-Identifier', $identifier );

		$replace = array( 'preheader' => 'You can delete this message!', 'notification' => 'This message was sent from your WordPress blog to test your bounce server. You can delete this message!' );

		$return['success'] = $mail->send_notification( $identifier, $mail->subject, $replace );

		$this->json_return( $return );

	}


	private function bounce_test_check() {

		$return['success'] = false;
		$return['msg'] = '';

		if ( isset( $_POST['formdata'] ) ) {
			parse_str( $_POST['formdata'], $formdata );
			mailster_update_option( $formdata['mailster_options'], true );
		}

		$passes = (int) $_POST['passes'];
		$identifier = $_POST['identifier'];

		$return['success'] = true;
		$return['msg'] = __( 'checking for new messages', 'mailster' ) . str_repeat( '.', $passes );

		$result = mailster( 'bounce' )->test( $identifier );

		if ( $result ) {

			$return['complete'] = true;
			if ( is_wp_error( $result ) ) {

				$return['msg'] = $result->get_error_message();

			} else {

				$return['complete'] = true;
				$return['msg'] = __( 'Your bounce server is good!', 'mailster' );
			}
		} elseif ( $passes > 20 ) {

				$return['complete'] = true;
				$return['msg'] = __( 'Unable to get test message! Please check your settings.', 'mailster' );

		}

		$this->json_return( $return );

	}


	private function get_system_info() {

		$return['success'] = false;
		$return['msg'] = 'You have no permission to access the stats';

		$this->ajax_nonce( json_encode( $return ) );

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->json_return( $return );
		}

		$space = 30;
		$infos = mailster( 'settings' )->get_system_info( $space );
		$output = "### Begin System Info ###\n\n";
		$output .= "## Please include this information when posting support requests ##\n\n";

		foreach ( $infos as $name => $value ) {
			if ( $value == '--' ) {
				$output .= "\n";
				continue;
			}
			$output .= $name . str_repeat( ' ', $space - strlen( $name ) ) . $value . "\n";
		}

		$output .= "### End System Info ###\n";

		$return['msg'] = $output;

		$this->json_return( $return );

	}


	private function get_gravatar() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$email = esc_attr( $_POST['email'] );

		if ( get_option( 'show_avatars' ) ) {
			$return['success'] = true;
			$return['url'] = mailster( 'subscribers' )->get_gravatar_uri( $email, 400 );
		} else {
			$return['url'] = null;
		}

		$this->json_return( $return );

	}


	private function check_email() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$email = esc_attr( $_POST['email'] );

		$subscriber = mailster( 'subscribers' )->get_by_mail( $email );
		$return['exists'] = ! ! $subscriber && $subscriber->ID != (int) $_POST['id'];
		$return['success'] = true;

		$this->json_return( $return );

	}


	private function create_list() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$name = stripslashes( $_POST['name'] );
		$campaign_id = (int) $_POST['id'];
		$listtype = $_POST['listtype'];

		$return['success'] = mailster( 'campaigns' )->create_list_from_option( $name, $campaign_id, $listtype );
		$return['msg'] = $return['success'] ? __( 'List has been created', 'mailster' ) : __( 'Couldn\'t create List', 'mailster' );

		$this->json_return( $return );

	}


	private function get_create_list_count() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$campaign_id = (int) $_POST['id'];
		$listtype = esc_attr( $_POST['listtype'] );

		$return['count'] = mailster( 'campaigns' )->create_list_from_option( '', $campaign_id, $listtype, true );
		$return['success'] = true;

		$this->json_return( $return );

	}


	private function get_subscriber_count() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		parse_str( $_POST['data'], $data );

		$lists = isset( $data['lists'] ) ? (array) $data['lists'] : -1;
		$conditions = isset( $data['conditions'] ) ? array_values( $data['conditions'] ) : false;
		$status = isset( $data['status'] ) ? (array) $data['status'] : false;

		$args = array(
			'return_count' => true,
			'lists' => $lists,
			'status' => $status,
			'conditions' => $conditions,
		);

		$return['count'] = mailster( 'subscribers' )->query( $args );

		$return['success'] = true;

		$this->json_return( $return );

	}


	private function editor_image_upload_handler() {

		global $wpdb;

		$memory_limit = @ini_get( 'memory_limit' );
		$max_execution_time = @ini_get( 'max_execution_time' );

		$return['success'] = false;

		@set_time_limit( 0 );

		if ( (int) $max_execution_time < 300 ) {
			@ini_set( 'max_execution_time', 300 );
		}
		if ( (int) $memory_limit < 256 ) {
			@ini_set( 'memory_limit', '256M' );
		}

		if ( isset( $_FILES['async-upload'] ) ) {

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$width = (int) $_POST['width'];
			$height = (int) $_POST['height'];
			$factor = (int) $_POST['factor'];

			$wp_upload_dir = wp_upload_dir();
			$image = false;

			$filename = $_FILES['async-upload']['name'];

			if ( file_exists( $wp_upload_dir['path'] . '/' . $filename ) &&
				md5_file( $_FILES['async-upload']['tmp_name'] ) == md5_file( $wp_upload_dir['path'] . '/' . $filename ) ) {

				$url = $wp_upload_dir['url'] . '/' . $filename;
				if ( $attach_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid = %s;", $url ) ) ) {
					$image = mailster( 'helper' )->create_image( $attach_id, null, $width, null, false );
				}
			}

			if ( ! $image ) {

				$result = wp_handle_upload( $_FILES['async-upload'], array(
					'test_form' => false,
					'mimes' => array(
						'jpeg' => 'image/jpeg',
						'jpg' => 'image/jpeg',
						'png' => 'image/png',
						'tiff' => 'image/tiff',
						'tif' => 'image/tiff',
						'gif' => 'image/gif',
					),
				) );

				$filename = basename( $result['file'] );
				$filetype = wp_check_filetype( $filename, null );

				// don't add to library if alt key is pressed
				$add_to_library = ! ( $_POST['altKey'] == 'true' );

				if ( $add_to_library ) {

					$post_id = isset( $_POST['ID'] ) ? (int) $_POST['ID'] : 0;

					$attachment = array(
						'guid' => $wp_upload_dir['url'] . '/' . $filename,
						'post_mime_type' => $filetype['type'],
						'post_title' => preg_replace( '/\.[^.]+$/', '', $filename ),
						'post_content' => '',
						'post_status' => 'inherit',
						'post_parent' => $post_id,
					);
					$attach_id = wp_insert_attachment( $attachment, $result['file'] );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $result['file'] );
					wp_update_attachment_metadata( $attach_id, $attach_data );

					$image = mailster( 'helper' )->create_image( $attach_id, null, $width, null, false );

				} else {

					$image = mailster( 'helper' )->create_image( null, $result['file'], $width, null, false );

				}
			}

			if ( $image ) {
				$return['image'] = $image;
				$return['success'] = true;
			}
		}

		if ( isset( $return ) ) {

			$this->json_return( $return );

		}

	}


	private function template_upload_handler() {

		global $wpdb;

		if ( ! current_user_can( 'mailster_upload_templates' ) ) {
			die( 'not allowed' );
		}

		$memory_limit = @ini_get( 'memory_limit' );
		$max_execution_time = @ini_get( 'max_execution_time' );

		$return['success'] = false;

		@set_time_limit( 0 );

		if ( (int) $max_execution_time < 300 ) {
			@ini_set( 'max_execution_time', 300 );
		}
		if ( (int) $memory_limit < 256 ) {
			@ini_set( 'memory_limit', '256M' );
		}

		if ( isset( $_FILES['async-upload'] ) ) {

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$result = wp_handle_upload( $_FILES['async-upload'], array(
				'test_form' => false,
				'test_type' => false,
				'mimes' => array( 'zip' => 'multipart/x-zip' ),
			) );

			if ( isset( $result['error'] ) ) {

				$return['error'] = $result['error'];

			} else {

				$result = mailster( 'templates' )->unzip_template( $result['file'] );

				if ( is_wp_error( $result ) ) {

					$return['error'] = $result->get_error_message();

				} else {

					mailster_notice( sprintf( __( 'Template %s has been uploaded', 'mailster' ), '"' . $result['name'] . ' ' . $result['version'] . '"' ), 'success', true );
					$return['success'] = true;
				}
			}
		}

		if ( isset( $return ) ) {

			$this->json_return( $return );

		}

	}


	private function get_dashboard_data() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$type = $_POST['type'];
		$id = (int) $_POST['id'];

		switch ( $type ) {
			case 'campaigns':
				if ( $campaign = mailster( 'campaigns' )->get( $id ) ) {
					$data = array(
						'name' => $campaign->post_title,
						'status' => $campaign->post_status,
						'ID' => $campaign->ID,
						'totals' => mailster( 'campaigns' )->get_totals( $id ),
						'totals_formatted' => number_format_i18n( mailster( 'campaigns' )->get_totals( $id ) ),
						'sent' => mailster( 'campaigns' )->get_sent( $id ),
						'sent_formatted' => number_format_i18n( mailster( 'campaigns' )->get_sent( $id ) ),
						'openrate' => mailster( 'campaigns' )->get_open_rate( $id ),
						'clickrate' => mailster( 'campaigns' )->get_click_rate( $id ),
						'bouncerate' => mailster( 'campaigns' )->get_bounce_rate( $id ),
						'unsubscriberate' => mailster( 'campaigns' )->get_unsubscribe_rate( $id ),
					);
					$return['data'] = $data;
					$return['success'] = true;
				}
			break;
			case 'lists':
				if ( $list = mailster( 'lists' )->get( $id ) ) {
					$data = array(
						'name' => $list->name,
						'ID' => $list->ID,
						'totals' => mailster( 'lists' )->get_totals( $id ),
						'totals_formatted' => number_format_i18n( mailster( 'lists' )->get_totals( $id ) ),
						'sent' => mailster( 'lists' )->get_sent( $id ),
						'sent_formatted' => number_format_i18n( mailster( 'lists' )->get_sent( $id ) ),
						'openrate' => mailster( 'lists' )->get_open_rate( $id ),
						'clickrate' => mailster( 'lists' )->get_click_rate( $id ),
						'bouncerate' => mailster( 'lists' )->get_bounce_rate( $id ),
						'unsubscriberate' => mailster( 'lists' )->get_unsubscribe_rate( $id ),
					);
					$return['data'] = $data;
					$return['success'] = true;
				}
			break;

			default:
			break;
		}

		$this->json_return( $return );
	}


	private function get_dashboard_chart() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );
		$range = isset( $_POST['range'] ) ? $_POST['range'] : '7 days';
		$return['chart'] = mailster( 'stats' )->get_dashboard( $range );

		$return['success'] = true;

		$this->json_return( $return );
	}


	private function check_language() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$return['language'] = mailster( 'translations' )->get_translation_data( true );

		if ( $return['language'] ) {

			if ( $return['language']['current'] ) {
				$return['html'] = __( 'An update to the Mailster translation is available!', 'mailster' );
			} else {
				$return['html'] = __( 'Mailster is available in your language!', 'mailster' );
			}
			$return['html'] .= ' <a class="load-language" href="#">' . __( 'load it', 'mailster' ) . '</a>';

		} elseif ( null === $return['language'] && get_locale() != 'en_US' ) {
				$return['html'] = __( 'Mailster is not available in your languages!', 'mailster' );

		} else {
			$return['html'] = '';
		}

		$return['success'] = true;

		$this->json_return( $return );
	}


	private function load_language() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		if ( $return['success'] = mailster( 'translations' )->download_language() ) {
			$return['html'] = __( 'Language as been loaded successfully.', 'mailster' ) . ' ' . __( 'reloading', 'mailster' ) . '&hellip;';
		} else {
			$return['html'] = __( 'Couldn\'t load language file. Please try again later.', 'mailster' );
		}

		$this->json_return( $return );
	}


	private function register() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ), 'mailster_register' );
		$purchasecode = trim( $_POST['purchasecode'] );
		$slug = trim( $_POST['slug'] );

		if ( empty( $purchasecode ) ) {
			$return['error'] = __( 'Please enter your Purchase Code!', 'mailster' );

		} elseif ( isset( $_POST['data'] ) ) {

			parse_str( $_POST['data'], $userdata );

			if ( empty( $userdata['email'] ) ) {
				$return['error'] = __( 'Please enter your email address', 'mailster' );
			} elseif ( ! isset( $userdata['tos'] ) ) {
				$return['error'] = __( 'You have to accept the terms of service.', 'mailster' );
			} else {
				$result = UpdateCenterPlugin::register( $slug, $userdata, $purchasecode );

				if ( is_wp_error( $result ) ) {
					$return['error'] = mailster()->get_update_error( $result );
					$return['code'] = $result->get_error_code();

				} else {
					update_option( 'mailster_username', $result['username'] );
					update_option( 'mailster_email', $result['email'] );
					update_option( 'mailster_tos_accepted', $userdata['tos'] );

					do_action( 'mailster_register', $result['username'], $result['email'], $purchasecode );
					do_action( 'mailster_register_' . $slug, $result['username'], $result['email'], $purchasecode );

					$return['username'] = $result['username'];
					$return['email'] = $result['email'];
					$return['purchasecode'] = $purchasecode;
					$return['success'] = true;
				}
			}
		} else {
			$result = UpdateCenterPlugin::verify( $slug, $purchasecode );
			if ( is_wp_error( $result ) && 681 != $result->get_error_code() ) {
				$return['error'] = mailster()->get_update_error( $result );
				$return['code'] = $result->get_error_code();
			} else {
				$return['success'] = true;
			}
		}

		$this->json_return( $return );
	}


	private function envato_verify() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		if ( isset( $_GET['email'] ) ) {

			$args = array( trim( $_GET['slug'] ), trim( $_GET['purchasecode'] ), trim( $_GET['username'] ), trim( $_GET['email'] ) );

			?><script>window.opener.verifymailster('<?php echo implode( "','", $args ) ?>');window.close();</script><?php

			exit;

		} else {

			$slug = $_GET['slug'];

			$url = UpdateCenterPlugin::get( $slug, 'remote_url' );

			$url = add_query_arg( array(
				'envato-signup' => 1,
				'slug' => $slug,
				'token' => wp_create_nonce( 'mailster_nonce' ),
				'redirect' => add_query_arg( array(
					'action' => 'mailster_envato_verify',
					'_wpnonce' => wp_create_nonce( 'mailster_nonce' ),
				), admin_url( 'admin-ajax.php' ) ),
				'location' => home_url(),
			), $url );

			wp_redirect( $url );
			exit;
		}

		$this->json_return( $return );
	}


	private function check_for_update() {
		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		if ( $plugin_info = mailster()->plugin_info( null, true ) ) {
			$return['update'] = $plugin_info->update;
			$return['version'] = $plugin_info->new_version;
			$return['last_update'] = human_time_diff( $plugin_info->last_update );
			$return['plugin_info'] = $plugin_info;

			$return['success'] = true;
		}

		$this->json_return( $return );
	}


	private function quick_install() {

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		$plugin = sanitize_key( $_POST['plugin'] );
		$method = sanitize_key( $_POST['method'] );
		$step = sanitize_key( $_POST['step'] );

		switch ( $step ) {
			case 'install':
				$return['success'] = mailster( 'helper' )->install_plugin( $plugin );
			break;
			case 'activate':
				$return['success'] = mailster( 'helper' )->activate_plugin( $plugin );
			break;
			case 'content':

				ob_start();

				do_action( "mailster_deliverymethod_tab_{$method}" );

				$content = ob_get_contents();

				ob_end_clean();
				$return['content'] = $content;
				$return['success'] = true;
			break;
		}

		$this->json_return( $return );
	}


	private function wizard_save() {

		global $mailster_options;

		$return['success'] = false;

		$this->ajax_nonce( json_encode( $return ) );

		parse_str( $_POST['data'], $data );
		$id = sanitize_key( $_POST['id'] );

		switch ( $id ) {
			case 'homepage':

				// homepage exists => update
				if ( $homepage = get_post( mailster_option( 'homepage' ) ) ) {
					$homepage->post_title = $data['post_title'];
					$homepage->post_content = $data['post_content'];
					if ( isset( $data['post_name'] ) ) {
						$homepage->post_name = $data['post_name'];
					}
					$return['success'] = wp_update_post( $homepage );

					// create new one
				} else {
					include MAILSTER_DIR . 'includes/static.php';
					$homepage = wp_parse_args( $homepage, $mailster_homepage );
					$homepage['post_status'] = 'publish';
					$id = wp_insert_post( $homepage );
					if ( $id && ! is_wp_error( $id ) ) {
						mailster_remove_notice( 'no_homepage' );
						mailster_remove_notice( 'wrong_homepage_status' );
						$return['success'] = $id;
						mailster_update_option( 'homepage', $id );
					} else {
						$return['success'] = false;
					}
				}

			break;

			case 'validation':
			break;
			case 'finish':
			 	// maybe
			 	mailster( 'templates' )->schedule_screenshot( mailster_option( 'default_template' ), 'index.html', true, 1 );
				update_option( 'mailster_setup', time() );
				flush_rewrite_rules();
			break;
			case 'delivery':
			default:
				if ( isset( $data['mailster_options'] ) ) {
					$mailster_options = wp_parse_args( $data['mailster_options'], $mailster_options );
					update_option( 'mailster_options', $mailster_options );
				}
			break;
		}

		$this->json_return( $return );

	}


	private function test() {

		$return['success'] = false;

		$test_id = isset( $_POST['test_id'] ) ? $_POST['test_id'] : null;

		$test = mailster( 'test' );
		$return['success'] = $test->run( $test_id );
		$return['message'] = $test->get_message();
		$return['nexttest'] = $test->get_next();
		$return['total'] = $test->get_total();
		$return['errors'] = $test->get_error_counts();
		$return['current'] = $test->get_current();
		$return['type'] = $test->get_current_type();

		$this->json_return( $return );

	}


}
