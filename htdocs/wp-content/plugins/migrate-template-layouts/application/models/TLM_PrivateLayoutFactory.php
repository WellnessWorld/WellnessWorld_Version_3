<?php

/**
 * Class TLM_PrivateLayoutFactory
 */
class TLM_PrivateLayoutFactory extends WPDDL_LayoutsCleaner{

	protected $layout;

	const LAYOUT_TYPE = 'private';

	public function __construct( $layout, $args = array() ) {
		$this->remapped = false;
		$this->removed = array();
		$this->layout = $layout;
		$this->layout->name = TLM_NAME_PREFIX . $args['post_type'] . ' ' . $args['post_title'];
		$this->layout->id = $args['id'];
		$this->layout_id = $this->layout->id;
		$this->layout->layout_type = self::LAYOUT_TYPE;
		$this->layout->parent = '';
		$this->layout->slug = 'layout-for-'.$args['post_name'];
	}

	public function get_layout(){
		return $this->layout;
	}



	public function process_layout_cleanup( ){
		$this->layout = $this->clean_up_layout();
	}

	public function change_layout_rows_modes(){
		$this->layout = $this->change_rows_row_mode( );
	}

	private function clean_up_layout( ){
		$this->remove_cells_of_type_by_property( $this->get_forbidden_cells(), 'cell_type', array( $this, 'is_type_forbidden' ) );
		return $this->get_layout();
	}

	private function get_forbidden_cells(){
		return apply_filters( 'ddl-disabled_cells_on_content_layout', array() );
	}

	private function append_visual_cell_with_content_to_private_row( $content = null ) {

		return (object) array(
			'kind'                   => "Row",
			'Cells'                  => array(
				WPDD_Utils::create_cell( 'Post Content Cell', 1, 'cell-text', array(
					'content' => array( 'content' => __( $content ) ),
					'width'   => 12
				) )
			),
			'cssClass'               => 'row-fluid',
			'name'                   => 'Post content row',
			'additionalCssClasses'   => '',
			'row_divider'            => 1,
			'layout_type'            => 'fluid',
			'mode'                   => 'full-width',
			'cssId'                  => '',
			'tag'                    => 'div',
			'width'                  => 1,
			'editorVisualTemplateID' => ''
		);
	}

	public function add_content_row( $content = null ){
		$row = $this->append_visual_cell_with_content_to_private_row( $content );
		return array_unshift( $this->layout->Rows, $row );
	}

	public function remove_cells_of_type_by_property( $cell_type, $property, $callable = array( 'TLM_PrivateLayoutFactory', "is_type_forbidden" ) )
	{
		$this->remapped = false;
		$this->cell_type = $cell_type;
		$this->property = $property;
		$rows = $this->get_rows();
		$rows = $this->remap_rows($rows, $callable);

		if( null !== $rows ){
			$this->layout->Rows = $rows;
		}

		return $this->removed;
	}

	public function is_type_forbidden( $cell ){
		return in_array( $cell->{$this->property}, $this->cell_type );
	}

	public function change_rows_row_mode( ){
		$rows = array();

		foreach( $this->layout->Rows as $row ){
			$rows[] = $this->set_row_mode( $row );
		}

		$this->layout->Rows = $rows;

		return $this->layout;
	}

	function filtered_cells_recurse( $cells, $callable = array( 'WPDD_Utils', "is_post_published" ) ){
		$array = array();
		foreach( $cells as $key => $cell ){
			if( is_object($cell) && $cell->kind === 'Container' ){
				$container_rows = $this->remap_rows( $cell->Rows, $callable );
				if( null !== $container_rows ){
					$cell->Rows = $container_rows;
				}
			} else if(
				is_object($cell) &&
				property_exists($cell, 'cell_type') &&
				in_array( $cell->cell_type, $this->cell_type ) &&
				property_exists( $cell, $this->property ) &&
				call_user_func( $callable, $cell ) === true
			){
				$array[] = $cell;
			}
		}

		return $array;
	}

	protected function set_row_mode( $row ){
		// if it's full width background leave it
		if( property_exists( $row, 'mode' ) === false || $row->mode === "normal" ){
			$row->mode = "full-width";
		}

		return $row;
	}

}