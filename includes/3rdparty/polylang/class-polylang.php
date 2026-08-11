<?php
/**
 * Polylang Integration.
 *
 * @since      1.0.274
 * @package    RankMath
 * @subpackage RankMath\ThirdParty
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ThirdParty\Polylang;

use RankMath\Traits\Hooker;
use RankMath\Helpers\Str;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Polylang class.
 */
class Polylang {

	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'pll_init', 'init' );
	}

	/**
	 * Initialize Polylang integration.
	 */
	public function init() {
		// Register option keys with Polylang so get_option() returns per-language values.
		$this->register_option_translations();

		// Reset Rank Math's settings cache so translated option values are used.
		$this->action( 'wp_loaded', 'maybe_reset_settings_cache' );

		if ( \PLL() instanceof \PLL_Frontend ) {
			$this->register_frontend_hooks();
			return;
		}

		$this->register_admin_hooks();
	}

	/**
	 * Register hooks active only on the frontend.
	 */
	private function register_frontend_hooks() {
		// Filter sitemap post queries by language to exclude inactive and foreign-domain posts.
		$this->filter( 'rank_math/sitemap/post_count/join', 'sitemap_join_clause', 10, 2 );
		$this->filter( 'rank_math/sitemap/post_count/where', 'sitemap_where_clause', 10, 2 );
		$this->filter( 'rank_math/sitemap/get_posts/join', 'sitemap_join_clause', 10, 2 );
		$this->filter( 'rank_math/sitemap/get_posts/where', 'sitemap_where_clause', 10, 2 );

		$this->filter( 'pll_home_url_white_list', 'whitelist_home_url' );
		$this->filter( 'rank_math/frontend/canonical', 'fix_home_canonical' );
		$this->filter( 'rank_math/sitemap/xml_post_url', 'fix_front_page_sitemap_url', 10, 2 );
		$this->filter( 'rank_math/opengraph/facebook', 'add_og_alternate_locales' );

		if ( \PLL()->options['force_lang'] > 1 ) {
			// Rewrite the home URL for multi-domain sitemaps; disable caching to prevent cross-domain URLs.
			$this->filter( 'home_url', 'sitemap_home_url', 10, 2 );
			$this->filter( 'rank_math/sitemap/enable_caching', 'enable_sitemap_caching' );
		} else {
			// Fix taxonomy sitemap queries and inject language-specific homepage/archive entries.
			$this->filter( 'get_terms_args', 'update_term_query_args' );
			$this->filter( 'rank_math/sitemap/exclude_post_type', 'inject_language_sitemap_entries', 0, 2 );
		}
	}

	/**
	 * Register hooks active only in the admin.
	 */
	private function register_admin_hooks() {
		$this->filter( 'pll_copy_post_metas', 'sync_post_metas', 10, 4 );
		$this->filter( 'pll_translate_post_meta', 'translate_post_meta', 10, 3 );
		$this->filter( 'pll_post_metas_to_export', 'export_post_metas' );

		// Clear the sitemap cache when language settings change to prevent stale URLs.
		$this->action( 'pll_add_language', 'clear_sitemap_cache' );
		$this->action( 'pll_update_language', 'clear_sitemap_cache' );
		$this->action( 'delete_term', 'clear_sitemap_cache_on_language_delete', 10, 3 );
	}

	/**
	 * Register Rank Math option keys with Polylang for per-language values.
	 */
	private function register_option_translations() {
		$general_keys = [
			'breadcrumbs_separator',
			'breadcrumbs_prefix',
			'breadcrumbs_home_label',
			'breadcrumbs_archive_format',
			'breadcrumbs_search_format',
			'breadcrumbs_404_label',
			'content_ai_country',
			'content_ai_tone',
			'content_ai_audience',
			'content_ai_language',
			'toc_block_title',
		];
		new \PLL_Translate_Option( 'rank-math-options-general', array_fill_keys( $general_keys, 1 ), [ 'context' => 'rank-math' ] );

		$title_keys = [
			'website_name',
			'knowledgegraph_name',
			'homepage_title',
			'homepage_description',
			'homepage_facebook_title',
			'homepage_facebook_description',
			'author_archive_title',
			'author_archive_description',
			'date_archive_title',
			'date_archive_description',
			'search_title',
			'404_title',
		];

		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			$title_keys[] = "pt_{$post_type}_title";
			$title_keys[] = "pt_{$post_type}_description";
			$title_keys[] = "pt_{$post_type}_archive_title";
			$title_keys[] = "pt_{$post_type}_archive_description";
			$title_keys[] = "pt_{$post_type}_default_snippet_*";
		}

		new \PLL_Translate_Option( 'rank-math-options-titles', array_fill_keys( $title_keys, 1 ), [ 'context' => 'rank-math' ] );
	}

	/**
	 * Reset Rank Math's settings cache so translated option values are used.
	 */
	public static function maybe_reset_settings_cache() {
		if ( function_exists( 'pll_current_language' ) ) {
			rank_math()->settings->reset();
		}
	}

	/**
	 * The cache is keyed by post type only, so a sitemap cached for one domain
	 * would be served unchanged to every other domain, producing wrong URLs.
	 *
	 * @return false
	 */
	public function enable_sitemap_caching() {
		return false;
	}

	/**
	 * Invalidate the entire Rank Math sitemap cache.
	 */
	public function clear_sitemap_cache() {
		\RankMath\Sitemap\Cache_Watcher::clear();
	}

	/**
	 * `delete_term` fires for every taxonomy, so guard for `language` terms only.
	 *
	 * @param int    $term_id  Deleted term ID.
	 * @param int    $tt_id    Deleted term taxonomy ID.
	 * @param string $taxonomy Taxonomy the term belonged to.
	 */
	public function clear_sitemap_cache_on_language_delete( $term_id, $tt_id, $taxonomy ) {
		if ( 'language' === $taxonomy ) {
			$this->clear_sitemap_cache();
		}
	}

	/**
	 * Rewrite home_url to the current-language domain when serving sitemaps.
	 *
	 * @param string $url  URL being filtered.
	 * @param string $path URL path component.
	 * @return string
	 */
	public function sitemap_home_url( $url, $path ) {
		$uri = empty( $path )
			? ltrim( (string) wp_parse_url( pll_get_requested_url(), PHP_URL_PATH ), '/' )
			: $path;

		if ( 'sitemap_index.xml' === $uri || preg_match( '#([^/]+?)-sitemap([0-9]+)?\.xml|([a-z]+)?-?sitemap\.xsl#', $uri ) ) {
			$url = \PLL()->links_model->switch_language_in_link( $url, \PLL()->curlang );
		}

		return $url;
	}

	/**
	 * Add a JOIN clause for Polylang's language table to sitemap post queries.
	 *
	 * @param string $sql       Existing JOIN clause.
	 * @param string $post_type Post type being queried.
	 * @return string
	 */
	public function sitemap_join_clause( $sql, $post_type ) {
		return pll_is_translated_post_type( $post_type )
			? $sql . \PLL()->model->post->join_clause( 'p' )
			: $sql;
	}

	/**
	 * Add a WHERE clause to include only active-language posts in sitemap queries.
	 *
	 * @param string $sql       Existing WHERE clause.
	 * @param string $post_type Post type being queried.
	 * @return string
	 */
	public function sitemap_where_clause( $sql, $post_type ) {
		if ( ! pll_is_translated_post_type( $post_type ) ) {
			return $sql;
		}

		if ( \PLL()->options['force_lang'] > 1 && \PLL()->curlang instanceof \PLL_Language ) {
			return $sql . \PLL()->model->post->where_clause( \PLL()->curlang );
		}

		$languages = $this->get_active_language_slugs();
		if ( empty( $languages ) ) {
			$languages = pll_languages_list();
		}

		return $sql . \PLL()->model->post->where_clause( $languages );
	}

	/**
	 * Include all active-language terms in taxonomy sitemap queries.
	 *
	 * @param array $args get_terms() arguments.
	 * @return array
	 */
	public function update_term_query_args( $args ) {
		if ( isset( $GLOBALS['wp_query']->query['sitemap'] ) ) {
			$args['lang'] = implode( ',', $this->get_active_language_slugs() );
		}

		return $args;
	}

	/**
	 * Hooked at priority 0, before exclusion decisions; $exclude always passes through unchanged.
	 *
	 * @param bool   $exclude   Whether this post type is excluded from the sitemap.
	 * @param string $post_type Post type slug.
	 * @return bool  Unchanged $exclude value.
	 */
	public function inject_language_sitemap_entries( $exclude, $post_type ) {
		if ( ! pll_is_translated_post_type( $post_type ) || ( 'post' === $post_type && get_option( 'page_on_front' ) ) ) {
			return $exclude;
		}

		$this->filter( "rank_math/sitemap/{$post_type}_content", 'append_language_sitemap_entries' );

		return $exclude;
	}

	/**
	 * Post type is parsed from the filter tag so one method can serve every post-type hook.
	 *
	 * @param string $content Existing sitemap XML for this post type.
	 * @return string
	 */
	public function append_language_sitemap_entries( $content ) {
		preg_match( '#rank_math/sitemap/(.+)_content$#', current_filter(), $matches );
		$post_type = $matches[1] ?? '';
		if ( ! $post_type ) {
			return $content;
		}

		$entries = 'post' === $post_type
			? $this->home_language_sitemap_entries( $post_type )
			: $this->archive_language_sitemap_entries( $post_type );

		return $content . $entries;
	}

	/**
	 * Build sitemap <url> entries for the homepage in each active language.
	 *
	 * @param string $post_type Always 'post'.
	 * @return string
	 */
	private function home_language_sitemap_entries( $post_type ) {
		$generator = new \RankMath\Sitemap\Generator();
		$languages = wp_list_filter( \PLL()->model->get_languages_list(), [ 'active' => false ], 'NOT' );
		$mod       = \RankMath\Sitemap\Sitemap::get_last_modified_gmt( $post_type );

		if ( ! empty( \PLL()->options['hide_default'] ) ) {
			// The default-language homepage is already added by Rank Math.
			$languages = wp_list_filter( $languages, [ 'slug' => pll_default_language() ], 'NOT' );
		}

		$output = '';
		foreach ( $languages as $lang ) {
			$output .= $generator->sitemap_url(
				[
					'loc' => \pll_home_url( $lang->slug ),
					'mod' => $mod,
				]
			);
		}

		return $output;
	}

	/**
	 * Build sitemap <url> entries for a post-type archive in each active language.
	 *
	 * @param string $post_type Post type slug.
	 * @return string
	 */
	private function archive_language_sitemap_entries( $post_type ) {
		$post_type_obj = get_post_type_object( $post_type );
		if ( ! $post_type_obj->has_archive ) {
			return '';
		}

		$slug = true === $post_type_obj->has_archive
			? $post_type_obj->rewrite['slug']
			: $post_type_obj->has_archive;

		// Skip archive URLs that are backed by a WordPress page (e.g. WooCommerce shop).
		$page_for_slug = function_exists( 'wpcom_vip_get_page_by_path' )
			? wpcom_vip_get_page_by_path( $slug )
			: get_page_by_path( $slug );

		if ( $page_for_slug ) {
			return '';
		}

		$generator = new \RankMath\Sitemap\Generator();
		$languages = wp_list_filter( \PLL()->model->get_languages_list(), [ 'active' => false ], 'NOT' );
		$mod       = \RankMath\Sitemap\Sitemap::get_last_modified_gmt( $post_type );

		// The archive for the current language is already added by Rank Math.
		$languages = wp_list_filter( $languages, [ 'slug' => pll_current_language() ], 'NOT' );

		$original_curlang = \PLL()->curlang;
		$output           = '';
		foreach ( $languages as $lang ) {
			\PLL()->curlang = $lang;
			$output        .= $generator->sitemap_url(
				[
					'loc' => \PLL()->links_model->switch_language_in_link( get_post_type_archive_link( $post_type ), $lang ),
					'mod' => $mod,
				]
			);
		}
		\PLL()->curlang = $original_curlang;

		return $output;
	}

	/**
	 * Allow Rank Math to modify home URLs, needed for sitemap URL rewriting.
	 *
	 * @param array $allowlist Existing Polylang home URL allowlist.
	 * @return array
	 */
	public function whitelist_home_url( $allowlist ) {
		return array_merge( $allowlist, [ [ 'file' => 'seo-by-rank-math' ] ] );
	}

	/**
	 * Append the language path to the canonical URL for language homepages.
	 *
	 * @param string $canonical Canonical URL produced by Rank Math.
	 * @return string
	 */
	public function fix_home_canonical( $canonical ) {
		global $post;
		$post_id = (int) get_option( 'page_on_front' );
		if ( ! is_home() && ( empty( $post ) || $post->ID !== pll_get_post( $post_id ) ) ) {
			return $canonical;
		}

		$path = ltrim( (string) wp_parse_url( \pll_home_url( pll_current_language() ), PHP_URL_PATH ), '/' );
		return $canonical . $path;
	}

	/**
	 * Point the front page's sitemap entry at its language home URL, not its slug.
	 *
	 * @param string   $url  URL being written to the sitemap.
	 * @param \WP_Post $post Post object.
	 * @return string
	 */
	public function fix_front_page_sitemap_url( $url, $post ) {
		$page_on_front_id = (int) get_option( 'page_on_front' );
		if ( ! $page_on_front_id ) {
			return $url;
		}

		$lang = pll_get_post_language( $post->ID );
		if ( ! $lang ) {
			return $url;
		}

		if ( (int) $post->ID === (int) pll_get_post( $page_on_front_id, $lang ) ) {
			return \PLL()->links_model->switch_language_in_link( trailingslashit( get_option( 'home' ) ), \PLL()->model->get_language( $lang ) );
		}

		return $url;
	}

	/**
	 * Output og:locale:alternate tags for each language that has a translation
	 * of the current page.
	 */
	public function add_og_alternate_locales() {
		$og          = new \RankMath\OpenGraph\OpenGraph();
		$og->network = 'facebook';
		foreach ( $this->get_og_alternate_languages() as $locale ) {
			$og->tag( 'og:locale:alternate', $locale );
		}
	}

	/**
	 * Add Rank Math meta keys to the list copied when a post is duplicated or synced.
	 *
	 * @param string[] $metas Meta keys that will be copied.
	 * @param bool     $sync  True when syncing translations, false when duplicating.
	 * @param int      $from  Source post ID.
	 * @param int      $to    Target post ID.
	 * @return string[]
	 */
	public function sync_post_metas( $metas, $sync, $from, $to ) {
		if ( ! $sync ) {
			$metas   = array_merge( $metas, $this->translatable_meta_keys() );
			$metas[] = 'rank_math_facebook_image';
			$metas[] = 'rank_math_facebook_image_id';
			$metas[] = 'rank_math_twitter_use_facebook';
			$metas[] = 'rank_math_twitter_image';
			$metas[] = 'rank_math_twitter_image_id';
			$metas[] = 'rank_math_robots';
		}

		$sync_taxonomies = \PLL()->sync->taxonomies->get_taxonomies_to_copy( $sync, $from, $to );

		foreach ( array_intersect(
			get_taxonomies(
				[
					'hierarchical' => true,
					'public'       => true,
				]
			),
			$sync_taxonomies
		) as $taxonomy ) {
			$metas[] = 'rank_math_primary_' . $taxonomy;
		}

		return $metas;
	}

	/**
	 * Translate the primary-term meta value when syncing a post to another language.
	 *
	 * @param int    $value Term ID in the source language.
	 * @param string $key   Meta key.
	 * @param string $lang  Target language slug.
	 * @return int
	 */
	public function translate_post_meta( $value, $key, $lang ) {
		if ( ! Str::starts_with( 'rank_math_primary_', $key ) ) {
			return $value;
		}

		$taxonomy = str_replace( 'rank_math_primary_', '', $key );
		if ( ! \PLL()->model->is_translated_taxonomy( $taxonomy ) ) {
			return $value;
		}

		return pll_get_term( $value, $lang );
	}

	/**
	 * Include Rank Math's translatable meta keys in Polylang's export list.
	 *
	 * @param array $metas Existing export meta map (key => 1).
	 * @return array
	 */
	public function export_post_metas( $metas ) {
		return array_merge( $metas, array_fill_keys( $this->translatable_meta_keys(), 1 ) );
	}

	/**
	 * Return active language slugs, or an empty array when all languages are active.
	 *
	 * @return string[]
	 */
	private function get_active_language_slugs() {
		$languages          = \PLL()->model->get_languages_list();
		$inactive_languages = wp_list_filter( $languages, [ 'active' => false ] );
		if ( $inactive_languages ) {
			return wp_list_pluck( wp_list_filter( $languages, [ 'active' => false ], 'NOT' ), 'slug' );
		}

		return [];
	}

	/**
	 * Collect Facebook locale codes for every language with a translation of the current page.
	 *
	 * @return string[]
	 */
	private function get_og_alternate_languages() {
		$alternates = [];

		foreach ( \PLL()->model->get_languages_list() as $language ) {
			if (
				isset( \PLL()->curlang ) &&
				\PLL()->curlang->slug !== $language->slug &&
				\PLL()->links->get_translation_url( $language ) &&
				isset( $language->facebook )
			) {
				$alternates[] = $language->facebook;
			}
		}

		// Two languages can share the same Facebook locale; deduplicate.
		return array_unique( $alternates );
	}

	/**
	 * Meta keys whose values are language-specific (title, description, etc.).
	 *
	 * @return string[]
	 */
	private function translatable_meta_keys() {
		return [
			'rank_math_title',
			'rank_math_breadcrumb_title',
			'rank_math_description',
			'rank_math_facebook_title',
			'rank_math_facebook_description',
			'rank_math_twitter_title',
			'rank_math_twitter_description',
			'rank_math_focus_keyword',
		];
	}
}
