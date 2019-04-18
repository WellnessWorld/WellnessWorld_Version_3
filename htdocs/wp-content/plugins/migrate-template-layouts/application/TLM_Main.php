<?php

/**
 * Class TLM_Main
 */
class TLM_Main{

	private static $instance = null;

	private function __construct(){
		add_action( 'init', array( &$this, 'init') );
	}

	function init(){
		TLM_Admin::get_instance();
	}

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}