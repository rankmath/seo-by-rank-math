<?php
/**
 * Ability: rank-math/set-global-seo-settings
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/set-global-seo-settings ability.
 */
class Set_Global_Seo_Settings extends Abstract_Ability {

	/**
	 * Allowed values for the twitter_card_type field.
	 */
	const TWITTER_CARD_TYPES = [ 'summary_large_image', 'summary_card' ];

	/**
	 * Constructor.
	 *
	 * @param string $category    Ability category slug.
	 * @param array  $shared_meta Shared meta args.
	 */
	public function __construct( string $category, array $shared_meta ) {
		parent::__construct( $category, $shared_meta, 'rank_math_titles' );
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/set-global-seo-settings',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Set global SEO settings', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Updates site-wide SEO defaults: global robots meta directives, whether to noindex empty taxonomy archives, the title separator character, title capitalization, and the default Twitter card type. All fields are optional — omitted fields keep their current stored value.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'robots_global'            => [
							'type'        => 'array',
							'items'       => [
								'type' => 'string',
								'enum' => self::ROBOTS_DIRECTIVES,
							],
							'description' => esc_html__( 'Global robots meta directives applied site-wide (e.g. ["index"], ["noindex","nofollow"]).', 'seo-by-rank-math' ),
						],
						'noindex_empty_taxonomies' => [
							'type'        => 'boolean',
							'description' => esc_html__( 'Whether to noindex taxonomy archives that have no posts assigned.', 'seo-by-rank-math' ),
						],
						'title_separator'          => [
							'type'        => 'string',
							'description' => esc_html__( 'Character used to separate parts of the SEO title (e.g. "-", "|", "&mdash;"). A custom string is also accepted.', 'seo-by-rank-math' ),
						],
						'capitalize_titles'        => [
							'type'        => 'boolean',
							'description' => esc_html__( 'Whether to capitalize the first letter of every word in SEO titles.', 'seo-by-rank-math' ),
						],
						'twitter_card_type'        => [
							'type'        => 'string',
							'enum'        => self::TWITTER_CARD_TYPES,
							'description' => esc_html__( 'Default Twitter card type used when sharing content.', 'seo-by-rank-math' ),
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => $this->write_success_schema( true ),
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
		if ( isset( $input['robots_global'] ) ) {
			foreach ( (array) $input['robots_global'] as $value ) {
				if ( ! in_array( $value, self::ROBOTS_DIRECTIVES, true ) ) {
					return [
						'error' => [
							'code'    => 'invalid_robots_global',
							'message' => esc_html__( 'robots_global may only contain: index, noindex, nofollow, noarchive, noimageindex, nosnippet.', 'seo-by-rank-math' ),
						],
					];
				}
			}
		}

		if ( isset( $input['twitter_card_type'] ) && ! in_array( $input['twitter_card_type'], self::TWITTER_CARD_TYPES, true ) ) {
			return [
				'error' => [
					'code'    => 'invalid_twitter_card_type',
					'message' => esc_html__( 'twitter_card_type must be either "summary_large_image" or "summary_card".', 'seo-by-rank-math' ),
				],
			];
		}

		$titles = $this->get_titles_option();

		if ( isset( $input['robots_global'] ) ) {
			$titles['robots_global'] = array_map( 'sanitize_text_field', (array) $input['robots_global'] );
		}

		$this->apply_toggle_fields( $titles, $input, [ 'noindex_empty_taxonomies', 'capitalize_titles' ] );

		if ( isset( $input['title_separator'] ) ) {
			$titles['title_separator'] = sanitize_text_field( $input['title_separator'] );
		}

		if ( isset( $input['twitter_card_type'] ) ) {
			$titles['twitter_card_type'] = sanitize_text_field( $input['twitter_card_type'] );
		}

		$this->save_and_reset( null, $titles, null );

		$keys = array_keys( $input );

		rank_math()->tracking->track_ability_executed(
			'Global SEO Settings Updated',
			[ 'fields_updated' => $keys ],
			'rank_math_titles'
		);

		return [
			'saved'   => true,
			'updated' => $keys,
		];
	}
}
