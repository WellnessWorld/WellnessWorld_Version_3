<?php

/**
 * Class tlm
 */
class TLM_LayoutsGroupedHelper  {

	/**
	 * @var WPDD_LayoutsListing
	 */
	private $layoutsListing;

	/**
	 * @var mixed|void
	 */
	private $layouts;

	/**
	 * @var array|mixed|string|void
	 */
	private $post_types_options;

	/**
	 * @var array
	 */
	private $layouts_post_types;

	/**
	 * @var array
	 */
	private $all_layouts;

	private static $instance;

	const SINGLE_INDEX = 2;
	const DEFAULT_STATUS = 'publish';

	/**
	 * tlm constructor.
	 */
	function __construct( TLM_Migration_Candidate_Query $wpddLayoutsListing, WPDD_Layouts_PostTypesManager $layouts_PostTypesManager ) {
		$this->post_types_options = $layouts_PostTypesManager->get_post_types_options();
		$this->layouts_post_types = $this->set_post_types_layouts( );
		$this->all_layouts = $this->prepare_layouts_for_selector( TLM_GroupedLayouts::get_all_layouts( ) );
		$this->layoutsListing = $wpddLayoutsListing;
	}

	/**
	 * @return TLM_LayoutsGroupedHelper
	 */
	public static function getInstance( )
	{
		if (!self::$instance) {
			self::$instance = new TLM_LayoutsGroupedHelper( new TLM_Migration_Candidate_Query(), WPDD_Layouts_PostTypesManager::getInstance() );
		}

		return self::$instance;
	}

	/**
	 * @return array
	 * not used
	 */
	protected function set_post_types_layouts( ){

		$options = $this->post_types_options;

		$layouts = array();

		foreach( $options as $layout => $types ){
			$layout = explode( 'layout_', $layout );
			$layout_id = $layout[1];
			$item = TLM_GroupedLayouts::get_post_by_id( $layout_id );
			if( null !== $item ){
				$item->types = $types;
				$layouts[] = $item;
			}
		}

		return $layouts;
	}

	/**
	 * @return array
	 */
	protected function set_layouts_post_types( ){

		$options = $this->post_types_options;

		$post_types = array();

		foreach( $options as $layout => $types ){

			$layout = explode( 'layout_', $layout );
			$layout_id = $layout[1];

			foreach( $types as $type ){
				$item = array();
				$item['layout_id'] = (int) $layout_id;
				$item['post_type'] = $type;
				$obj = get_post_type_object( $type );
				$item['label'] = $obj->labels->name;
				$item['singular'] = $obj->labels->singular_name;
				$item['plural'] = $obj->labels->name;
				$post_types[] = $item;
			}

		}

		return $post_types;

	}

	function get_layouts_post_types( ){
		return $this->layouts_post_types;
	}

	/**
	 * @return mixed|void
	 */
	function get_layouts( ) {
		return $this->layouts;
	}

	/**
	 * @return mixed
	 */
	function get_layouts_for_single( $batch_num = 0 ) {
		return $this->layoutsListing->get_batch( $batch_num );
	}

	/**
	 * Get ID-s of layouts assigned to archives
	 * @return array
	 */
	function get_layouts_for_archive(){
		$get_archive_layouts = get_option( 'ddlayouts_options', array() );
		if( !is_array( $get_archive_layouts ) ){
			return array();
		}
		return array_unique( array_map('intval', array_values( $get_archive_layouts ) ) );
	}

	/**
	 * Compares all available layouts with archive layouts and layouts assigned to post types and removes
	 * unnecessary items
	 * @return array
	 */
	function prepare_layouts_for_selector( $all_layouts ){

		$all_possible_layouts = $all_layouts;
		$template_layouts_ids = array_map( array( $this, 'template_layout_ids' ), $this->layouts_post_types );
		$archive_layouts = $this->get_layouts_for_archive();

		foreach($all_possible_layouts as $key => $one_layout){

			if( in_array( $one_layout->ID, $template_layouts_ids ) ){
				unset($all_possible_layouts[$key]);
				continue;
			}

			if( in_array ($one_layout->ID, $archive_layouts ) ){
				unset($all_possible_layouts[$key]);
			} else {
				$all_possible_layouts[$key]->types = array();
			}
		}

		return $all_possible_layouts;
	}

