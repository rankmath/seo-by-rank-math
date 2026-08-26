<?php
/**
 * Ability: rank-math/set-homepage-seo
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/set-homepage-seo ability.
 *
 * Automatically detects whether the homepage is a static page or the latest-posts
 * feed and writes to the appropriate storage in each case:
 */
class Set_Homepage_Seo extends Abstract_Ability {

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
			'rank-math/set-homepage-seo',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Set homepage SEO', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Updates the SEO title, meta description, and focus keyword for the site homepage. Automatically detects whether the homepage is a static page or a latest-posts feed and writes to the correct storage in each case. Call get-settings first to see current homepage values. All fields are optional — omitted fields keep their current stored value.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'title'         => [
							'type'        => 'string',
							'title'       => esc_html__( 'SEO Title', 'seo-by-rank-math' ),
							'description' => esc_html__( 'SEO title for the homepage. Supports Rank Math title variables (e.g. %sitename%, %sep%, %sitedesc%). For static-page homepages, stored as post meta; for latest-posts homepages, stored in Titles & Meta settings.', 'seo-by-rank-math' ), //phpcs:ignore WordPress.WP.I18n
						],
						'description'   => [
							'type'        => 'string',
							'title'       => esc_html__( 'Meta Description', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Meta description for the homepage. For static-page homepages, stored as post meta; for latest-posts homepages, stored in Titles & Meta settings.', 'seo-by-rank-math' ),
						],
						'focus_keyword' => [
							'type'        => 'string',
							'title'       => esc_html__( 'Focus Keyword', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Primary focus keyword for the homepage. Only applicable when the homepage is a static page — ignored for latest-posts homepages.', 'seo-by-rank-math' ),
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
		if ( empty( $input ) ) {
			return [
				'error' => [
					'code'    => 'no_fields',
					'message' => esc_html__( 'No fields provided. Pass at least one of: title, description, focus_keyword.', 'seo-by-rank-math' ),
				],
			];
		}

		if ( 'page' === get_option( 'show_on_front' ) ) {
			return $this->update_static_page( $input );
		}

		return $this->update_latest_posts( $input );
	}

	/**
	 * Write title/description post meta on the static front page.
	 *
	 * @param array $input Ability input.
	 * @return array
	 */
	private function update_static_page( array $input ): array {
		$page_id = (int) get_option( 'page_on_front' );

		if ( ! $page_id ) {
			return [
				'error' => [
					'code'    => 'no_front_page',
					'message' => esc_html__( 'show_on_front is set to "page" but no front page is assigned (page_on_front is empty). Assign a static front page under WP Dashboard > Settings > Reading.', 'seo-by-rank-math' ),
				],
			];
		}

		$updated = [];

		if ( isset( $input['title'] ) ) {
			update_post_meta( $page_id, 'rank_math_title', sanitize_text_field( $input['title'] ) );
			$updated[] = 'title';
		}

		if ( isset( $input['description'] ) ) {
			update_post_meta( $page_id, 'rank_math_description', sanitize_textarea_field( $input['description'] ) );
			$updated[] = 'description';
		}

		if ( isset( $input['focus_keyword'] ) ) {
			update_post_meta( $page_id, 'rank_math_focus_keyword', sanitize_text_field( $input['focus_keyword'] ) );
			$updated[] = 'focus_keyword';
		}

		rank_math()->tracking->track_ability_executed(
			'Homepage SEO Updated',
			[
				'homepage_type'  => 'static_page',
				'page_id'        => $page_id,
				'fields_updated' => $updated,
			],
			'rank_math_titles'
		);

		return [
			'saved'   => true,
			'updated' => $updated,
		];
	}

	/**
	 * Write title/description into the Titles & Meta settings for the
	 * latest-posts homepage.
	 *
	 * @param array $input Ability input.
	 * @return array
	 */
	private function update_latest_posts( array $input ): array {
		$titles  = $this->get_titles_option();
		$updated = [];

		if ( isset( $input['title'] ) ) {
			$titles['homepage_title'] = sanitize_text_field( $input['title'] );
			$updated[]                = 'title';
		}

		if ( isset( $input['description'] ) ) {
			$titles['homepage_description'] = sanitize_textarea_field( $input['description'] );
			$updated[]                      = 'description';
		}

		$this->save_and_reset( null, $titles, null );

		rank_math()->tracking->track_ability_executed(
			'Homepage SEO Updated',
			[
				'homepage_type'  => 'latest_posts',
				'fields_updated' => $updated,
			],
			'rank_math_titles'
		);

		return [
			'saved'   => true,
			'updated' => $updated,
		];
	}
}
