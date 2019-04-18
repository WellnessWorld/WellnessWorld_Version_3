<?php

/**
 * Queries for layouts that are used only for one post.
 *
 * Goes behind the usual mechanism which required first loading all layouts and then iterating over them.
 * As usual, the WPML interoperability is problematic. When WPML is active, we are actually looking for
 * layouts that are used for one *translation group*. This will obviously break if user deactivates WPML
 * and expects us to magically know what his intentions are. :)
 *
 * Usage:
 *      $batch_number = 0;
 *      $query = new TLM_Migration_Candidate_Query();
 *      return $query->get_batch( $batch_number );
 */
class TLM_Migration_Candidate_Query {


	/** @var int Size of the first batch */
	private $initial_batch_size;

	/** @var int Size of consecutive batches */
	private $regular_batch_size;


	const DEFAULT_BATCH_SIZE = 15;


	/**
	 * TLM_Migration_Candidate_Query constructor.
	 *
	 * @param null|int $initial_batch_size Size of the first batch. Defaults to DEFAULT_BATCH_SIZE.
	 * @param null|int $regular_batch_size Size of consecutive batches. Defaults to DEFAULT_BATCH_SIZE * 2.
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $initial_batch_size = null, $regular_batch_size = null ) {

		$this->initial_batch_size = ( $initial_batch_size ? (int) $initial_batch_size : self::DEFAULT_BATCH_SIZE );
		$this->regular_batch_size = ( $regular_batch_size ? (int) $regular_batch_size : $this->initial_batch_size * 2 );

		if( $this->initial_batch_size <= 0 || $this->regular_batch_size <= 0 ) {
			throw new InvalidArgumentException();
		}

	}


	/**
	 * Calculate offset and limit values from batch number.
	 *
	 * @param $batch_number
	 *
	 * @return int[] List of offset and limit values.
	 */
	private function get_pagination_for_batch( $batch_number ) {
		if( 0 === $batch_number ) {
			return array( 0, $this->initial_batch_size );
		} else {
			return array(
				$this->initial_batch_size + ( ( $batch_number -1 ) * $this->regular_batch_size ),
				$this->regular_batch_size
			);
		}
	}


	/**
	 * Load a batch of results.
	 *
	 * @param int $batch_number A zero-based number of the batch. First batch may be of a different size than the rest.
	 *
	 * @return array[] Array of results. Each result is an associative array with following keys:
	 *     - current_layout_id
	 *     - current_layout_slug
	 *     - current_layout_title
	 *     - post_id
	 *     - post_slug
	 *     - post_title
	 *     - post_type
	 *
	 * @throws InvalidArgumentException
	 */
	public function get_batch( $batch_number ) {
		if( $batch_number !== (int) $batch_number || $batch_number < 0 ) {
			throw new InvalidArgumentException();
		}

		list( $offset, $limit ) = $this->get_pagination_for_batch( $batch_number );

		$result = null;

		if( Toolset_WPML_Compatibility::get_instance()->is_wpml_active_and_configured() ) {
			$result = $this->get_batch_with_wpml( $offset, $limit );
		} else {
			$result = $this->get_batch_without_wpml( $offset, $limit );
		}

		return apply_filters( 'tlm_get_batch_results', $result, $offset, $limit );
	}


	/**
	 * Load a batch of results when using WPML.
	 *
	 * @see self::build_query_with_wpml
	 *
	 * @param $offset
	 * @param $limit
	 *
	 * @return array
	 */
	private function get_batch_with_wpml( $offset, $limit ) {

		global $wpdb;
		$query = $this->build_query_with_wpml( $offset, $limit );

		$results_with_any_post = $wpdb->get_results( $query );

		$results_with_good_translation = array();
		foreach( $results_with_any_post as $intermediary_result ) {

			$final_result = array(
				'current_layout_slug' => sanitize_title( $intermediary_result->layout_slug ),
				'current_layout_id' => (int) $intermediary_result->layout_id,
				'current_layout_title' => sanitize_text_field( $intermediary_result->layout_title ),
				'post_type' => sanitize_text_field( $intermediary_result->post_type )
			);

			// We got *a* post ID in the results, but not neccesarily the right one.
			// The post is from the correct translation group but we still need to translate it.
			$final_result = $this->add_missing_post_information( $final_result, $intermediary_result->post_id );

			$results_with_good_translation[] = $final_result;
		}

		return $results_with_good_translation;
	}


