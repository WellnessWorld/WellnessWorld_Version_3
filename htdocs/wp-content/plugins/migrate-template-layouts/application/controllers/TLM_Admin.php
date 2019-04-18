<?php

/**
 * Class TLM_Admin
 */
class TLM_Admin {

	/** @var TLM_Helper_Twig|null */
	protected $twig;

	/** @var TLM_LayoutsGroupedHelper|null */
	protected $helper;

	/** @var TLM_MigratePreProcess|null */
	protected $migrate;
	/**
	 * @var int
	 */
	private $step = 1;

	/**
	 *
	 */
	const PAGE_SLUG = 'dd_layouts_migration';

	/**
	 * TLM_Admin constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 *
	 */
	public function init() {

		add_filter( 'toolset_filter_register_menu_pages', array(&$this, 'tlm_create_menu'), 70, 1 );

		if ( $this->is_migration_page() ) {

			$this->helper = TLM_LayoutsGroupedHelper::getInstance( );
			$this->twig = new TLM_Helper_Twig( true );
			add_action( 'admin_init', array( &$this, 'load_dialog_boxes' ), 9999 );
			add_action( 'admin_init', array( &$this, 'register_scripts_and_styles' ) );
			add_action( 'admin_action_submit-form', array( &$this, 'tlm_handle_form_action' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
			add_action( 'admin_footer', array(&$this, 'load_backbone_templates') );
		}

		add_action( 'wp_ajax_finalise_migration_process', array( &$this, 'finalise_migration_process' ) );
		add_action( 'wp_ajax_cancel_migration_process', array( &$this, 'cancel_migration_process') );
		add_action( 'get_layout_id_for_render', array( &$this, 'handle_preview' ), 9999, 1 );
		add_action( 'wp_ajax_tlm_populate_table', array( TLM_LayoutsGroupedHelper::getInstance(), 'ajax_populate' ) );
		add_filter( 'tlm_get_batch_results', array( &$this, 'posts_not_in' ), 99, 1 );
		add_filter( 'tlm_private_layout_preview', array(__CLASS__, 'private_layout_preview'), 10, 2 );

	}

	public static final function private_layout_preview( $bool, $post_id ){
		return get_post_meta( $post_id, TLM_MIGRATION_BOOL_META, true ) === 'yes';
	}

	/**
	 * @param $id
	 *
	 * @return int
	 */
	public function handle_preview( $id ) {

		if ( isset( $_POST['private_layout_preview'] ) ) {

			global $post;

			if ( is_object( $post ) === false ) {
				return $id;
			}

			$meta = get_post_meta( $post->ID, TLM_LAYOUTS_META_KEY, true );
			$data = get_post_meta( $post->ID, TLM_MIGRATION_DATA_META, true );

			if( !is_object( $data ) && !$meta ){
				// not a migration object use default
				return $id;

			} elseif( is_object( $data ) && !$meta ){
				// no template layout use Wordpress public template
				return 0;
			} else {
				// use newly assigned template layout
				if ( is_object( $data ) && property_exists( $data, 'template' ) && property_exists( $data, 'template_slug' ) && $data->template_slug == $meta ) {
					$id = (int) $data->template;
				}
			}
		}

		return $id;
	}

	/**
	 * @return int
	 */
	private function get_step() {
		if ( ! isset( $_GET['step'] ) ) {
			return $this->step;
		} else {
			$this->step = $_GET['step'] + 1;

			return $this->step;
		}
	}

	/**
	 * @param $results
	 *
	 * @return array
	 */
	public function posts_not_in( $results ){

		if( ( isset( $_GET['page'] ) && $_GET['page'] === self::PAGE_SLUG ) ||
		    ( isset( $_POST['action'] ) && $_POST['action'] === 'tlm_populate_table' )
		    && !empty( $results )
		){
			$results = $this->get_filtered_first_table_results( $results );
		}

		return $results;
	}

	/**
	 * @param $results
	 *
	 * @return array
	 */
	protected function get_filtered_first_table_results( $results ){

		if( $this->migrate === null ){
			$this->migrate = new TLM_MigratePreProcess( array() );
		}

		return array_filter( $results, array(&$this, 'filter_first_table_results') );
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public function filter_first_table_results( $item ) {
		$ids = $this->migrate->get_layout_ids();
		return !in_array( $item['current_layout_id'], $ids );
	}

	/**
	 * @param $migrate_data
	 */
	private function load_candidates_listing( $migrate_data ) {
		if ( count( $migrate_data ) > 0 ) {
			$context = $this->build_generic_twig_context( $migrate_data );

			echo $this->twig->render( '/list-eligible/tlm-singled-assigned-items-list.tpl.twig', $context );
		}
	}

	/**
	 * @param $no_templates_data
	 */
	private function load_no_templates_list( $no_templates_data ) {
		$context = $this->build_generic_twig_context( $no_templates_data );
		echo $this->twig->render( '/list-eligible/tlm-no-template-notice.tpl.twig', $context );
	}

	/**
	 *
	 */
	private function load_main_tpl() {

		$context = $this->build_generic_twig_context( array() /* avoid php notice*/ );

		echo $this->twig->render( '/list-eligible/tlm-main-template.tpl.twig', $context );
	}

	/**
	 * @return array
	 */
	private function build_migrate_data() {

		$migrate_data = $this->helper->setup_migration_table_data();

		return $migrate_data;
	}

	/**
	 * @param $items
	 *
	 * @return array
	 */
	private function filter_no_templates( $items ) {

		return $this->helper->filter_missing_template_layout_items( $items );

	}

	/**
	 * @param $groups_data
	 *
	 * @return array
	 */
	private function build_generic_twig_context( $groups_data ) {

		$admin_url = add_query_arg( array(
			'page' => self::PAGE_SLUG,
			'step' => $this->get_step(),
		), admin_url( 'admin.php' ) );

		$context = array(
			'admin_url' => $admin_url,
			'items' => $groups_data
		);

		return $context;
	}

	/**
	 * @return mixed
	 */
	final public static function get_instance() {
		static $instances = array();
		$called_class = get_called_class();
		if ( ! isset( $instances[ $called_class ] ) ) {
			$instances[ $called_class ] = new $called_class();
		}

		return $instances[ $called_class ];
	}

	/**
	 *
	 */
	function register_scripts_and_styles() {
		wp_register_style( 'tlm-core-css', TLM_PUBLIC_URI . '/css/tlm.css', array(), TLM_VERSION, 'screen' );
		wp_register_script( 'tlm-main', TLM_PUBLIC_URI . '/js/main.js', array('jquery', 'toolset-utils', 'toolset-event-manager', 'backbone', 'underscore', 'headjs'), TLM_VERSION, false );
	}

	/**
	 *
	 */
	function enqueue_scripts() {
		wp_enqueue_script( 'tlm-main' );
		wp_localize_script( 'tlm-main', 'TLM_ManagerSettings', array(
				'create_layout_nonce' => wp_create_nonce( 'wp_nonce_create_layout' ),
				'parent_default' => apply_filters( 'ddl-get-default-' . WPDDL_Options::PARENTS_OPTIONS, WPDDL_Options::PARENTS_OPTIONS ),
				'edit_link' => admin_url( 'admin.php?page=dd_layouts_edit&layout_id=%LAYOUT_ID%&action=edit' ),
				'finalise_migration_nonce' => wp_create_nonce( 'wp_nonce_finalise_migration' ),
				'tlm_populate_nonce' => wp_create_nonce( 'tlm_populate_nonce' ),
				'TLM_PUBLIC_URI' => TLM_PUBLIC_URI,
				'DEFAULT_BATCH_SIZE' => TLM_Migration_Candidate_Query::DEFAULT_BATCH_SIZE,
				'is_wpml_active_and_configured' => self::is_wpml_active(),
				'strings' => array(
					'summary' => __( 'Migration summary', 'ddl-layouts' )
				)
			)
		);
	}

	/**
	 *
	 */
	function enqueue_styles() {
		wp_enqueue_style( 'tlm-core-css' );
	}

	/**
	 * @param $pages
	 *
	 * @return array
	 */
	function tlm_create_menu( $pages ) {
		//create new menu under Toolset
		$action = '';

		if ( isset( $_POST['action'] ) ) {
			$action = '_' . $_POST['action'];
		}

		$pages[] = array(
			'slug' => self::PAGE_SLUG,
			'menu_title' => __( 'Layouts Migration', 'ddl-layouts' ),
			'page_title' => __( 'Toolset Layouts Migration', 'ddl-layouts' ),
			'callback' => array( &$this, 'print_admin_page' . $action ),
			'capability' => DDL_ASSIGN
		);

		return $pages;
	}

	/**
	 *
	 */
	private function handle_candidate_table_and_relative_orphans() {
		$migrate_data = $this->build_migrate_data();
		$this->handle_notice_no_templates( $migrate_data );
		$this->load_candidates_listing( $migrate_data );
	}

	/**
	 * @param $migrate_data
	 */
	private function handle_notice_no_templates( $migrate_data ) {

		$no_templates = $this->filter_no_templates( $migrate_data );

		if ( $no_templates && count( $no_templates ) ) {
			$no_templates = $this->reduce_to_single_occurence_each_post_type( $no_templates );
			$this->load_no_templates_list( $no_templates );
		}
	}

	/**
	 * @return void
	 */
	public function print_admin_page() {

		if ( $this->is_migration_page() === false ) {
			return;
		}

		$this->load_main_tpl();

		$this->create_migrate_process( array() );

		$this->handle_migrate_data_display();
		$this->handle_candidate_table_and_relative_orphans();
	}

	/**
	 * @return void
	 */
	public function print_admin_page_step_one() {

		if ( $this->is_migration_page() === false ) {
			return;
		}

		$this->load_main_tpl();

		$this->create_migrate_process( $_POST );
		$this->migrate_process_migrate( $_POST );

		$this->handle_migrate_data_display();
		$this->handle_candidate_table_and_relative_orphans();
	}

	/**
	 * @param $data
	 */
	protected function create_migrate_process( $data ){
		$this->migrate = new TLM_MigratePreProcess( $data );
	}

	/**
	 * @param $data
	 */
	protected function migrate_process_migrate( $data ){
		if( isset( $data['action'] ) && $data['action'] === 'step_one' ){
			$this->migrate->migrate();
		}
	}

	/**
	 * @return void
	 */
	private function handle_migrate_data_display() {

		$models = $this->migrate->to_json();

		if ( count( $models ) ) {
			usort( $models, array ( $this->helper, 'sort_by_post_title' ) );
			$this->load_migrate_list_template( $models );
		}
	}

	private function load_migrate_list_template( array $migrate_data ) {

		$context = $this->build_generic_twig_context( $migrate_data );
		echo $this->twig->render( '/list-eligible/tlm-migrate-items-list.tpl.twig', $context );

	}

	/**
	 * @param $items
	 *
	 * @return array
	 */
	function reduce_to_single_occurence_each_post_type( $items ) {
		return $this->helper->get_reduced_post_types_list( $items );
	}

	/**
	 * @return bool
	 */
	private function is_migration_page() {
		return isset( $_GET['page'] ) && $_GET['page'] === self::PAGE_SLUG;
	}

	/**
	 * @return void
	 */
	public function finalise_migration_process() {
		if ( $_POST && wp_verify_nonce( $_POST['finalise_migration_nonce'], 'wp_nonce_finalise_migration' ) ) {

			if( isset( $_POST['post_id'] ) ){

				$post_id = (int) $_POST['post_id'];

				$data = $this->get_migration_object_data( $post_id );

				if ( null !== $data ) {

					$migrate = new TLM_MigrationObject( $post_id, $data );
					$migrate->finish_migration();


					if( self::is_wpml_active() ){

						$factory = new TLM_FactoryTranslationHelper( $migrate->id, $migrate->post_type, $migrate->template );

						try{
							$factory->translations_apply_method( 'set_disabled' );
						} catch( Exception $exception ){
							wp_send_json( array( 'error' => $exception ), $status_code = $exception->getCode() );
						}
					}

					$send = array( 'data' => $migrate->getIterator(), 'id' => $post_id );

				} else {
					$send = array( 'error' => __( sprintf( 'Migration data are missing from the database. %s', __METHOD__ ), 'ddl-layouts' ) );
				}

			} else {
				$send = array( 'error' => __( sprintf( 'There is a problem with this data, object $id is missing. %s', __METHOD__), 'ddl-layouts') );
			}

		} else {
			$send = array( 'error' => __(sprintf('Nonce problem: apparently we do not know where the request comes from. %s', __METHOD__), 'ddl-layouts') );
		}

		wp_send_json( $send );
	}

	/**
	 * @return void
	 */
	public function cancel_migration_process(){
		if ($_POST && wp_verify_nonce($_POST['finalise_migration_nonce'], 'wp_nonce_finalise_migration')) {

			if( isset( $_POST['post_id'] ) ){

				$post_id = (int) $_POST['post_id'];

				$data = $this->get_migration_object_data( $post_id );

				if( null !== $data ){

					$migrate = new TLM_MigrationObject( $post_id, $data );
					$migrate->clean_up_migration_data( );

					if( self::is_wpml_active() ){

						$factory = new TLM_FactoryTranslationHelper( $migrate->id, $migrate->post_type, $migrate->template );

						try{
							$factory->translations_apply_method( 'clean_up_migration_data' );
						} catch( Exception $exception ){
							wp_send_json( array( 'error' => $exception ), $status_code = $exception->getCode() );
						}

					}

					$send= array( 'data' => $migrate->getIterator(), 'id' => $post_id );

				} else {
					$send = array( 'error' => __(sprintf('Migration data are missing from the database. %s', __METHOD__), 'ddl-layouts') );
				}

			} else {
				$send = array( 'error' => __(sprintf('There is a problem with this data, object $id is missing. %s', __METHOD__ ), 'ddl-layouts' ) );
			}

		} else {
			$send = array( 'error' => __( sprintf( 'Nonce problem: apparently we do not know where the request comes from. %s', __METHOD__ ), 'ddl-layouts' ) );
		}

		wp_send_json( $send );
	}

	private function get_migration_object_data( $id ) {
		$meta = get_post_meta( $id, TLM_MIGRATION_DATA_META, true );

		if ( $meta ) {
			return (array) $meta;
		} else {
			return null;
		}
	}

	/**
	 * @return TLM_EditorDialogs
	 */
	function load_dialog_boxes() {

		$dialog = new TLM_EditorDialogs(
			array(
				'toolset_page_dd_layouts_migration'
			)
		);

		add_action( 'current_screen', array( &$dialog, 'init_screen_render' ) );

		return $dialog;
	}

	function load_backbone_templates(){
		include_once TLM_PUBLIC_ABSPATH . '/js/templates/tlm-row-template.tpl.js.phtml';
	}

	/**
	 * @return bool
	 */
	public static final function is_wpml_active(){
		return Toolset_WPML_Compatibility::get_instance()->is_wpml_active_and_configured();
	}

}
