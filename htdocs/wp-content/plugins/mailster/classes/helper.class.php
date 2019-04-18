<?php

class MailsterHelper {

	/**
	 *
	 *
	 * @param unknown $attach_id (optional)
	 * @param unknown $img_url   (optional)
	 * @param unknown $width
	 * @param unknown $height    (optional)
	 * @param unknown $crop      (optional)
	 * @return unknown
	 */
	public function create_image( $attach_id = null, $img_url = null, $width, $height = null, $crop = false ) {

		$image = apply_filters( 'mailster_pre_create_image', null, $attach_id, $img_url, $width, $height, $crop );
		if ( ! is_null( $image ) ) {
			return $image;
		}

		if ( $attach_id ) {

			$attach_id = (int) $attach_id;

			$image_src = wp_get_attachment_image_src( $attach_id, 'full' );
			if ( ! $image_src ) {
				return false;
			}

			$actual_file_path = get_attached_file( $attach_id );

			if ( ! $width && ! $height ) {
				$orig_size = getimagesize( $actual_file_path );
				$width = $orig_size[0];
				$height = $orig_size[1];
			}
		} elseif ( $img_url ) {

			$file_path = parse_url( $img_url );

			if ( file_exists( $img_url ) ) {

				$actual_file_path = $img_url;
				$img_url = str_replace( ABSPATH, site_url( '/' ), $img_url );
			} elseif ( strpos( $img_url, admin_url( 'admin-ajax' ) ) === 0 ) {

				parse_str( $file_path['query'], $query );
				$width = $query['w'];
				$height = $query['h'];
				$crop = $query['c'];

				return apply_filters( 'mailster_create_image', array(
					'id' => $attach_id,
					'url' => $img_url,
					'width' => $width,
					'height' => $height,
					'asp' => $width / $height,
				), $attach_id, $img_url, $width, $height, $crop);

			} else {

				$actual_file_path = realpath( $_SERVER['DOCUMENT_ROOT'] ) . $file_path['path'];

				/* todo: reconize URLs */
				if ( ! file_exists( $actual_file_path ) ) {

					return apply_filters( 'mailster_create_image', array(
						'id' => $attach_id,
						'url' => $img_url,
						'width' => $width,
						'height' => null,
						'asp' => null,
					), $attach_id, $img_url, $width, $height, $crop);

				}
			}

			$actual_file_path = ltrim( $file_path['path'], '/' );
			$actual_file_path = rtrim( ABSPATH, '/' ) . $file_path['path'];

			if ( ! file_exists( $actual_file_path ) ) {
				$actual_file_path = ABSPATH . str_replace( site_url( '/' ), '', $img_url );
			}

			$orig_size = getimagesize( $actual_file_path );

			$image_src[0] = $img_url;
			$image_src[1] = $orig_size[0];
			$image_src[2] = $orig_size[1];

		}

		if ( ! $height && isset( $image_src[1] ) && $image_src[1] && isset( $image_src[2] ) && $image_src[2] ) {
			$height = round( $width / ( $image_src[1] / $image_src[2] ) );
		}

		$file_info = pathinfo( $actual_file_path );
		$extension = $file_info['extension'];

		$no_ext_path = trailingslashit( $file_info['dirname'] ) . $file_info['filename'];

		$new_img_size = array( $width, $height );
		if ( ! $crop ) {
			$new_img_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
		}
		$resized_img_path = $no_ext_path . '-' . $new_img_size[0] . 'x' . $new_img_size[1] . '.' . $extension;
		$new_img = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

		if ( ! file_exists( $resized_img_path ) && file_exists( $actual_file_path ) ) {

			$image = wp_get_image_editor( $actual_file_path );
			if ( ! is_wp_error( $image ) ) {
				$image->resize( $width, $height, $crop );
				$imageobj = $image->save();
				$new_img_path = ! is_wp_error( $imageobj ) ? $imageobj['path'] : $actual_file_path;
			} else {
				$new_img_path = $actual_file_path;
			}

			$new_img_size = getimagesize( $new_img_path );
			$new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

			$meta_data = wp_get_attachment_metadata( $attach_id );
			if ( $meta_data ) {
				$size_id = '_mailster-' . $width . 'x' . $height . '|' . $crop;
				$meta_data['sizes'][ $size_id ] = array(
					'file' => basename( $new_img_path ),
					'width' => $width,
					'height' => $height,
					'mime-type' => $new_img_size['mime'],
				);
				wp_update_attachment_metadata( $attach_id, $meta_data );
			}
		}

		return apply_filters( 'mailster_create_image', array(
			'id' => $attach_id,
			'url' => $new_img,
			'width' => $new_img_size[0],
			'height' => $new_img_size[1],
			'asp' => $new_img_size[1] ? $new_img_size[0] / $new_img_size[1] : null,
		), $attach_id, $img_url, $width, $height, $crop);

	}



	/**
	 *
	 *
	 * @param unknown $force (optional)
	 * @return unknown
	 */
	public function get_wpuser_meta_fields( $force = false ) {

		global $wpdb;

		$cache_key = 'mailster_wpuser_meta_fields';

		if ( $force || false === ( $meta_values = get_transient( $cache_key ) ) ) {
			$exclude = array( 'comment_shortcuts', 'first_name', 'last_name', 'nickname', 'use_ssl', 'default_password_nag', 'dismissed_wp_pointers', 'rich_editing', 'show_admin_bar_front', 'show_welcome_panel', 'admin_color', 'screen_layout_dashboard', 'screen_layout_newsletter', 'show_try_gutenberg_panel', 'syntax_highlighting', 'locale', 'sites_network_per_page' );

			$meta_values = $wpdb->get_col( "SELECT meta_key FROM {$wpdb->usermeta} WHERE meta_value NOT LIKE '%:{%' GROUP BY meta_key ASC" );
			$meta_values = preg_grep( '/^(?!' . preg_quote( $wpdb->prefix ) . ')/', $meta_values );
			$meta_values = array_diff( $meta_values, $exclude );
			$meta_values = array_values( $meta_values );

			set_transient( $cache_key, $meta_values, DAY_IN_SECONDS );

		}

		return $meta_values;

	}


