<?php

/**
 * Class TLM_MigrationObject
 */
class TLM_MigrationObject extends stdClass implements IteratorAggregate{

	protected $under_migration_key = TLM_MIGRATION_BOOL_META;

	private $migrate_key = TLM_MIGRATION_DATA_META;
	private $in_use_key = WPDDL_PRIVATE_LAYOUTS_IN_USE;
	private $template_tmp_key = TLM_LAYOUTS_META_KEY;

	public $status = 'none';
	public $template_name = '';
	public $layout_name = '';
	public $template_slug = '';
	public $layout_slug = '';
	public $current_layout_deleted = false;
	public $post_title;
	public $post_type;
	public $post_name;
	public $current_layout;
	public $template;
	public $translate = null;
	public $disabled = '';

	/**
	 * @var array
	 */
	private static $statuses = array(
		'process',
		'created',
		'existing',
		'finish'
	);

	/**
	 * TLM_MigrationObject constructor.
	 *
	 * @param $id
	 * @param array $arguments
	 */
	public function __construct( $id, $arguments = array() )
	{
		$this->id = $id;
		$this->translate = $id;
		$this->populate( $arguments );
	}

	/**
	 * @param array $arguments
	 *
	 * @return object|Traversable
	 */
	private function populate( array $arguments = array() ){

		$arguments = (array) $arguments;

		if ( !empty($arguments) ) {
			if ( isset( $arguments[$this->id] ) && is_array( $arguments[$this->id] ) ){
				foreach( $arguments[$this->id]  as $key => $arg ){
					if( $key === 'template' && $arg == -1 ){
						$this->{$key} = null;
					} else {
						$this->{$key} = $arg;
					}
				}
			} else {

				$properties = array_keys( get_object_vars( $this ) );

				foreach ( $properties as $prefix ) {
					if ( isset( $arguments[$prefix.'_'.$this->id] ) ) {
						if( $prefix === 'template' && $arguments[$prefix.'_'.$this->id] == -1 ){
							$this->{$prefix} = null;
						} else {
							$this->{$prefix} = $arguments[$prefix.'_'.$this->id];
						}
					} elseif ( isset( $arguments[$prefix] ) ) {
						if( $prefix === 'template' && $arguments[$prefix] == -1 ){
							$this->{$prefix} = null;
						} else {
							$this->{$prefix} = $arguments[$prefix];
						}
					}
				}
			}
		}
		return $this->getIterator();
	}

	/**
	 * @param $method
	 * @param $arguments
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function __call($method, $arguments) {
		if ( method_exists( $this, $method ) ) {
			return call_user_func_array( array($this, $method), $arguments );
		} else {
			throw new Exception("Fatal error: Call to undefined method stdObject::{$method}()");
		}
	}

	/**
	 *
	 */
	public function start_migration( $is_translation = false ){
		if( $this->status === 'none' ){
			$this->status = self::$statuses[0];
			$this->update_meta( $this->under_migration_key, 'yes' );
			$this->migrate(  $is_translation );
		}
	}

	public function set_translate( $post_id ){
		$this->translate = $post_id;
	}

	/**
	 * @return object
	 */
	public function getIterator()
	{
		return  (object) iterator_to_array( new RecursiveArrayIterator( $this ) );
	}

