<?php
/**
 * Ability: rank-math/set-post-type-seo-settings
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/set-post-type-seo-settings ability.
 */
class Set_Post_Type_Seo_Settings extends Abstract_Ability {

	/**
	 * Constructor.
	 *
	 * @param string $category    Ability category slug.
	 * @param array  $shared_meta Shared meta args.
	 */
	public function __construct( string $category, array $shared_meta ) {
		parent::__construct( $category, $shared_meta, 'rank_math_titles' );
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/set-post-type-seo-settings',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Set post type SEO settings', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Updates per-post-type SEO defaults: default title/description templates, default Schema type, default article type, custom robots meta, and whether to show SEO controls on the editor screen. Accepts a map keyed by post type slug; only provided post types and fields are changed.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'post_types' ],
					'properties'           => [
						'post_types' => [
							'type'                 => 'object',
							'title'                => esc_html__( 'Post Types', 'seo-by-rank-math' ),
							'description'          => esc_html__( 'Map of post type slug to its SEO settings. Call get-settings first to see registered post type slugs.', 'seo-by-rank-math' ),
							'additionalProperties' => [
								'type'                 => 'object',
								'properties'           => [
									'title'                => [
										'type'        => 'string',
										'title'       => esc_html__( 'Single Title', 'seo-by-rank-math' ),
										'description' => esc_html__( 'Default title tag for single pages of this post type. Supports Rank Math title variables.', 'seo-by-rank-math' ),
									],
									'description'          => [
										'type'        => 'string',
										'title'       => esc_html__( 'Single Description', 'seo-by-rank-math' ),
										'description' => esc_html__( 'Default meta description for single pages of this post type. Supports Rank Math variables.', 'seo-by-rank-math' ),
									],
									'default_rich_snippet' => [
										'type'        => 'string',
										'title'       => esc_html__( 'Schema Type', 'seo-by-rank-math' ),
										'description' => esc_html__( 'Default Schema type slug for new posts of this type (e.g. "off", "article", "product", "event"). Call get-settings first or see the Schema Type list in WP Admin for valid values — the list varies per post type and active modules.', 'seo-by-rank-math' ),
									],
									'default_article_type' => [
										'type'        => 'string',
										'enum'        => [ 'Article', 'BlogPosting', 'NewsArticle' ],
										'title'       => esc_html__( 'Article Type', 'seo-by-rank-math' ),
										'description' => esc_html__( 'Default article schema sub-type. Only used when default_rich_snippet is "article".', 'seo-by-rank-math' ),
									],
									'custom_robots'        => [
										'type'        => 'boolean',
										'title'       => esc_html__( 'Custom Robots Meta', 'seo-by-rank-math' ),
										'description' => esc_html__( 'Whether to use custom robots meta values for this post type instead of the Global Meta default.', 'seo-by-rank-math' ),
									],
									'robots'               => [
										'type'        => 'array',
										'items'       => [
											'type' => 'string',
											'enum' => self::ROBOTS_DIRECTIVES,
										],
										'title'       => esc_html__( 'Robots Meta', 'seo-by-rank-math' ),
										'description' => esc_html__( 'Custom robots meta values for this post type. Only takes effect when custom_robots is enabled.', 'seo-by-rank-math' ),
									],
									'add_meta_box'         => [
										'type'        => 'boolean',
										'title'       => esc_html__( 'Add SEO Controls', 'seo-by-rank-math' ),
										'description' => esc_html__( 'Whether to add SEO controls to the editor screen for posts of this type.', 'seo-by-rank-math' ),
									],
								],
								'additionalProperties' => false,
							],
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => $this->write_success_schema( true, 'List of "post_type.field" identifiers that were updated.' ),
				'permission_callback' => [ $this, 'check_permissions' ],
				'execute_callback'    => [ $this, 'execute' ],
				'meta'                => $this->build_meta( false ),
			]
		);
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$post_types = isset( $input['post_types'] ) && is_array( $input['post_types'] ) ? $input['post_types'] : [];

		if ( empty( $post_types ) ) {
			return [
				'error' => [
					'code'    => 'missing_post_types',
					'message' => esc_html__( 'post_types must be a non-empty map of post type slug to settings.', 'seo-by-rank-math' ),
				],
			];
		}

		$accessible = $this->get_accessible_post_types();

		foreach ( $post_types as $post_type => $fields ) {
			if ( ! in_array( $post_type, $accessible, true ) ) {
				return [
					'error' => [
						'code'    => 'invalid_post_type',
						'message' => sprintf(
							/* translators: post type slug */
							esc_html__( 'Unknown or inaccessible post type: %s', 'seo-by-rank-math' ),
							esc_html( $post_type )
						),
					],
				];
			}

			if ( isset( $fields['robots'] ) ) {
				foreach ( (array) $fields['robots'] as $value ) {
					if ( ! in_array( $value, self::ROBOTS_DIRECTIVES, true ) ) {
						return [
							'error' => [
								'code'    => 'invalid_robots',
								'message' => esc_html__( 'robots may only contain: index, noindex, nofollow, noarchive, noimageindex, nosnippet.', 'seo-by-rank-math' ),
							],
						];
					}
				}
			}

			if ( isset( $fields['default_article_type'] ) && ! in_array( $fields['default_article_type'], [ 'Article', 'BlogPosting', 'NewsArticle' ], true ) ) {
				return [
					'error' => [
						'code'    => 'invalid_article_type',
						'message' => esc_html__( 'default_article_type must be one of: Article, BlogPosting, NewsArticle.', 'seo-by-rank-math' ),
					],
				];
			}
		}

		$titles  = $this->get_titles_option();
		$updated = [];

		foreach ( $post_types as $post_type => $fields ) {
			$prefix = "pt_{$post_type}_";

			foreach ( [ 'title', 'description' ] as $field ) {
				if ( isset( $fields[ $field ] ) ) {
					$titles[ $prefix . $field ] = sanitize_text_field( $fields[ $field ] );
					$updated[]                  = "{$post_type}.{$field}";
				}
			}

			if ( isset( $fields['default_rich_snippet'] ) ) {
				$titles[ $prefix . 'default_rich_snippet' ] = sanitize_text_field( $fields['default_rich_snippet'] );
				$updated[]                                  = "{$post_type}.default_rich_snippet";
			}

			if ( isset( $fields['default_article_type'] ) ) {
				$titles[ $prefix . 'default_article_type' ] = $fields['default_article_type'];
				$updated[]                                  = "{$post_type}.default_article_type";
			}

			if ( isset( $fields['custom_robots'] ) ) {
				$titles[ $prefix . 'custom_robots' ] = $fields['custom_robots'] ? 'on' : 'off';
				$updated[]                           = "{$post_type}.custom_robots";
			}

			if ( isset( $fields['robots'] ) ) {
				$titles[ $prefix . 'robots' ] = array_map( 'sanitize_text_field', (array) $fields['robots'] );
				$updated[]                    = "{$post_type}.robots";
			}

			if ( isset( $fields['add_meta_box'] ) ) {
				$titles[ $prefix . 'add_meta_box' ] = $fields['add_meta_box'] ? 'on' : 'off';
				$updated[]                          = "{$post_type}.add_meta_box";
			}
		}

		$this->save_and_reset( null, $titles, null );

		rank_math()->tracking->track_ability_executed(
			'Post Type SEO Settings Updated',
			[
				'post_types'     => array_keys( $post_types ),
				'fields_updated' => $updated,
			],
			'rank_math_titles'
		);

		return [
			'saved'   => true,
			'updated' => $updated,
		];
	}
}
