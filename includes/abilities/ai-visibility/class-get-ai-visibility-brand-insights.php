<?php
/**
 * Ability: rank-math/get-ai-visibility-brand-insights
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
 * Registers and executes the rank-math/get-ai-visibility-brand-insights ability.
 */
class Get_AI_Visibility_Brand_Insights implements Ability_Interface {

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
			'rank-math/get-ai-visibility-brand-insights',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get AI Visibility brand insights', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns the full analysis results for a single brand — AI Visibility score, rank, sentiment, mention and citation counts, per-competitor breakdown, and per-query results including the raw AI model responses.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'brand_id' ],
					'properties'           => [
						'brand_id' => [
							'type'        => 'string',
							'description' => esc_html__( 'Brand UUID. Obtain from get-ai-visibility-overview.', 'seo-by-rank-math' ),
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

		$cached = Cache::get_analysis( $brand_id );

		// Cache miss or stale — signal the caller to trigger a fetch via the REST endpoint.
		if ( false === $cached || Cache::is_brand_stale( $brand_id ) ) {
			rank_math()->tracking->track_ability_executed(
				'AI Visibility Brand Insights Fetched',
				[
					'brand_id'         => $brand_id,
					'pending'          => true,
					'query_count'      => 0,
					'competitor_count' => 0,
				],
				'manage_options'
			);

			return [ 'pending' => true ];
		}

		$query_count      = count( $cached['query_results'] ?? [] );
		$competitor_count = count( $cached['competitors'] ?? [] );

		rank_math()->tracking->track_ability_executed(
			'AI Visibility Brand Insights Fetched',
			[
				'brand_id'         => $brand_id,
				'pending'          => false,
				'query_count'      => $query_count,
				'competitor_count' => $competitor_count,
			],
			'manage_options'
		);

		return array_merge( $cached, [ 'pending' => false ] );
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
				'pending'       => [
					'type'        => 'boolean',
					'description' => 'True when no completed analysis exists yet (e.g. brand was just added). All other fields are absent in this case.',
				],
				'score'         => [
					'type'        => [ 'number', 'null' ],
					'description' => 'AI Visibility score for this brand.',
				],
				'rank'          => [
					'type'        => [ 'number', 'null' ],
					'description' => 'Brand ranking position among competitors.',
				],
				'avg_sentiment' => [
					'type'        => [ 'number', 'null' ],
					'description' => 'Average sentiment score across all mentions.',
				],
				'mentions'      => [
					'type'        => [ 'number', 'null' ],
					'description' => 'Total number of AI mentions.',
				],
				'citations'     => [
					'type'        => [ 'number', 'null' ],
					'description' => 'Total number of AI citations.',
				],
				'analysis'      => [
					'type'        => [ 'object', 'null' ],
					'description' => 'Metadata about the latest analysis run.',
					'properties'  => [
						'id'               => [
							'type'        => [ 'string', 'null' ],
							'description' => 'Analysis UUID.',
						],
						'status'           => [
							'type'        => [ 'string', 'null' ],
							'description' => 'Analysis status (e.g. done, processing, error).',
						],
						'started_at'       => [
							'type'        => [ 'string', 'null' ],
							'format'      => 'date-time',
							'description' => 'ISO 8601 datetime when the analysis started.',
						],
						'finished_at'      => [
							'type'        => [ 'string', 'null' ],
							'format'      => 'date-time',
							'description' => 'ISO 8601 datetime when the analysis completed.',
						],
						'duration_seconds' => [
							'type'        => [ 'number', 'null' ],
							'description' => 'How long the analysis took in seconds.',
						],
					],
				],
				'competitors'   => [
					'type'        => 'array',
					'description' => 'Per-competitor breakdown for this brand.',
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'name'          => [
								'type'        => 'string',
								'description' => 'Competitor brand name.',
							],
							'url'           => [
								'type'        => [ 'string', 'null' ],
								'format'      => 'uri',
								'description' => 'Competitor website URL.',
							],
							'mentions'      => [
								'type'        => [ 'number', 'null' ],
								'description' => 'Number of AI mentions for this competitor.',
							],
							'avg_sentiment' => [
								'type'        => [ 'number', 'null' ],
								'description' => 'Average sentiment score for this competitor.',
							],
						],
					],
				],
				'query_results' => [
					'type'        => 'array',
					'description' => 'Per-query analysis results including raw AI model responses.',
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'query_id'        => [
								'type'        => [ 'string', 'null' ],
								'description' => 'Query UUID.',
							],
							'query_text'      => [
								'type'        => 'string',
								'description' => 'The query that was submitted to the AI model.',
							],
							'found'           => [
								'type'        => 'boolean',
								'description' => 'Whether the brand was mentioned in the AI response.',
							],
							'rank'            => [
								'type'        => [ 'number', 'null' ],
								'description' => 'Brand rank among competitors for this query.',
							],
							'sentiment'       => [
								'type'        => [ 'number', 'null' ],
								'description' => 'Sentiment score for the brand mention in this query.',
							],
							'citations'       => [
								'type'        => [ 'number', 'null' ],
								'description' => 'Number of citations in the AI response for this query.',
							],
							'response'        => [
								'type'        => 'string',
								'description' => 'Raw text response returned by the AI model for this query.',
							],
							'extraction_data' => [
								'type'        => 'string',
								'description' => 'Raw extraction data used to derive the structured results.',
							],
						],
					],
				],
			],
		];
	}
}
