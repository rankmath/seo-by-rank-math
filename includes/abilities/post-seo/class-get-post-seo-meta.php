<?php
/**
 * Ability: rank-math/get-post-seo-meta
 *
 * @since      1.0.272
 * @package    RankMath
 * @subpackage RankMath\Abilities\Post_SEO
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Post_SEO;

use RankMath\Abilities\Ability_Interface;
use RankMath\Paper\Singular;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-post-seo-meta ability.
 */
class Get_Post_SEO_Meta implements Ability_Interface {

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
			'rank-math/get-post-seo-meta',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get post SEO metadata', 'seo-by-rank-math' ),
				'description'         => esc_html__( 'Returns the full SEO metadata for a post: title, description, focus keyword, robots settings, canonical URL, Open Graph and Twitter overrides, and the current SEO score.', 'seo-by-rank-math' ),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'post_id' ],
					'properties'           => [
						'post_id' => [
							'type'        => 'integer',
							'description' => esc_html__( 'The ID of the post to retrieve SEO metadata for.', 'seo-by-rank-math' ),
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
							'readonly'    => true,
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
		$result  = ( new Singular() )->get_seo_meta( $post_id );

		if ( empty( $result['error'] ) ) {
			rank_math()->tracking->track_ability_executed(
				'Post SEO Meta Fetched',
				[ 'post_id' => $post_id ],
				'rank_math_onpage_general'
			);
		}

		return $result;
	}

	/**
	 * JSON schema for the ability output.
	 *
	 * @return array
	 */
	private function output_schema(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'post_id'             => [
					'type' => 'integer',
				],
				'title'               => [
					'type' => 'string',
				],
				'description'         => [
					'type' => 'string',
				],
				'focus_keyword'       => [
					'type' => 'string',
				],
				'robots'              => [
					'type'  => 'array',
					'items' => [ 'type' => 'string' ],
				],
				'canonical'           => [
					'type' => 'string',
				],
				'og_title'            => [
					'type' => 'string',
				],
				'og_description'      => [
					'type' => 'string',
				],
				'twitter_title'       => [
					'type' => 'string',
				],
				'twitter_description' => [
					'type' => 'string',
				],
				'seo_score'           => [
					'type'    => 'integer',
					'minimum' => 0,
					'maximum' => 100,
				],
			],
		];
	}
}
