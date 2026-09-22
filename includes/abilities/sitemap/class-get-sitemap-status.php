<?php
/**
 * Ability: rank-math/get-sitemap-status
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Sitemap;

use RankMath\Abilities\Ability_Interface;
use RankMath\Helper;
use RankMath\Sitemap\Generator;
use RankMath\Sitemap\Router;
use RankMath\Sitemap\Sitemap;
use RankMath\Sitemap\Providers\Post_Type as Post_Type_Provider;
use RankMath\Sitemap\Providers\Taxonomy as Taxonomy_Provider;
use RankMath\Sitemap\Providers\Author as Author_Provider;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and executes the rank-math/get-sitemap-status ability.
 */
class Get_Sitemap_Status implements Ability_Interface {

	/**
	 * Ability category slug.
	 *
	 * @var string
	 */
	private $category;

	/**
	 * Shared meta args.
	 *
	 * @var array
	 */
	private $shared_meta;

	/**
	 * Constructor.
	 *
	 * @param string $category    Ability category slug.
	 * @param array  $shared_meta Shared meta args.
	 */
	public function __construct( string $category, array $shared_meta ) {
		$this->category    = $category;
		$this->shared_meta = $shared_meta;
	}

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		\wp_register_ability(
			'rank-math/get-sitemap-status',
			[
				'category'            => $this->category,
				'label'               => esc_html__( 'Get sitemap status', 'seo-by-rank-math' ),
				'description'         => esc_html__(
					'Returns whether the XML sitemap is enabled, the sitemap index URL, and the active sitemap types (post types, taxonomies, and the Local SEO sitemap, when active) with their URLs and estimated entry counts.',
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
				'meta'                => array_merge(
					$this->shared_meta,
					[
						'annotations' => [
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						],
					]
				),
			]
		);
	}

	/**
	 * Check if the current user has permission to execute this ability.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rank_math_sitemap' );
	}

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array {
		$enabled = Helper::is_module_active( 'sitemap' );

		$result = [
			'enabled'   => $enabled,
			'index_url' => Router::get_base_url( Sitemap::get_sitemap_index_slug() . '.xml' ),
			'sitemaps'  => $this->get_sitemaps(),
		];

		rank_math()->tracking->track_ability_executed(
			'Sitemap Status Fetched',
			[
				'enabled'        => $enabled,
				'sitemaps_count' => count( $result['sitemaps'] ),
			],
			'rank_math_sitemap'
		);

		return $result;
	}

	/**
	 * Build the list of active sitemap types with their URL and estimated entry count.
	 *
	 * Reuses the real Generator's provider list, so PRO types (e.g. news/video)
	 * show up automatically. Local SEO's `local-sitemap.xml` isn't a Provider
	 * though, so it's captured separately via get_non_provider_entries().
	 *
	 * @return array
	 */
	private function get_sitemaps(): array {
		$max_entries = absint( Helper::get_settings( 'sitemap.items_per_page', 100 ) );
		$providers   = ( new Generator() )->providers;

		$sitemaps = [];
		foreach ( $providers as $provider ) {
			$sitemaps = array_merge( $sitemaps, $this->build_sitemap_entries( $provider, $max_entries ) );
		}

		$local_sitemap = $this->get_local_sitemap();
		if ( null !== $local_sitemap ) {
			$sitemaps[] = $local_sitemap;
		}

		return array_merge( $sitemaps, $this->get_non_provider_entries() );
	}

	/**
	 * Capture sitemap entries added outside the Provider system, via the
	 * `rank_math/sitemap/index` filter (currently only Local SEO's KML_File).
	 *
	 * The 'local' type is excluded here because get_local_sitemap() already
	 * builds that entry with an accurate location count; KML_File::add_local_sitemap()
	 * fires this same filter internally, so without the exclusion the local
	 * sitemap would be added twice.
	 *
	 * @return array
	 */
	private function get_non_provider_entries(): array {
		$entries = [];
		$capture = function ( $item, $label, $type_slug = null ) use ( &$entries ) {
			$type = ( is_string( $type_slug ) && '' !== $type_slug ) ? $type_slug : $label;
			if ( ! empty( $item['loc'] ) && 'local' !== $type ) {
				$entries[] = [
					'type'            => $type,
					'url'             => $item['loc'],
					'estimated_count' => 1,
				];
			}
			return $item;
		};

		add_filter( 'rank_math/sitemap/index/entry', $capture, 10, 3 );
		apply_filters( 'rank_math/sitemap/index', '' );
		remove_filter( 'rank_math/sitemap/index/entry', $capture, 10 );

		return $entries;
	}

