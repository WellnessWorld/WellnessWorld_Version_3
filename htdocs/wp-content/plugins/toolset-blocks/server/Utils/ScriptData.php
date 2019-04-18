<?php


namespace ToolsetBlocks\Utils;

class ScriptData {

	/** @var array */
	private $data = array();

	/**
	 * @param $key
	 * @param $data
	 */
	public function add_data( $key, $data ) {
		$this->data[ $key ] = $data;
	}

	/**
	 * @action admin_print_scripts
	 */
	public function admin_print_scripts() {
		if( empty( $this->data ) ) {
			return;
		}

		echo '<script id="toolset_blocks_data" type="text/plain">'
			 . base64_encode( wp_json_encode( $this->data ) )
			 .'</script>';
	}
}
