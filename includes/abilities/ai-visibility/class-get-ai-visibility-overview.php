<?php
/**
 * Ability: rank-math/get-ai-visibility-overview
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
 * Registers and executes the rank-math/get-ai-visibility-overview ability.
 */
class Get_AI_Visibility_Overview implements Ability_Interface {

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
			'rank-math/get-ai-visibility-overview',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get AI Visibility overview', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns the site-level AI Visibility summary and the full list of tracked brands with their current scores, ranks, sentiment, and analysis status.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'refresh' => [
							'type'        => 'boolean',
							'description' => esc_html__( 'Pass true to bypass the 12-hour dashboard cache and return a stale-flagged response. Use the REST endpoint to trigger a fresh upstream fetch.', 'seo-by-rank-math' ),
							'default'     => false,
						],
						'search'  => [
							'type'        => 'string',
							'description' => esc_html__( 'Filter brands by name or URL (case-insensitive substring match).', 'seo-by-rank-math' ),
							'default'     => '',
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
		$refresh = (bool) ( $input['refresh'] ?? false );
		$search  = sanitize_text_field( (string) ( $input['search'] ?? '' ) );

		$data = Cache::get_dashboard();

		$summary = isset( $data['summary'] ) ? (array) $data['summary'] : [];
		$brands  = isset( $data['brands'] ) ? (array) $data['brands'] : [];

		if ( '' !== $search ) {
			$brands = array_values(
				array_filter(
					$brands,
					function ( $row ) use ( $search ) {
						return false !== stripos( $row['name'] ?? '', $search )
							|| false !== stripos( $row['url'] ?? '', $search );
					}
				)
			);
		}

		// Strip internal cache fields not relevant to MCP consumers.
		$brands = array_map(
			function ( $row ) {
				unset( $row['stale'], $row['next_scheduled'], $row['created_at'] );
				return $row;
			},
			$brands
		);

		rank_math()->tracking->track_ability_executed(
			'AI Visibility Overview Fetched',
			[
				'brand_count' => count( $brands ),
				'refresh'     => $refresh,
			],
			'manage_options'
		);

		return [
			'summary' => $summary,
			'brands'  => $brands,
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
				'summary' => [
					'type'        => 'object',
					'description' => 'Site-level AI Visibility summary metrics.',
				],
				'brands'  => [
					'type'        => 'array',
					'description' => 'List of tracked brands with their current AI Visibility data.',
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'id'              => [
								'type'        => 'string',
								'description' => 'Brand UUID.',
							],
							'name'            => [
								'type'        => 'string',
								'description' => 'Brand name.',
							],
							'url'             => [
								'type'        => 'string',
								'format'      => 'uri',
								'description' => 'Brand website URL.',
							],
							'locale'          => [
								'type'        => [ 'string', 'null' ],
								'description' => 'ISO 3166-1 alpha-2 country code (e.g. "US").',
							],
							'status'          => [
								'type'        => 'string',
								'enum'        => [ 'active', 'inactive' ],
								'description' => 'Whether the brand is actively tracked.',
							],
							'score'           => [
								'type'        => [ 'number', 'null' ],
								'description' => 'AI Visibility score for this brand.',
							],
							'rank'            => [
								'type'        => [ 'number', 'null' ],
								'description' => 'Brand ranking position.',
							],
							'avg_sentiment'   => [
								'type'        => [ 'number', 'null' ],
								'description' => 'Average sentiment score across mentions.',
							],
							'mentions'        => [
								'type'        => [ 'number', 'null' ],
								'description' => 'Total number of AI mentions.',
							],
							'citations'       => [
								'type'        => [ 'number', 'null' ],
								'description' => 'Total number of AI citations.',
							],
							'analysis_status' => [
								'type'        => [ 'string', 'null' ],
								'description' => 'Current analysis status (e.g. pending, processing, success, error).',
							],
							'last_analyzed'   => [
								'type'        => [ 'string', 'null' ],
								'format'      => 'date-time',
								'description' => 'ISO 8601 datetime of the last completed analysis.',
							],
						],
					],
				],
			],
		];
	}
}
