<?php
/**
 * Ability: rank-math/set-breadcrumb-settings
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/set-breadcrumb-settings ability.
 */
class Set_Breadcrumb_Settings extends Abstract_Ability {

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
			'rank-math/set-breadcrumb-settings',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Set breadcrumb settings', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Updates breadcrumb display settings: enabling breadcrumbs, the separator, home link visibility/label/URL, a prefix before the trail, archive/search/404 label formats, ancestor category display, hiding the taxonomy name, removing the current post title, and showing the blog page. All fields are optional — omitted fields keep their current stored value.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'breadcrumbs'                     => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Enable Breadcrumbs', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Enable the Breadcrumbs feature.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_separator'           => [
							'type'        => 'string',
							'title'       => esc_html__( 'Separator Character', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Character to display between breadcrumb links.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_home'                => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Show Home Link', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Show a link to the homepage at the start of the breadcrumb trail.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_home_label'          => [
							'type'        => 'string',
							'title'       => esc_html__( 'Home Label', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Label used for the homepage link in the breadcrumb trail.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_home_link'           => [
							'type'        => 'string',
							'title'       => esc_html__( 'Home Link', 'seo-by-rank-math' ),
							'description' => esc_html__( 'URL used for the homepage link in the breadcrumb trail.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_prefix'              => [
							'type'        => 'string',
							'title'       => esc_html__( 'Prefix Breadcrumb', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Text to show before the breadcrumb trail.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_archive_format'      => [
							'type'        => 'string',
							'title'       => esc_html__( 'Archive Format', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Label format used for archive pages in the breadcrumb trail. Include a placeholder token for the archive title.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_search_format'       => [
							'type'        => 'string',
							'title'       => esc_html__( 'Search Results Format', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Label format used for search results pages in the breadcrumb trail. Include a placeholder token for the search query.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_404_label'           => [
							'type'        => 'string',
							'title'       => esc_html__( '404 Label', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Label used for the 404 error item in the breadcrumb trail.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_remove_post_title'   => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Hide Post Title', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Hide the current post/page title from the breadcrumb trail.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_ancestor_categories' => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Show Category(s)', 'seo-by-rank-math' ),
							'description' => esc_html__( 'If the category is a child category, show all ancestor categories in the breadcrumb trail.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_hide_taxonomy_name'  => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Hide Taxonomy Name', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Hide the current taxonomy term name from the breadcrumb trail.', 'seo-by-rank-math' ),
						],
						'breadcrumbs_blog_page'           => [
							'type'        => 'boolean',
							'title'       => esc_html__( 'Show Blog Page', 'seo-by-rank-math' ),
							'description' => esc_html__( 'Show the blog page in the breadcrumb trail. Only applies when a static homepage is set.', 'seo-by-rank-math' ),
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
				'breadcrumbs',
				'breadcrumbs_home',
				'breadcrumbs_remove_post_title',
				'breadcrumbs_ancestor_categories',
				'breadcrumbs_hide_taxonomy_name',
				'breadcrumbs_blog_page',
			]
		);

		foreach ( [ 'breadcrumbs_separator', 'breadcrumbs_home_label', 'breadcrumbs_prefix', 'breadcrumbs_archive_format', 'breadcrumbs_search_format', 'breadcrumbs_404_label' ] as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$general[ $field ] = sanitize_text_field( $input[ $field ] );
			}
		}

		if ( isset( $input['breadcrumbs_home_link'] ) ) {
			$general['breadcrumbs_home_link'] = esc_url_raw( $input['breadcrumbs_home_link'] );
		}

		$this->save_and_reset( $general, null, null );

		$keys = array_keys( $input );

		rank_math()->tracking->track_ability_executed(
			'Breadcrumb Settings Updated',
			[ 'fields_updated' => $keys ],
			'rank_math_general'
		);

		return [
			'saved'   => true,
			'updated' => $keys,
		];
	}
}
