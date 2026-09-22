<?php
/**
 * The Sitepress helpers.
 *
 * @since      1.0.40
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Sitepress class.
 */
class Sitepress {

	/**
	 * Has filter removed.
	 *
	 * @var boolean
	 */
	private $has_get_category = false;

	/**
	 * Has filter removed.
	 *
	 * @var boolean
	 */
	private $has_get_term = false;

	/**
	 * Has filter removed.
	 *
	 * @var boolean
	 */
	private $has_terms_clauses = false;

	/**
	 * Has filter removed.
	 *
	 * @var boolean
	 */
	private $has_get_terms_args = false;

	/**
	 * Has home_url filter removed.
	 *
	 * @var boolean
	 */
	private $has_home_url = false;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Sitepress
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Sitepress ) ) {
			$instance = new Sitepress();
		}

		return $instance;
	}

	/**
	 * Remove term filters.
	 */
	public function remove_term_filters() {
		if ( ! $this->is_active() ) {
			return;
		}

		$sitepress = $this->get_var();

		$this->has_get_category   = remove_filter( 'category_link', [ $sitepress, 'category_link_adjust_id' ], 1 );
		$this->has_get_term       = remove_filter( 'get_term', [ $sitepress, 'get_term_adjust_id' ], 1 );
		$this->has_terms_clauses  = remove_filter( 'terms_clauses', [ $sitepress, 'terms_clauses' ] );
		$this->has_get_terms_args = remove_filter( 'get_terms_args', [ $sitepress, 'get_terms_args_filter' ] );
	}

	/**
	 * Restore term filters.
	 */
	public function restore_term_filters() {
		if ( ! $this->is_active() ) {
			return;
		}

		$sitepress = $this->get_var();

		if ( $this->has_get_category ) {
			$this->has_get_category = false;
			add_filter( 'category_link', [ $sitepress, 'category_link_adjust_id' ], 1, 1 );
		}

		if ( $this->has_get_term ) {
			$this->has_get_term = false;
			add_filter( 'get_term', [ $sitepress, 'get_term_adjust_id' ], 1, 1 );
		}

		if ( $this->has_terms_clauses ) {
			$this->has_terms_clauses = false;
			add_filter( 'terms_clauses', [ $sitepress, 'terms_clauses' ], 10, 3 );
		}

		if ( $this->has_get_terms_args ) {
			$this->has_get_terms_args = false;
			add_filter( 'get_terms_args', [ $sitepress, 'get_terms_args_filter' ], 10, 2 );
		}
	}

	/**
	 * Remove home_url filter.
	 */
	public function remove_home_url_filter() {
		if ( ! $this->is_active() ) {
			return;
		}

		global $wpml_url_filters;
		$this->has_home_url = remove_filter( 'home_url', [ $wpml_url_filters, 'home_url_filter' ], -10 );
	}

	/**
	 * Restore home_url filter.
	 */
	public function restore_home_url_filter() {
		if ( ! $this->is_active() ) {
			return;
		}

		if ( $this->has_home_url ) {
			global $wpml_url_filters;
			$this->has_home_url = false;
			add_filter( 'home_url', [ $wpml_url_filters, 'home_url_filter' ], -10, 4 );
		}
	}

	/**
	 * Is plugin active.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return isset( $GLOBALS['sitepress'] );
	}

	/**
	 * Get sitepress global variable.
	 *
	 * @return object|null
	 */
	public function get_var() {
		return ! empty( $GLOBALS['sitepress'] ) ? $GLOBALS['sitepress'] : null;
	}

	/**
	 * Delete cached tax permalink.
	 *
	 * @param int    $term_id The term ID.
	 * @param string $taxonomy The taxonomy name.
	 * @return void
	 */
	public function delete_cached_tax_permalink( $term_id, $taxonomy ) {
		if ( ! $this->is_active() ) {
			return;
		}

		wp_cache_delete(
			md5( wp_json_encode( [ $term_id, $taxonomy, false ] ) ),
			'icl_tax_permalink_filter'
		);
	}

	/**
	 * Get permalink for a post, resolved in the post's own language.
	 *
	 * Temporarily switches WPML's active language to the post's language before
	 * calling get_permalink(), then restores it. This avoids returning the wrong
	 * language's URL when the active request language differs from the post's
	 * language, e.g. in background/async jobs or REST/MCP requests. For
	 * separate-domain installs, the scheme+host is also stripped so the caller
	 * can rebuild the URL against the correct language domain.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	public function get_permalink( $post_id ) {
		if ( ! $this->is_active() ) {
			return get_permalink( $post_id );
		}

		$sitepress = $this->get_var();

		$details  = apply_filters( 'wpml_post_language_details', null, $post_id );
		$code     = $details['language_code'] ?? '';
		$current  = $sitepress->get_current_language();
		$switched = $code && $code !== $current;

		if ( $switched ) {
			$sitepress->switch_lang( $code, true );
		}

		try {
			$permalink = get_permalink( $post_id );
		} finally {
			if ( $switched ) {
				$sitepress->switch_lang( $current, true );
			}
		}

		$language_domains = $sitepress->get_setting( 'language_domains', [] );
		if ( $language_domains ) {
			$permalink = apply_filters( 'wpml_permalink', $permalink, $code );
			foreach ( $language_domains as $domain ) {
				$permalink = preg_replace( "#https?://{$domain}#i", '', $permalink );
			}
		}

		return $permalink;
	}

	/**
	 * Is per domain negotiation type.
	 *
	 * @return string
	 */
	public function is_per_domain() {
		if ( ! $this->is_active() ) {
			return false;
		}

		$sitepress = $this->get_var();
		$type      = (int) $sitepress->get_setting( 'language_negotiation_type', 0 );

		if ( absint( $type ) === 2 ) {
			return true;
		}

		return false;
	}
}
