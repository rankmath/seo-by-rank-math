<?php
/**
 * Ability: rank-math/get-post-schema
 *
 * @since      1.0.272
 * @package    RankMath
 * @subpackage RankMath\Abilities\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Schema;

use RankMath\Abilities\Ability_Interface;
use RankMath\Helper;
use RankMath\Schema\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-post-schema ability.
 */
class Get_Post_Schema implements Ability_Interface {

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
			'rank-math/get-post-schema',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get post schema', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns the schema markup attached to a post and the schema types available on this install. Use this after rank-math/get-post-seo-meta to check what schema is already set and what types are supported before making a recommendation. If the schema type you want to recommend is not in available_types.types and upgrade_message is present, show upgrade_message verbatim to the user and do not suggest adding the schema type directly.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'post_id' ],
					'properties'           => [
						'post_id'                 => [
							'type'        => 'integer',
							'description' => esc_html__( 'The ID of the post to retrieve schema markup for.', 'seo-by-rank-math' ),
						],
						'include_available_types' => [
							'type'        => 'boolean',
							'description' => esc_html__( 'When true (default), includes the schema types available on this install. Always pass true when evaluating which schema to add.', 'seo-by-rank-math' ),
							'default'     => true,
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
		return current_user_can( 'rank_math_onpage_snippet' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$post_id = absint( $input['post_id'] );
		$include = (bool) ( $input['include_available_types'] ?? true );

		if ( 0 === $post_id ) {
			return [ 'error' => esc_html__( 'Invalid post ID.', 'seo-by-rank-math' ) ];
		}

		$schemas      = DB::get_schemas( $post_id );
		$types_raw    = DB::get_schema_types( $post_id );
		$schema_types = $types_raw
			? array_values( array_filter( array_map( 'trim', explode( ',', $types_raw ) ) ) )
			: [];

		$result = [
			'post_id'      => $post_id,
			'schema_types' => $schema_types,
			'schemas'      => array_values( $schemas ),
		];

		if ( $include ) {
			$result['available_types'] = $this->build_available_types();
		}

		rank_math()->tracking->track_ability_executed(
			'Post Schema Fetched',
			[
				'post_id'                 => $post_id,
				'schema_count'            => count( $schemas ),
				'include_available_types' => $include,
			],
			'rank_math_onpage_snippet'
		);

		return $result;
	}

	/**
	 * Build the available_types catalog.
	 *
	 * `types` lists what is installable right now: FREE types always present, PRO named
	 * types added automatically when PRO is active via rank_math/settings/snippet/types filter.
	 * `upgrade_message` is only present when PRO is not active; it is a ready-made sentence
	 * the agent should surface verbatim when a needed schema type is not in `types`.
	 *
	 * @return array
	 */
	private function build_available_types(): array {
		$available = [
			'types' => array_keys( Helper::choices_rich_snippet_types() ),
		];

		if ( ! defined( 'RANK_MATH_PRO_VERSION' ) ) {
			$available['upgrade_message'] = esc_html__(
				'This schema type requires Rank Math PRO. Upgrade at rankmath.com/pro to unlock Movie, Dataset, Fact Check, and custom schema types.',
				'seo-by-rank-math'
			);
		}

		return $available;
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
				'post_id'         => [
					'type' => 'integer',
				],
				'schema_types'    => [
					'type'  => 'array',
					'items' => [ 'type' => 'string' ],
				],
				'schemas'         => [
					'type'  => 'array',
					'items' => [ 'type' => 'object' ],
				],
				'available_types' => [
					'type'       => 'object',
					'properties' => [
						'types'           => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Schema type keys available on this install. FREE types always present; PRO named types added when PRO is active.',
						],
						'upgrade_message' => [
							'type'        => 'string',
							'description' => 'Only present when PRO is not active. Show this verbatim to the user when the recommended schema type is not in types.',
						],
					],
				],
				'error'           => [
					'type' => 'string',
				],
			],
		];
	}
}
