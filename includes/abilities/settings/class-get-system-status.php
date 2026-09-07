<?php
/**
 * Ability: rank-math/get-system-status
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

use RankMath\Status\System_Status;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-system-status ability.
 *
 * Delegates entirely to System_Status::get_json_data() which already
 * collects Rank Math health data and WordPress core / server info.
 */
class Get_System_Status extends Abstract_Ability {

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
			'rank-math/get-system-status',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get system status', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns a structured system health snapshot identical to Status & Tools → System Status in the admin: Rank Math version, plan, DB table health, Google token status, WordPress core info, server environment, and active plugins. Call this alongside get-settings at the start of any AI configuration flow to detect environment issues before making changes.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'    => 'object',
					'default' => [],
				],
				'output_schema'       => [
					'type'        => 'object',
					'description' => 'System info sections keyed by section ID (e.g. rank-math, wp-core, wp-server).',
				],
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
		$result = $this->fetch_system_data()['systemInfo'];

		rank_math()->tracking->track_ability_executed(
			'System Status Fetched',
			[],
			'manage_options'
		);

		return $result;
	}

	/**
	 * Fetch raw data from System_Status. Extracted for testability.
	 *
	 * @return array
	 */
	protected function fetch_system_data(): array {
		return System_Status::get_json_data();
	}
}