	/**
	 *
	 *
	 * @param unknown $force (optional)
	 * @return unknown
	 */
	public function get_addons( $force = false ) {

		if ( $force || false === ( $addons = get_transient( 'mailster_addons' ) ) ) {
			$url = 'https://mailster.github.io/v1/addons.json';

			$response = wp_remote_get( $url, array() );

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			if ( is_wp_error( $response ) ) {
				set_transient( 'mailster_addons', $response, 360 );
				return $response;
			}

			$addons = json_decode( $response_body );
			set_transient( 'mailster_addons', $addons, DAY_IN_SECONDS );
		}

		return $addons;

	}


	/**
	 *
	 *
	 * @param unknown $plugin
	 * @return unknown
	 */
	public function install_plugin( $plugin ) {

		$plugins = array_keys( get_plugins() );
		$pluginslugs = preg_replace( '/^(.*)\/.*$/', '$1', $plugins );

		// already installed
		if ( in_array( $plugin, $pluginslugs ) ) {
			return true;
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$api = plugins_api( 'plugin_information', array(
				'slug' => $plugin,
				'fields' => array(
					'short_description' => false,
					'sections' => false,
					'requires' => false,
					'rating' => false,
					'ratings' => false,
					'downloaded' => false,
					'last_updated' => false,
					'added' => false,
					'tags' => false,
					'compatibility' => false,
					'homepage' => false,
					'donate_link' => false,
				),
		) );

		if ( is_wp_error( $api ) ) {
			wp_die( $api );
		}

		$title = __( 'Plugin Install', 'mailster' );
		$parent_file = 'plugins.php';
		$submenu_file = 'plugin-install.php';

		$title = sprintf( __( 'Installing Plugin: %s', 'mailster' ), $api->name . ' ' . $api->version );
		$nonce = 'install-plugin_' . $plugin;
		$url = 'update.php?action=install-plugin&plugin=' . urlencode( $plugin );

		$type = 'web'; // Install plugin type, From Web or an Upload.

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
		return $upgrader->install( $api->download_link );

	}


	/**
	 *
	 *
	 * @param unknown $plugin
	 * @return unknown
	 */
	public function activate_plugin( $plugin ) {

		$plugins = array_keys( get_plugins() );

		$plugin = array_values( preg_grep( '/^' . $plugin . '\/.*$/', $plugins ) );
		if ( empty( $plugin ) ) {
			return false;
		}

		$plugin = $plugin[0];

		if ( is_plugin_active( $plugin ) ) {
			return true;
		}

		activate_plugin( $plugin );

		return is_plugin_active( $plugin );

	}


	/**
	 *
	 *
	 * @param unknown $args      (optional)
	 * @param unknown $countonly (optional)
	 * @return unknown
	 */
	public function link_query( $args = array(), $countonly = false ) {

		global $wpdb;

		$pts = get_post_types( array( 'public' => true ), 'objects' );
		$pt_names = array_keys( $pts );

		$defaults = array(
			'post_type' => $pt_names,
			'suppress_filters' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'post_status' => 'publish',
			'order' => 'DESC',
			'orderby' => 'post_date',
			'posts_per_page' => -1,
			'offset' => 0,
		);

		$query = wp_parse_args( $args, $defaults );

		if ( isset( $args['s'] ) ) {
			$query['s'] = $args['s'];
		}

		if ( $countonly ) {
			// Do main query with only one result to reduce server load
			$get_posts = new WP_Query( wp_parse_args( array( 'posts_per_page' => 1, 'offset' => 0 ), $query ) );
			return $wpdb->query( str_ireplace( 'LIMIT 0, 1', '', $get_posts->request ) );
		}

		// Do main query.
		$get_posts = new WP_Query( $query );

		$sql = str_replace( 'posts.ID', 'posts.*', $get_posts->request );

		$posts = $wpdb->get_results( $sql );

		// Build results.
		$results = array();
		foreach ( $posts as $post ) {
			if ( 'post' == $post->post_type ) {
				$info = mysql2date( __( 'Y/m/d', 'mailster' ), $post->post_date );
			} else {
				$info = $pts[ $post->post_type ]->labels->singular_name;
			}

			$results[] = array(
				'ID' => $post->ID,
				'title' => trim( esc_html( strip_tags( get_the_title( $post ) ) ) ),
				'permalink' => get_permalink( $post->ID ),
				'info' => $info,
			);
		}

		return $results;
	}


	/**
	 *
	 *
	 * @param unknown $username           (optional)
	 * @param unknown $only_with_username (optional)
	 * @return unknown
	 */
	public function get_social_links( $username = '', $only_with_username = false ) {

		$links = array(
			'amazon' => 'https://amazon.com',
			'android' => 'https://android.com',
			'apple' => 'https://apple.com',
			'appstore' => 'https://apple.com',
			'behance' => 'https://www.behance.net/%%USERNAME%%',
			'blogger' => 'https://%%USERNAME%%.blogspot.com/',
			'delicious' => 'https://delicious.com/%%USERNAME%%',
			'deviantart' => 'https://%%USERNAME%%.deviantart.com',
			'digg' => 'https://digg.com/users/%%USERNAME%%',
			'dribbble' => 'https://dribbble.com/%%USERNAME%%',
			'drive' => 'https://drive.google.com',
			'dropbox' => 'https://dropbox.com',
			'ebay' => 'https://www.ebay.com',
			'facebook' => 'https://facebook.com/%%USERNAME%%',
			'flickr' => 'https://www.flickr.com/photos/%%USERNAME%%',
			'forrst' => 'https://forrst.me/%%USERNAME%%',
			'google' => 'https://www.google.com',
			'googleplus' => 'https://plus.google.com/%%USERNAME%%',
			'html5' => 'https://html5.com',
			'instagram' => 'https://instagram.com/%%USERNAME%%',
			'lastfm' => 'https://www.lastfm.de/user/%%USERNAME%%',
			'linkedin' => 'https://www.linkedin.com/%%USERNAME%%',
			'myspace' => 'https://www.myspace.com/%%USERNAME%%',
			'paypal' => 'https://paypal.com',
			'picasa' => 'https://picasa.com',
			'pinterest' => 'https://pinterest.com/%%USERNAME%%',
			'rss' => get_bloginfo( 'rss2_url' ),
			'skype' => 'skype:%%USERNAME%%',
			'soundcloud' => 'https://soundcloud.com/%%USERNAME%%',
			'stumbleupon' => 'https://stumbleupon.com',
			'technorati' => 'https://technorati.com',
			'tumblr' => 'https://%%USERNAME%%.tumblr.com',
			'twitter' => 'https://twitter.com/%%USERNAME%%',
			'vimeo' => 'https://vimeo.com/%%USERNAME%%',
			'windows' => 'https://microsoft.com',
			'windows_8' => 'https://microsoft.com',
			'wordpress' => 'https://profiles.wordpress.org/%%USERNAME%%',
			'yahoo' => 'https://yahoo.com',
			'youtube' => 'https://youtube.com/%%USERNAME%%',
		);

		$links = apply_filters( 'mailster_get_social_links', $links );

		if ( $only_with_username ) {
			$links = preg_grep( '/%%USERNAME%%/', $links );
		}

		$links = str_replace( '%%USERNAME%%', $username, $links );

		return $links;

	}


	/**
	 *
	 *
	 * @param unknown $service
	 * @param unknown $username           (optional)
	 * @param unknown $only_with_username (optional)
	 * @return unknown
	 */
	public function get_social_link( $service, $username = '', $only_with_username = false ) {

		$links = $this->get_social_links( $username, $only_with_username );

		$link = ( isset( $links[ $service ] ) ) ? $links[ $service ] : '';

		return $link;

	}


	/**
	 *
	 *
	 * @param unknown $utc_start
	 * @param unknown $interval
	 * @param unknown $time_frame
	 * @param unknown $weekdays   (optional)
	 * @param unknown $in_future  (optional)
	 * @return unknown
	 */
	public function get_next_date_in_future( $utc_start, $interval, $time_frame, $weekdays = array(), $in_future = true ) {

		// in local time
		$offset = $this->gmt_offset( true );
		$now = time() + $offset;
		$utc_start += $offset;
		$times = 1;

		// must be in future and starttime in the past
		if ( $in_future && $utc_start - $now < 0 ) {
			// get how many $time_frame are in the time between now and the starttime
			switch ( $time_frame ) {
				case 'year':
					$count = date( 'Y', $now ) - date( 'Y', $utc_start );
				break;
				case 'month':
					$count = ( date( 'Y', $now ) - date( 'Y', $utc_start ) ) * 12 + ( date( 'm', $now ) - date( 'm', $utc_start ) );
				break;
				case 'week':
					$count = floor( ( ( $now - $utc_start ) / 86400 ) / 7 );
				break;
				case 'day':
					$count = floor( ( $now - $utc_start ) / 86400 );
				break;
				case 'hour':
					$count = floor( ( $now - $utc_start ) / 3600 );
				break;
				default:
					$count = $interval;
				break;
			}

			$times = $interval ? ceil( $count / $interval ) : 0;
		}

		$nextdate = strtotime( date( 'Y-m-d H:i:s', $utc_start ) . ' +' . ( $interval * $times ) . " {$time_frame}" );

		// add a single entity if date is still in the past or just now
		if ( $in_future && ( $nextdate - $now < 0 || $nextdate == $utc_start ) ) {
			$nextdate = strtotime( date( 'Y-m-d H:i:s', $utc_start ) . ' +' . ( $interval * $times + $interval ) . " {$time_frame}" );
		}

		if ( ! empty( $weekdays ) && count( $weekdays ) < 7 ) {

			$dayofweek = date( 'w', $nextdate );
			$i = 0;
			if ( ! $interval ) {
				$interval = 1;
			}

			while ( ! in_array( $dayofweek, $weekdays ) ) {

				// try next $time_frame
				// if week go day by day otherwise infinity loop
				if ( 'week' == $time_frame ) {
					$nextdate = strtotime( '+1 day', $nextdate );
				} else {
					$nextdate = strtotime( "+{$interval} {$time_frame}", $nextdate );
				}
				$dayofweek = date( 'w', $nextdate );

				// force a break to prevent infinity loops
				if ( $i > 500 ) {
					break;
				}

				$i++;
			}
		}

		// return as UTC
		return $nextdate - $offset;

	}


	/**
	 *
	 *
	 * @param unknown $post_type (optional)
	 * @param unknown $labels    (optional)
	 * @param unknown $names     (optional)
	 * @param unknown $values    (optional)
	 * @return unknown
	 */
	public function get_post_term_dropdown( $post_type = 'post', $labels = true, $names = false, $values = array() ) {

		$taxonomies = get_object_taxonomies( $post_type, 'objects' );

		$html = '';

		$taxwraps = array();

		foreach ( $taxonomies as $id => $taxonomy ) {
			$tax = '<div class="dynamic_embed_options_taxonomy_container">' . ( $labels ? '<label class="dynamic_embed_options_taxonomy_label">' . $taxonomy->labels->name . ': </label>' : '' ) . '<span class="dynamic_embed_options_taxonomy_wrap">';

			$cats = get_categories( array( 'hide_empty' => false, 'taxonomy' => $id, 'type' => $post_type, 'orderby' => 'id', 'number' => 999 ) );

			if ( ! isset( $values[ $id ] ) ) {
				$values[ $id ] = array( '-1' );
			}

			$selects = array();

			foreach ( $values[ $id ] as $term ) {
				$select = '<select class="dynamic_embed_options_taxonomy check-for-posts" ' . ( $names ? 'name="mailster_data[autoresponder][terms][' . $id . '][]"' : '' ) . '>';
				$select .= '<option value="-1">' . sprintf( __( 'any %s', 'mailster' ), $taxonomy->labels->singular_name ) . '</option>';
				foreach ( $cats as $cat ) {
					$select .= '<option value="' . $cat->term_id . '" ' . selected( $cat->term_id, $term, false ) . '>' . $cat->name . '</option>';
				}
				$select .= '</select>';
				$selects[] = $select;
			}

			$tax .= implode( ' ' . __( 'or', 'mailster' ) . ' ', $selects );

			$tax .= '</span><div class="mailster-list-operator"><span class="operator-and">' . __( 'and', 'mailster' ) . '</span></div></div>';

			$taxwraps[] = $tax;
		}

		$html = ( ! empty( $taxwraps ) )
			? implode( ( $labels
				? '<label class="dynamic_embed_options_taxonomy_label">&nbsp;</label>'
			: '' ), $taxwraps )
			: '';

		return $html;

	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function social_services() {
		include MAILSTER_DIR . 'includes/social_services.php';

		return $mailster_social_services;

	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function using_permalinks() {
		global $wp_rewrite;
		return apply_filters( 'mailster_using_permalinks', is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() );
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_first_form_id() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}mailster_forms ORDER BY ID ASC LIMIT 1" );
	}


	/**
	 *
	 *
	 * @param unknown $attachemnt_id
	 * @param unknown $fieldname
	 * @param unknown $size          (optional)
	 */
	public function media_editor_link( $attachemnt_id, $fieldname, $size = 'thumbnail' ) {

		$image_url = wp_get_attachment_image_src( $attachemnt_id, $size );

		if ( ! function_exists( 'wpview_media_sandbox_styles' ) ) { // since 4.0 ?>
			<?php if ( $image_url ) : ?>
			<img src="<?php echo esc_attr( $image_url[0] ) ?> width="150">
			<?php endif; ?>
			<label><?php esc_html_e( 'Image ID', 'mailster' );?>:
			<input class="small-text" type="text" name="<?php echo esc_attr( $fieldname ); ?>" value="<?php echo esc_attr( $attachemnt_id ); ?>"></label>

<?php
		} else {
			wp_enqueue_media();
			wp_add_inline_style( 'media-views', '
				.media-editor-link{
					display: inline-block;
					height: 80px;
					overflow: hidden;
					max-width: 280px;
					min-width: 80px;
					position:relative;
				}
				.media-editor-link-has-image{
					border: 1px solid #ccc;
				}
				.media-editor-link-remove{
					position: absolute;
					top: 0;
					right: 0;
					text-decoration: none;
					padding: 0 2px;
					display: none;
				}
				.media-editor-link-has-image .media-editor-link-select{
					display: none;
				}
				.media-editor-link-has-image:hover .media-editor-link-remove{
					display: block;
				}
				.media-editor-link-has-image:hover{
					box-shadow: 0 0 0 1px white, 0 0 0 5px #1E8CBE;
				}
				.media-editor-link-empty:before{
					content: "\f128";
					font-size: 80px;
				}
				.media-editor-link img{
					transform: translateY(-50%);
					top: 50%;
					position: relative;
					max-width: 200px;
				}' );

			$suffix = SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'mailster-media-editor-link-script', MAILSTER_URI . 'assets/js/media-editor-link-script' . $suffix . '.js', array( 'jquery' ), MAILSTER_VERSION );

			$image_url = $image_url ? $image_url[0] : '';
			$classes = array( 'media-editor-link' );
			if ( $image_url ) {
				$classes[] = 'media-editor-link-has-image';
			}

?>

			<div class="<?php echo esc_attr( implode( ' ', $classes ) ) ?>" title="<?php esc_attr_e( 'Change Image', 'mailster' ); ?>" data-title="<?php esc_attr_e( 'Add Image', 'mailster' ); ?>">
				<img class="media-editor-link-img" <?php if ( $image_url ) : ?>src="<?php echo esc_attr( $image_url ) ?>"<?php endif; ?>>
				<a class="media-editor-link-select button" href="#"><?php esc_html_e( 'Select Image', 'mailster' ); ?></a>
				<a class="media-editor-link-remove" href="#" title="<?php esc_attr_e( 'Remove Image', 'mailster' ); ?>">&#10005;</a>
				<input class="media-editor-link-input" type="hidden" name="<?php echo esc_attr( $fieldname ); ?>" value="<?php echo esc_attr( $attachemnt_id ); ?>">
			</div>

	<?php

		}

	}


	/**
	 *
	 *
	 * @param unknown $in_seconds (optional)
	 * @param unknown $timestamp  (optional)
	 * @return unknown
	 */
	public function gmt_offset( $in_seconds = false, $timestamp = null ) {

		$offset = get_option( 'gmt_offset' );

		if ( $offset == '' ) {
			$tzstring = get_option( 'timezone_string' );
			$current = date_default_timezone_get();
			date_default_timezone_set( $tzstring );
			$offset = date( 'Z' ) / 3600;
			date_default_timezone_set( $current );
		}

		// check if timestamp has DST
		if ( ! is_null( $timestamp ) ) {
			$l = localtime( $timestamp, true );
			if ( $l['tm_isdst'] ) {
				$offset++;
			}
		}

		return $in_seconds ? $offset * 3600 : (int) $offset;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function dateformat() {

		$format = get_option( 'date_format' );

		return apply_filters( 'mailster_dateformat', $format );

	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function timeformat() {

		$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		return apply_filters( 'mailster_timeformat', $format );

	}


	/**
	 *
	 *
	 * @param unknown $value
	 * @param unknown $format   (optional)
	 * @return unknown
	 */
	public function do_timestamp( $value, $format = null ) {
		if ( is_null( $format ) ) {
			$format = $this->timeformat();
		}
		$timestamp = is_numeric( $value ) ? strtotime( '@' . $value ) : strtotime( '' . $value );
		if ( false !== $timestamp ) {
			$value = date( $format, $timestamp );
		} elseif ( is_numeric( $value ) ) {
			$value = date( $format, $value );
		} else {
			$value = '';
		}

		return $value;
	}

	/**
	 *
	 *
	 * @param unknown $string
	 * @param unknown $last   (optional)
	 * @return unknown
	 */
	public function get_timestamp_by_string( $string, $last = false ) {

		$offset = $this->gmt_offset();
		$current_timezone = date_default_timezone_get();
		date_default_timezone_set( 'UTC' );

		$day = strtotime( $offset . ' hours' );

		switch ( $string ) {
			case 'day':
				$str = ( $last ? 'yesterday' : 'tomorrow' ) . ' midnight';
				break;
			case 'week':
				$str = $last ? 'last sunday -' . ( 7 - get_option( 'start_of_week', 1 ) ) . ' days' : 'next sunday +' . get_option( 'start_of_week', 1 ) . ' days';
				break;
			case 'month':
				$str = 'midnight first day of ' . ( $last ? 'last' : 'next' ) . ' month';
				break;
		}

		$utcMidnight = strtotime( $str, $day );
		$zoneMidnight = strtotime( ( $offset * -1 ) . ' hours', $utcMidnight );

		date_default_timezone_set( $current_timezone );
		return $zoneMidnight;

	}


	/**
	 *
	 *
	 * @param unknown $html
	 * @param unknown $body (optional)
	 * @return unknown
	 */
	public function format_html( $html, $body = false ) {

		$doc = new DOMDocument();

		$doc->preserveWhiteSpace = false;
		$i_error = libxml_use_internal_errors( true );
		$doc->loadHTML( $html );
		libxml_clear_errors();
		libxml_use_internal_errors( $i_error );

		$doc->formatOutput = true;
		// remove <!DOCTYPE
		$doc->removeChild( $doc->doctype );
		// remove <html><body></body></html>
		if ( ! $body ) {
			$doc->replaceChild( $doc->firstChild->firstChild->firstChild, $doc->firstChild );
		}

		return trim( $doc->saveHTML() );

	}


	/**
	 *
	 *
	 * @param unknown $status
	 * @param unknown $original (optional)
	 * @return unknown
	 */
	public function get_bounce_message( $status, $original = null ) {

		$res = apply_filters( 'mailster_get_bounce_message', null, $status, $original );
		if ( ! is_null( $res ) ) {
			return $res;
		}

		include MAILSTER_DIR . 'classes/libs/bounce/bounce_statuscodes.php';

		if ( is_null( $original ) ) {
			$original = $status;
		}

		if ( isset( $status_code_classes[ $status ] ) ) {
			$message = $status_code_classes[ $status ];
			return '[' . $message['title'] . '] ' . $message['descr'];
		}
		if ( isset( $status_code_subclasses[ $status ] ) ) {
			$message = $status_code_subclasses[ $status ];
			return '[' . $message['title'] . '] ' . $message['descr'];
		}

		if ( $status = substr( $status, 0, strrpos( $status, '.' ) ) ) {
			return $this->get_bounce_message( $status, $original );
		}

		return $original;

	}


	/**
	 *
	 *
	 * @param unknown $status
	 * @param unknown $original (optional)
	 * @return unknown
	 */
	public function get_unsubscribe_message( $status, $original = null ) {

		$res = apply_filters( 'mailster_get_unsubscribe_message', null, $status, $original );
		if ( ! is_null( $res ) ) {
			return $res;
		}

		if ( is_null( $original ) ) {
			$original = $status;
		}

		switch ( $status ) {
			case 'list_unsubscribe':
			case 'list_unsubscribe_list':
				return  __( 'The user clicked on the unsubscribe option in the Mail application', 'mailster' );
			case 'link_unsubscribe':
			case 'link_unsubscribe_list':
				return __( 'The user clicked on an unsubscribe link in the campaign.', 'mailster' );
			case 'email_unsubscribe':
			case 'email_unsubscribe_list':
				return __( 'The user canceled the subscription via the website.', 'mailster' );
			case 'spam_complaint':
			case 'spam_complaint_list':
				return __( 'The user marked this message as Spam in the Mail application.', 'mailster' );
		}

		return $status;

	}


	/**
	 *
	 *
	 * @param unknown $content
	 * @return unknown
	 */
	public function prepare_content( $content ) {

		if ( empty( $content ) ) {
			return false;
		}

		// strip all unwanted stuff from the content
		$content = $this->strip_unwanted_html( $content );

		// fix for Yahoo background color FIXED!!
		// if(!strpos($this->content, 'body{background-image'))
		// $this->content = preg_replace('/body{background-color/','body,.bodytbl{background-color', $this->content, 1);
		// adding a inline width attribute to images for a bug in Apple Mail 7 with embeded images
		// if($this->embed_images){
		preg_match_all( '#(<img.*?)(width="(\d+)")(.*?>)#', $content, $images );
		foreach ( $images[0] as $i => $image ) {
			$oldstyle = '';
			$styleattr = '';
			if ( preg_match( '#style="([^"]*)"#', $image, $style ) ) {
				$oldstyle = $style[1];
				$styleattr = $style[0];
			}
			$imgstr = str_replace( $styleattr, '', $images[1][ $i ] . 'style="width:' . $images[3][ $i ] . 'px;' . $oldstyle . '" ' . $images[2][ $i ] . $images[4][ $i ] );
			$content = str_replace( $image, $imgstr, $content );
		}
		// }
		// custom styles
		$content = $this->add_mailster_styles( $content );

		// Inline CSS
		$content = $this->inline_style( $content );

		$content = str_replace( array( '%7B', '%7D' ), array( '{', '}' ), $content );

		return apply_filters( 'mailster_prepare_content', $content );

	}


	/**
	 *
	 *
	 * @param unknown $content
	 * @return unknown
	 */
	private function strip_unwanted_html( $content ) {

		if ( empty( $content ) ) {
			return false;
		}

		// template language stuff
		$content = preg_replace( '#<(modules?|buttons|multi|single)([^>]*)>#', '', $content );
		$content = preg_replace( '#<\/(modules?|buttons|multi|single)>#', '', $content );

		// remove comments
		$content = preg_replace( '#<!-- (.*) -->\s*#', '', $content );

		return $content;

	}


	/**
	 *
	 *
	 * @param unknown $content
	 * @return unknown
	 */
	public function inline_style( $content ) {

		// save comments with conditional stuff
		preg_match_all( '#<!--\s?\[\s?if(.*)?>(.*)?<!\[endif\]-->#sU', $content, $comments );

		$commentid = uniqid();
		foreach ( $comments[0] as $i => $comment ) {
			$content = str_replace( $comment, '<!--Mailster:html_comment_' . $i . '_' . $commentid . '-->', $content );
		}

		// get all style blocks
		if ( preg_match_all( '#(<style ?[^<]+?>([^<]+)<\/style>)#', $content, $originalstyles ) ) {

			@error_reporting( E_ERROR | E_PARSE );
			@ini_set( 'display_errors', '0' );

			// strip media queries
			foreach ( $originalstyles[2] as $i => $styleblock ) {
				$mediaBlocks = $this->parseMediaBlocks( $styleblock );
				if ( ! empty( $mediaBlocks ) ) {
					$originalstyles[2][ $i ] = str_replace( $mediaBlocks, '', $originalstyles[2][ $i ] );
				}
			}

			require MAILSTER_DIR . 'classes/libs/InlineStyle/autoload.php';

			$htmldoc = new \InlineStyle\InlineStyle( $content );

			$htmldoc->applyStylesheet( $originalstyles[2] );

			$html = $htmldoc->getHTML();

			// convert urlencode back for links with unallowed characters (only images)
			preg_match_all( "/(src|background)=[\"'](.*)[\"']/Ui", $html, $urls );
			$urls = ! empty( $urls[2] ) ? array_unique( $urls[2] ) : array();
			foreach ( $urls as $url ) {
				$html = str_replace( $url, rawurldecode( $url ), $html );
			}
			$content = $html;

		}

		foreach ( $comments[0] as $i => $comment ) {
			$content = str_replace( '<!--Mailster:html_comment_' . $i . '_' . $commentid . '-->', $comment, $content );
		}

		return $content;

	}


	/**
	 *
	 *
	 * @param unknown $css
	 * @return unknown
	 */
	private function parseMediaBlocks( $css ) {

		$mediaBlocks = array();

		$start = 0;
		while ( ( $start = strpos( $css, '@media', $start ) ) !== false ) {
			// stack to manage brackets
			$s = array();

			// get the first opening bracket
			$i = strpos( $css, '{', $start );

			// if $i is false, then there is probably a css syntax error
			if ( $i !== false ) {
				// push bracket onto stack
				array_push( $s, $css[ $i ] );

				// move past first bracket
				$i++;

				while ( ! empty( $s ) ) {
					// if the character is an opening bracket, push it onto the stack, otherwise pop the stack
					if ( $css[ $i ] == '{' ) {

						array_push( $s, '{' );

					} elseif ( $css[ $i ] == '}' ) {

						array_pop( $s );
					}

					$i++;
				}

				// cut the media block out of the css and store
				$mediaBlocks[] = substr( $css, $start, ( $i + 1 ) - $start );

				// set the new $start to the end of the block
				$start = $i;
			}
		}

		return $mediaBlocks;
	}


	/**
	 *
	 *
	 * @param unknown $content
	 * @return unknown
	 */
	public function add_mailster_styles( $content ) {
		// custom styles
		global $mailster_mystyles;

		if ( $mailster_mystyles ) {
			// check for existing styles
			preg_match_all( '#(<style ?[^<]+?>([^<]+)<\/style>)#', $content, $originalstyles );

			if ( ! empty( $originalstyles[0] ) ) {
				foreach ( $mailster_mystyles as $style ) {
					$block = end( $originalstyles[0] );
					$content = str_replace( $block, $block . '<style type="text/css">' . "\n" . $style . "\n" . '</style>', $content );
				}
			} else {
				$content = str_replace( '</head>', '<style type="text/css">' . "\n" . $style . "\n" . '</style></head>', $content );
			}
		}

		return $content;
	}


	/**
	 *
	 *
	 * @param unknown $handle
	 */
	public function wp_print_embedded_scripts( $handle ) {

		global $wp_scripts;

		if ( ! $wp_scripts->registered[ $handle ] ) {
			return;
		}

		$path = untrailingslashit( ABSPATH );

		foreach ( $wp_scripts->registered[ $handle ]->deps as $h ) {
			$this->wp_print_embedded_scripts( $h );
		}

		if ( isset( $wp_scripts->registered[ $handle ]->extra['data'] ) ) {
			echo '<script>' . $wp_scripts->registered[ $handle ]->extra['data'] . '</script>';
		}

		ob_start();

		( @file_exists( $path . $wp_scripts->registered[ $handle ]->src ) )
			? include $path . $wp_scripts->registered[ $handle ]->src
			: include str_replace( MAILSTER_URI, MAILSTER_DIR, $wp_scripts->registered[ $handle ]->src );
		$output = ob_get_contents();

		ob_end_clean();

		echo "<script id='$handle'>$output</script>";

	}


	/**
	 *
	 *
	 * @param unknown $handle
	 */
	public function wp_print_embedded_styles( $handle ) {

		global $wp_styles;

		if ( ! $wp_styles->registered[ $handle ] ) {
			return;
		}

		$path = untrailingslashit( ABSPATH );
		$before = '';
		$after = '';

		foreach ( $wp_styles->registered[ $handle ]->deps as $h ) {
			$this->wp_print_embedded_styles( $h );
		}
		foreach ( $wp_styles->registered[ $handle ]->extra as $type => $styles ) {
			switch ( $type ) {
				case 'before':
					$before .= implode( ' ', $styles );
					break;
				case 'after':
					$after .= implode( ' ', $styles );
					break;
			}
		}

		ob_start();

		( @file_exists( $path . $wp_styles->registered[ $handle ]->src ) )
			? include $path . $wp_styles->registered[ $handle ]->src
			: include str_replace( MAILSTER_URI, MAILSTER_DIR, $wp_styles->registered[ $handle ]->src );
		$output = ob_get_contents();

		ob_end_clean();

		preg_match_all( '#url\((\'|")?([^\'"]+)(\'|")?\)#i', $output, $urls );
		$base = trailingslashit( dirname( $wp_styles->registered[ $handle ]->src ) );
		foreach ( $urls[0] as $i => $url ) {
			if ( substr( $urls[2][ $i ], 0, 5 ) == 'data:' ) {
				continue;
			}

			$output = str_replace( 'url(' . $urls[1][ $i ] . $urls[2][ $i ] . $urls[3][ $i ] . ')', 'url(' . $urls[1][ $i ] . $base . $urls[2][ $i ] . $urls[3][ $i ] . ')', $output );
		}

		echo "<style id='$handle' type='text/css'>{$before}{$output}{$after}</style>";

	}


	/**
	 *
	 *
	 * @param unknown $filename
	 * @param unknown $data     (optional)
	 * @param unknown $flags    (optional)
	 * @return unknown
	 */
	public function file_put_contents( $filename, $data = '', $flags = 'w' ) {

		mailster_require_filesystem();

		if ( ! is_dir( dirname( $filename ) ) ) {
			wp_mkdir_p( dirname( $filename ) );
		}

		if ( $file_handle = @fopen( $filename, $flags ) ) {
			fwrite( $file_handle, $data );
			fclose( $file_handle );
		}

		return is_file( $filename );

	}


	/**
	 *
	 *
	 * @param unknown $folder         (optional)
	 * @param unknown $prevent_access (optional)
	 * @return unknown
	 */
	public function mkdir( $folder = '', $prevent_access = true ) {

		mailster_require_filesystem();

		if ( 0 === strrpos( $folder, ABSPATH ) ) {
			$path = trailingslashit( $folder );
		} else {
			$path = trailingslashit( trailingslashit( MAILSTER_UPLOAD_DIR ) . $folder );
		}

		if ( ! is_dir( $path ) ) {

			if ( ! wp_mkdir_p( $path ) ) {
				return false;
			}
		}

		if ( $prevent_access ) {
			if ( ! file_exists( $path . 'index.html' ) ) {
				$this->file_put_contents( $path . 'index.html', '<!DOCTYPE html><html><head><title>.</title><meta name="robots" content="noindex,nofollow"></head></html>' );
			}
		}
		return $path;

	}


	/**
	 *
	 *
	 * @param unknown $host
	 * @param unknown $type  (optional)
	 * @param unknown $force (optional)
	 * @return unknown
	 */
	public function dns_query( $host, $type = 'ANY', $force = true ) {

		$type = strtoupper( $type );

		$key = 'mailster_dns_' . $host;

		if ( $force || false === ( $records = get_transient( $key ) ) ) {

			// request TXT first
			@dns_get_record( $host, DNS_TXT );
			$records = @dns_get_record( $host, DNS_ALL - DNS_PTR );

			set_transient( $key, $records, 90 );

		}

		if ( ! is_array( $records ) ) {
			return false;
		}

		$return = array();

		foreach ( $records as $record ) {
			if ( $type == $record['type'] || $type == 'ANY' ) {
				$return[] = (object) $record;
			}
		}

		return $return;

	}


	public function in_timeframe( $timestamp = null ) {

		$from = mailster_option( 'time_frame_from', 0 );
		$to = mailster_option( 'time_frame_to', 0 );
		$days = mailster_option( 'time_frame_day' );
		if ( is_null( $timestamp ) ) {
			$timestamp = current_time( 'timestamp' );
		}
		$hour = date( 'G', $timestamp );
		$day = date( 'w', $timestamp );

		// further check if not 24h
		if ( abs( $from - $to ) ) {
			if ( $to < $from ) {
				$to += 24;
			}
			if ( $from > $hour || $hour >= $to ) {
				return false;
			}
		}
		return ! is_array( $days ) || in_array( $day, $days );

	}

	/**
	 *
	 *
	 * @return unknown
	 */
	public function got_url_rewrite() {

		$got_url_rewrite = true;

		if ( ! function_exists( 'got_url_rewrite' ) ) {
			require_once ABSPATH . 'wp-admin/includes/misc.php';
		}

		if ( function_exists( 'got_url_rewrite' ) ) {
			$got_url_rewrite = got_url_rewrite();
		}

		return $got_url_rewrite;

	}


	/**
	 *
	 *
	 * @param unknown $obj
	 * @return unknown
	 */
	public function object_to_array( $obj ) {
		if ( is_object( $obj ) ) {
			$obj = (array) $obj;
		}

		if ( is_array( $obj ) ) {
			$new = array();
			foreach ( $obj as $key => $val ) {
				$new[ $key ] = $this->object_to_array( $val );
			}
		} else {
			$new = $obj;
		}

		return $new;

	}


	/**
	 *
	 *
	 * @param unknown $public_only (optional)
	 * @param unknown $output      (optional)
	 * @param unknown $exclude     (optional)
	 * @return unknown
	 */
	public function get_post_types( $public_only = true, $output = 'names', $exclude = array( 'attachment', 'newsletter' ) ) {

		$post_types = get_post_types( array( 'public' => $public_only ), $output );

		if ( ! empty( $exclude ) ) {
			$post_types = array_diff_key( $post_types, array_flip( $exclude ) );
		}

		return $post_types;

	}


	/**
	 *
	 *
	 * @param unknown $org_string
	 * @param unknown $length     (optional)
	 * @param unknown $more       (optional)
	 * @return unknown
	 */
	public function get_excerpt( $org_string, $length = null, $more = null ) {

		if ( is_null( $length ) ) {
			$length = 55;
		}

		$excerpt = apply_filters( 'mymail_pre_get_excerpt', apply_filters( 'mailster_pre_get_excerpt', null, $org_string, $length, $more ), $org_string, $length, $more );
		if ( is_string( $excerpt ) ) {
			return $excerpt;
		}

		$string = str_replace( "\n", '<!--Mailster:newline-->', $org_string );
		$string = html_entity_decode( wp_trim_words( htmlentities( $string ), $length, $more ) );
		$maybe_broken_html = str_replace( '<!--Mailster:newline-->', "\n", $string );

		if ( $maybe_broken_html !== $org_string ) {
			$doc = new DOMDocument();
			// Note the meta charset is used to prevent UTF-8 data from being interpreted as Latin1, thus corrupting it
			$html = '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>';
			$html .= $maybe_broken_html;
			$html .= '</body></html>';

			$i_error = libxml_use_internal_errors( true );
			$doc->loadHTML( $html );
			libxml_clear_errors();
			libxml_use_internal_errors( $i_error );

			$body = $doc->getElementsByTagName( 'body' )->item( 0 );

			$excerpt = $doc->saveHTML( $body );
		} else {
			$excerpt = $org_string;
		}

		$excerpt = trim( strip_tags( $excerpt, '<p><br><a><strong><em><i><b><ul><ol><li><span>' ) );

		return apply_filters( 'mymail_get_excerpt', apply_filters( 'mailster_get_excerpt', $excerpt, $org_string, $length, $more ), $org_string, $length, $more );

	}


	/**
	 *
	 *
	 * @param unknown $html
	 * @param unknown $linksonly (optional)
	 * @return unknown
	 */
	public function plain_text( $html, $linksonly = false ) {

		// allow to hook into this method
		$result = apply_filters( 'mymail_plain_text', apply_filters( 'mailster_plain_text', null, $html, $linksonly ), $html, $linksonly );
		if ( ! is_null( $result ) ) {
			return $result;
		}

		if ( $linksonly ) {
			$links = '/< *a[^>]*href *= *"([^#]*)"[^>]*>(.*)< *\/ *a *>/Uis';
			$text = preg_replace( $links, '${2} [${1}]', $html );
			$text = str_replace( array( ' ', '&nbsp;' ), ' ', strip_tags( $text ) );
			$text = @html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );

			return trim( $text );

		} else {
			require_once MAILSTER_DIR . 'classes/libs/class.html2text.php';
			$htmlconverter = new \MailsterHtml2Text\Html2Text( $html, array( 'width' => 200, 'do_links' => 'table' ) );

			$text = trim( $htmlconverter->get_text() );
			$text = preg_replace( '/\s*$^\s*/mu', "\n\n", $text );
			$text = preg_replace( '/[ \t]+/u', ' ', $text );

			return $text;

		}

	}


	/**
	 *
	 *
	 * @param unknown $serialized_string
	 * @return unknown
	 */
	public function unserialize( $serialized_string ) {

		$object = maybe_unserialize( $serialized_string );
		if ( empty( $object ) ) {
			$d = html_entity_decode( $serialized_string, ENT_QUOTES, 'UTF-8' );

			$d = preg_replace_callback( '!s:(\d+):"(.*?)";!', function( $matches ) {
					return 's:' . strlen( $matches[2] ) . ':"' . $matches[2] . '";';
			}, $d );
			$object = maybe_unserialize( $d );
		}

		return $object;

	}


	/**
	 *
	 *
	 * @param unknown $content
	 * @param unknown $args    (optional)
	 * @return unknown
	 */
	public function dialog( $content, $args = array() ) {

		$defaults = array(
			'id' => uniqid(),
			'button_label' => __( 'Ok, got it!', 'mailster' ),
			'classes' => array(),
		);

		if ( is_string( $args ) ) {
			$args = array( 'id' => $args );
		}

		$args = wp_parse_args( $args, $defaults );
		$args['id'] = 'mailster-' . $args['id'];

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'mailster-dialog', MAILSTER_URI . 'assets/js/dialog-script' . $suffix . '.js', array( 'jquery' ), MAILSTER_VERSION, true );
		wp_enqueue_style( 'mailster-dialog', MAILSTER_URI . 'assets/css/dialog-style' . $suffix . '.css', array(), MAILSTER_VERSION );

?>
		<div id="<?php echo esc_attr( $args['id'] ) ?>" class="mailster-notification-dialog notification-dialog-wrap <?php echo esc_attr( $args['id'] ) ?> hidden <?php echo implode( ' ', $args['classes'] ) ?>">
			<div class="notification-dialog-background"></div>
			<div class="notification-dialog" role="dialog" aria-labelledby="<?php echo esc_attr( $args['id'] ) ?>" tabindex="0">
				<div class="notification-dialog-content <?php echo esc_attr( $args['id'] ) ?>-content">
					<?php echo $content; ?>
				</div>
				<div class="notification-dialog-footer">
					<button type="button" class="<?php echo esc_attr( $args['id'] ) ?>-submit notification-dialog-submit button button-primary"><?php echo esc_html( $args['button_label'] ) ?></button>
				</div>
			</div>
		</div>
<?php

	}


}
