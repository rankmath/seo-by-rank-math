<?php
/**
 * Ability: rank-math/set-plugin-preferences
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
 * Registers and executes the rank-math/set-plugin-preferences ability.
 */
class Set_Plugin_Preferences extends Abstract_Ability {

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
			'rank-math/set-plugin-preferences',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Set plugin preferences', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Updates Rank Math plugin-level preferences that are not part of a settings section. Currently supports toggling automatic plugin updates.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'required'             => [ 'enable_auto_update' ],
					'properties'           => [
						'enable_auto_update' => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Enable Automatic Updates', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Whether WordPress should automatically install new Rank Math plugin updates.', 'seo-by-rank-math' ),
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => $this->write_success_schema(),
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
		$enable = ! empty( $input['enable_auto_update'] );

		Helper::toggle_auto_update_setting( $enable ? 'on' : 'off' );

		rank_math()->tracking->track_ability_executed(
			'Plugin Preferences Updated',
			[ 'enable_auto_update' => $enable ],
			'manage_options'
		);

		return [
			'saved'   => true,
			'updated' => [ 'enable_auto_update' ],
		];
	}
}
