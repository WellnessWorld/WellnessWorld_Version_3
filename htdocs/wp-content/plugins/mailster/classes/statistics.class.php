<?php

class MailsterStatistics {

	private $calendar_table = null;

	public function __construct() {

	}


	/**
	 *
	 *
	 * @param unknown $range (optional)
	 * @return unknown
	 */
	public function get_dashboard( $range = '7 days' ) {

		$rawdata = $this->get_signups( strtotime( '-' . $range ), time() );

		return array(
			'labels' => $this->get_labels( $rawdata ),
			'datasets' => $this->get_datasets( $rawdata ),
		);

	}


	/**
	 *
	 *
	 * @param unknown $rawdata
	 * @return unknown
	 */
	private function get_labels( $rawdata ) {

		global $wp_locale;

		$dates = array_keys( $rawdata );

		$i = 0;
		$prev = null;

		foreach ( $rawdata as $date => $count ) {
			$d = strtotime( $date );
			$str = $wp_locale->weekday_abbrev[ $wp_locale->weekday[ date( 'w', $d ) ] ];
			if ( ! is_null( $prev ) ) {
				$grow = $count - $prev;
				if ( $grow > 0 ) {
					$str .= ' ▲+' . $this->format( $grow ) . ' ';
				} elseif ( $grow < 0 ) {
					$str .= ' ▼-' . $this->format( $grow ) . ' ';
				}
			}
			$prev = $count;
			$dates[ $i ] = $str;
			$i++;
		}

		return $dates;

	}


	/**
	 *
	 *
	 * @param unknown $rawdata
	 * @return unknown
	 */
	private function get_datasets( $rawdata ) {

		return array(
			// array(
			// 'data' => array_values( $rawdata ),
			// 'fillColor' => "rgba(111,191,77,0.2)",
			// 'strokeColor' => "rgba(111,191,77,1)",
			// 'pointColor' => "rgba(111,191,77,1)",
			// 'pointStrokeColor' => "#fff",
			// 'pointHighlightFill' => "#fff",
			// 'pointHighlightStroke' => "rgba(111,191,77,1)",
			// ),
			// array(
			// 'data' => array_values( $rawdata ),
			// 'fillColor' => "rgba(43,179,231,0.2)",
			// 'strokeColor' => "rgba(43,179,231,1)",
			// 'pointColor' => "rgba(43,179,231,1)",
			// 'pointStrokeColor' => "#fff",
			// 'pointHighlightFill' => "#fff",
			// 'pointHighlightStroke' => "rgba(43,179,231,1)",
			// ),
			array(
				'data' => array_values( $rawdata ),
				'backgroundColor' => 'rgba(43,179,231,0.2)',
				'borderColor' => 'rgba(43,179,231,1)',
				'pointColor' => 'rgba(43,179,231,1)',
				'pointBorderColor' => 'rgba(43,179,231,1)',
				'pointBackgroundColor' => '#fff',
				'pointHoverBackgroundColor' => 'rgba(43,179,231,1)',
			),
		);

	}


	/**
	 *
	 *
	 * @param unknown $from (optional)
	 * @param unknown $to   (optional)
	 * @return unknown
	 */
	public function get_signups( $from = null, $to = null ) {

		global $wpdb;

		$from = is_null( $from ) ? time() : $from;
		$to = is_null( $to ) ? time() + DAY_IN_SECONDS - 1 : $to;

		$dates = $this->get_date_range( $from, $to );
		$dates = array_fill_keys( $dates, 0 );

		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mailster_subscribers AS subscribers LEFT JOIN {$wpdb->prefix}mailster_lists_subscribers AS list_subscribers ON subscribers.ID = list_subscribers.subscriber_id WHERE subscribers.status = 1 AND (list_subscribers.added != 0 OR list_subscribers.added IS NULL) AND IF(subscribers.confirm, subscribers.confirm, subscribers.signup) < %d", $from ) );

		$sql = "SELECT FROM_UNIXTIME(IF(subscribers.confirm, subscribers.confirm, subscribers.signup), '%Y-%m-%d') AS the_date, COUNT(*) AS increase FROM {$wpdb->prefix}mailster_subscribers AS subscribers LEFT JOIN {$wpdb->prefix}mailster_lists_subscribers AS list_subscribers ON subscribers.ID = list_subscribers.subscriber_id WHERE subscribers.status = 1 AND (list_subscribers.added != 0 OR list_subscribers.added IS NULL) GROUP BY the_date HAVING the_date >= '" . date( 'Y-m-d', $from ) . "' AND the_date <= '" . date( 'Y-m-d', $to ) . "'";

		$increase_data = $wpdb->get_results( $sql );

		$increase_data = array_combine( wp_list_pluck( $increase_data, 'the_date' ), wp_list_pluck( $increase_data, 'increase' ) );

		foreach ( $dates as $date => $count ) {

			if ( isset( $increase_data[ $date ] ) ) {
				$total += $increase_data[ $date ];
			}
			$dates[ $date ] = $total;
		}

			return $dates;
	}



	/**
	 *
	 *
	 * @param unknown $from
	 * @param unknown $to
	 * @param unknown $format (optional)
	 * @return unknown
	 */
	private function get_calendar_table( $from, $to, $format = 'Y-m-d' ) {

		global $wpdb;
		$dates = $this->get_date_range( $from, $to, '+1 day', $format );
		$count = count( $dates );

		if ( ! $this->calendar_table ) {
			$this->calendar_table = "{$wpdb->prefix}mailster_" . uniqid();
			$sql = "CREATE TEMPORARY TABLE {$this->calendar_table} ( thedate date );";
			if ( false == $wpdb->query( $sql ) ) {
				return false;
			}
		} else {
			$sql = "TRUNCATE {$this->calendar_table};";
			if ( false == $wpdb->query( $sql ) ) {
				return false;
			}
		}

		$sql = "INSERT INTO {$this->calendar_table} (thedate) VALUES ('" . implode( "'),('", $dates ) . "');";

		if ( false !== $wpdb->query( $sql ) ) {
			return $dates;
		}

		return false;

	}


	/**
	 *
	 *
	 * @param unknown $first
	 * @param unknown $last
	 * @param unknown $step   (optional)
	 * @param unknown $format (optional)
	 * @return unknown
	 */
	private function get_date_range( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) {

		$dates = array();
		$current = $first;

		while ( $current <= $last ) {

			$dates[] = date( $format, $current );
			$current = strtotime( $step, $current );
		}

		return $dates;
	}


	/**
	 *
	 *
	 * @param unknown $value
	 * @return unknown
	 */
	private function format( $value ) {

		$value = (int) $value;

		if ( $value >= 1000000 ) {
			return round( $value / 1000, 1 ) . 'M';
		} elseif ( $value >= 1000 ) {
			return round( $value / 1000, 1 ) . 'K';
		}

		return ! ($value % 1) ? $value : '';
	}

}
