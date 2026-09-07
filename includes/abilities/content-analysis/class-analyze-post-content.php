<?php
/**
 * Ability: rank-math/analyze-post-content
 *
 * @since      1.0.274
 * @package    RankMath
 * @subpackage RankMath\Abilities\Content_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Content_Analysis;

use RankMath\Abilities\Ability_Interface;
use RankMath\Rest\Rest_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/analyze-post-content ability.
 */
class Analyze_Post_Content implements Ability_Interface {

	/**
	 * KB article covering all 24 on-page SEO tests, used as the scoring rubric reference.
	 */
	const KB_LINK = 'https://rankmath.com/kb/score-100-in-tests/';

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
	 * Content analysis data gatherer.
	 *
	 * @var Content_Analysis_Data
	 */
	private $data;

	/**
	 * Constructor.
	 *
	 * @param string                     $category    Ability category slug.
	 * @param array                      $shared_meta Shared meta args.
	 * @param Content_Analysis_Data|null $data        Content analysis data gatherer (injectable for tests).
	 */
	public function __construct( string $category, array $shared_meta, ?Content_Analysis_Data $data = null ) {
		$this->category    = $category;
		$this->shared_meta = $shared_meta;
		$this->data        = $data ?? new Content_Analysis_Data();
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/analyze-post-content',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Analyze post content', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Gathers the raw content, metadata, and list of on-page SEO tests for a saved post so an AI agent can run Rank Math\'s full on-page analysis itself, since the scoring engine only runs client-side. Use tests_to_run and content together with kb_link (the scoring rubric for all 24 tests) to evaluate each test and produce a per-test score, status, and fix message.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'required'             => [ 'post_id' ],
					'properties'           => [
						'post_id' => [
							'type'        => 'integer',
							'description' => esc_html__( 'ID of the post to analyze.', 'seo-by-rank-math' ),
						],
						'keyword' => [
							'type'        => 'string',
							'description' => esc_html__( 'Optional focus keyword to analyze against, overriding the post\'s saved focus keyword.', 'seo-by-rank-math' ),
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
		$post_id = absint( $input['post_id'] );
		$post    = get_post( $post_id );

		if ( ! $post || ! in_array( get_post_status( $post_id ), [ 'publish', 'draft', 'private', 'pending' ], true ) ) {
			return [
				'error' => [
					'code'    => 'invalid_post',
					'message' => esc_html__( 'No post found with the given ID, or the post status is not supported.', 'seo-by-rank-math' ),
				],
			];
		}

		if ( ! Rest_Helper::can_edit_post( $post ) ) {
			return [
				'error' => [
					'code'    => 'rest_cannot_edit',
					'message' => esc_html__( 'Sorry, you are not allowed to edit this post.', 'seo-by-rank-math' ),
				],
			];
		}

		if ( post_password_required( $post ) ) {
			return [
				'error' => [
					'code'    => 'post_password_required',
					'message' => esc_html__( 'This post is password protected.', 'seo-by-rank-math' ),
				],
			];
		}

		$keyword = isset( $input['keyword'] ) ? sanitize_text_field( $input['keyword'] ) : '';
		$payload = $this->data->get( $post, $keyword );

		$result = [
			'post_id'      => $post_id,
			'keyword'      => $payload['content']['focus_keyword'],
			'tests_to_run' => $payload['tests_to_run'],
			'content'      => $payload['content'],
			'kb_link'      => self::KB_LINK,
			'instructions' => esc_html__(
				'Evaluate each test ID in tests_to_run against the fields in content, using kb_link as the scoring rubric for how each test is scored. Return a per-test breakdown with score, max_score, status (pass/fail), and a message with a specific fix. For the contentHasTOC test, when suggesting a fix or drafting content, tell the user to insert the Rank Math Table of Contents block (block name rank-math/toc-block, available in the block inserter as "Table of Contents") right after the intro, instead of a manual list of links — it auto-generates from the post headings.',
				'seo-by-rank-math'
			),
			'error'        => null,
		];

		rank_math()->tracking->track_ability_executed(
			'Post Content Analyzed',
			[
				'post_id'     => $post_id,
				'tests_count' => count( $payload['tests_to_run'] ),
				'has_keyword' => ! empty( $result['keyword'] ),
			],
			'rank_math_onpage_analysis'
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
				'post_id'      => [ 'type' => 'integer' ],
				'keyword'      => [
					'type'        => 'string',
					'description' => 'Focus keyword used for analysis (input override, or the post\'s saved keyword).',
				],
				'tests_to_run' => [
					'type'        => 'array',
					'items'       => [ 'type' => 'string' ],
					'description' => 'IDs of the on-page SEO tests registered for this post, to be evaluated by the caller.',
				],
				'content'      => [
					'type'        => 'object',
					'description' => 'Raw content and metadata needed to evaluate each test in tests_to_run.',
					'properties'  => [
						'title'         => [ 'type' => 'string' ],
						'description'   => [ 'type' => 'string' ],
						'permalink'     => [ 'type' => 'string' ],
						'focus_keyword' => [ 'type' => 'string' ],
						'body'          => [ 'type' => 'string' ],
						'word_count'    => [ 'type' => 'integer' ],
						'excerpt_10pct' => [
							'type'        => 'string',
							'description' => 'First 10% of the content body, used by the keywordIn10Percent test.',
						],
						'headings'      => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'level' => [ 'type' => 'string' ],
									'text'  => [ 'type' => 'string' ],
								],
							],
						],
						'images'        => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'alt' => [ 'type' => 'string' ],
								],
							],
						],
						'links'         => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'url'      => [ 'type' => 'string' ],
									'external' => [ 'type' => 'boolean' ],
								],
							],
						],
					],
				],
				'kb_link'      => [
					'type'        => 'string',
					'description' => 'KB article covering all 24 on-page SEO tests and how each is scored.',
				],
				'instructions' => [
					'type'        => 'string',
					'description' => 'Guidance for the calling agent on how to use tests_to_run, content, and kb_link to produce a per-test analysis.',
				],
				'error'        => [
					'type'       => [ 'object', 'null' ],
					'properties' => [
						'code'    => [ 'type' => 'string' ],
						'message' => [ 'type' => 'string' ],
					],
				],
			],
		];
	}
}
