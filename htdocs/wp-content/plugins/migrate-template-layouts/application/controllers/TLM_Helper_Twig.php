<?php

/**
 * Types_Helper_Twig
 *
 * @since 2.0
 */
class TLM_Helper_Twig {

	private $filesystem;
	private $twig;

	public function __construct( $debug = false ) {
		// backwards compatibility for php5.2
		if ( ! defined( 'E_DEPRECATED' ) )
			define( 'E_DEPRECATED', 8192 );

		if ( ! defined( 'E_USER_DEPRECATED' ) )
			define( 'E_USER_DEPRECATED', 16384 );

		$this->filesystem = new Twig_Loader_Filesystem();
		$this->filesystem->addPath( TLM_TEMPLATES_ABSPATH . '/twig' );

		$this->twig = new Twig_Environment( $this->filesystem, array( 'debug' => $debug ) );
		$this->twig->addFunction( new Twig_SimpleFunction( '__', array( $this, 'translate' ) ) );
		$this->twig->addFunction( new Twig_SimpleFunction( 'admin_url', array( $this, 'admin_url' ) ) );
		$this->twig->addFunction( new Twig_SimpleFunction( 'get_permalink', array( $this, 'get_permalink' ) ) );
		$this->twig->addFunction( new Twig_SimpleFunction( 'get_lang', array( $this, 'get_lang' ) ) );
		$this->twig->addFunction( new Twig_SimpleFunction( 'is_wpml', array( $this, 'is_wpml' ) ) );
	}

	/**
	 * This allows to use __( 'Text to translate', 'text-domain' ) in twig templates
	 *
	 * @param $text
	 * @param string $domain
	 *
	 * @return mixed
	 */
	public function translate( $text, $domain = 'ddl-layouts' ) {
		return __( $text, $domain );
	}

	public function admin_url( $path = '', $scheme = 'admin' ){
			if( $path ){
				return admin_url( $path, $scheme );
			} else {
				return admin_url();
			}
	}

	public function get_permalink( $post_id = 0, $leavename = false ){
		return get_permalink( $post_id, $leavename );
	}

	public function render( $file, $data ) {
		if( $this->filesystem->exists( $file ) )
			return $this->twig->render( $file, $data );

		return false;
	}

	public function get_lang( $post_id ){
		$lang = apply_filters( 'wpml_post_language_details', null,  $post_id );

		if( $lang  ){
			return $lang['display_name'];
		}

		return '';
	}

	public function is_wpml(){
		return TLM_Admin::is_wpml_active();
	}
}