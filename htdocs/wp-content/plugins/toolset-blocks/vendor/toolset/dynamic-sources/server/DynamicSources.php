<?php

namespace Toolset\DynamicSources;

use Toolset\DynamicSources\PostProviders\IdentityPost;
use Toolset\DynamicSources\Sources\Source;

class DynamicSources {
	const TOOLSET_BLOCKS_DYNAMIC_SOURCES_STORE = 'toolset/dynamic-sources';

	const POST_GROUP = 'post';

	const AUTHOR_GROUP = 'author';

	const SITE_GROUP = 'site';

	const COMMENTS_GROUP = 'comments';

	const MEDIA_GROUP = 'media';

	const OTHER_GROUP = 'other';

	const TEXT_CATEGORY = 'text';

	const NUMBER_CATEGORY = 'number';

	const IMAGE_CATEGORY = 'image';

	const DATE_CATEGORY = 'date';

	const AUDIO_CATEGORY = 'audio';

	const VIDEO_CATEGORY = 'video';

	const URL_CATEGORY = 'url';

	const VALUE_SEPARATOR = '#+*#';

	private $switched_post_data;


	/** @var Source[] */
	private $sources_for_registration = array();


	/** @var PostProvider[] */
	private $post_providers = array();


	/** @var SourceContext */
	private $source_context;


	/** @var SourceStorage */
	private $source_storage;

	/** @var DicLoader */
	private $dic;

	public function __construct() {
		$this->dic = DicLoader::get_instance()->get_dic();
	}

	/**
	 * Initializes the Dynamic Sources API
	 */
	public function initialize() {
		add_shortcode( 'tb-dynamic-container', array( $this, 'dynamic_container_shortcode_render' ) );

		add_shortcode( 'tb-dynamic', array( $this, 'dynamic_shortcode_render' ) );

		add_action( 'init', array( $this, 'initialize_toolset_fields_sources' ) );

		add_action( 'init', array( $this, 'initialize_sources' ) );

		add_action( 'init', array( $this, 'initialize_other_fields_sources' ) );

		add_action( 'rest_api_init', array( $this, 'initialize_rest' ) );

		add_action( 'enqueue_block_editor_assets', array( $this, 'register_sources' ), 1 );

		add_action( 'toolset/dynamic_sources/actions/register_sources', array( $this, 'register_sources' ) );

		add_filter( 'toolset/dynamic_sources/filters/get_post_providers', array( $this, 'get_post_providers' ) );

		add_filter( 'toolset/dynamic_sources/filters/get_post_providers_for_select', array( $this, 'get_post_providers_for_select' ) );

		add_filter( 'toolset/dynamic_sources/filters/get_grouped_sources', array( $this, 'get_grouped_sources' ) );

		add_filter( 'toolset/dynamic_sources/filters/get_source_fields', array( $this, 'get_source_fields' ), 10, 4 );

		add_filter( 'toolset/dynamic_sources/filters/get_source_content', array( $this, 'get_source_content' ), 10, 5 );

		add_filter( 'toolset/dynamic_sources/filters/client_side_info', array( $this, 'prepare_client_side_information' ) );

		add_filter( 'the_content', array( $this, 'shortcode_render' ), -1 );

		add_filter( 'wpv_filter_content_template_output', array( $this, 'shortcode_render' ), -1 );
	}

	public function initialize_toolset_fields_sources() {
		// Toolset related sources.
		$toolset_sources_controller = $this->dic->make( ToolsetSources\Main::class );
		$toolset_sources_controller->initialize();
	}

	/**
	 * Post meta now goes in a different group
	 */
	public function initialize_other_fields_sources() {
		$sources_controller = $this->dic->make( OtherFieldsSources\Main::class );
		$sources_controller->initialize();
	}

	public function initialize_rest() {
		$rest_api = $this->dic->make( Rest::class );
		$rest_api->initialize();
	}

