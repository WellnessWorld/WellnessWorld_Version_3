<?php

namespace ToolsetBlocks;

/**
 * Dependency Injection Wrapper
 * The one and only Singelton
 */
class DicLoader {

	/** @var DicLoader */
	private static $instance;

	/** @var \Auryn\Injector */
	private $dic;


	/**
	 * @returns DicLoader
	 */
	public static function get_instance() {
		return self::$instance = self::$instance ?: new self();
	}

	/**
	 * DicLoader constructor.
	 */
	private function __construct() {
		$this->dic = new \Auryn\Injector();
		$this->dic->share( '\ToolsetBlocks\Utils\ScriptData' );
	}

	/**
	 * @return \Auryn\Injector
	 */
	public function get_dic() {
		return $this->dic;
	}
}

// Routes
require_once( TB_PATH . '/server/routes.php' );


/**
 * Toolset Blocks plugin's block public loader.
 *
 * @package  toolset-blocks
 * @todo Refactor to only load when it's required. Move entry to ./routes.php
 */
require_once( TB_PATH . '/vendor/autoload.php' );
