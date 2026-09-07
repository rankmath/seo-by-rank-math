<?php
/**
 * Ability: rank-math/set-module-status
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
 * Registers and executes the rank-math/set-module-status ability.
 */
class Set_Module_Status extends Abstract_Ability {

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
			'rank-math/set-module-status',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Set module status', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Enables or disables Rank Math modules (e.g. sitemap, redirections, 404-monitor, rich-snippet). Only provided module slugs are changed; omitted modules keep their current state. Modules with unmet dependencies (e.g. Video Sitemap requires Sitemap and Schema) cannot be enabled until their dependencies are active or enabled in the same call. Modules whose external requirements are not met (e.g. WooCommerce module without the WooCommerce plugin active) cannot be enabled at all.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'modules' ],
					'properties'           => [
						'modules' => [
							'type'                 => 'object',
							'title'                => esc_html__( 'Modules', 'seo-by-rank-math' ),
							'description'          => esc_html__( 'Map of module slug to desired active state. Call get-settings first to see the full list of registered module slugs and their current status.', 'seo-by-rank-math' ),
							'additionalProperties' => [ 'type' => 'boolean' ],
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => $this->write_success_schema( true, 'List of module slugs whose status was changed.' ),
				'permission_callback' => [ $this, 'check_permissions' ],
				'execute_callback'    => [ $this, 'execute' ],
				'meta'                => $this->build_meta( false ),
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
		$requested = isset( $input['modules'] ) && is_array( $input['modules'] ) ? $input['modules'] : [];

		if ( empty( $requested ) ) {
			return [
				'error' => [
					'code'    => 'missing_modules',
					'message' => esc_html__( 'modules must be a non-empty map of module slug to boolean.', 'seo-by-rank-math' ),
				],
			];
		}

		$registered = $this->get_registered_modules();
		$active     = $this->get_active_modules();
		$to_update  = [];

		foreach ( $requested as $slug => $enable ) {
			if ( ! array_key_exists( $slug, $registered ) ) {
				return [
					'error' => [
						'code'    => 'invalid_module',
						'message' => sprintf(
							/* translators: module slug */
							esc_html__( 'Unknown module: %s', 'seo-by-rank-math' ),
							esc_html( $slug )
						),
					],
				];
			}

			if ( $enable ) {
				if ( $registered[ $slug ]->is_disabled() ) {
					$disabled_text = $registered[ $slug ]->get( 'disabled_text' );
					if ( empty( $disabled_text ) ) {
						$disabled_text = sprintf(
							/* translators: module slug */
							esc_html__( 'Cannot enable "%s" — its requirements are not met.', 'seo-by-rank-math' ),
							esc_html( $slug )
						);
					}

					return [
						'error' => [
							'code'    => 'module_disabled',
							'message' => $disabled_text,
						],
					];
				}

				foreach ( $registered[ $slug ]->get_dependencies() as $dependency ) {
					$dependency_met = in_array( $dependency, $active, true )
						|| ( isset( $requested[ $dependency ] ) && $requested[ $dependency ] );

					if ( ! $dependency_met ) {
						return [
							'error' => [
								'code'    => 'unmet_dependency',
								'message' => sprintf(
									/* translators: 1: module slug, 2: required module slug */
									esc_html__( 'Cannot enable "%1$s" — it requires the "%2$s" module to be active or enabled in the same call.', 'seo-by-rank-math' ),
									esc_html( $slug ),
									esc_html( $dependency )
								),
							],
						];
					}
				}
			}

			$to_update[ $slug ] = $enable ? 'on' : 'off';
		}

		Helper::update_modules( $to_update );

		foreach ( $to_update as $slug => $state ) {
			if ( 'off' === $state && in_array( $slug, [ 'sitemap', 'llms-txt' ], true ) ) {
				delete_option( 'rewrite_rules' );
			}

			do_action( 'rank_math/module_changed', $slug, $state );
		}

		$keys = array_keys( $requested );

		rank_math()->tracking->track_ability_executed(
			'Module Status Updated',
			[ 'modules_updated' => $keys ],
			'manage_options'
		);

		return [
			'saved'   => true,
			'updated' => $keys,
		];
	}

	/**
	 * Registered modules keyed by slug. Extracted for testability.
	 *
	 * @return array<string, \RankMath\Module\Module>
	 */
	protected function get_registered_modules(): array {
		return rank_math()->manager->modules;
	}
}
