<?php
/**
 * Ability: rank-math/get-ai-visibility-brand-queries
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\Abilities\AI_Visibility
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\AI_Visibility;

use RankMath\Abilities\Ability_Interface;
use RankMath\AI_Visibility\Cache;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-ai-visibility-brand-queries ability.
 */
class Get_AI_Visibility_Brand_Queries implements Ability_Interface {

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
			'rank-math/get-ai-visibility-brand-queries',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get AI Visibility brand queries', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns the list of search queries being monitored for a brand, including which are currently enabled and which were auto-generated as baselines.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'brand_id' ],
					'properties'           => [
						'brand_id' => [
							'type'        => 'string',
							'description' => esc_html__( 'Brand UUID. Obtain from get-ai-visibility-overview or get-ai-visibility-brand-insights.', 'seo-by-rank-math' ),
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
		return current_user_can( 'manage_options' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$brand_id = sanitize_text_field( (string) ( $input['brand_id'] ?? '' ) );

		$cached  = Cache::get_queries( $brand_id );
		$queries = null !== $cached ? (array) ( $cached['queries'] ?? [] ) : [];
		$total   = count( $queries );

		$enabled_count = count(
			array_filter(
				$queries,
				function ( $q ) {
					return ! empty( $q['enabled'] );
				}
			)
		);

		rank_math()->tracking->track_ability_executed(
			'AI Visibility Brand Queries Fetched',
			[
				'brand_id'      => $brand_id,
				'total'         => $total,
				'enabled_count' => $enabled_count,
			],
			'manage_options'
		);

		return [
			'brand_id' => $brand_id,
			'queries'  => $queries,
			'total'    => $total,
		];
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
				'brand_id' => [
					'type'        => 'string',
					'description' => 'The brand UUID the queries belong to.',
				],
				'queries'  => [
					'type'        => 'array',
					'description' => 'List of monitored queries for this brand.',
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'id'          => [
								'type'        => 'string',
								'description' => 'Query UUID.',
							],
							'text'        => [
								'type'        => 'string',
								'description' => 'The query text submitted to AI models.',
							],
							'enabled'     => [
								'type'        => 'boolean',
								'description' => 'Whether this query is currently active for analysis.',
							],
							'is_baseline' => [
								'type'        => 'boolean',
								'description' => 'True when this query was auto-generated as a baseline by Rank Math.',
							],
							'created_at'  => [
								'type'        => [ 'string', 'null' ],
								'format'      => 'date-time',
								'description' => 'ISO 8601 datetime when the query was created.',
							],
							'updated_at'  => [
								'type'        => [ 'string', 'null' ],
								'format'      => 'date-time',
								'description' => 'ISO 8601 datetime when the query was last updated.',
							],
						],
					],
				],
				'total'    => [
					'type'        => 'integer',
					'description' => 'Total number of queries returned.',
				],
			],
		];
	}
}