	/**
	 * Build sitemap entries for a single provider.
	 *
	 * Get_index_links() only returns {loc, lastmod}, so type and page number
	 * are captured via the `sitemap/index/entry` filter it fires internally.
	 * Per-page counts use each provider's own safe public method instead of
	 * get_sitemap_links(), which can die() via Sitemap::maybe_redirect() when
	 * called outside an actual sitemap page request.
	 *
	 * @param object $provider    Sitemap provider instance.
	 * @param int    $max_entries Entries per sitemap page.
	 * @return array
	 */
	private function build_sitemap_entries( $provider, int $max_entries ): array {
		$entries = [];
		$capture = function ( $item, $label, $type_slug = null ) use ( &$entries ) {
			if ( ! empty( $item['loc'] ) ) {
				$entries[] = [
					'type' => ( is_string( $type_slug ) && '' !== $type_slug ) ? $type_slug : $label,
					'url'  => $item['loc'],
				];
			}
			return $item;
		};

		add_filter( 'rank_math/sitemap/index/entry', $capture, 10, 3 );
		$provider->get_index_links( $max_entries );
		remove_filter( 'rank_math/sitemap/index/entry', $capture, 10 );

		$post_type_totals = [];
		$page_numbers     = [];
		$sitemaps         = [];
		foreach ( $entries as $entry ) {
			$type                  = $entry['type'];
			$page_numbers[ $type ] = ( $page_numbers[ $type ] ?? 0 ) + 1;
			$page                  = $page_numbers[ $type ];

			if ( $provider instanceof Post_Type_Provider ) {
				if ( ! isset( $post_type_totals[ $type ] ) ) {
					$post_type_totals[ $type ] = (int) $provider->get_post_type_count( $type );
				}

				$offset          = ( $page - 1 ) * $max_entries;
				$estimated_count = max( 0, min( $max_entries, $post_type_totals[ $type ] - $offset ) );
			} elseif ( $provider instanceof Taxonomy_Provider ) {
				$estimated_count = $this->count_taxonomy_page( $provider, $type, $max_entries, $page );
			} elseif ( $provider instanceof Author_Provider ) {
				$estimated_count = count(
					(array) $provider->get_users(
						[
							'offset' => ( $page - 1 ) * $max_entries,
							'number' => $max_entries,
						]
					)
				);
			} else {
				$estimated_count = $max_entries;
			}

			$sitemaps[] = [
				'type'            => $type,
				'url'             => $entry['url'],
				'estimated_count' => $estimated_count,
			];
		}

		return $sitemaps;
	}

	/**
	 * Build the Local SEO sitemap entry, if the Local Sitemap is active.
	 *
	 * Mirrors the conditions in RankMath\Local_Seo\Local_Seo::location_sitemap(),
	 * which is what actually decides whether local-sitemap.xml gets served.
	 *
	 * @return array|null
	 */
	private function get_local_sitemap(): ?array {
		if (
			! Helper::is_module_active( 'sitemap' ) ||
			! Helper::is_module_active( 'local-seo' ) ||
			'company' !== Helper::get_settings( 'titles.knowledgegraph_type' ) ||
			! apply_filters( 'rank_math/sitemap/locations', false ) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		) {
			return null;
		}

		$locations = apply_filters( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			'rank_math/sitemap/locations/data',
			[ [ 'name' => Helper::get_settings( 'titles.knowledgegraph_name' ) ] ]
		);

		return [
			'type'            => 'local',
			'url'             => Router::get_base_url( 'local-sitemap.xml' ),
			'estimated_count' => is_array( $locations ) ? count( $locations ) : 0,
		];
	}

	/**
	 * Real per-page term count for a taxonomy, via Taxonomy_Provider::get_terms().
	 *
	 * @param Taxonomy_Provider $provider     Taxonomy provider instance.
	 * @param string            $taxonomy     Taxonomy slug.
	 * @param int               $max_entries  Entries per sitemap page.
	 * @param int               $current_page Page number (1-indexed).
	 * @return int
	 */
	private function count_taxonomy_page( Taxonomy_Provider $provider, string $taxonomy, int $max_entries, int $current_page ): int {
		$taxonomy_object = get_taxonomy( $taxonomy );
		if ( ! $taxonomy_object ) {
			return 0;
		}

		return count( (array) $provider->get_terms( $taxonomy_object, $max_entries, $current_page ) );
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
				'enabled'   => [
					'type'        => 'boolean',
					'description' => esc_html__( 'Whether the XML sitemap module is active.', 'seo-by-rank-math' ),
				],
				'index_url' => [
					'type'        => 'string',
					'description' => esc_html__( 'The URL of the sitemap index file.', 'seo-by-rank-math' ),
				],
				'sitemaps'  => [
					'type'        => 'array',
					'description' => esc_html__( 'The active sitemap types, e.g. post types, taxonomies, and the Local SEO sitemap (PRO adds news/video when active).', 'seo-by-rank-math' ),
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'type'            => [ 'type' => 'string' ],
							'url'             => [ 'type' => 'string' ],
							'estimated_count' => [ 'type' => 'integer' ],
						],
					],
				],
			],
		];
	}
}
