<?php
/**
 * Ability: rank-math/set-sitemap-settings
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/set-sitemap-settings ability.
 */
class Set_Sitemap_Settings extends Abstract_Ability {

	/**
	 * Constructor.
	 *
	 * @param string $category    Ability category slug.
	 * @param array  $shared_meta Shared meta args.
	 */
	public function __construct( string $category, array $shared_meta ) {
		parent::__construct( $category, $shared_meta, 'rank_math_sitemap' );
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/set-sitemap-settings',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Set sitemap settings', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Updates XML sitemap settings: links per sitemap page, image inclusion, excluded post/term IDs, author archive inclusion, and per-post-type/per-taxonomy sitemap inclusion. All fields are optional — omitted fields keep their current stored value. Requires the sitemap module to be active; enable it first with set-module-status if needed.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'items_per_page'         => [
							'type'        => 'integer',
							'title'       => esc_html__( 'Links Per Sitemap', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Max number of links on each sitemap page.', 'seo-by-rank-math' ),
						],
						'include_images'         => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Images in Sitemaps', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Include reference to images from the post content in sitemaps.', 'seo-by-rank-math' ),
						],
						'include_featured_image' => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Include Featured Images', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Include the Featured Image too, even if it does not appear directly in the post content. Only takes effect when include_images is also enabled.', 'seo-by-rank-math' ),
						],
						'exclude_posts'          => [
							'type'        => 'string',
							'title'       => esc_html__( 'Exclude Posts', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Comma-separated post IDs to exclude from the sitemap. Applies to all post types.', 'seo-by-rank-math' ),
						],
						'exclude_terms'          => [
							'type'        => 'string',
							'title'       => esc_html__( 'Exclude Terms', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Comma-separated term IDs to exclude from the sitemap. Applies to all taxonomies.', 'seo-by-rank-math' ),
						],
						'authors_sitemap'        => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Include Authors in Sitemap', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Include author archives in the XML sitemap.', 'seo-by-rank-math' ),
						],
						'post_types'             => [
							'type'                 => 'object',
							'title'                => esc_html__( 'Post Types', 'seo-by-rank-math' ),
							'description'          => esc_html__( 'Map of post type slug to whether it should be included in the XML sitemap. Call get-settings first to see registered post type sitemap status.', 'seo-by-rank-math' ),
							'additionalProperties' => [ 'type' => 'boolean' ],
						],
						'taxonomies'             => [
							'type'                 => 'object',
							'title'                => esc_html__( 'Taxonomies', 'seo-by-rank-math' ),
							'description'          => esc_html__( 'Map of taxonomy slug to whether it should be included in the XML sitemap.', 'seo-by-rank-math' ),
							'additionalProperties' => [ 'type' => 'boolean' ],
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => $this->write_success_schema( true, 'List of top-level field names that were updated.' ),
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
		if ( isset( $input['post_types'] ) ) {
			$accessible_post_types = $this->get_accessible_post_types();
			foreach ( array_keys( $input['post_types'] ) as $post_type ) {
				if ( ! in_array( $post_type, $accessible_post_types, true ) ) {
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
			}
		}

		if ( isset( $input['taxonomies'] ) ) {
			$accessible_taxonomies = $this->get_accessible_taxonomies();
			foreach ( array_keys( $input['taxonomies'] ) as $taxonomy ) {
				if ( ! in_array( $taxonomy, $accessible_taxonomies, true ) ) {
					return [
						'error' => [
							'code'    => 'invalid_taxonomy',
							'message' => sprintf(
								/* translators: taxonomy slug */
								esc_html__( 'Unknown or inaccessible taxonomy: %s', 'seo-by-rank-math' ),
								esc_html( $taxonomy )
							),
						],
					];
				}
			}
		}

		$sitemap = $this->get_sitemap_option();

		if ( isset( $input['items_per_page'] ) ) {
			$sitemap['items_per_page'] = (string) absint( $input['items_per_page'] );
		}

		$this->apply_toggle_fields(
			$sitemap,
			$input,
			[ 'include_images', 'include_featured_image', 'authors_sitemap' ]
		);

		foreach ( [ 'exclude_posts', 'exclude_terms' ] as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sitemap[ $field ] = sanitize_text_field( $input[ $field ] );
			}
		}

		if ( isset( $input['post_types'] ) ) {
			foreach ( $input['post_types'] as $post_type => $enabled ) {
				$sitemap[ "pt_{$post_type}_sitemap" ] = $enabled ? 'on' : 'off';
			}
		}

		if ( isset( $input['taxonomies'] ) ) {
			foreach ( $input['taxonomies'] as $taxonomy => $enabled ) {
				$sitemap[ "tax_{$taxonomy}_sitemap" ] = $enabled ? 'on' : 'off';
			}
		}

		$this->save_and_reset( null, null, $sitemap );

		/**
		 * Fires after set-sitemap-settings saves its FREE fields, so PRO can persist
		 * its own sitemap fields (news_sitemap_publication_name, video_sitemap_post_type,
		 * hide_video_sitemap, local_sitemap) from the same input.
		 *
		 * @param array $input Raw ability input.
		 */
		do_action( 'rank_math/abilities/set_sitemap_settings/after_save', $input );

		$keys = array_keys( $input );

		rank_math()->tracking->track_ability_executed(
			'Sitemap Settings Updated',
			[ 'fields_updated' => $keys ],
			'rank_math_sitemap'
		);

		return [
			'saved'   => true,
			'updated' => $keys,
		];
	}

	/**
	 * Current raw sitemap option. Extracted for testability.
	 *
	 * @return array
	 */
	protected function get_sitemap_option(): array {
		return (array) get_option( 'rank-math-options-sitemap', [] );
	}

	/**
	 * Accessible taxonomy slugs. Extracted for testability.
	 *
	 * @return array<int, string>
	 */
	protected function get_accessible_taxonomies(): array {
		return array_keys( Helper::get_accessible_taxonomies() );
	}
}
