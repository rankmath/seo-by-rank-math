<?php
/**
 * Ability: rank-math/update-post-seo-meta
 *
 * @since      1.0.274
 * @package    RankMath
 * @subpackage RankMath\Abilities\Post_SEO
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Post_SEO;

use RankMath\Abilities\Ability_Interface;
use RankMath\Paper\Singular;
use RankMath\Rest\Sanitize;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/update-post-seo-meta ability — the
 * write counterpart to rank-math/get-post-seo-meta.
 */
class Update_Post_SEO_Meta implements Ability_Interface {

	/**
	 * Ability category slug.
	 *
	 * @var string
	 */
	private $category;

	/**
	 * Shared meta args.
	 *
	 * @var array
	 */
	private $shared_meta;

	/**
	 * Map of ability input fields to their Rank Math post-meta keys.
	 *
	 * Keys verified against Rank Math's own importer field map
	 * (includes/admin/importers/class-aioseo.php) and Singular::get_seo_meta().
	 *
	 * @var array<string, string>
	 */
	private const FIELD_META_MAP = [
		'title'               => 'rank_math_title',
		'description'         => 'rank_math_description',
		'focus_keyword'       => 'rank_math_focus_keyword',
		'canonical'           => 'rank_math_canonical_url',
		'robots'              => 'rank_math_robots',
		'og_title'            => 'rank_math_facebook_title',
		'og_description'      => 'rank_math_facebook_description',
		'twitter_title'       => 'rank_math_twitter_title',
		'twitter_description' => 'rank_math_twitter_description',
	];

	/**
	 * Constructor.
	 *
	 * @param string $category    Ability category slug.
	 * @param array  $shared_meta Shared meta args.
	 */
	public function __construct( string $category, array $shared_meta ) {
		$this->category    = $category;
		$this->shared_meta = $shared_meta;
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/update-post-seo-meta',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Update post SEO metadata', 'seo-by-rank-math' ),
				'description'         => esc_html__( 'Updates the SEO metadata for a post. Only the fields you provide are changed; a field set to an empty value is cleared. Writable fields: title, description, focus keyword, canonical URL, robots, and the Open Graph and Twitter overrides.', 'seo-by-rank-math' ),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'post_id' ],
					'properties'           => [
						'post_id'             => [
							'type'        => 'integer',
							'description' => esc_html__( 'The ID of the post to update.', 'seo-by-rank-math' ),
						],
						'title'               => [
							'type'        => [ 'string', 'null' ],
							'description' => esc_html__( 'SEO title.', 'seo-by-rank-math' ),
						],
						'description'         => [
							'type'        => [ 'string', 'null' ],
							'description' => esc_html__( 'SEO meta description.', 'seo-by-rank-math' ),
						],
						'focus_keyword'       => [
							'type'        => [ 'string', 'null' ],
							'description' => esc_html__( 'Focus keyword(s), comma-separated.', 'seo-by-rank-math' ),
						],
						'canonical'           => [
							'type'        => [ 'string', 'null' ],
							'description' => esc_html__( 'Canonical URL override.', 'seo-by-rank-math' ),
						],
						'robots'              => [
							'type'        => [ 'array', 'null' ],
							'items'       => [ 'type' => 'string' ],
							'description' => esc_html__( 'Robots meta directives (e.g. index, noindex, nofollow).', 'seo-by-rank-math' ),
						],
						'og_title'            => [
							'type'        => [ 'string', 'null' ],
							'description' => esc_html__( 'Open Graph (Facebook) title override.', 'seo-by-rank-math' ),
						],
						'og_description'      => [
							'type'        => [ 'string', 'null' ],
							'description' => esc_html__( 'Open Graph (Facebook) description override.', 'seo-by-rank-math' ),
						],
						'twitter_title'       => [
							'type'        => [ 'string', 'null' ],
							'description' => esc_html__( 'Twitter title override.', 'seo-by-rank-math' ),
						],
						'twitter_description' => [
							'type'        => [ 'string', 'null' ],
							'description' => esc_html__( 'Twitter description override.', 'seo-by-rank-math' ),
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => $this->output_schema(),
				'permission_callback' => [ $this, 'check_permissions' ],
				'execute_callback'    => [ $this, 'execute' ],
				'meta'                => array_merge(
					$this->shared_meta,
					[
						'annotations' => [
							'readonly'    => false,
							'destructive' => false,
							'idempotent'  => true,
						],
					]
				),
			]
		);
	}

	/**
	 * Check if the current user has permission to execute this ability.
	 *
	 * Writing requires the same Rank Math on-page capability the read ability
	 * uses; the per-post edit_post check happens in execute() once post_id is
	 * known.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rank_math_onpage_general' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$post_id = absint( $input['post_id'] );

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [ 'error' => esc_html__( 'Post not found.', 'seo-by-rank-math' ) ];
		}

		if ( ! current_user_can( 'rank_math_onpage_general' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return [ 'error' => esc_html__( 'You are not allowed to edit this post.', 'seo-by-rank-math' ) ];
		}

		$sanitizer = Sanitize::get();
		$updated   = [];

		foreach ( self::FIELD_META_MAP as $field => $meta_key ) {
			if ( ! array_key_exists( $field, $input ) ) {
				continue;
			}

			$value = $input[ $field ];

			// Empty value clears the meta, matching the REST meta-save behavior.
			if ( empty( $value ) ) {
				delete_post_meta( $post_id, $meta_key );
				$updated[] = $field;
				continue;
			}

			update_post_meta( $post_id, $meta_key, $sanitizer->sanitize( $meta_key, $value ) );
			$updated[] = $field;
		}

		// Return the fresh state so the caller sees the applied result.
		$result            = ( new Singular() )->get_seo_meta( $post_id );
		$result['updated'] = $updated;

		if ( empty( $result['error'] ) ) {
			rank_math()->tracking->track_ability_executed(
				'Post SEO Meta Updated',
				[
					'post_id' => $post_id,
					'fields'  => $updated,
				],
				'rank_math_onpage_general'
			);
		}

		return $result;
	}

	/**
	 * JSON schema for the ability output.
	 *
	 * Mirrors get-post-seo-meta and adds the list of fields that were written.
	 *
	 * @return array
	 */
	private function output_schema(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'post_id'             => [ 'type' => 'integer' ],
				'title'               => [ 'type' => 'string' ],
				'description'         => [ 'type' => 'string' ],
				'focus_keyword'       => [ 'type' => 'string' ],
				'robots'              => [
					'type'  => 'array',
					'items' => [ 'type' => 'string' ],
				],
				'canonical'           => [ 'type' => 'string' ],
				'og_title'            => [ 'type' => 'string' ],
				'og_description'      => [ 'type' => 'string' ],
				'twitter_title'       => [ 'type' => 'string' ],
				'twitter_description' => [ 'type' => 'string' ],
				'seo_score'           => [
					'type'    => 'integer',
					'minimum' => 0,
					'maximum' => 100,
				],
				'updated'             => [
					'type'        => 'array',
					'items'       => [ 'type' => 'string' ],
					'description' => 'Names of the fields that were written.',
				],
				'error'               => [
					'type' => 'string',
				],
			],
		];
	}
}