	/**
	 * Get a list of post statuses we care about when migrating layouts.
	 *
	 * @param bool $implode Whether to return the imploded string that can be used in a MySQL query.
	 *
	 * @return array|string
	 */
	private function get_relevant_post_statuses( $implode = true ) {
		if( $implode ) {
			$post_statuses = $this->get_relevant_post_statuses( false );
			return '\'' . implode( '\', \'', $post_statuses ) . '\'';
		} else {
			return array( 'publish', 'draft', 'pending', 'private', 'future' );
		}
	}


	/**
	 * Build the query if WPML is being used.
	 *
	 * This assumes the existence of the icl_translations table. Unlike in build_query_without_wpml(),
	 * we don't care about a layout having a single post ID that uses it, but a single translation group of posts.
	 * Another assumption is that all posts in a translation group share the same post type.
	 *
	 * The query will give us all information about the layout. For the post, we need to do this in PHP later
	 * because of WPML logic which is too complex/arbitrary to mimic purely in MySQL.
	 *
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return string Query string.
	 */
	private function build_query_with_wpml( $offset, $limit ) {

		global $wpdb;

		$relevant_post_statuses = $this->get_relevant_post_statuses();

		// This will give us the layout slug and a post ID.
		$query = $wpdb->prepare( "
			SELECT -- get all necessary info about the layout from the results of the inner query 
				result.post_id AS post_id,
			    result.post_type AS post_type,
				layout.ID AS layout_id, 
				layout.post_name AS layout_slug,
				layout.post_title AS layout_title
			FROM (
				SELECT -- this will give us the layout slug and a post ID 
					layout_slug_meta.meta_value AS layout_slug, 
					
					# we need just ANY post ID from the translation group, and this is the way to get it
					MIN(post.ID) AS post_id, 
					
					# this value could come from a different post than post_id, but we don't care
					MIN(post.post_type) AS post_type 
					
				FROM {$wpdb->posts} AS post -- start with all posts
				
					# this natural join will give us posts which have any layout assigned directly,
					# together with the first WHERE clause [1] (it's faster this way)
					JOIN {$wpdb->postmeta} AS layout_slug_meta
						ON ( post.ID = layout_slug_meta.post_id )
						  
					# we join the row from the icl_translations table if the post has one there, nulls otherwise
					# together with the second WHERE clause [2] (it's faster this way)
					LEFT JOIN {$wpdb->prefix}icl_translations AS translation_origin
					    ON ( post.ID = translation_origin.element_id ) 
				WHERE
				 	/* [1] */ layout_slug_meta.meta_key = %s
				 	/* [2] */ AND translation_origin.element_type LIKE %s
					AND post.post_status IN ( {$relevant_post_statuses} )
				GROUP BY layout_slug_meta.meta_value -- one result per layout slug
				HAVING 
					COUNT(post.ID) >= 1 -- get layout slugs that have at least one post assigned...  
					
					# ...and now we have two cases: a set of posts in one translation group, or one untranslated post
					AND ( 
						( 
							# all posts assigned to this layout are in the same translation group
							COUNT(DISTINCT translation_origin.trid) = 1  
							# and there are no untranslated posts assigned
							AND COUNT(CASE WHEN translation_origin.trid IS NULL THEN 1 ELSE NULL END) = 0 
						) OR (
							
							# there is exactly one untranslated post
						    COUNT(CASE WHEN translation_origin.trid IS NULL THEN 1 ELSE NULL END) = 1
						     
						    # and there are no translated posts
						    AND COUNT(translation_origin.trid) = 0 
						)
					)
				LIMIT %d, %d
				
		  	) AS result
			JOIN {$wpdb->posts} AS layout
				ON ( result.layout_slug = layout.post_name )
			WHERE layout.post_type = %s",
			WPDDL_LAYOUTS_META_KEY,
			'post_%',
			$offset,
			$limit,
			WPDDL_LAYOUTS_POST_TYPE
		);

		return $query;
	}


