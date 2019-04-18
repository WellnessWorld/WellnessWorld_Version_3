<?php

/**
 * Class TLM_FactoryTranslationHelper
 */
class TLM_FactoryTranslationHelper{
	protected $post_id = 0;
	protected $post_type = 0;
	protected $template = null;

	/**
	 * TLM_FactoryTranslationHelper constructor.
	 *
	 * @param $post_id
	 * @param $post_type
	 * @param int $template
	 */
	public function __construct( $post_id, $post_type, $template = -1 ){
		$this->post_id = $post_id;
		$this->post_type = $post_type;
		$this->template = (int) $template;
	}

	/**
	 * @return array|null
	 */
	public function process_translations( ){

		$ret = array();

		$translations  = apply_filters('wpml_content_translations', null, $this->post_id, $this->post_type);

		if( !$translations ){
			return null;
		}

		foreach( $translations as $translation){

			if( (int) $translation->element_id !== (int) $this->post_id ){
				$ret[] = $this->build_new_element( $translation->element_id, $this->post_id );
			}
		}

		return count( $ret ) > 0 ? $ret : null;
	}

	/**
	 * @return array|null
	 */
	public function get_translations_data( ){
		$ret = array();

		$translations  = apply_filters('wpml_content_translations', null, $this->post_id, $this->post_type);

		if( !$translations ){
			return null;
		}

		foreach( $translations as $translation){

			if( $translation->element_id !== $this->post_id ){

				if( get_post_meta( $translation->element_id, TLM_MIGRATION_BOOL_META, true) === 'yes' ){

					$data = (array) get_post_meta( $translation->element_id, TLM_MIGRATION_DATA_META, true);
					$trans = new TLM_MigrationObject( $translation->element_id, $data );
					$ret[] = $trans;
				}
			}
		}

		return count( $ret ) > 0 ? $ret : null;
	}

	/**
	 * @param $element_id
	 * @param $original_id
	 *
	 * @return TLM_MigrationObject
	 */
	protected function build_new_element( $element_id, $original_id ){

		$data = $this->build_element_data( $element_id );

		$migrate = new TLM_MigrationObject( $element_id, $data );
		$migrate->set_translate( $original_id );
		$migrate->start_migration( true );

		$migrate->translate_private_layout( $this->get_updater( ), $this->post_id );

		return $migrate;
	}

	/**
	 * @param $element_id
	 *
	 * @return array
	 */
	private function build_element_data( $element_id ){
		$layout = get_post_meta( $element_id, WPDDL_LAYOUTS_META_KEY, true );
		$layout_id = WPDD_Utils::get_layout_id_from_post_name( $layout );
		$data = array(
			'post_title' => get_post_field( 'post_title', $element_id ),
			'post_name' => get_post_field( 'post_name', $element_id ),
			'post_type' => $this->post_type,
			'current_layout' => (int) $layout_id,
			'template' => $this->template
		);
		return $data;
	}

	/**
	 * @return WPDDL_Update_Translated_Private_Layout
	 */
	private function get_updater( ){
		return new WPDDL_Update_Translated_Private_Layout( new WPDDL_WPML_Private_Layout( new WPDD_json2layout( ), new WPDDL_Private_Layout() ) );
	}

	/**
	 * @param $method
	 * @param $arguments
	 *
	 * @throws Exception
	 */
	public function translations_apply_method( $method, $arguments = array() ){
		if( null === $method ){
			throw new Exception( sprintf('Fatal error: %s first parameter $method should be a php callable, %s given', __METHOD__, gettype( $method ) ) );
		}

		$translations  = apply_filters('wpml_content_translations', null, $this->post_id, $this->post_type);

		if( !$translations ){
			return null;
		}

		foreach( $translations as $translation){

			if( $translation->element_id !== $this->post_id ){

				if( get_post_meta( $translation->element_id, TLM_MIGRATION_BOOL_META, true) === 'yes' ){

					$data = (array) get_post_meta( $translation->element_id, TLM_MIGRATION_DATA_META, true);
					$trans = new TLM_MigrationObject( $translation->element_id, $data );

					try{
						$trans->__call( $method, $arguments );
					} catch( Exception $exception ){
						return null;
					}

				}
			}
		}
	}
}