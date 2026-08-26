<?php
/**
 * Ability: rank-math/get-settings
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-settings ability.
 */
class Get_Settings extends Abstract_Ability {

	/**
	 * Keys stripped from the general section before returning to AI — contain credentials or internal state.
	 */
	const SENSITIVE_GENERAL_KEYS = [
		'facebook_secret',
		'console_profile',
		'searchConsole',
		'analyticsData',
		'analytics',
		'console_caching_control',
	];

	/**
	 * Constructor.
	 *
	 * @param string $category    Ability category slug.
	 * @param array  $shared_meta Shared meta args.
	 */
	public function __construct( string $category, array $shared_meta ) {
		parent::__construct( $category, $shared_meta, 'manage_options' );
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/get-settings',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get settings', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns the full normalized Rank Math settings snapshot: all four sections (general, titles, sitemap, instant_indexing), active module status, and auto-update state. Call this first in any AI configuration flow to understand what is already configured before making changes.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'sections' => [
							'type'        => 'array',
							'description' => esc_html__( 'Return only these top-level sections. Omit to return the full snapshot.', 'seo-by-rank-math' ),
							'items'       => [
								'type' => 'string',
								'enum' => [ 'general', 'titles', 'sitemap', 'instant_indexing', 'modules', 'auto_update' ],
							],
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => $this->output_schema(),
				'permission_callback' => [ $this, 'check_permissions' ],
				'execute_callback'    => [ $this, 'execute' ],
				'meta'                => $this->build_meta( true ),
			]
		);
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$settings = rank_math()->settings->all();

		foreach ( self::SENSITIVE_GENERAL_KEYS as $key ) {
			unset( $settings['general'][ $key ] );
		}

		$settings['modules']     = $this->get_module_status();
		$settings['auto_update'] = $this->get_auto_update_setting();

		if ( ! empty( $input['sections'] ) ) {
			$settings = array_intersect_key( $settings, array_fill_keys( $input['sections'], true ) );
		}

		rank_math()->tracking->track_ability_executed(
			'Settings Fetched',
			[ 'sections' => array_keys( $settings ) ],
			'manage_options'
		);

		return $settings;
	}

	/**
	 * Build a map of user-facing modules with their effective active status.
	 *
	 * Excludes internal modules (always loaded, not user-toggleable) and
	 * skip-only entries. Modules whose dependencies are not met (disabled = true)
	 * are always reported as inactive regardless of the stored option value.
	 *
	 * @return array<string, bool>
	 */
	private function get_module_status(): array {
		$active_modules = $this->get_active_modules();
		$status         = [];

		foreach ( rank_math()->manager->modules as $slug => $module ) {
			$only = $module->get( 'only' );

			// Internal and skip-only modules are not user-configurable.
			if ( 'internal' === $only || 'skip' === $only ) {
				continue;
			}

			// A module whose dependency is missing is always inactive.
			if ( $module->get( 'disabled', false ) ) {
				$status[ $slug ] = false;
				continue;
			}

			$status[ $slug ] = in_array( $slug, $active_modules, true );
		}

		return $status;
	}

	/**
	 * Whether automatic plugin updates are enabled. Extracted for testability.
	 *
	 * @return bool
	 */
	protected function get_auto_update_setting(): bool {
		return Helper::get_auto_update_setting();
	}

	/**
	 * JSON schema for the ability output.
	 *
	 * @return array
	 */
	private function output_schema(): array {
		return [
			'type'        => 'object',
			'description' => 'Normalized plugin settings snapshot.',
			'properties'  => [
				'general'          => [
					'type'        => 'object',
					'description' => 'General settings (rank-math-options-general). Boolean values are normalized from on/off storage strings.',
				],
				'titles'           => [
					'type'        => 'object',
					'description' => 'Titles & Meta settings (rank-math-options-titles).',
				],
				'sitemap'          => [
					'type'        => 'object',
					'description' => 'Sitemap settings (rank-math-options-sitemap).',
				],
				'instant_indexing' => [
					'type'        => 'object',
					'description' => 'Instant Indexing settings (rank-math-options-instant-indexing).',
				],
				'modules'          => [
					'type'                 => 'object',
					'description'          => 'Map of all registered module slugs to their active status.',
					'additionalProperties' => [ 'type' => 'boolean' ],
				],
				'auto_update'      => [
					'type'        => 'boolean',
					'description' => 'Whether automatic plugin updates are enabled.',
				],
			],
		];
	}
}
