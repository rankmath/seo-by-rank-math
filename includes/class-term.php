<?php
/**
 * The Term Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use WP_Term;

defined( 'ABSPATH' ) || exit;

/**
 * Term class.
 */
class Term extends Metadata {

	/**
	 * Type of object metadata is for
	 *
	 * @var string
	 */
	protected $meta_type = 'term';

	/**
	 * Retrieve Term instance.
	 *
	 * @param mixed  $term     Term to get either (string) term name, (int) term id or (object) term.
	 * @param string $taxonomy Optional. Limit matched terms to those matching `$taxonomy`. Only used for
	 *                         disambiguating potentially shared terms.
	 * @return Term|false Term object, false otherwise.
	 */
	public static function get( $term = 0, $taxonomy = null ) {
		$term = self::get_term_id( $term, $taxonomy );
		if ( empty( $term ) ) {
			return null;
		}

		if ( isset( self::$objects[ $term ] ) && 'term' === self::$objects[ $term ]->meta_type ) {
			return self::$objects[ $term ];
		}

		$_term                  = new self( WP_Term::get_instance( $term, $taxonomy ) );
		$_term->object_id       = $term;
		self::$objects[ $term ] = $_term;

		return $_term;
	}

	/**
	 * Get term id
	 *
	 * @param mixed  $term     Term to get either (string) term name, (int) term id or (object) term.
	 * @param string $taxonomy Optional. Limit matched terms to those matching `$taxonomy`. Only used for
	 *                         disambiguating potentially shared terms.
	 */
	private static function get_term_id( $term = 0, $taxonomy = null ) {
		if ( is_string( $term ) ) {
			$term = get_term_by( 'slug', $term, $taxonomy );
		} elseif ( is_int( $term ) && 0 === absint( $term ) ) {
			$term = $GLOBALS['wp_query']->get_queried_object();
		}

		if ( is_object( $term ) && isset( $term->term_id ) ) {
			return $term->term_id;
		}

		return false;
	}

	/**
	 * Get term meta value.
	 *
	 * @param string $key      Meta key.
	 * @param mixed  $term     Term name, term ID, or term object.
	 * @param string $taxonomy Optional. Limit matched terms to those matching `$taxonomy`. Only used for
	 *                         disambiguating potentially shared terms.
	 * @return mixed
	 */
	public static function get_meta( $key, $term = 0, $taxonomy = null ) {
		$term = self::get( $term, $taxonomy );

		if ( is_null( $term ) || ! $term->is_found() ) {
			return '';
		}

		return $term->get_metadata( $key );
	}

	/**
	 * Check if the current query is for multiple terms (e.g. /term-1,term-2/).
	 *
	 * @return bool
	 */
	public static function is_multiple_terms_query() {
		global $wp_query;

		if ( ! is_tax() && ! is_tag() && ! is_category() ) {
			return false;
		}

		$term          = get_queried_object();
		$queried_terms = $wp_query->tax_query->queried_terms;

		if ( empty( $queried_terms[ $term->taxonomy ]['terms'] ) ) {
			return false;
		}

		return count( $queried_terms[ $term->taxonomy ]['terms'] ) > 1;
	}
}