	/**
	 *
	 * @return array
	 */
	private function prepare_page_layout_data( $batch_num = 0 ){
		$ret = array();

		$single_layouts = $this->get_layouts_for_single( $batch_num );
		$template_layouts = $this->layouts_post_types;
		$template_layouts_ids = array_map( array( $this, 'template_layout_ids' ), $template_layouts );
		$all_possible_layouts = $this->all_layouts;

		$template_layouts = array_merge( $template_layouts, $all_possible_layouts );

			foreach ( $single_layouts as $item ) {
				$in_use = $this->is_private_layout_in_use( $item['post_id'] );
				if( !$in_use && !in_array( $item['current_layout_id'], $template_layouts_ids) ){
					$item = (object) $item;
					$item->id = $item->post_id;
					$item->permalink = get_permalink( $item->post_id );
					$item->has_selected = false;
					foreach ( $template_layouts as $template_layout ) {

						$template = new stdClass();
						$template->slug = $template_layout->post_name;
						$template->title = $template_layout->post_title;
						$template->id = $template_layout->ID;

						if((int) $item->current_layout_id === (int) $template_layout->ID){
							continue;
						}

						if(count($template_layout->types) > 0){
							if( in_array( $item->post_type, $template_layout->types ) ){
								$template->selected = 'selected';
								$item->has_selected = true;
							} else {
								continue;
							}
						}

						$item->eligible_templates[] = $template;


					}
					$ret[] = $item;
				}
			}


		usort( $ret, array ( &$this, 'sort_by_post_title' ) );

		return $ret;
	}

	public function template_layout_ids( $item ){
		return $item->ID;
	}

	private function is_private_layout_in_use( $post_id ){
		return WPDD_Utils::is_private_layout_in_use( $post_id );
	}

	private function get_as_objects( $arrays ){
		return array_map( array(&$this, 'to_objects'), $arrays );
	}

	public function to_objects( $item ){
		return (object) $item;
	}

	public function setup_migration_table_data( $batch_num = 0 ){
		return $this->prepare_page_layout_data( $batch_num );
	}

	public function ajax_populate( ){
		if ( $_POST && wp_verify_nonce( $_POST['tlm_populate_nonce'], 'tlm_populate_nonce' ) ) {

			if ( isset( $_POST['paged'] ) ) {

				$batch_num = (int) $_POST['paged'];

				$data = $this->setup_migration_table_data( $batch_num );

				$send = array( 'data' => $data );

			} else {
				$send = array( 'error' => __( sprintf( 'Pagination value required, no value given. %s', __METHOD__), 'ddl-layouts') );
			}

		} else {
			$send = array( 'error' => __(sprintf('Nonce problem: apparently we do not know where the request comes from. %s', __METHOD__), 'ddl-layouts') );
		}

		wp_send_json( $send );
	}

	function sort_by_post_title($a, $b) {
		return strcmp( $a->post_title, $b->post_title );
	}

	function filter_missing_template_layout_items( $items ){
			return array_filter( $items, array( &$this, 'filter_no_template') );
	}

	function get_reduced_post_types_list( $items ){
		return array_map( array(&$this, 'as_post_type_object'), array_unique( array_map( array(&$this, 'reduce_to_post_type'), $items ) ) );
	}

	function filter_no_template( $item ){
		return $item->has_selected === false;
	}

	function reduce_to_post_type( $item ){
		return  $item->post_type;
	}

	function as_post_type_object( $item ){
		return get_post_type_object( $item );
	}
}