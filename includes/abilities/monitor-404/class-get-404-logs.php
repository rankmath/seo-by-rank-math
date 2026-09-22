<?php
/**
 * Ability: rank-math/get-404-logs
 *
 * @since      1.0.279
 * @package    RankMath
 * @subpackage RankMath\Abilities\Monitor_404
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Monitor_404;

use RankMath\Abilities\Ability_Interface;
use RankMath\Monitor\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-404-logs ability.
 */
class Get_404_Logs implements Ability_Interface {

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
			'rank-math/get-404-logs',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get 404 logs', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns the 404 error monitor log entries, with hit counts and referer/user-agent data.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'per_page' => [
							'type'        => 'integer',
							'description' => esc_html__( 'The number of log entries to return per page.', 'seo-by-rank-math' ),
							'default'     => 100,
							'minimum'     => 1,
							'maximum'     => 1000,
						],
						'page'     => [
							'type'        => 'integer',
							'description' => esc_html__( 'The page number to return.', 'seo-by-rank-math' ),
							'default'     => 1,
						],
						'orderby'  => [
							'type'        => 'string',
							'enum'        => [ 'hits', 'accessed' ],
							'default'     => 'hits',
							'description' => esc_html__( 'Field to order results by.', 'seo-by-rank-math' ),
						],
						'order'    => [
							'type'        => 'string',
							'enum'        => [ 'asc', 'desc' ],
							'default'     => 'desc',
							'description' => esc_html__( 'Sort direction.', 'seo-by-rank-math' ),
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
		return current_user_can( 'rank_math_404_monitor' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$per_page = isset( $input['per_page'] ) ? absint( $input['per_page'] ) : 100;
		$page     = max( 1, isset( $input['page'] ) ? (int) $input['page'] : 1 );
		$orderby  = isset( $input['orderby'] ) ? sanitize_text_field( $input['orderby'] ) : 'hits';
		$order    = isset( $input['order'] ) && in_array( $input['order'], [ 'asc', 'desc' ], true ) ? $input['order'] : 'desc';

		$order_map = [
			'hits'     => 'times_accessed',
			'accessed' => 'accessed',
		];

		$logs = DB::get_logs(
			[
				'orderby' => $order_map[ $orderby ] ?? 'times_accessed',
				'order'   => strtoupper( $order ),
				'limit'   => $per_page,
				'paged'   => $page,
			]
		);

		$items = array_map(
			function ( $log ) {
				return [
					'id'         => (int) $log['id'],
					'uri'        => $log['uri'],
					'hits'       => (int) $log['times_accessed'],
					'referer'    => $log['referer'],
					'user_agent' => $log['user_agent'],
					'accessed'   => $log['accessed'],
				];
			},
			$logs['logs']
		);

		$stats = DB::get_stats();

		$result = [
			'stats' => [
				'total_urls' => (int) ( $stats->total ?? 0 ),
				'total_hits' => (int) ( $stats->hits ?? 0 ),
			],
			'total' => (int) $logs['count'],
			'items' => $items,
		];

		rank_math()->tracking->track_ability_executed(
			'404 Logs Fetched',
			[ 'count' => (int) $logs['count'] ],
			'rank_math_404_monitor'
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
				'stats' => [
					'type'        => 'object',
					'description' => esc_html__( 'Log counts across the entire table, independent of pagination.', 'seo-by-rank-math' ),
					'properties'  => [
						'total_urls' => [ 'type' => 'integer' ],
						'total_hits' => [ 'type' => 'integer' ],
					],
				],
				'total' => [
					'type'        => 'integer',
					'description' => esc_html__( 'Number of log entries included in this response (the size of items).', 'seo-by-rank-math' ),
				],
				'items' => [
					'type'        => 'array',
					'description' => esc_html__( 'The 404 log entries for the requested page.', 'seo-by-rank-math' ),
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'id'         => [ 'type' => 'integer' ],
							'uri'        => [ 'type' => 'string' ],
							'hits'       => [ 'type' => 'integer' ],
							'referer'    => [ 'type' => 'string' ],
							'user_agent' => [ 'type' => 'string' ],
							'accessed'   => [ 'type' => 'string' ],
						],
					],
				],
			],
		];
	}
}