	public function initialize_sources() {
		// Add Dynamic Sources for registration.
		$post_sources = array(
			'PostID',
			'PostName',
			'PostTitle',
			'PostContent',
			'PostType',
			'PostStatus',
			'PostURL',
			'PostExcerpt',
			'PostDate',
			'PostDateGMT',
			'PostDateModified',
			'PostDateModifiedGMT',
			// 'PostFields',
			'PostTaxonomies',
			'AuthorID',
			'AuthorName',
			'CommentsNumber',
			'CommentsStatus',
			'MediaFeaturedImageData',
		);

		// Do not offer the PostContent source outside of Content Templates
		global $pagenow;
		if ( 'post.php' === $pagenow ) {
			$edited_post = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
			if ( 'view-template' !== get_post_type( $edited_post ) ) {
				$post_sources = array_filter(
					$post_sources,
					function( $source ) {
						return ( 'PostContent' !== $source );
					}
				);
			}
		}
		if ( 'post-new.php' === $pagenow ) {
			$post_sources = array_filter(
				$post_sources,
				function( $source ) {
					return ( 'PostContent' !== $source );
				}
			);
		}

		$other_sources = array(
			'SiteTagline',
		);

		foreach ( array_merge( $post_sources, $other_sources ) as $source ) {
			$sourceClass = '\Toolset\DynamicSources\Sources\\' . $source;
			$this->add_source_for_registration( $this->dic->make( $sourceClass ) );
		}

		// Dynamic Sources Cache.
		$dynamic_sources_cache = $this->dic->make(
			'\Toolset\DynamicSources\Cache',
			array(
				':sources' => array(
					'post' => array(
						'post_relevant' => true,
						'sources' => $post_sources
					),
					'other' => array(
						'post_relevant' => false,
						'sources' => $other_sources
					),
				),
			)
		);
		$dynamic_sources_cache->initialize();
	}

	/**
	 * Registers the Dynamic Sources.
	 */
	public function register_sources() {
		$post_id = get_the_ID();
		$post_type = get_post_type();

		// When the editor autosaves, the post type is "revision", so we need to find the parent of the revision post
		// and get its post ID.
		if ( 'revision' === $post_type ) {
			$post_id = wp_get_post_parent_id( $post_id );
			$post_type = get_post_type( $post_id );
		}

		if (
			! $post_type &&
			defined( 'REST_REQUEST' ) &&
			REST_REQUEST &&
			isset( $_GET['post'] ) &&
			is_numeric( $_GET['post'] )
		) {
			$post_id = absint( $_GET[ 'post' ] );
			$post_type = get_post_type( $post_id );
		}

		$post_type = apply_filters( 'toolset/dynamic_sources/filters/post_type_for_source_context', $post_type, $post_id );

		if ( ! $post_type ) {
			$post_type = array();
		}

		// Get the initial context, in which everything happens.
		$this->source_context = $this->build_source_context( $post_type );

		if ( null === $this->source_context ) {
			return;
		}

		// Based on this context, get all sources of posts which we can use.
		$this->post_providers = $this->register_post_providers( $this->source_context );

		// Register data sources that can be used with available post providers.
		$this->register_data_sources( $this->post_providers );
	}


	public function build_source_context( $post_type ) {
		/**
		 * toolset/dynamic_sources/filters/source_context
		 *
		 * Filter that allows altering the SourceContext object before it is used.
		 *
		 * @param SourceContext
		 * @return SourceContext
		 */
		$source_context = apply_filters(
			'toolset/dynamic_sources/filters/source_context',
			new PostTypeSourceContext( $post_type )
		);

		if( ! $source_context instanceof SourceContext ) {
			throw new \InvalidArgumentException();
		}

		return $source_context;
	}


	/**
	 * Register all post providers available within the given source context.
	 *
	 * @param SourceContext $source_context
	 *
	 * @return PostProvider[]
	 */
	private function register_post_providers( SourceContext $source_context ) {
		$identity_post = new IdentityPost( $source_context->get_post_types() );
		$post_providers = array_filter(
			apply_filters(
				'toolset/dynamic_sources/filters/register_post_providers',
				array( $identity_post->get_unique_slug() => $identity_post ),
				$source_context
			),
			function( $post_provider ) {
				return $post_provider instanceof PostProvider;
			}
		);

		return $post_providers;
	}


