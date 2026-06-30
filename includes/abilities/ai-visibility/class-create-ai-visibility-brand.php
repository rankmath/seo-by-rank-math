<?php
/**
 * Ability: rank-math/create-ai-visibility-brand
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\Abilities\AI_Visibility
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\AI_Visibility;

use WP_REST_Request;
use RankMath\Abilities\Ability_Interface;
use RankMath\AI_Visibility\Api\Brands_Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/create-ai-visibility-brand ability.
 */
class Create_AI_Visibility_Brand implements Ability_Interface {

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
	 * Brands REST controller instance.
	 *
	 * @var Brands_Controller
	 */
	private $controller;

	/**
	 * Constructor.
	 *
	 * @param string                 $category    Ability category slug.
	 * @param array                  $shared_meta Shared meta args.
	 * @param Brands_Controller|null $controller  Controller instance.
	 */
	public function __construct( string $category, array $shared_meta, ?Brands_Controller $controller = null ) {
		$this->category    = $category;
		$this->shared_meta = $shared_meta;
		$this->controller  = $controller ?? new Brands_Controller();
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/create-ai-visibility-brand',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Create AI Visibility brand', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Creates a new brand for AI Visibility monitoring, triggering an initial analysis automatically.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'name', 'url' ],
					'properties'           => [
						'name'        => [
							'type'        => 'string',
							'description' => esc_html__( 'Brand name.', 'seo-by-rank-math' ),
						],
						'url'         => [
							'type'        => 'string',
							'format'      => 'uri',
							'description' => esc_html__( 'Brand website URL.', 'seo-by-rank-math' ),
						],
						'description' => [
							'type'        => 'string',
							'description' => esc_html__( 'Short description of the brand or product.', 'seo-by-rank-math' ),
							'default'     => '',
						],
						'locale'      => [
							'type'        => 'string',
							'description' => esc_html__( 'ISO 3166-1 alpha-2 country code (e.g. "US", "HU"). Defaults to no country filter.', 'seo-by-rank-math' ),
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
		return current_user_can( 'manage_options' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$request = new WP_REST_Request( 'POST' );
		$request->set_param( 'name', sanitize_text_field( (string) ( $input['name'] ?? '' ) ) );
		$request->set_param( 'url', esc_url_raw( (string) ( $input['url'] ?? '' ) ) );
		$request->set_param( 'description', sanitize_textarea_field( (string) ( $input['description'] ?? '' ) ) );
		$request->set_param( 'locale', sanitize_text_field( (string) ( $input['locale'] ?? '' ) ) );

		$response = $this->controller->create_brand( $request );

		if ( is_wp_error( $response ) ) {
			return [ 'error' => $response->get_error_message() ];
		}

		$data  = $response->get_data();
		$brand = $data['data']['brand'] ?? [];

		rank_math()->tracking->track_ability_executed(
			'AI Visibility Brand Created',
			[ 'locale' => ! empty( $input['locale'] ) ? $input['locale'] : null ],
			'manage_options'
		);

		return [
			'id'              => $brand['id'] ?? '',
			'name'            => $brand['name'] ?? '',
			'url'             => $brand['url'] ?? '',
			'analysis_status' => 'pending',
			'created_at'      => $brand['created_at'] ?? null,
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
				'id'              => [
					'type'        => 'string',
					'description' => 'UUID of the newly created brand. Use this as brand_id in subsequent ability calls.',
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
				'analysis_status' => [
					'type'        => 'string',
					'enum'        => [ 'pending' ],
					'description' => 'Always "pending" on creation — the initial analysis is seeded automatically and will complete asynchronously.',
				],
				'created_at'      => [
					'type'        => [ 'string', 'null' ],
					'format'      => 'date-time',
					'description' => 'ISO 8601 datetime when the brand was created.',
				],
				'error'           => [
					'type'        => 'string',
					'description' => 'Present only on failure — human-readable error from the upstream API.',
				],
			],
		];
	}
}
