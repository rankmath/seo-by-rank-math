<?php
/**
 * The Polylang helpers.
 *
 * @since      1.0.279
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Polylang class.
 */
class Polylang {

	/**
	 * Build the `/{lang}/` rewrite rule variants for a translated category, which Polylang can't add itself.
	 *
	 * @param int         $term_id     Category term ID.
	 * @param string      $cat_slug    Category slug or path used in the plain rule.
	 * @param string      $product_cat The `product_cat` query var value.
	 * @param \WP_Rewrite $wp_rewrite  WP_Rewrite instance, for its feed/pagination bases.
	 * @param string      $feed        Feed regex alternation, e.g. `(feed|rdf|rss|rss2|atom)`.
	 *
	 * @return array<string, string> Rules to merge in, empty when no prefix is needed.
	 */
	public static function get_category_rewrite_rules( $term_id, $cat_slug, $product_cat, $wp_rewrite, $feed ) {
		$language = self::get_rewrite_language( $term_id );
		if ( empty( $language ) ) {
			return [];
		}

		$prefix = $language['path'];
		$query  = 'lang=' . $language['lang'] . '&product_cat=' . $product_cat;

		return [
			"{$prefix}/{$cat_slug}/?\$"         => "index.php?{$query}",
			"{$prefix}/{$cat_slug}/embed/?\$"   => "index.php?{$query}&embed=true",
			"{$prefix}/{$cat_slug}/{$wp_rewrite->feed_base}/{$feed}/?\$" => "index.php?{$query}&feed=\$matches[1]",
			"{$prefix}/{$cat_slug}/{$feed}/?\$" => "index.php?{$query}&feed=\$matches[1]",
			"{$prefix}/{$cat_slug}/{$wp_rewrite->pagination_base}/?([0-9]{1,})/?\$" => "index.php?{$query}&paged=\$matches[1]",
		];
	}

	/**
	 * Get a term's language prefix, mirroring `PLL_Links_Directory::add_language_to_link()`.
	 *
	 * @param int $term_id Term ID.
	 *
	 * @return array{lang: string, path: string}|array{} `lang` for the query var, `path` for the rule pattern. Empty when no prefix is needed.
	 */
	private static function get_rewrite_language( $term_id ) {
		$pll = self::get_instance();
		if ( ! $pll ) {
			return [];
		}

		// Only directory mode ("/es/...") needs a prefix; 0 and 1 both map to it - see PLL_Model::get_links_model().
		if ( ! in_array( (int) $pll->options['force_lang'], [ 0, 1 ], true ) ) {
			return [];
		}

		$lang = pll_get_term_language( $term_id );
		if ( ! $lang ) {
			return [];
		}

		if ( pll_default_language() === $lang && $pll->options['hide_default'] ) {
			return [];
		}

		$path = $pll->options['rewrite'] ? $lang : "language/{$lang}";

		return [
			'lang' => $lang,
			'path' => $path,
		];
	}

	/**
	 * Stop Polylang limiting term queries to the current language. Pair with `restore_term_filters()`.
	 *
	 * @return void
	 */
	public static function remove_term_filters() {
		if ( ! self::get_instance() ) {
			return;
		}

		add_filter( 'get_terms_args', [ __CLASS__, 'unset_term_query_language' ], 5 );
	}

	/**
	 * Restore Polylang's term query language filter.
	 *
	 * @return void
	 */
	public static function restore_term_filters() {
		if ( ! self::get_instance() ) {
			return;
		}

		remove_filter( 'get_terms_args', [ __CLASS__, 'unset_term_query_language' ], 5 );
	}

	/**
	 * Force a term query to span every language.
	 *
	 * @param array $args WP_Term_Query arguments.
	 *
	 * @return array
	 */
	public static function unset_term_query_language( $args ) {
		$args['lang'] = '';

		return $args;
	}

	/**
	 * Warm the term-language cache in one query, as Polylang's own `_prime_terms_cache()` does.
	 *
	 * @param int[] $term_ids Term IDs.
	 *
	 * @return void
	 */
	public static function prime_term_language_cache( array $term_ids ) {
		if ( empty( $term_ids ) || ! self::get_instance() ) {
			return;
		}

		update_object_term_cache( $term_ids, 'term' );
	}

	/**
	 * Get the Polylang instance, only when it's active and safe to use.
	 *
	 * @return \PLL_Base|null
	 */
	private static function get_instance() {
		if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_get_term_language' ) || ! function_exists( 'pll_default_language' ) ) {
			return null;
		}

		$pll = \PLL();

		return $pll instanceof \PLL_Base ? $pll : null;
	}
}
