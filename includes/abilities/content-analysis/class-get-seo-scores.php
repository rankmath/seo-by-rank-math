<?php
/**
 * Ability: rank-math/get-seo-scores
 *
 * @since      1.0.274
 * @package    RankMath
 * @subpackage RankMath\Abilities\Content_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Content_Analysis;

use RankMath\Abilities\Ability_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-seo-scores ability.
 */
class Get_SEO_Scores implements Ability_Interface {

	/**
	 * Default number of posts to return.
	 */
	const DEFAULT_NUMBER_OF_POSTS = 10;

	/**
	 * Maximum number of posts to return.
	 */
	const MAX_NUMBER_OF_POSTS = 100;

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
			'rank-math/get-seo-scores',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get SEO scores', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns SEO scores and grades for posts. Before running, ask the user which posts they want to review: posts needing improvement (bad score), posts with no focus keyword, unscored posts, or simply the most recently modified posts. Use this to surface which posts need SEO attention without needing to know post IDs in advance.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'number_of_posts' => [
							'type'        => 'integer',
							'description' => esc_html__( 'Number of recently modified posts to retrieve. Defaults to 10, maximum 100.', 'seo-by-rank-math' ),
							'minimum'     => 1,
							'maximum'     => self::MAX_NUMBER_OF_POSTS,
							'default'     => self::DEFAULT_NUMBER_OF_POSTS,
						],
						'post_type'       => [
							'type'        => 'string',
							'description' => esc_html__( 'Post type to filter by (e.g. "post", "page"). Defaults to all public post types.', 'seo-by-rank-math' ),
						],
						'seo_filter'      => [
							'type'        => 'string',
							'description' => esc_html__( 'Ask the user which posts they want to review before running this ability. Available options: "bad" (score ≤ 50, needs improvement), "good" (score 51–80, ok), "great" (score > 80), "empty-fk" (no focus keyword set), "unscored" (never analyzed). If the user does not specify, omit this parameter to return the most recently modified posts.', 'seo-by-rank-math' ),
							'enum'        => [ 'bad', 'good', 'great', 'empty-fk', 'unscored' ],
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
		return current_user_can( 'rank_math_onpage_analysis' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$limit     = min( self::MAX_NUMBER_OF_POSTS, max( 1, absint( $input['number_of_posts'] ?? self::DEFAULT_NUMBER_OF_POSTS ) ) );
		$post_type = isset( $input['post_type'] ) ? sanitize_key( $input['post_type'] ) : '';

		$seo_filter = isset( $input['seo_filter'] ) ? sanitize_key( $input['seo_filter'] ) : '';
		$meta_query = $this->build_meta_query( $seo_filter );

		$query_args = [
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'fields'         => 'ids',
		];

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
			// Order by score ascending for score-based filters so worst offenders surface first.
			if ( in_array( $seo_filter, [ 'bad', 'good', 'great' ], true ) ) {
				$query_args['orderby']  = 'meta_value_num';
				$query_args['meta_key'] = 'rank_math_seo_score';
				$query_args['order']    = 'ASC';
			}
		}

		if ( ! empty( $post_type ) ) {
			$query_args['post_type'] = $post_type;
		} else {
			$post_types = get_post_types( [ 'public' => true ] );
			unset( $post_types['attachment'] );
			$query_args['post_type'] = array_values( $post_types );
		}

		$post_ids = get_posts( $query_args );
		$results  = [];

		foreach ( $post_ids as $post_id ) {
			$results[] = $this->build_score_result( $post_id );
		}

		rank_math()->tracking->track_ability_executed(
			'SEO Scores Retrieved',
			[
				'count'      => count( $results ),
				'post_type'  => ! empty( $post_type ) ? $post_type : 'all',
				'seo_filter' => ! empty( $seo_filter ) ? $seo_filter : 'none',
			],
			'rank_math_onpage_analysis'
		);

		return $results;
	}

	/**
	 * JSON schema for the ability output.
	 *
	 * @return array
	 */
	private function output_schema(): array {
		return [
			'type'  => 'array',
			'items' => [
				'type'       => 'object',
				'properties' => [
					'post_id'      => [ 'type' => 'integer' ],
					'title'        => [ 'type' => 'string' ],
					'keyword'      => [
						'type'        => [ 'string', 'null' ],
						'description' => 'Primary focus keyword, or null if not set.',
					],
					'score'        => [
						'type'    => [ 'integer', 'null' ],
						'minimum' => 0,
						'maximum' => 100,
					],
					'grade'        => [
						'type' => 'string',
						'enum' => [ 'good', 'ok', 'bad', 'na' ],
					],
					'label'        => [
						'type'        => 'string',
						'description' => 'Human-readable grade label.',
					],
					'last_updated' => [ 'type' => 'integer' ],
				],
			],
		];
	}

	/**
	 * Build the meta query for a given SEO filter.
	 *
	 * Mirrors the filter conditions defined in Post_Filters::set_seo_filters().
	 *
	 * @param string $filter SEO filter slug.
	 * @return array
	 */
	private function build_meta_query( string $filter ): array {
		$hash = [
			'empty-fk' => [
				'key'     => 'rank_math_focus_keyword',
				'compare' => 'NOT EXISTS',
			],
			'unscored' => [
				'key'     => 'rank_math_seo_score',
				'compare' => 'NOT EXISTS',
			],
			'bad'      => [
				'key'     => 'rank_math_seo_score',
				'value'   => 50,
				'compare' => '<=',
				'type'    => 'numeric',
			],
			'good'     => [
				'key'     => 'rank_math_seo_score',
				'value'   => [ 51, 80 ],
				'compare' => 'BETWEEN',
				'type'    => 'numeric',
			],
			'great'    => [
				'key'     => 'rank_math_seo_score',
				'value'   => 80,
				'compare' => '>',
				'type'    => 'numeric',
			],
		];

		if ( ! isset( $hash[ $filter ] ) ) {
			return [];
		}

		$meta_query = [];

		// Score-based filters exclude noindexed posts and require a focus keyword, matching Post_Filters behaviour.
		if ( in_array( $filter, [ 'bad', 'good', 'great' ], true ) ) {
			$meta_query['relation'] = 'AND';
			$meta_query[]           = [
				'relation' => 'OR',
				[
					'key'     => 'rank_math_robots',
					'value'   => 'noindex',
					'compare' => 'NOT LIKE',
				],
				[
					'key'     => 'rank_math_robots',
					'compare' => 'NOT EXISTS',
				],
			];
			$meta_query[]           = [
				'key'     => 'rank_math_focus_keyword',
				'compare' => 'EXISTS',
			];
			$meta_query[]           = [
				'key'     => 'rank_math_focus_keyword',
				'value'   => '',
				'compare' => '!=',
			];
		}

		$meta_query[] = $hash[ $filter ];

		return $meta_query;
	}

	/**
	 * Build the score result array for a single post.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function build_score_result( int $post_id ): array {
		$raw_score = get_post_meta( $post_id, 'rank_math_seo_score', true );
		$keyword   = $this->get_primary_keyword( $post_id );

		if ( '' === $raw_score || false === $raw_score ) {
			return [
				'post_id'      => $post_id,
				'title'        => get_the_title( $post_id ),
				'keyword'      => ! empty( $keyword ) ? $keyword : null,
				'score'        => null,
				'grade'        => 'na',
				'label'        => esc_html__( 'Not available', 'seo-by-rank-math' ),
				'last_updated' => (int) get_post_modified_time( 'U', true, $post_id ),
			];
		}

		$score = absint( $raw_score );
		$grade = $score >= 80 ? 'good' : ( $score >= 50 ? 'ok' : 'bad' );
		$label = $this->grade_to_label( $grade );

		return [
			'post_id'      => $post_id,
			'title'        => get_the_title( $post_id ),
			'keyword'      => ! empty( $keyword ) ? $keyword : null,
			'score'        => $score,
			'grade'        => $grade,
			'label'        => $label,
			'last_updated' => (int) get_post_modified_time( 'U', true, $post_id ),
		];
	}

	/**
	 * Convert a grade slug to a human-readable label.
	 *
	 * @param string $grade Grade slug.
	 * @return string
	 */
	private function grade_to_label( string $grade ): string {
		$labels = [
			'good' => esc_html__( 'Good', 'seo-by-rank-math' ),
			'ok'   => esc_html__( 'OK', 'seo-by-rank-math' ),
			'bad'  => esc_html__( 'Needs improvement', 'seo-by-rank-math' ),
		];

		return $labels[ $grade ] ?? esc_html__( 'Not available', 'seo-by-rank-math' );
	}

	/**
	 * Get the primary focus keyword for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_primary_keyword( int $post_id ): string {
		$keywords = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
		if ( empty( $keywords ) ) {
			return '';
		}

		$parts = explode( ',', $keywords );
		return trim( $parts[0] );
	}
}
