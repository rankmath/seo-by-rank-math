<?php
/**
 * Ability: rank-math/get-post-links
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\Abilities\Link_Genius
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Link_Genius;

use RankMath\Abilities\Ability_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-post-links ability.
 *
 * Returns a paginated list of internal and external links stored for a given
 * post, including URL, link type, and target post metadata for internal links.
 */
class Get_Post_Links implements Ability_Interface {

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
	 * Link data runner instance.
	 *
	 * @var Post_Links_Runner
	 */
	private $runner;

	/**
	 * Constructor.
	 *
	 * @param string                 $category    Ability category slug.
	 * @param array                  $shared_meta Shared meta args.
	 * @param Post_Links_Runner|null $runner      Runner instance.
	 */
	public function __construct( string $category, array $shared_meta, ?Post_Links_Runner $runner = null ) {
		$this->category    = $category;
		$this->shared_meta = $shared_meta;
		$this->runner      = $runner ?? new Post_Links_Runner();
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/get-post-links',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get post links', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns a paginated list of internal and external links stored for a given post, including the URL, link type, and target post details for internal links.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'required'             => [ 'post_id' ],
					'properties'           => [
						'post_id' => [
							'type'        => 'integer',
							'description' => esc_html__( 'ID of the post whose links to retrieve.', 'seo-by-rank-math' ),
							'minimum'     => 1,
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
		return current_user_can( 'rank_math_link_builder' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$post_id = absint( $input['post_id'] ?? 0 );

		$result = $this->runner->run(
			[
				'source_id' => $post_id,
			]
		);

		$internal = [];
		$external = [];

		foreach ( $result['links'] ?? [] as $link ) {
			$item = [
				'url'      => $link['url'],
				'anchor'   => $link['anchor'],
				'dofollow' => $link['dofollow'],
			];

			if ( 'internal' === $link['type'] ) {
				$item['target_post_id'] = $link['target_post_id'];
				$internal[]             = $item;
			} else {
				$external[] = $item;
			}
		}

		$output = [
			'post_id'  => $post_id,
			'internal' => $internal,
			'external' => $external,
			'counts'   => [
				'internal' => count( $internal ),
				'external' => count( $external ),
			],
		];

		rank_math()->tracking->track_ability_executed(
			'Post Links Fetched',
			[
				'post_id'  => $post_id,
				'internal' => count( $internal ),
				'external' => count( $external ),
			],
			'rank_math_link_builder'
		);

		return $output;
	}

	/**
	 * JSON schema for the ability output.
	 *
	 * @return array
	 */
	private function output_schema(): array {
		$link_item = [
			'type'       => 'object',
			'properties' => [
				'url'      => [
					'type'        => 'string',
					'description' => 'Full URL of the link.',
				],
				'anchor'   => [
					'type'        => [ 'string', 'null' ],
					'description' => 'Anchor text of the link. Null when anchor data is unavailable (FREE plugin).',
				],
				'dofollow' => [
					'type'        => [ 'boolean', 'null' ],
					'description' => 'True when the link is dofollow. Null when rel data is unavailable.',
				],
			],
		];

		$internal_item                                 = $link_item;
		$internal_item['properties']['target_post_id'] = [
			'type'        => 'integer',
			'description' => 'WordPress post ID of the link target.',
		];

		return [
			'type'       => 'object',
			'properties' => [
				'post_id'  => [
					'type'        => 'integer',
					'description' => 'WordPress post ID of the source post.',
				],
				'internal' => [
					'type'        => 'array',
					'description' => 'Internal links found in the post.',
					'items'       => $internal_item,
				],
				'external' => [
					'type'        => 'array',
					'description' => 'External links found in the post.',
					'items'       => $link_item,
				],
				'counts'   => [
					'type'        => 'object',
					'description' => 'Count of links returned per type.',
					'properties'  => [
						'internal' => [
							'type'        => 'integer',
							'description' => 'Number of internal links returned.',
						],
						'external' => [
							'type'        => 'integer',
							'description' => 'Number of external links returned.',
						],
					],
				],
			],
		];
	}
}