	/**
	 * Adjust results by getting the correct post translation and completing post information.
	 *
	 * @param array $result An associative array which must at least contain the 'post_type' key.
	 * @param int $post_id ID of the post to translate.
	 *
	 * @return array
	 */
	private function add_missing_post_information( $result, $post_id ) {

		// "return original if missing", so we always have at least something.
		$translated_post_id = apply_filters( 'wpml_object_id', (int) $post_id, $result['post_type'], true );

		$post = WP_Post::get_instance( $translated_post_id );

		$result = array_merge(
			$result,
			array(
				'post_id' => $post->ID,
				'post_slug' => $post->post_name,
				'post_title' => $post->post_title,
				'post_type' => $post->post_type
			)
		);

		return $result;
	}


	/**
	 * Load a batch of results when not using WPML.
	 *
	 * @see self::build_query_without_wpml
	 *
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	private function get_batch_without_wpml( $offset, $limit ) {

		global $wpdb;

		$query = $this->build_query_without_wpml( $offset, $limit );

		$rows = $wpdb->get_results( $query );
		$results = array();

		foreach( $rows as $row ) {
			$results[] = array(
				'current_layout_id' => (int) $row->current_layout_id,
				'current_layout_slug' => sanitize_title( $row->current_layout_slug ),
				'current_layout_title' => sanitize_text_field( $row->current_layout_title ),
				'post_id' => (int) $row->post_id,
				'post_slug' => sanitize_title( $row->post_slug ),
				'post_title' => sanitize_text_field( $row->post_title ),
				'post_type' => sanitize_text_field( $row->post_type )
			);
		}

		return $results;
	}


	/**
	 * Build the query if WPML is NOT being used.
	 *
	 * Much simpler scenario than with WPML. We look for posts that have a layout assigned directly,
	 * and select only such that have unique layout slug value.
	 *
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return string Query string.
	 */
	private function build_query_without_wpml( $offset, $limit ) {

		global $wpdb;

		$post_statuses = $this->get_relevant_post_statuses();

		$query = $wpdb->prepare(
			"SELECT -- use the inner query to get all the information necessary about the layout as well as the post 
				post.ID as post_id, 
				post.post_name AS post_slug,
				post.post_title AS post_title,
				post.post_type AS post_type,
				layout.ID AS current_layout_id,
				layout.post_name AS current_layout_slug,
				layout.post_title AS current_layout_title
			FROM (
			 
				SELECT 
					layout_slug_meta.meta_value AS layout_slug,
					 
					# this value is not grouped so we can't access it directly, 
					# but we know that there would be only one result
					MIN(post.ID) AS post_id 
					
				FROM {$wpdb->posts} AS post
					# get posts that have any layout asssigned
					# works together with [1]
					JOIN {$wpdb->postmeta} AS layout_slug_meta
						ON ( layout_slug_meta.post_id = post.ID )  
				WHERE 
					post.post_status IN ( {$post_statuses} )
					/* [1] */ AND layout_slug_meta.meta_key = %s 
				GROUP BY layout_slug_meta.meta_value 
				HAVING COUNT( post.ID ) = 1 -- exclude posts with layouts that are used multiple times
				LIMIT %d, %d
				
			) AS result
				JOIN {$wpdb->posts} AS post
					ON ( result.post_id = post.ID )
				JOIN {$wpdb->posts} AS layout
					ON ( result.layout_slug = layout.post_name )
			WHERE layout.post_type = %s
			",
			WPDDL_LAYOUTS_META_KEY,
			$offset,
			$limit,
			WPDDL_LAYOUTS_POST_TYPE
		);

		return $query;
	}



}