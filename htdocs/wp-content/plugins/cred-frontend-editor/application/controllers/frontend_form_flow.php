<?php

/**
 * Class CRED_Frontend_Flow
 *
 * @since m2m
 */

class CRED_Frontend_Form_Flow {

	/**
	 * @var null|int
	 */
	private $current_form_id;

	/**
	 * @var null|string
	 */
	private $current_form_type;

	/**
	 * @var null|array
	 */
	private $current_form_attributes;

	/**
	 * @var array
	 */
	private $rendered_forms = array();
    /**
     * @var int
     */
	private $current_form_count = 0;

	/**
	 * Temporal auxiliar hooks APi while post and user forms do not get their shortcodes
	 * properly managed, hence do not get this frontend flow properly avalable as a dependency.
	 *
	 * @since 2.2.1.1
	 */
	public function initialize_hooks() {

		/**
		 * Track a form on demand.
		 *
		 * @param \WP_Post $form
		 * @param array attributes Set of attributes passed to this form shortcode
		 * @since 2.2.1.1
		 */
		add_action( 'toolset_forms_frontend_flow_form_start', array( $this, 'form_start' ), 10, 2 );

		/**
		 * End tracking a form on demand.
		 *
		 * @since 2.2.1.1
		 */
		add_action( 'toolset_forms_frontend_flow_form_end', array( $this, 'form_end' ) );

		/**
		 * Get a form index, as number of times it has been rendered in this request.
		 *
		 * @since 2.2.1.1
		 */
		add_filter( 'toolset_forms_frontend_flow_get_form_index', array( $this, 'get_form_index' ) );

		add_filter( 'toolset_filter_toolset_admin_bar_menu_insert', array( $this, 'extend_toolset_admin_bar_menu' ), 20, 3 );
	}


	public function form_start( $form_object, $form_attributes = array() ) {
		$this->set_current_form_id( $form_object->ID );
		$this->store_rendered_form_id( $form_object->ID );
		$this->set_current_form_type( $form_object->post_type );
		$this->set_current_form_attributes( $form_attributes );
		$this->current_form_count++;
	}

	public function form_end() {
		$this->render_custom_css_and_js();
		$this->clear_current_form_id();
		$this->clear_current_form_type();
		$this->clear_current_form_attributes();
	}

	public function get_current_form_count() {
	    return $this->current_form_count;
    }

	public function get_rendered_forms() {
		return array_unique( $this->rendered_forms );
	}

	private function set_current_form_id( $id = null ) {
		$this->current_form_id = $id;
	}

	private function clear_current_form_id() {
		$this->current_form_id = null;
	}

	public function get_current_form_id() {
		return $this->current_form_id;
	}

	private function store_rendered_form_id( $form_id ) {
		if ( $form_id ) {
			$this->rendered_forms[] = $form_id;
		}
	}

	private function set_current_form_type( $post_type ){
		$this->current_form_type = $post_type;
	}

	private function clear_current_form_type() {
		$this->current_form_type = null;
	}

	public function get_current_form_type() {
		return $this->current_form_type;
	}

	private function set_current_form_attributes( $form_attributes = array() ) {
		$this->current_form_attributes = $form_attributes;
	}

	private function clear_current_form_attributes() {
		$this->current_form_attributes = null;
	}

	public function get_current_form_attributes() {
		return $this->current_form_attributes;
	}

	/**
	 * Get the index of the current form,
	 * meaning the number of times this specific form has been printed already.
	 *
	 * @param int $index
	 * @return int
	 * @since 2.2.1.1
	 */
	public function get_form_index( $index = 0 ) {
		$current_form_id = $this->get_current_form_id();

		if ( null === $current_form_id ) {
			return $index;
		}

		$forms_to_times = array_count_values( $this->rendered_forms );

		return toolset_getarr( $forms_to_times, $current_form_id, $index );
	}

	private function render_custom_css_and_js() {
		// Right now, only relationship forms extra JS and CSS are managed this way
		if ( CRED_Association_Form_Main::ASSOCIATION_FORMS_POST_TYPE !== $this->get_current_form_type() ) {
			return;
		}

		$custom_js = trim( $this->render_custom_js() );
		$custom_css = trim( $this->render_custom_css() );

		if ( $custom_js ) {
			wp_add_inline_script( CRED_Association_Form_Front_End::JS_FRONT_END_MAIN, $custom_js );
		}
		if ( $custom_css ) {
			wp_add_inline_style( CRED_Association_Form_Front_End::CSS_FRONT_END_HANDLE, $custom_css );
		}
	}

	public function render_custom_css() {
		return get_post_meta( $this->get_current_form_id(), 'form_style', true);
	}

	public function render_custom_js() {
		return get_post_meta( $this->get_current_form_id(), 'form_script', true);
	}

	/**
	 * Optionally add an item to edit forms to the "Design with Toolset" admin bar menu.
	 *
	 * See the toolset_filter_toolset_admin_bar_menu_insert filter.
	 *
	 * @param array|mixed $menu_item_definitions
	 * @param string $context
	 * @param int $post_id
	 * @return array Menu item definitions.
	 * @since 2.3.2
	 */
    public function extend_toolset_admin_bar_menu( $menu_item_definitions,
		/** @noinspection PhpUnusedParameterInspection */ $context,
		/** @noinspection PhpUnusedParameterInspection */ $post_id ) {
        if ( ! is_array( $menu_item_definitions ) ) {
            $menu_item_definitions = array();
        }

        $used_form_ids = $this->get_rendered_forms();

		foreach ( $used_form_ids as $form_id ) {
			$form = get_post( $form_id );

			if ( null === $form ) {
				continue;
			}

			switch( $form->post_type ) {
				case \OTGS\Toolset\CRED\Controller\Forms\Post\Main::POST_TYPE:
					$menu_item_definitions[] = array(
						'title' => sprintf( '%s: %s', __( 'Edit post form', 'wp-cred' ), $form->post_title ),
						'menu_id' => sprintf( 'toolset_design_form_%s', $form->post_name ),
						'href' => admin_url( 'post.php?post=' . esc_attr( $form_id ) . '&action=edit' ),
					);
					break;
				case \OTGS\Toolset\CRED\Controller\Forms\User\Main::POST_TYPE:
					$menu_item_definitions[] = array(
						'title' => sprintf( '%s: %s', __( 'Edit user form', 'wp-cred' ), $form->post_title ),
						'menu_id' => sprintf( 'toolset_design_form_%s', $form->post_name ),
						'href' => admin_url( 'post.php?post=' . esc_attr( $form_id ) . '&action=edit' ),
					);
					break;
				case CRED_Association_Form_Main::ASSOCIATION_FORMS_POST_TYPE:
					$menu_item_definitions[] = array(
						'title' => sprintf( '%s: %s', __( 'Edit relationship', 'wp-cred' ), $form->post_title ),
						'menu_id' => sprintf( 'toolset_design_form_%s', $form->post_name ),
						'href' => admin_url( 'admin.php?page=cred_relationship_form&action=edit&id=' . esc_attr( $form_id ) ),
					);
					break;
			}
		}

		return $menu_item_definitions;
    }

}
