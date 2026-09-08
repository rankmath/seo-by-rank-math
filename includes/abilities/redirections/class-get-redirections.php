<?php
/**
 * Ability: rank-math/get-redirections
 *
 * @since      1.0.278
 * @package    RankMath
 * @subpackage RankMath\Abilities\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Redirections;

use RankMath\Abilities\Ability_Interface;
use RankMath\Helper;
use RankMath\Redirections\DB;
use RankMath\Admin\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-redirections ability.
 */
class Get_Redirections implements Ability_Interface {

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
			'rank-math/get-redirections',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get redirections', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns the redirections for a site.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'status'   => [
							'type'        => 'string',
							'enum'        => [ 'active', 'inactive', 'all' ],
							'default'     => 'all',
							'description' => esc_html__( 'Filter redirections by status.', 'seo-by-rank-math' ),
						],
						'per_page' => [
							'type'        => 'integer',
							'description' => esc_html__( 'The number of redirections to return per page.', 'seo-by-rank-math' ),
							'default'     => 100,
							'minimum'     => 1,
							'maximum'     => 1000,
						],
						'page'     => [
							'type'        => 'integer',
							'description' => esc_html__( 'The page number to return.', 'seo-by-rank-math' ),
							'default'     => 1,
						],
						'search'   => [
							'type'        => 'string',
							'description' => esc_html__( 'Search redirections by source or target URL.', 'seo-by-rank-math' ),
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
		return Helper::has_cap( 'redirections' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$search   = isset( $input['search'] ) ? sanitize_text_field( $input['search'] ) : '';
		$status   = isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'all';
		$per_page = isset( $input['per_page'] ) ? absint( $input['per_page'] ) : 100;
		$page     = isset( $input['page'] ) ? absint( $input['page'] ) : 1;

		$redirections = DB::get_redirections(
			[
				'limit'  => $per_page,
				'paged'  => $page,
				'search' => $search,
				'status' => $status,
			]
		);

		$items = array_map(
			function ( $item ) {
				$item['sources'] = maybe_unserialize( $item['sources'] );
				return $item;
			},
			$redirections['redirections']
		);

		$counts = DB::get_counts();

		$result = [
			'stats'    => [
				'total'    => absint( $counts['all'] ),
				'active'   => absint( $counts['active'] ),
				'inactive' => absint( $counts['inactive'] ),
				'by_type'  => DB::get_counts_by_type(),
			],
			'total'    => count( $items ),
			'page'     => $page,
			'per_page' => $per_page,
			'items'    => $items,
		];

		rank_math()->tracking->track_ability_executed(
			'Redirections Fetched',
			[
				'count'         => $redirections['count'],
				'status_filter' => $status,
			],
			'rank_math_redirections'
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
				'stats'    => [
					'type'        => 'object',
					'description' => esc_html__( 'Redirection counts across all statuses, independent of the status filter applied to items.', 'seo-by-rank-math' ),
					'properties'  => [
						'total'    => [ 'type' => 'integer' ],
						'active'   => [ 'type' => 'integer' ],
						'inactive' => [ 'type' => 'integer' ],
						'by_type'  => [
							'type'                 => 'object',
							'description'          => esc_html__( 'Non-trashed redirection counts grouped by HTTP redirect type (header code), e.g. { "301": 12, "302": 4 }.', 'seo-by-rank-math' ),
							'additionalProperties' => [ 'type' => 'integer' ],
						],
					],
				],
				'total'    => [
					'type'        => 'integer',
					'description' => esc_html__( 'Number of redirections included in this response (the size of items).', 'seo-by-rank-math' ),
				],
				'page'     => [
					'type' => 'integer',
				],
				'per_page' => [
					'type' => 'integer',
				],
				'items'    => [
					'type'        => 'array',
					'description' => esc_html__( 'The redirections for the requested page.', 'seo-by-rank-math' ),
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'id'            => [ 'type' => 'integer' ],
							'sources'       => [ 'type' => 'array' ],
							'url_to'        => [ 'type' => 'string' ],
							'header_code'   => [ 'type' => 'integer' ],
							'hits'          => [ 'type' => 'integer' ],
							'status'        => [
								'type' => 'string',
								'enum' => [ 'active', 'inactive', 'trashed' ],
							],
							'created'       => [ 'type' => 'string' ],
							'updated'       => [ 'type' => 'string' ],
							'last_accessed' => [ 'type' => 'string' ],
						],
					],
				],
			],
		];
	}
}
