<?php
/**
 * Ability: rank-math/fix-site-seo
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
 * Registers and executes the rank-math/fix-site-seo ability.
 */
class Fix_Site_SEO implements Ability_Interface {

	/**
	 * Fixable test IDs — kept in sync with Fix_Runner handler methods.
	 */
	const FIXABLE_TEST_IDS = [
		'blog_public',
		'permalink_structure',
		'site_description',
		'sitemaps',
		'schema',
		'noindex',
		'opengraph',
		'robots_txt',
		'focus_keywords',
		'post_titles',
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
	 * Fix runner instance.
	 *
	 * @var Fix_Runner
	 */
	private $runner;

	/**
	 * Constructor.
	 *
	 * @param string          $category    Ability category slug.
	 * @param array           $shared_meta Shared meta args.
	 * @param Fix_Runner|null $runner      Fix runner instance.
	 */
	public function __construct( string $category, array $shared_meta, ?Fix_Runner $runner = null ) {
		$this->category    = $category;
		$this->shared_meta = $shared_meta;
		$this->runner      = $runner ?? new Fix_Runner();
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/fix-site-seo',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Fix site SEO', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Applies an automatic fix for a single failing Rank Math SEO test. Pass the test_id from an audit-site-seo finding. Some fixes require additional input (e.g. value for site_description). Bulk fixes accept an optional post_limit.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'test_id' ],
					'properties'           => [
						'test_id'    => [
							'type'        => 'string',
							'enum'        => self::FIXABLE_TEST_IDS,
							'description' => esc_html__( 'The SEO test identifier to fix (from audit-site-seo findings).', 'seo-by-rank-math' ),
						],
						'value'      => [
							'type'        => 'string',
							'description' => esc_html__( 'Required for site_description: the new tagline to set.', 'seo-by-rank-math' ),
						],
						'post_limit' => [
							'type'        => 'integer',
							'minimum'     => 1,
							'maximum'     => 500,
							'description' => esc_html__( 'For bulk post fixes (focus_keywords, post_titles): max posts to update in one call. Default 100.', 'seo-by-rank-math' ),
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
							'readonly'    => false,
							'destructive' => false,
							'idempotent'  => false,
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
		$test_id = (string) $input['test_id'];
		$result  = $this->runner->fix( $test_id, (array) $input );

		rank_math()->tracking->track_ability_executed(
			'SEO Audit Test Fixed',
			[
				'test_id' => $test_id,
				'fixed'   => ! empty( $result['fixed'] ),
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
				'fixed'   => [ 'type' => 'boolean' ],
				'summary' => [ 'type' => 'string' ],
				'details' => [ 'type' => 'object' ],
			],
		];
	}
}
