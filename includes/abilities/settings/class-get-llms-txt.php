<?php
/**
 * Ability: rank-math/get-llms-txt
 *
 * @since      1.0.278
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

use RankMath\Helper;
use RankMath\LLMS\LLMS_Txt;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-llms-txt ability.
 *
 * Delegates entirely to LLMS_Txt::output(), capturing its echoed Markdown
 * output via an output buffer rather than reimplementing the generation logic.
 */
class Get_Llms_Txt extends Abstract_Ability {

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
			'rank-math/get-llms-txt',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get llms.txt', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns whether the llms.txt file is enabled, its URL, and the current generated Markdown content.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [],
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
		$enabled = $this->is_enabled();

		$result = [
			'enabled' => $enabled,
			'url'     => home_url( '/llms.txt' ),
			'content' => $enabled ? $this->fetch_content() : '',
		];

		rank_math()->tracking->track_ability_executed(
			'LLMs Txt Fetched',
			[ 'enabled' => $enabled ],
			'rank_math_general'
		);

		return $result;
	}

	/**
	 * Whether the llms.txt module is active. Extracted for testability.
	 *
	 * @return bool
	 */
	protected function is_enabled(): bool {
		return Helper::is_module_active( 'llms-txt' );
	}

	/**
	 * Capture the LLMS_Txt::output() Markdown content via an output buffer.
	 *
	 * Output() also sends a couple of headers (Content-Type: text/plain,
	 * X-Robots-Tag) as a side effect of reuse — harmless here since the
	 * REST/MCP response sends its own headers afterward. Extracted for
	 * testability.
	 *
	 * @return string
	 */
	protected function fetch_content(): string {
		ob_start();
		( new LLMS_Txt() )->output();
		return (string) ob_get_clean();
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
				'enabled' => [
					'type'        => 'boolean',
					'description' => esc_html__( 'Whether the llms.txt module is active.', 'seo-by-rank-math' ),
				],
				'url'     => [
					'type'        => 'string',
					'description' => esc_html__( 'The URL of the llms.txt file.', 'seo-by-rank-math' ),
				],
				'content' => [
					'type'        => 'string',
					'description' => esc_html__( 'The generated llms.txt Markdown content. Empty when the module is disabled.', 'seo-by-rank-math' ),
				],
			],
		];
	}
}
