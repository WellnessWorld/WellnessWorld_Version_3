<?php

namespace OTGS\Toolset\Layouts\ClassesAuto\Gutenberg\PrivateLayout;

/**
 * Class WPDD_Gutenberg_Editor_Condition_Post_Editor
 * @since 2.5.2
 * @author Riccardo Strobbia
 * A class to detect if we are in a Gutenberg editor page either to create or edit a post
 */
class ConditionPostEditor extends \Toolset_Condition_Plugin_Gutenberg_Active {

	/**
	 * @var string
	 */
	protected $page = 'post.php';
	/**
	 * @var string
	 */
	protected $page_new = 'post-new.php';
	/**
	 * @var string
	 */
	protected $query_var = 'action';
	/**
	 * @var string
	 */
	protected $var_value = 'edit';
	/**
	 * @var string
	 *  Action added in Gutenberg editor page only: https://github.com/WordPress/gutenberg/issues/1316
	 */
	protected $default_action = 'the_post';
	/**
	 * @var string
	 */
	private $pagenow;

	/**
	 * Condition_Post_Editor constructor.
	 *
	 * @param $pagenow
	 */
	public function __construct( $pagenow ) {
		$this->pagenow = $pagenow;
	}

	/**
	 * @return bool
	 */
	public function is_met() {
		if ( ! parent::is_met() ) {
			return false;
		}

		return ( ( $this->pagenow === $this->page
			&& isset( $_GET[ $this->query_var ] )
			&& $_GET[ $this->query_var ] === $this->var_value ) || $this->pagenow === $this->page_new )
			&& did_action( $this->default_action );
	}
}