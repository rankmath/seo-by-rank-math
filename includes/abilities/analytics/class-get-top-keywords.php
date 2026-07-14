<?php
/**
 * Ability: rank-math/get-top-keywords
 *
 * @since      1.0.274
 * @package    RankMath
 * @subpackage RankMath\Abilities\Analytics
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Analytics;

use RankMath\Abilities\Ability_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-top-keywords ability.
 */
class Get_Top_Keywords implements Ability_Interface {

	/**
	 * Maps date_range enum values to Stats::set_date_range() arguments.
	 */
	const DATE_RANGE_MAP = [
		'last_7_days'    => '-7 days',
		'last_30_days'   => '-30 days',
		'last_3_months'  => '-3 months',
		'last_6_months'  => '-6 months',
		'last_12_months' => '-1 year',
	];

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
	 * Runner instance.
	 *
	 * @var Top_Keywords_Runner
	 */
	private $runner;

	/**
	 * Constructor.
	 *
	 * @param string                   $category    Ability category slug.
	 * @param array                    $shared_meta Shared meta args.
	 * @param Top_Keywords_Runner|null $runner      Runner instance.
	 */
	public function __construct( string $category, array $shared_meta, ?Top_Keywords_Runner $runner = null ) {
		$this->category    = $category;
		$this->shared_meta = $shared_meta;
		$this->runner      = $runner ?? new Top_Keywords_Runner();
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/get-top-keywords',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get top keywords', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns the top-performing keywords by clicks from Google Search Console, with impressions, CTR, and average position for the selected date range.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'date_range' => [
							'type'        => 'string',
							'enum'        => array_keys( self::DATE_RANGE_MAP ),
							'default'     => 'last_30_days',
							'description' => esc_html__( 'Date range for which to fetch keyword data.', 'seo-by-rank-math' ),
						],
						'limit'      => [
							'type'        => 'integer',
							'default'     => 25,
							'minimum'     => 1,
							'maximum'     => 100,
							'description' => esc_html__( 'Maximum number of keywords to return.', 'seo-by-rank-math' ),
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
		return current_user_can( 'rank_math_analytics' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$date_range = isset( $input['date_range'] ) && in_array( $input['date_range'], array_keys( self::DATE_RANGE_MAP ), true )
			? (string) $input['date_range']
			: 'last_30_days';

		$limit = isset( $input['limit'] ) ? max( 1, min( 100, absint( $input['limit'] ) ) ) : 25;

		$result = $this->runner->run( $date_range, $limit );

		rank_math()->tracking->track_ability_executed(
			'Top Keywords Fetched',
			[
				'limit'      => $limit,
				'date_range' => $date_range,
			],
			'rank_math_analytics'
		);

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
				'keywords'   => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'keyword'       => [
								'type'        => 'string',
								'description' => esc_html__( 'The search query keyword.', 'seo-by-rank-math' ),
							],
							'clicks'        => [
								'type'        => 'integer',
								'description' => esc_html__( 'Total clicks from Google Search Console.', 'seo-by-rank-math' ),
							],
							'impressions'   => [
								'type'        => 'integer',
								'description' => esc_html__( 'Total impressions from Google Search Console.', 'seo-by-rank-math' ),
							],
							'ctr'           => [
								'type'        => 'number',
								'description' => esc_html__( 'Click-through rate as a percentage (0–100).', 'seo-by-rank-math' ),
							],
							'position'      => [
								'type'        => 'number',
								'description' => esc_html__( 'Average position in Google Search results.', 'seo-by-rank-math' ),
							],
							'trend'         => [
								'type'        => 'string',
								'enum'        => [ 'winning', 'losing', 'stable' ],
								'description' => esc_html__( 'Position trend vs. the previous period. Provided by Rank Math PRO for tracked keywords.', 'seo-by-rank-math' ),
							],
							'is_tracked'    => [
								'type'        => 'boolean',
								'description' => esc_html__( 'Whether this keyword is tracked in Rank Math PRO Rank Tracker.', 'seo-by-rank-math' ),
							],
							'ranking_posts' => [
								'type'        => 'array',
								'description' => esc_html__( 'Pages on your site that rank for this keyword. Provided by Rank Math PRO.', 'seo-by-rank-math' ),
								'items'       => [
									'type'       => 'object',
									'properties' => [
										'post_id' => [
											'type'        => 'integer',
											'description' => esc_html__( 'WordPress post ID.', 'seo-by-rank-math' ),
										],
										'title'   => [
											'type'        => 'string',
											'description' => esc_html__( 'Post title.', 'seo-by-rank-math' ),
										],
										'slug'    => [
											'type'        => 'string',
											'description' => esc_html__( 'Relative URL path of the page.', 'seo-by-rank-math' ),
										],
									],
								],
							],
						],
					],
				],
				'date_range' => [
					'type' => 'string',
					'enum' => array_keys( self::DATE_RANGE_MAP ),
				],
				'connected'  => [
					'type'        => 'boolean',
					'description' => esc_html__( 'Whether Google Search Console is connected.', 'seo-by-rank-math' ),
				],
			],
		];
	}
}