	/**
	 * @param PostProvider[] $post_providers
	 */
	private function register_data_sources( $post_providers ) {

		/**
		 * Filters the the Dynamic Sources offered.
		 *
		 * @param array       $sources   The dynamic sources
		 * @param null|string $post_type The post type of the post with ID coming from the URL parameters.
		 */
		$this->sources_for_registration = apply_filters(
			'toolset/dynamic_sources/filters/register_sources',
			$this->sources_for_registration,
			$post_providers
		);

		$this->source_storage = new SourceStorage( $post_providers );

		foreach( $this->sources_for_registration as $source_for_registration ) {
			$this->register_source( $source_for_registration );
		}

	}

	/**
	 * Gets source content
	 *
	 * @param string     $post_provider Post provider
	 * @param int        $post Post ID
	 * @param string     $source Source ID
	 * @param string     $field Field ID
	 * @param array|null $extra_attributes Extra attributes comming from [tb-dynamic] shortcode
	 */
	public function get_source_content(
		/** @noinspection PhpUnusedParameterInspection */ $content,
		$post_provider,
		$post,
		$source,
		$field = null,
		$extra_attributes = null
	) {
		if ( ! $post ) {
			return '';
		}

		// If a post provider is not set, then most probably the source is not post relevant.
		if (
			$post_provider &&
			isset( $this->post_providers[ $post_provider ] ) &&
			$this->post_providers[ $post_provider ] instanceof PostProvider
		) {
			$post = $this->post_providers[ $post_provider ]->get_post( $post );
		} else {
			return '';
		}

		$this->switch_to_post( $post );

		$source = $this->create_source( $source, $this->post_providers[ $post_provider ] );

		if ( ! $source || ! $source instanceof Sources\Source ) {
			return '';
		}

		$content = $source->get_content( $field, $extra_attributes );

		$this->restore_current_post();

		return $content;
	}

	public function get_source_fields(
		/** @noinspection PhpUnusedParameterInspection */ $fields, $post, $source, $post_provider
	) {
		if (
			! $post_provider ||
			! isset( $this->post_providers[ $post_provider ] ) ||
			! ( $this->post_providers[ $post_provider ] instanceof PostProvider )
		) {
			return '';
		}

		$this->switch_to_post( $post );

		$source = $this->create_source( $source, $this->post_providers[ $post_provider ] );

		if ( ! $source || ! $source instanceof Sources\Source ) {
			return '';
		}

		$fields = $source->get_fields();

		$this->restore_current_post();

		return $fields;
	}

	/**
	 * Gets the post providers for internal use.
	 *
	 * Callback for the 'toolset/dynamic_sources/filters/get_post_providers' filter.
	 *
	 * @return PostProvider[]
	 */
	public function get_post_providers() {
		return $this->post_providers;
	}

	/**
	 * Gets the post providers, ready for the UI Select control of the Dynamic panel.
	 *
	 * Callback for the 'toolset/dynamic_sources/filters/get_post_providers_for_select' filter.
	 *
	 * @return array
	 */
	public function get_post_providers_for_select() {
		$post_providers = array();

		foreach ( $this->post_providers as $post_provider ) {
			$post_providers[] = array(
				'value' => $post_provider->get_unique_slug(),
				'label' => $post_provider->get_label(),
			);
		}

		return $post_providers;
	}