	/**
	 * @return bool|int
	 */
	public function save(){
		return $this->update_meta( $this->migrate_key, $this->getIterator() );
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	protected function get_meta( $key ){
		return get_post_meta( $this->id, $key, true );
	}

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	protected function delete_meta( $key ){
		return delete_post_meta( $this->id, $key, $this->get_meta( $key ) );
	}

	/**
	 * @param $key
	 * @param $data
	 *
	 * @return bool|int
	 */
	protected function update_meta( $key, $data ){
		return update_post_meta( $this->id, $key, $data, $this->get_meta( $key ) );
	}

	/**
	 * @return bool
	 */
	public function clean_up_migration_data( ){
		$this->delete_meta( $this->template_tmp_key );
		$this->delete_meta( $this->under_migration_key );
		return $this->delete_meta( $this->migrate_key );
	}

	/**
	 *
	 */
	protected function assign_temporary_template( ){
		if( $this->__isset( 'template' ) && $this->template ){
			$this->template_slug = $this->get_post_slug( $this->template );
			$this->template_name = $this->get_post_title( $this->template );
			$this->update_meta( $this->template_tmp_key, $this->template_slug );
		}
	}

	/**
	 * @return bool|int|null
	 */
	public function migrate(  $is_translation = false){
		try{

			$created = false;

			if( !$is_translation ){
				$created = $this->create_new_private_layout();
			}

			$this->assign_temporary_template();

		} catch( Exception $e ){

			return null;
		}

		if( $created ){
			$this->status = self::$statuses[1];
		} else {
			$this->status = self::$statuses[2];
		}

		return $this->save();
	}

	/**
	 * @param WPDDL_Update_Translated_Private_Layout $updater
	 * @param $original_post_id
	 *
	 * @return bool|int
	 */
	public function translate_private_layout( WPDDL_Update_Translated_Private_Layout $updater, $original_post_id ){
		$updater->update_translated_layouts_when_original_is_updated( $original_post_id );
		$this->set_layout_properties( );
		return $this->save();
	}

	private function set_layout_properties( ){
		$this->layout_name = TLM_NAME_PREFIX . ' ' . $this->post_type . ' ' . $this->post_title;
		$this->layout_slug = 'layout-for-'.$this->post_name;
	}

	/**
	 * @return bool
	 */
	public function finish_migration( ){
		$this->status = self::$statuses[3];
		$this->assign_template( );
		$this->delete_old_layout();
		$this->update_post_content_for_private_layout( );
		return $this->clean_up_migration_data( );
	}

	public function update_post_content_for_private_layout( ){
		$private_layout = $this->get_private_layout( );
		return $private_layout->update_post_content_for_private_layout( $this->id );
	}

	private function get_private_layout( ){
		return new WPDDL_Private_Layout();
	}

	/**
	 *
	 */
	private function delete_old_layout(){
		if( $this->current_layout && (int) WPDD_Utils::layout_assigned_count_num( $this->current_layout ) === 0 ){
			$deleted = WPDD_LayoutsListing::delete_layout( array( $this->current_layout ) );
			$this->current_layout_deleted = count( $deleted ) > 0;
		}
	}

	/**
	 * @return bool
	 */
	public function is_under_migration(){
		return $this->get_meta( $this->under_migration_key ) === 'yes';
	}

	/**
	 * @return mixed|void
	 */
	private function fetch_current_layout_object( ){
		return apply_filters( 'ddl-get_layout_settings', $this->current_layout, true, false );
	}

	/**
	 * @return bool|int
	 * @throws Exception
	 */
	private function create_new_private_layout() {

		$layout_data = $this->fetch_current_layout_object();

		$factory = $this->get_private_layout_factory( $layout_data );

		$layout = $this->handle_private_layout_rows_and_cells( $factory );

		if ( is_object( $layout ) ) {
			$this->layout_name = $layout->name;
			$this->layout_slug = $layout->slug;
			$ret               = $this->save_layout_data( $this->id, $layout );
			return $ret;
		} else {
			throw new Exception( 'Expected result is layout object' );
		}

	}

	private function get_private_layout_factory( $layout_data ){

		return new TLM_PrivateLayoutFactory( $layout_data, array(
			'id'         => $this->id,
			'post_title' => $this->post_title,
			'post_name' => $this->post_name,
			'post_type'  => $this->post_type
		) );

	}

	/**
	 * @param $factory
	 *
	 * @return mixed
	 */
	private function handle_private_layout_rows_and_cells( $factory ) {

		$factory->change_layout_rows_modes();

		$factory->process_layout_cleanup( );

		return $factory->get_layout();
	}

	/**
	 * @return null|string
	 * @deprecated
	 */
	private function handle_post_content_data( ){

		$post_content = get_post_field( 'post_content', $this->id );

		if( $post_content ){
			update_post_meta( $this->id , WPDDL_PRIVATE_LAYOUTS_ORIGINAL_CONTENT_META_KEY, $post_content );
			return $post_content;
		}

		return null;
	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	private function get_post_slug( $post_id ){
		return get_post_field( 'post_name', $post_id );
	}

	/**
	 * @param $post_id
	 *
	 * @return string
	 */
	private function get_post_title( $post_id ){
		return get_post_field( 'post_title', $post_id );
	}

	/**
	 * @param $post_id
	 * @param $layout_id
	 * @param $post_type
	 *
	 * @return mixed|void
	 */
	private function assign_template_layout( $post_id, $layout_id, $post_type ) {
		$layout_name = $this->get_post_slug( $layout_id );

		$ret = update_post_meta( $post_id, WPDDL_LAYOUTS_META_KEY, $layout_name, get_post_meta( $post_id, WPDDL_LAYOUTS_META_KEY, true ) );
		$tpl = $this->get_layout_template( $this->get_post_types_manager( ), $post_type );

		if ( $ret && $tpl !== null ) {
			update_post_meta( $post_id, '_wp_page_template', $tpl );
		}

		return $ret;
	}

	/**
	 * @param WPDD_Layouts_PostTypesManager $manager
	 * @param string $post_type
	 *
	 * @return string
	 */
	private function get_layout_template( WPDD_Layouts_PostTypesManager $manager, $post_type = 'post'){
		return $manager->get_layout_template_for_post_type( $post_type );
	}

	/**
	 * @return WPDD_Layouts_PostTypesManager
	 */
	private function get_post_types_manager( ){
		return WPDD_Layouts_PostTypesManager::getInstance();
	}

	/**
	 *
	 */
	private function assign_template( ){
		if( $this->__isset( 'template' ) && $this->template ){
			$this->assign_template_layout( $this->id, $this->template, $this->post_type );
		}
	}

	/**
	 * @param $id
	 * @param $data
	 *
	 * @return bool|int
	 */
	private function save_layout_data( $id, $data ){
		return WPDD_Layouts::save_layout_settings( $id, $data );
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

		if( $property === 'template' && property_exists( $this, $property ) && $this->{$property} == -1 ){
			return false;
		}

		return property_exists( $this, $property );
	}

	public function set_disabled( ){
		$this->disabled = 'disabled';
		return $this->save();
	}
}