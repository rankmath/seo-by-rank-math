<?php
/**
 * This class handles the category and author rewrites.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath;

use RankMath\Traits\Hooker;
use RankMath\Helpers\Sitepress;
use RankMath\Helpers\DB as DB_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Rewrite class.
 */
class Rewrite {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( Helper::get_settings( 'general.strip_category_base' ) ) {
			$this->filter( 'query_vars', 'query_vars' );
			$this->filter( 'request', 'request' );
			$this->filter( 'category_rewrite_rules', 'category_rewrite_rules' );
			$this->filter( 'term_link', 'no_category_base', 10, 3 );

			add_action( 'created_category', 'RankMath\\Helper::schedule_flush_rewrite' );
			add_action( 'delete_category', 'RankMath\\Helper::schedule_flush_rewrite' );
			add_action( 'edited_category', 'RankMath\\Helper::schedule_flush_rewrite' );
		}

		if ( ! Helper::get_settings( 'titles.disable_author_archives' ) ) {
			if ( ! empty( Helper::get_settings( 'titles.url_author_base' ) ) ) {
				add_action( 'init', 'RankMath\\Rewrite::change_author_base', 4 );
			}

			$this->filter( 'author_link', 'author_link', 10, 3 );
			$this->filter( 'request', 'author_request' );
		}
	}

	/**
	 * Change the URL to the author's page.
	 *
	 * @param  string $link            The URL to the author's page.
	 * @param  int    $author_id       The author's ID.
	 * @param  string $author_nicename The author's nice name.
	 * @return string
	 */
	public function author_link( $link, $author_id, $author_nicename ) {
		$custom_url = get_user_meta( $author_id, 'rank_math_permalink', true );
		if ( $custom_url ) {
			$link = str_replace( $author_nicename, $custom_url, $link );
		}

		return $link;
	}

	/**
	 * Redirect the old user permalink to the new one.
	 *
	 * @param  array $query_vars Query vars to check for author_name var.
	 *
	 * @return array
	 */
	public function author_request( $query_vars ) {
		global $wpdb;

		if ( ! array_key_exists( 'author_name', $query_vars ) ) {
			return $query_vars;
		}

		$author_id = DB_Helper::get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key='rank_math_permalink' AND meta_value = %s", $query_vars['author_name'] ) );
		if ( $author_id ) {
			$query_vars['author'] = $author_id;
			unset( $query_vars['author_name'] );
		}

		return $query_vars;
	}

	/**
	 * Remove the rewrite rules.
	 */
	public function remove_rules() {
		$this->remove_filter( 'query_vars', 'query_vars' );
		$this->remove_filter( 'request', 'request' );
		$this->remove_filter( 'category_rewrite_rules', 'category_rewrite_rules' );
		$this->remove_filter( 'term_link', 'no_category_base', 10 );

		remove_action( 'init', 'RankMath\\Rewrite::change_author_base', 4 );
	}

	/**
	 * Change the base for author permalinks.
	 */
	public static function change_author_base() {
		global $wp_rewrite;

		/**
		 * Filter: Change the author base.
		 *
		 * @param string $base The author base.
		 */
		$base = apply_filters( 'rank_math/author_base', sanitize_title_with_dashes( Helper::get_settings( 'titles.url_author_base' ), '', 'save' ) );
		if ( empty( $base ) ) {
			return;
		}

		$wp_rewrite->author_base      = $base;
		$wp_rewrite->author_structure = '/' . $wp_rewrite->author_base . '/%author%';
	}

	/**
	 * Add the redirect var to the query vars if the "strip category bases" option is enabled.
	 *
	 * @param  array $query_vars Query vars to filter.
	 *
	 * @return array
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'rank_math_category_redirect';

		return $query_vars;
	}

	/**
	 * Redirect the original category URL to the new one.
	 *
	 * @param  array $query_vars Query vars to check for redirect var.
	 * @return array
	 */
	public function request( $query_vars ) {
		if ( isset( $query_vars['rank_math_category_redirect'] ) ) {
			$catlink = trailingslashit( get_option( 'home' ) ) . user_trailingslashit( $query_vars['rank_math_category_redirect'], 'category' );
			Helper::redirect( $catlink, 301 );
			exit;
		}

		return $query_vars;
	}

	/**
	 * This function was taken and slightly adapted from WP No Category Base plugin by Saurabh Gupta.
	 *
	 * @return array
	 */
	public function category_rewrite_rules() {
		global $wp_rewrite;

		$category_rewrite = $this->get_category_rules();

		$old_base                            = str_replace( '%category%', '(.+)', $wp_rewrite->get_category_permastruct() );
		$old_base                            = trim( $old_base, '/' );
		$category_rewrite[ $old_base . '$' ] = 'index.php?rank_math_category_redirect=$matches[1]';

		return $category_rewrite;
	}

	/**
	 * Remove the category base from the category link.
	 *
	 * @param  string $link     Term link.
	 * @param  object $term     Current Term Object.
	 * @param  string $taxonomy Current Taxonomy.
	 * @return string
	 */
	public function no_category_base( $link, $term, $taxonomy ) {
		if ( 'category' !== $taxonomy ) {
			return $link;
		}

		$category_base = get_option( 'category_base' );
		if ( empty( $category_base ) ) {
			global $wp_rewrite;
			$category_base = trim( str_replace( '%category%', '', $wp_rewrite->get_category_permastruct() ), '/' );
		}

		// Remove initial slash, if there is one (we remove the trailing slash in the regex replacement and don't want to end up short a slash).
		if ( '/' === substr( $category_base, 0, 1 ) ) {
			$category_base = substr( $category_base, 1 );
		}

		$category_base .= '/';

		return preg_replace( '`' . preg_quote( $category_base, '`' ) . '`u', '', $link, 1 );
	}
	/**
	 * Get category re-write rules.
	 *
	 * @return array
	 */
	private function get_category_rules() {
		global $wp_rewrite;

		$category_rewrite = [];
		$categories       = $this->get_categories();
		$blog_prefix      = $this->get_blog_prefix();

		if ( empty( $categories ) ) {
			return $category_rewrite;
		}

		foreach ( $categories as $category ) {
			$category_nicename = $this->get_category_parents( $category ) . $category->slug;
			$category_rewrite  = $this->add_category_rewrites( $category_rewrite, $category_nicename, $blog_prefix, $wp_rewrite->pagination_base );

			// Add rules for upper case encoded nicename.
			$category_nicename_filtered = $this->convert_encoded_to_upper( $category_nicename );

			if ( $category_nicename !== $category_nicename_filtered ) {
				$category_rewrite = $this->add_category_rewrites( $category_rewrite, $category_nicename_filtered, $blog_prefix, $wp_rewrite->pagination_base );
			}
		}

		return $category_rewrite;
	}

	/**
	 * Adds required category rewrites rules.
	 *
	 * @param array  $category_rewrite   The current set of rules.
	 * @param string $category_nicename   Category nicename.
	 * @param string $blog_prefix     Multisite blog prefix.
	 * @param string $pagination_base WP_Query pagination base.
	 *
	 * @return array The added set of rules.
	 */
	private function add_category_rewrites( $category_rewrite, $category_nicename, $blog_prefix, $pagination_base ) {

		$category_rewrite[ $blog_prefix . '(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$' ]    = 'index.php?category_name=$matches[1]&feed=$matches[2]';
		$category_rewrite[ $blog_prefix . '(' . $category_nicename . ')/' . $pagination_base . '/?([0-9]{1,})/?$' ] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
		$category_rewrite[ $blog_prefix . '(' . $category_nicename . ')/?$' ]                                       = 'index.php?category_name=$matches[1]';

		return $category_rewrite;
	}

	/**
	 * Walks through category nicename and convert encoded parts
	 * into uppercase using $this->encode_to_upper().
	 *
	 * @param string $name The encoded category URI string.
	 *
	 * @return string The converted URI string.
	 */
	private function convert_encoded_to_upper( $name ) {
		// Checks if name has any encoding in it.
		if ( strpos( $name, '%' ) === false ) {
			return $name;
		}

		$names = explode( '/', $name );
		$names = array_map( [ $this, 'encode_to_upper' ], $names );

		return implode( '/', $names );
	}

	/**
	 * Converts the encoded URI string to uppercase.
	 *
	 * @param string $encoded The encoded string.
	 *
	 * @return string The uppercased string.
	 */
	private function encode_to_upper( $encoded ) {
		if ( strpos( $encoded, '%' ) === false ) {
			return $encoded;
		}

		return strtoupper( $encoded );
	}

	/**
	 * Retrieve category parents with separator.
	 *
	 * @param WP_Term $category Category instance.
	 *
	 * @return string
	 */
	private function get_category_parents( $category ) {
		if ( $category->parent === $category->cat_ID || absint( $category->parent ) < 1 ) {
			return '';
		}

		$parents = get_category_parents( $category->parent, false, '/', true );
		return is_wp_error( $parents ) ? '' : $parents;
	}

	/**
	 * Get categories with WPML compatibility.
	 *
	 * @return array
	 */
	private function get_categories() {
		/**
		 * Remove WPML filters while getting terms, to get all languages
		 */
		Sitepress::get()->remove_term_filters();

		$categories = get_categories( [ 'hide_empty' => false ] );

		/**
		 * Register WPML filters back
		 */
		Sitepress::get()->restore_term_filters();

		return $categories;
	}

	/**
	 * Get the blog prefix.
	 *
	 * @return string
	 */
	private function get_blog_prefix() {
		$permalink_structure = get_option( 'permalink_structure' );
		if ( is_multisite() && ! is_subdomain_install() && is_main_site() && 0 === strpos( $permalink_structure, '/blog/' ) ) {
			return 'blog/';
		}

		return '';
	}
}
