<?php
/**
 * Ability: rank-math/get-link-report
 *
 * @since      1.0.272
 * @package    RankMath
 * @subpackage RankMath\Abilities\Link_Genius
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Link_Genius;

use RankMath\Abilities\Ability_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-link-report ability.
 */
class Get_Link_Report implements Ability_Interface {

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
	 * @var Link_Report_Runner
	 */
	private $runner;

	/**
	 * Constructor.
	 *
	 * @param string                  $category    Ability category slug.
	 * @param array                   $shared_meta Shared meta args.
	 * @param Link_Report_Runner|null $runner      Runner instance.
	 */
	public function __construct( string $category, array $shared_meta, ?Link_Report_Runner $runner = null ) {
		$this->category    = $category;
		$this->shared_meta = $shared_meta;
		$this->runner      = $runner ?? new Link_Report_Runner();
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/get-link-report',
			[
				'category'            => $this->category,
				'label'               => \esc_html__( 'Get link report', 'seo-by-rank-math' ),
				'description'         => \esc_html__(
					'Returns a site-wide link health report: total internal and external links, post counts with no internal or external links, and — on Rank Math PRO — broken links, redirects, nofollow counts, and HTTP status distribution from the Link Genius audit.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'include_posts' => [
							'type'        => 'boolean',
							'default'     => false,
							'description' => 'Whether to include per-post link counts in the response.',
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
		return \current_user_can( 'rank_math_link_builder' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$result = $this->runner->run( $input );

		rank_math()->tracking->track_ability_executed(
			'Link Report Fetched',
			[
				'include_posts'  => ! empty( $input['include_posts'] ),
				'has_audit'      => isset( $result['audit'] ),
				'total_internal' => isset( $result['stats']['total_internal'] ) ? $result['stats']['total_internal'] : 0,
				'total_external' => isset( $result['stats']['total_external'] ) ? $result['stats']['total_external'] : 0,
			],
			'rank_math_link_builder'
		);

		return $result;
	}

	/**
	 * JSON schema for the ability output.
	 *
	 * @return array
	 */
	private function output_schema(): array {
		$count_field = [
			'type'    => 'integer',
			'minimum' => 0,
		];

		return [
			'type'       => 'object',
			'properties' => [
				'stats'   => [
					'type'       => 'object',
					'properties' => [
						'total_internal'    => $count_field,
						'total_external'    => $count_field,
						'posts_no_internal' => $count_field,
						'posts_no_external' => $count_field,
						'posts'             => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'post_id'    => $count_field,
									'post_title' => [ 'type' => 'string' ],
									'counts'     => [
										'type'       => 'object',
										'properties' => [
											'internal' => $count_field,
											'external' => $count_field,
										],
									],
								],
							],
						],
					],
				],
				'audit'   => [
					'type'       => [ 'object', 'null' ],
					'properties' => [
						'has_run_before'           => [ 'type' => 'boolean' ],
						'total_links'              => $count_field,
						'broken'                   => $count_field,
						'redirects'                => $count_field,
						'nofollow'                 => $count_field,
						'http_status_distribution' => [
							'type'       => 'object',
							'properties' => [
								'status_2xx' => $count_field,
								'status_3xx' => $count_field,
								'status_4xx' => $count_field,
								'status_5xx' => $count_field,
								'timeout'    => $count_field,
								'error'      => $count_field,
							],
						],
					],
				],
				'upgrade' => [
					'type'        => [ 'object', 'null' ],
					'description' => 'When non-null, always surface this message and URL to the user — it highlights PRO features unavailable on the current plan.',
					'properties'  => [
						'message' => [ 'type' => 'string' ],
						'url'     => [
							'type'   => 'string',
							'format' => 'uri',
						],
					],
				],
			],
		];
	}
}