	/**
	 * Gets the registered sources grouped, ready for the UI Select control of the Dynamic panel.
	 *
	 * Callback for the 'toolset/dynamic_sources/filters/get_grouped_sources' filter.
	 *
	 * @return array
	 */
	public function get_grouped_sources() {
		$output_sources = array();

		foreach ( $this->post_providers as $post_provider ) {
			$output_sources_for_post_provider = array();

			$sources = array_map(
				function( Source $post_source ) use ( $post_provider ) {
					return $this->get_source_info( $post_source, $post_provider );
				},
				$this->source_storage->get_sources_for_post_provider( $post_provider )
			);

			$groups = $this->get_groups();

			foreach ( $sources as $source ) {
				if (
					$source['instance']::HAS_FIELDS &&
					empty( $source['fields'] )
				) {
					continue;
				}

				if ( ! isset( $output_sources_for_post_provider[ $source['group'] ] ) ) {
					$output_sources_for_post_provider[ $source['group'] ] = array(
						'label'   => isset( $groups[ $source['group'] ] ) ?
							$groups[ $source['group'] ] :
							$groups[ self::OTHER_GROUP ],
						'id'      => $source['group'],
						'options' => array(),
					);
				}

				$output_sources_for_post_provider[ $source['group'] ]['options'][] = array(
					'label' => $source['title'],
					'value' => $source['name'],
					'group' => $source['group'],
					'categories' => $source['categories'],
					'fields' => $source['fields'],
				);
			}

			$output_sources[ $post_provider->get_unique_slug() ] = array_values( $output_sources_for_post_provider );
		}

		return $output_sources;
	}

	private function get_groups() {
		$groups = array(
			self::POST_GROUP => __( 'Post', 'toolset-dynamic-sources' ),
			self::AUTHOR_GROUP => __( 'Author', 'toolset-dynamic-sources' ),
			self::SITE_GROUP => __( 'Site', 'toolset-dynamic-sources' ),
			self::MEDIA_GROUP => __( 'Media', 'toolset-dynamic-sources' ),
			self::COMMENTS_GROUP => __( 'Comments', 'toolset-dynamic-sources' ),
			self::OTHER_GROUP => __( 'Other', 'toolset-dynamic-sources' ),
		);

		/**
		 * Filters the groups of the Dynamic Sources offered.
		 *
		 * @param array $groups The groups of Dynamic Sources.
		 */
		$groups = apply_filters( 'toolset/dynamic_sources/filters/groups', $groups );

		return  $groups;
	}

	public function dynamic_container_shortcode_render( $attributes, $content ) {
		$atts = shortcode_atts(
			array(
				'provider' => '',
				'post' => 'current',
				'source' => '',
				'field' => '',
			),
			$attributes
		);

		// strings to array
		$providers = explode( self::VALUE_SEPARATOR, $atts['provider'] );
		$sources = explode( self::VALUE_SEPARATOR, $atts['source'] );
		$fields = explode( self::VALUE_SEPARATOR, $atts['field'] );

		// loop over all required sources
		for( $i = 0; $i < count( $providers ); $i++ ) {
			$has_content_attributes = array(
				'provider' => $providers[$i],
				'source' => $sources[$i],
				'field' => $fields[$i]
			);

			$has_content = $this->get_shortcode_content( $has_content_attributes );

			if( empty( $has_content ) ) {
				// one required sources has no value... means no output for this block
				return '';
			}
		}

		// all required dynamic sources have content, proceed...
		$content = do_shortcode( $content );

		if( empty( $content ) ) {
			// empty inner content, means theres probably some misconfiguration on the block
			// because the container should have the same parameters as the inner shortcode
			return '';
		}

		// content available
		return $content;
	}

	public function dynamic_shortcode_render( $attributes ) {
		return $this->get_shortcode_content( $attributes );
	}

