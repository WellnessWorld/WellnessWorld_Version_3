<?php

/**
 * Class TLM_MigratePreProcess
 */
class TLM_MigratePreProcess {

	private $models = array();
	private $layouts_ids;

	/**
	 * TLM_MigratePreProcess constructor.
	 *
	 * @param array $data
	 */
	public function __construct( array $data ) {
		$metas = $this->get_all_metas();

		if( isset( $data['posts'] ) && isset( $metas['posts'] ) ){
			$metas['posts'] = array_merge( $metas['posts'], $data['posts'] );
		}

		$data = $metas + $data ;
		$this->models = $this->process_post_data( $data );
		$this->set_layout_ids();
	}

	/**
	 * @return mixed
	 */
	public function get_layout_ids(){
		return $this->layouts_ids;
	}

	/**
	 *
	 */
	private function set_layout_ids(){
		$this->layouts_ids = array_map( array(&$this, 'map_ids_array'), $this->models );
	}

	/**
	 * @param $migrate
	 *
	 * @return int
	 */
	public function map_ids_array( $migrate ){
		return (int) $migrate->current_layout;
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	private function process_post_data( $data ) {

		if ( ! isset( $data['posts'] ) || count( $data['posts'] ) === 0 ) {
			return array();
		}

		return $this->populate( $data );
	}

	/**
	 * @return array
	 */
	private function get_all_metas(){
		global $wpdb;

		$query = $wpdb->prepare( "SELECT $wpdb->postmeta.meta_value FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key = '%s'", TLM_MIGRATION_DATA_META );

		$results = $wpdb->get_col( $query );

		if( count( $results ) > 0 ){
			$results = array_map( 'maybe_unserialize', $results );
			$ret = array();
			foreach( $results as $result ){
				$ret[$result->id] = (array) $result;
			}
			$ret['posts'] = array_keys( $ret );
			return $ret;
		}

		return array();
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	private function populate( $data ){
		$ret = array();

		foreach ( $data['posts'] as $id ) {

			$model = new TLM_MigrationObject( $id, $data );
			$ret[] = $model;
		}

		return $ret;
	}

	/**
	 * @return array
	 */
	public function migrate( ){

		foreach( $this->models as $key => $model ){
			/* process only if not done already */
			if( $model->__get('status') === 'none' ){
				$model->start_migration();
				$this->migrate_translations( $model );
			}
		}

		return $this->models;
	}

	/**
	 * @return bool
	 */
	public static function is_wpml_active(){
		return TLM_Admin::is_wpml_active();
	}

	/**
	 * @param $model
	 *
	 * @return array|null
	 */
	private function migrate_translations( $model ){

		if( false === self::is_wpml_active() ){
			return null;
		}

		$translate = new TLM_FactoryTranslationHelper( $model->__get( 'id' ), $model->__get( 'post_type' ), $model->__get( 'template' ) );
		$translations = $translate->process_translations();
		if( $translations ){
			foreach( $translations as $translation ){
				array_push( $this->models, $translation );
			}
		}

		return $this->models;
	}

	/**
	 * @return array
	 */
	public function to_json(){
		return array_map( array($this, 'get_iterators' ), $this->models );
	}

	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	public function get_iterators( $item ){
		return $item->getIterator();
	}

	/**
	 * @param $name
	 *
	 * @return null
	 */
	public function __get( $name ) {

		if( $this->__isset( $name ) ){
			return $this->{$name};
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);
		return null;
	}

	/**
	 * @param $property
	 *
	 * @return bool
	 */
	public function __isset( $property ){
		return property_exists( $this, $property );
	}
}