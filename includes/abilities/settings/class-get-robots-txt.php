<?php
/**
 * Ability: rank-math/get-robots-txt
 *
 * @since      1.0.278
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

use RankMath\Robots_Txt;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-robots-txt ability.
 */
class Get_Robots_Txt extends Abstract_Ability {

	/**
	 * Constructor.
	 *
	 * @param string $category    Ability category slug.
	 * @param array  $shared_meta Shared meta args.
	 */
	public function __construct( string $category, array $shared_meta ) {
		parent::__construct( $category, $shared_meta, 'rank_math_general' );
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/get-robots-txt',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get robots.txt', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns the current robots.txt content managed by Rank Math.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'    => 'object',
					'default' => [],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'content' => [
							'type'        => 'string',
							'description' => esc_html__( 'The current robots.txt content: the real file contents when a physical robots.txt exists, otherwise the content Rank Math generates (custom override or computed default).', 'seo-by-rank-math' ),
						],
						'exists'  => [
							'type'        => 'boolean',
							'description' => esc_html__( 'Whether a physical robots.txt file exists in the site root.', 'seo-by-rank-math' ),
						],
						'public'  => [
							'type'        => 'boolean',
							'description' => esc_html__( 'Whether the site is set to be visible to search engines (Settings > Reading > Search engine visibility).', 'seo-by-rank-math' ),
						],
					],
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
		$data = $this->get_robots_data();

		rank_math()->tracking->track_ability_executed(
			'Robots Txt Fetched',
			[],
			'rank_math_general'
		);

		return [
			'content' => $data['default'],
			'exists'  => ! empty( $data['exists'] ),
			'public'  => 0 !== absint( $data['public'] ),
		];
	}

	/**
	 * Fetch raw data from Robots_Txt. Extracted for testability.
	 *
	 * @return array
	 */
	protected function get_robots_data(): array {
		return Robots_Txt::get_robots_data();
	}
}
