<?php
/**
 * Ability: rank-math/set-link-settings
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/set-link-settings ability.
 */
class Set_Link_Settings extends Abstract_Ability {

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
			'rank-math/set-link-settings',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Set link settings', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Updates site-wide link behavior: nofollow for external and image links, opening external links in a new tab, nofollow domain allow/exclude lists, category base stripping, and attachment page redirects. All fields are optional — omitted fields keep their current stored value.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'nofollow_external_links'   => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Nofollow External Links', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Automatically add rel="nofollow" to external links in content.', 'seo-by-rank-math' ),
						],
						'new_window_external_links' => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Open External Links in New Tab/Window', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Automatically add target="_blank" to external links in content.', 'seo-by-rank-math' ),
						],
						'nofollow_image_links'      => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Nofollow Image File Links', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Automatically add rel="nofollow" to links pointing to external image files.', 'seo-by-rank-math' ),
						],
						'nofollow_domains'          => [
							'type'        => 'string',
							'title'       => esc_html__( 'Nofollow Domains', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Newline-separated list of domains to nofollow. If empty, nofollow applies to all external domains (when enabled). Only takes effect when nofollow_external_links or nofollow_image_links is also enabled — hidden in the UI otherwise.', 'seo-by-rank-math' ),
						],
						'nofollow_exclude_domains'  => [
							'type'        => 'string',
							'title'       => esc_html__( 'Nofollow Exclude Domains', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Newline-separated list of domains to exclude from nofollow. Only takes effect when nofollow_external_links or nofollow_image_links is also enabled — hidden in the UI otherwise.', 'seo-by-rank-math' ),
						],
						'strip_category_base'       => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Strip Category Base', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Remove /category/ from category archive URLs.', 'seo-by-rank-math' ),
						],
						'attachment_redirect_urls'  => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Redirect Attachments', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Redirect attachment page URLs to the post they appear in.', 'seo-by-rank-math' ),
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
		$general = $this->get_general_option();

		$this->apply_toggle_fields(
			$general,
			$input,
			[
				'nofollow_external_links',
				'new_window_external_links',
				'nofollow_image_links',
				'strip_category_base',
				'attachment_redirect_urls',
			]
		);

		foreach ( [ 'nofollow_domains', 'nofollow_exclude_domains' ] as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$general[ $field ] = sanitize_textarea_field( $input[ $field ] );
			}
		}

		$this->save_and_reset( $general, null, null );

		$keys = array_keys( $input );

		rank_math()->tracking->track_ability_executed(
			'Link Settings Updated',
			[ 'fields_updated' => $keys ],
			'rank_math_general'
		);

		return [
			'saved'   => true,
			'updated' => $keys,
		];
	}
}
