<?php
/**
 * Ability: rank-math/set-website-identity
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
 * Registers and executes the rank-math/set-website-identity ability.
 */
class Set_Website_Identity extends Abstract_Ability {

	/**
	 * Allowed values for the knowledgegraph_type field.
	 */
	const KNOWLEDGEGRAPH_TYPES = [ 'person', 'company' ];

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
			'rank-math/set-website-identity',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Set website identity', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Updates the site\'s Knowledge Graph identity used by Rank Math for schema and social markup: knowledge graph type (person/company), website name, alternate name, knowledge graph name, organization description, business type, and site URL override. All fields are optional — omitted fields keep their current stored value.',
					'seo-by-rank-math'
				),
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'knowledgegraph_type'      => [
							'type'        => 'string',
							'enum'        => self::KNOWLEDGEGRAPH_TYPES,
							'description' => esc_html__( 'Whether the site represents a person or a company/organization.', 'seo-by-rank-math' ),
						],
						'website_name'             => [
							'type'        => 'string',
							'description' => esc_html__( 'The website name used in schema and social markup.', 'seo-by-rank-math' ),
						],
						'website_alternate_name'   => [
							'type'        => 'string',
							'description' => esc_html__( 'An alternate name for the website.', 'seo-by-rank-math' ),
						],
						'knowledgegraph_name'      => [
							'type'        => 'string',
							'description' => esc_html__( 'The person or organization name used in the Knowledge Graph.', 'seo-by-rank-math' ),
						],
						'organization_description' => [
							'type'        => 'string',
							'description' => esc_html__( 'Description of the organization. Only used when knowledgegraph_type is "company".', 'seo-by-rank-math' ),
						],
						'local_business_type'      => [
							'type'        => 'string',
							'description' => esc_html__( 'Schema.org business type slug (e.g. "Restaurant", "LegalService", "Organization"), spaces removed. Only used when knowledgegraph_type is "company". Call get-settings first or see the Local SEO business type list in WP Admin for valid values.', 'seo-by-rank-math' ),
						],
						'url'                      => [
							'type'        => 'string',
							'description' => esc_html__( 'Site URL override used in Knowledge Graph markup.', 'seo-by-rank-math' ),
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
		if ( isset( $input['knowledgegraph_type'] ) && ! in_array( $input['knowledgegraph_type'], self::KNOWLEDGEGRAPH_TYPES, true ) ) {
			return [
				'error' => [
					'code'    => 'invalid_knowledgegraph_type',
					'message' => esc_html__( 'knowledgegraph_type must be either "person" or "company".', 'seo-by-rank-math' ),
				],
			];
		}

		if ( isset( $input['local_business_type'] ) && ! array_key_exists( $input['local_business_type'], $this->get_business_types() ) ) {
			return [
				'error' => [
					'code'    => 'invalid_local_business_type',
					'message' => esc_html__( 'local_business_type is not a recognized Schema.org business type slug.', 'seo-by-rank-math' ),
				],
			];
		}

		$titles = $this->get_titles_option();

		foreach ( [ 'knowledgegraph_type', 'website_name', 'website_alternate_name', 'knowledgegraph_name', 'local_business_type' ] as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$titles[ $field ] = sanitize_text_field( $input[ $field ] );
			}
		}

		if ( isset( $input['url'] ) ) {
			$titles['url'] = esc_url_raw( $input['url'] );
		}

		if ( isset( $input['organization_description'] ) ) {
			$titles['organization_description'] = wp_kses_post( $input['organization_description'] );
		}

		$this->save_and_reset( null, $titles, null );

		$keys = array_keys( $input );

		rank_math()->tracking->track_ability_executed(
			'Website Identity Updated',
			[ 'fields_updated' => $keys ],
			'rank_math_titles'
		);

		return [
			'saved'   => true,
			'updated' => $keys,
		];
	}

	/**
	 * Valid Schema.org business type slugs. Extracted for testability.
	 *
	 * @return array<string, string>
	 */
	protected function get_business_types(): array {
		return Helper::choices_business_types();
	}
}
