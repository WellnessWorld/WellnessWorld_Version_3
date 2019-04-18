<?php

class MailsterConditions {

	public function __construct( $conditions = array() ) {

	}


	public function __get( $name ) {

		if ( ! isset( $this->$name ) ) {
			$this->{$name} = $this->{'get_' . $name}();
		}

		return $this->{$name};

	}


	public function view( $conditions = array(), $inputname = null ) {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'mailster-conditions', MAILSTER_URI . 'assets/css/conditions-style' . $suffix . '.css', array(), MAILSTER_VERSION );
		wp_enqueue_script( 'mailster-conditions', MAILSTER_URI . 'assets/js/conditions-script' . $suffix . '.js', array( 'jquery' ), MAILSTER_VERSION, true );

		if ( is_null( $inputname ) ) {
			$inputname = 'mailster_data[conditions]';
		}

		if ( empty( $conditions ) ) {
			$conditions = array();
		}

		include MAILSTER_DIR . 'views/conditions/conditions.php';

	}

	public function render( $conditions = array(), $echo = true, $plain = false ) {

		if ( empty( $conditions ) ) {
			$conditions = array();
		}

		ob_start();
		include MAILSTER_DIR . 'views/conditions/render.php';
		$output = ob_get_contents();
		ob_end_clean();

		if ( $plain ) {
			$output = trim( strip_tags( $output ) );
			$output = preg_replace( '/\s*$^\s*/mu', "\n\n", $output );
			$output = preg_replace( '/[ \t]+/u', ' ', $output );
		}

		if ( ! $echo ) {
			return $output;
		}

		echo $output;

	}

	public function fielddropdown() {
		include MAILSTER_DIR . 'views/conditions/fielddropdown.php';
	}
	public function operatordropdown() {
		include MAILSTER_DIR . 'views/conditions/operatordropdown.php';
	}

	private function get_custom_fields() {
		$custom_fields = mailster()->get_custom_fields( );
		$custom_fields = wp_parse_args((array) $custom_fields,  array(
			'email' => array( 'name' => mailster_text( 'email' ) ),
			'firstname' => array( 'name' => mailster_text( 'firstname' ) ),
			'lastname' => array( 'name' => mailster_text( 'lastname' ) ),
			'rating' => array( 'name' => __( 'Rating', 'mailster' ) ),
		));

		return $custom_fields;
	}

	private function get_custom_date_fields() {
		$custom_date_fields = mailster()->get_custom_date_fields( true );

		return $custom_date_fields;
	}

	private function get_fields() {
		$fields = array(
			'id' => __( 'ID', 'mailster' ),
			'hash' => __( 'Hash', 'mailster' ),
			'email' => __( 'Email', 'mailster' ),
			'wp_id' => __( 'WordPress User ID', 'mailster' ),
			// 'status' => __( 'Status', 'mailster' ),
			'added' => __( 'Added', 'mailster' ),
			'updated' => __( 'Updated', 'mailster' ),
			'signup' => __( 'Signup', 'mailster' ),
			'confirm' => __( 'Confirm', 'mailster' ),
			'ip_signup' => __( 'IP on Signup', 'mailster' ),
			'ip_confirm' => __( 'IP on confirmation', 'mailster' ),
			// 'rating' => __( 'Rating', 'mailster' ),
		);

		return $fields;
	}

	private function get_time_fields() {
		$time_fields = array( 'added', 'updated', 'signup', 'confirm', 'gdpr' );
		$time_fields = array_merge( $time_fields,  $this->custom_date_fields );

		return $time_fields;
	}

	private function get_meta_fields() {
		$meta_fields = array(
			'form' => __( 'Form', 'mailster' ),
			'referer' => __( 'Referer', 'mailster' ),
			'client' => __( 'Client', 'mailster' ),
			'clienttype' => __( 'Clienttype', 'mailster' ),
			// 'coords' => __( 'Coords', 'mailster' ),
			'geo' => __( 'Location', 'mailster' ),
			'lang' => __( 'Language', 'mailster' ),
			// 'timeoffset' => __( 'Timeoffset', 'mailster' ),
			// 'lat' => __( 'Latitude', 'mailster' ),
			// 'lng' => __( 'Longitude', 'mailster' ),
			 'gdpr' => __( 'GDPR Consent given', 'mailster' ),
		);

		return $meta_fields;
	}

	private function get_wp_user_meta() {
		$wp_user_meta = wp_parse_args( mailster( 'helper' )->get_wpuser_meta_fields(), array( 'wp_user_level' => __( 'User Level', 'mailster' ), 'wp_capabilities' => __( 'User Role', 'mailster' ) ) );
		// removing custom fields from wp user meta to prevent conflicts
		$wp_user_meta = array_diff( $wp_user_meta, array_merge( array( 'email' ), array_keys( $this->custom_fields ) ) );

		return $wp_user_meta;
	}

	private function get_campaign_related() {
		return array(
			'_sent' => __( 'has received', 'mailster' ),
			'_sent__not_in' => __( 'has not received', 'mailster' ),
			'_open' => __( 'has received and opened', 'mailster' ),
			'_open__not_in' => __( 'has received but not opened', 'mailster' ),
			'_click' => __( 'has received and clicked', 'mailster' ),
			'_click__not_in' => __( 'has received and not clicked', 'mailster' ),
			'_click_link' => __( 'clicked link', 'mailster' ),
			'_click_link__not_in' => __( 'didn\'t clicked link', 'mailster' ),
		);

	}
	private function get_list_related() {
		return array(
			'_lists__not_in' => __( 'is not in List', 'mailster' ),
		);

	}
	private function get_operators() {
		return array(
			'is' => __( 'is', 'mailster' ),
			'is_not' => __( 'is not', 'mailster' ),
			'contains' => __( 'contains', 'mailster' ),
			'contains_not' => __( 'contains not', 'mailster' ),
			'begin_with' => __( 'begins with', 'mailster' ),
			'end_with' => __( 'ends with', 'mailster' ),
			'is_greater' => __( 'is greater', 'mailster' ),
			'is_smaller' => __( 'is smaller', 'mailster' ),
			'is_greater_equal' => __( 'is greater or equal', 'mailster' ),
			'is_smaller_equal' => __( 'is smaller or equal', 'mailster' ),
			'pattern' => __( 'match regex pattern', 'mailster' ),
			'not_pattern' => __( 'does not match regex pattern', 'mailster' ),
		);

	}
	private function get_simple_operators() {
		return array(
			'is' => __( 'is', 'mailster' ),
			'is_not' => __( 'is not', 'mailster' ),
			'is_greater' => __( 'is greater', 'mailster' ),
			'is_smaller' => __( 'is smaller', 'mailster' ),
			'is_greater_equal' => __( 'is greater or equal', 'mailster' ),
			'is_smaller_equal' => __( 'is smaller or equal', 'mailster' ),
		);

	}
	private function get_string_operators() {
		return array(
			'is' => __( 'is', 'mailster' ),
			'is_not' => __( 'is not', 'mailster' ),
			'contains' => __( 'contains', 'mailster' ),
			'contains_not' => __( 'contains not', 'mailster' ),
			'begin_with' => __( 'begins with', 'mailster' ),
			'end_with' => __( 'ends with', 'mailster' ),
			'pattern' => __( 'match regex pattern', 'mailster' ),
			'not_pattern' => __( 'does not match regex pattern', 'mailster' ),
		);

	}
	private function get_bool_operators() {
		return array(
			'is' => __( 'is', 'mailster' ),
			'is_not' => __( 'is not', 'mailster' ),
		);

	}
	private function get_date_operators() {
		return array(
			'is' => __( 'is on the', 'mailster' ),
			'is_not' => __( 'is not on the', 'mailster' ),
			'is_greater' => __( 'is after', 'mailster' ),
			'is_smaller' => __( 'is before', 'mailster' ),
			'is_greater_equal' => __( 'is after or on the', 'mailster' ),
			'is_smaller_equal' => __( 'is before or on the', 'mailster' ),
			// 'pattern' => __( 'match regex pattern', 'mailster' ),
			// 'not_pattern' => __( 'does not match regex pattern', 'mailster' ),
		);

	}
	private function get_special_campaigns() {
		return array(
			'_last_5' => __( 'Any of the Last 5 Campaigns', 'mailster' ),
			'_last_7day' => __( 'Any Campaigns within the last 7 days', 'mailster' ),
			'_last_1month' => __( 'Any Campaigns within the last 1 month', 'mailster' ),
			'_last_3month' => __( 'Any Campaigns within the last 3 months', 'mailster' ),
			'_last_6month' => __( 'Any Campaigns within the last 6 months', 'mailster' ),
			'_last_12month' => __( 'Any Campaigns within the last 12 months', 'mailster' ),
		);

	}
	private function get_field_operator( $operator ) {
		$operator = esc_sql( stripslashes( $operator ) );

		switch ( $operator ) {
			case '=':
				return 'is';
			case '!=':
				return 'is_not';
			case '<>':
				return 'contains';
			case '!<>':
				return 'contains_not';
			case '^':
				return 'begin_with';
			case '$':
				return 'end_with';
			case '>=':
				return 'is_greater_equal';
			case '<=':
				return 'is_smaller_equal';
			case '>':
				return 'is_greater';
			case '<':
				return 'is_smaller';
			case '%':
				return 'pattern';
			case '!%':
				return 'not_pattern';
		}

		return $operator;

	}


	private function print_condition( $condition, $formated = true ) {

		$field = isset( $condition['field'] ) ? $condition['field'] : $condition[0];
		$operator = isset( $condition['operator'] ) ? $condition['operator'] : $condition[1];
		$value = stripslashes_deep( isset( $condition['value'] ) ? $condition['value'] : $condition[2] );

		$return = array(
			'field' => '<strong>' . $this->nice_name( $field, 'field', $field ) . '</strong>',
			'operator' => '',
			'value' => '',
		);
		$opening_quote = _x( '&#8220;', 'opening curly double quote', 'mailster' );
		$closing_quote = _x( '&#8221;', 'closing curly double quote', 'mailster' );

		if ( isset( $this->campaign_related[ $field ] ) ) {
			$special_campaign_keys = array_keys( $this->special_campaigns );
			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}
			$urls = array();
			$campagins = array();
			if ( strpos( $field, '_click_link' ) !== false ) {
				foreach ( $value as $k => $v ) {
					if ( is_numeric( $v ) || in_array( $v, $special_campaign_keys ) ) {
						$campagins[] = $v;
					} else {
						$urls[] = $v;
					}
				}
				$return['value'] = implode( ' ' . esc_html__( 'or', 'mailster' ) . ' ', array_map( 'esc_url', $urls ) );
				if ( ! empty( $campagins ) ) {
					$return['value'] .= '<br> ' . esc_html__( 'in', 'mailster' ) . ' ' . $opening_quote . implode( $closing_quote . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $opening_quote, array_map( array( $this, 'get_campaign_title' ), $campagins ) ) . $closing_quote;
				}
			} else {
				$return['value'] = $opening_quote . implode( $closing_quote . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $opening_quote, array_map( array( $this, 'get_campaign_title' ), $value ) ) . $closing_quote;
			}
		} elseif ( isset( $this->list_related[ $field ] ) ) {
			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}
			$return['value'] = $opening_quote . implode( $closing_quote . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $opening_quote, array_map( array( $this, 'get_list_title' ), $value ) ) . $closing_quote;
		} elseif ( 'geo' == $field ) {
			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}
			$return['operator'] = '<em>' . $this->nice_name( $operator, 'operator', $field ) . '</em>';
			$return['value'] = $opening_quote . implode( $closing_quote . ' ' . esc_html__( 'or', 'mailster' ) . ' ' . $opening_quote, array_map( array( $this, 'get_country_name' ), $value ) ) . $closing_quote;
		} elseif ( 'rating' == $field ) {
			$stars = ( round( $this->sanitize_rating( $value ) / 10, 2 ) * 50 );
			$full = max( 0, min( 5, floor( $stars ) ) );
			$half = max( 0, min( 5, round( $stars - $full ) ) );
			$empty = max( 0, min( 5, 5 - $full - $half ) );
			$return['operator'] = '<em>' . $this->nice_name( $operator, 'operator', $field ) . '</em>';
			$return['value'] = '<span class="screen-reader-text">' . sprintf( esc_html__( '%d stars', 'mailster' ), $full ) . '</span>'
			. str_repeat( '<span class="mailster-icon mailster-icon-star"></span>', $full )
			. str_repeat( '<span class="mailster-icon mailster-icon-star-half"></span>', $half )
			. str_repeat( '<span class="mailster-icon mailster-icon-star-empty"></span>', $empty );

		} else {
			$return['operator'] = '<em>' . $this->nice_name( $operator, 'operator', $field ) . '</em>';
			$return['value'] = $opening_quote . '<strong>' . $this->nice_name( $value, 'value', $field ) . '</strong>' . $closing_quote;
		}

		return $formated ? $return : strip_tags( $return );

	}


	private function sanitize_rating( $value ) {
		if ( ! $value || ! (float) $value ) {
			return 0;
		}
		$value = str_replace( ',', '.', $value );
		if ( strpos( $value, '%' ) !== false || $value > 5 ) {
			$value = (float) $value / 100;
		} elseif ( $value > 1 ) {
			$value = (float) $value * 0.2;
		}
		return $value;
	}

	public function get_campaign_title( $post ) {

		if ( ! $post ) {
			return __( 'Any Campaign', 'maislter' );
		}

		if ( isset( $this->special_campaigns[ $post ] ) ) {
			return $this->special_campaigns[ $post ];
		}

		$title = get_the_title( $post );
		if ( empty( $title ) ) {
			$title = '#' . $post;
		}
		return $title;
	}

	public function get_list_title( $list_id ) {

		if ( $list = mailster( 'lists' )->get( $list_id ) ) {
			return $list->name;
		}
		return $list_id;
	}

	public function get_country_name( $code ) {

		return mailster( 'geo' )->code2Country( $code );
	}


	private function nice_name( $string, $type = null, $field = null ) {

		switch ( $type ) {
			case 'field':
				if ( isset( $this->fields[ $string ] ) ) {
					return $this->fields[ $string ];
				}
				if ( isset( $this->custom_fields[ $string ] ) ) {
					return $this->custom_fields[ $string ]['name'];
				}
				if ( isset( $this->campaign_related[ $string ] ) ) {
					return $this->campaign_related[ $string ];
				}
				if ( isset( $this->list_related[ $string ] ) ) {
					return $this->list_related[ $string ];
				}
				if ( isset( $this->meta_fields[ $string ] ) ) {
					return $this->meta_fields[ $string ];
				}
				if ( isset( $this->wp_user_meta[ $string ] ) ) {
					return $this->wp_user_meta[ $string ];
				}
				break;
			case 'operator':
				if ( in_array( $field, $this->time_fields ) && isset( $this->date_operators[ $string ] ) ) {
					return $this->date_operators[ $string ];
				}
				if ( isset( $this->operators[ $string ] ) ) {
					return $this->operators[ $string ];
				}
				if ( 'AND' == $string ) {
					return __( 'and', 'mailster' );
				}
				if ( 'OR' == $string ) {
					return __( 'or', 'mailster' );
				}
				break;
			case 'value':
				if ( in_array( $field, $this->time_fields ) ) {
					return date( mailster( 'helper' )->dateformat(), strtotime( $string ) );
				}
				if ( 'form' == $field ) {
					if ( $form = mailster( 'forms' )->get( (int) $string, false, false ) ) {
						return $form->name;
					}
				} elseif ( 'wp_capabilities' == $field ) {
					global $wp_roles;
					if ( isset( $wp_roles->roles[ $string ] ) ) {
	    				return translate_user_role( $wp_roles->roles[ $string ]['name'] );
					}
				}

				break;

		}

		return $string;

	}

}
