<?php
/**
 * Ability: rank-math/audit-site-seo
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\SEO_Analysis;

use RankMath\Abilities\Ability_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/audit-site-seo ability.
 */
class Audit_Site_SEO implements Ability_Interface {

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
	 * Audit runner instance.
	 *
	 * @var Audit_Runner
	 */
	private $runner;

	/**
	 * Constructor.
	 *
	 * @param string            $category    Ability category slug.
	 * @param array             $shared_meta Shared meta args.
	 * @param Audit_Runner|null $runner      Audit runner instance.
	 */
	public function __construct( string $category, array $shared_meta, ?Audit_Runner $runner = null ) {
		$this->category    = $category;
		$this->shared_meta = $shared_meta;
		$this->runner      = $runner ?? new Audit_Runner();
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/audit-site-seo',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Audit site SEO', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					"Runs Rank Math's site-wide SEO audit and returns a structured score plus per-test findings with fix hints. Combines local checks with the remote rankmath.com API tests.",
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'refresh' => [
							'type'        => 'boolean',
							'description' => esc_html__( 'Re-run the audit. Defaults to true (always fresh when called by an AI host).', 'seo-by-rank-math' ),
							'default'     => true,
						],
						'url'     => [
							'type'        => 'string',
							'format'      => 'uri',
							'description' => esc_html__( "Optional URL to audit. Defaults to this site's home URL. When provided, only remote API tests run (local checks are site-wide).", 'seo-by-rank-math' ),
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
		return current_user_can( 'rank_math_site_analysis' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$refresh = isset( $input['refresh'] ) ? (bool) $input['refresh'] : true;
		$url     = isset( $input['url'] ) ? $input['url'] : null;
		$result  = $this->runner->run( $refresh, $url )->to_array();

		rank_math()->tracking->track_ability_executed(
			'SEO Audit Run',
			[
				'score'     => isset( $result['score'] ) ? $result['score'] : 0,
				'grade'     => isset( $result['grade'] ) ? $result['grade'] : '',
				'has_url'   => ! empty( $url ),
				'has_error' => ! empty( $result['error'] ),
			],
			'rank_math_site_analysis'
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
				'url'               => [ 'type' => 'string' ],
				'score'             => [
					'type'    => 'integer',
					'minimum' => 0,
					'maximum' => 100,
				],
				'grade'             => [
					'type' => 'string',
					'enum' => [ 'good', 'average', 'bad' ],
				],
				'statuses'          => [
					'type'       => 'object',
					'properties' => [
						'ok'      => [ 'type' => 'integer' ],
						'fail'    => [ 'type' => 'integer' ],
						'warning' => [ 'type' => 'integer' ],
						'info'    => [ 'type' => 'integer' ],
					],
				],
				'total_tests'       => [ 'type' => 'integer' ],
				'last_run_at'       => [ 'type' => 'integer' ],
				'remote_api_status' => [
					'type' => 'string',
					'enum' => [ 'ok', 'unavailable', 'skipped' ],
				],
				'findings'          => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'test_id'     => [ 'type' => 'string' ],
							'category'    => [
								'type' => 'string',
								'enum' => [ 'priority', 'basic', 'advanced', 'performance', 'security' ],
							],
							'status'      => [
								'type' => 'string',
								'enum' => [ 'ok', 'fail', 'warning', 'info' ],
							],
							'score'       => [ 'type' => 'integer' ],
							'title'       => [ 'type' => 'string' ],
							'description' => [ 'type' => 'string' ],
							'fix_text'    => [ 'type' => 'string' ],
							'fix_html'    => [ 'type' => 'string' ],
							'fix_hint'    => [ 'type' => [ 'object', 'null' ] ],
							'kb_link'     => [ 'type' => 'string' ],
							'data'        => [ 'type' => [ 'object', 'array', 'null' ] ],
						],
					],
				],
				'error'             => [
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