	private function get_shortcode_content( $attributes ) {
		$atts = shortcode_atts(
			array(
				'provider' => IdentityPost::UNIQUE_SLUG,
				'post' => 'current',
				'source' => '',
				'field' => '',
				'force-string' => false,
			),
			$attributes
		);

		do_action( 'toolset/dynamic_sources/actions/register_sources' );

		$post_provider = sanitize_text_field( $atts['provider'] );
		$post = sanitize_text_field( $atts['post'] );
		$source = sanitize_text_field( $atts[ 'source' ] );
		$field = sanitize_text_field( $atts['field'] );

		if ( 'current' === $post ) {
			$post = get_the_ID();
		}

		$post = apply_filters( 'toolset/dynamic_sources/filters/shortcode_post', $post, $post_provider, $source, $field );

		$output = $this->get_source_content( '', $post_provider, $post, $source, $field, $attributes );

		if ( $atts[ 'force-string' ] && is_array( $output ) ) {
			if ( 'first' === $atts[ 'force-string' ] ) {
				$output = reset( $output );
			}
			if ( is_array( $output ) ) {
				$output = implode( ',', $output );
			}
		}
		return $output;
	}


	private function register_source( Sources\Source $source ) {
		$this->source_storage->add_source( $source );
	}


	public function add_source_for_registration( $source ) {
		$this->sources_for_registration[] = $source;
	}


	private function switch_to_post( $post_id ) {
		// If is already switched, or is the same post, return.
		if (
			get_the_ID() === $post_id
		) {
			$this->switched_post_data[] = false;
			return;
		}

		$this->switched_post_data[] = [
			'switched_id' => $post_id,
			'original_id' => get_the_ID(), // Note, it can be false if the global isn't set
		];

		global $post;

		$post = get_post( $post_id );

		setup_postdata( $post );
	}


	private function restore_current_post() {
		$data = array_pop( $this->switched_post_data );

		// If not switched, return.
		if ( ! $data ) {
			return;
		}

		global $post;

		// It was switched from an empty global post, restore this state and unset the global post
		if ( false === $data['original_id'] ) {
			unset( $post );
			return;
		}

		$post = get_post( $data['original_id'] );

		setup_postdata( $post );
	}

	private function create_source( $source_name, PostProvider $post_provider ) {
		$source_info = $this->get_source_info( $source_name, $post_provider );

		if ( ! $source_info ) {
			return null;
		}

		return $source_info['instance'];
	}

	/**
	 * @param string|Source $source_name
	 *
	 * @return array|null
	 */
	private function get_source_info( $source_name, PostProvider $post_provider ) {
		if ( $source_name instanceof Source ) {
			$source = $source_name;
		} else {
			$source = $this->source_storage->get_source( $source_name );
		}

		if ( null === $source ) {
			return null;
		}

		$source->set_post_provider( $post_provider );

		return array(
			'instance' => $source,
			'name' => $source->get_name(),
			'title' => $source->get_title(),
			'group' => $source->get_group(),
			'categories' => $source->get_categories(),
			'fields' => $source->get_fields(),
		);
	}

	/**
	 * This will render our shortcodes before any filtering, because WordPress has some very strict rules
	 * on html attributes.
	 *
	 * THIS IS MORE A START OF A PROPER FIX AND NEEDS TO BE WORKED OUT
	 * @TODO PROPER IMPLEMENTATION
	 *
	 * @param $content
	 *
	 * @return string|string[]|null
	 *
	 */
	public function shortcode_render( $content ) {
		return preg_replace_callback( '#(\[tb-dynamic\s.*?\])#', function( $shortcode ) {
			return do_shortcode( $shortcode[0] );
		}, $content );
	}

	/**
	 * Prepares the required client side information needed from the Dynamic Sources API.
	 *
	 * @param array $info
	 *
	 * @return array
	 */
	public function prepare_client_side_information( $info ) {
		return array_merge(
			$info,
			array(
				'postProviders' => apply_filters( 'toolset/dynamic_sources/filters/get_post_providers_for_select', array() ),
				'dynamicSources' => apply_filters( 'toolset/dynamic_sources/filters/get_grouped_sources', array() ),
				'dynamicSourcesStore' => self::TOOLSET_BLOCKS_DYNAMIC_SOURCES_STORE,
				'cache' => apply_filters( 'toolset/dynamic_sources/filters/cache', array(), get_the_ID() ),
			)
		);
	}
}
