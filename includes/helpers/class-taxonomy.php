<?php
/**
 * The Taxonomy helpers.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomy class.
 */
trait Taxonomy {

	/**
	 * Is term indexable.
	 *
	 * @param WP_Term $term Term to check.
	 *
	 * @return boolean
	 */
	public static function is_term_indexable( $term ) {
		$robots = self::is_term_meta_indexable( $term );
		if ( is_bool( $robots ) ) {
			return $robots;
		}

		$robots = Helper::get_settings( 'titles.tax_' . $term->taxonomy . '_custom_robots' );
		$robots = false === $robots ? Helper::get_settings( 'titles.robots_global' ) : Helper::get_settings( 'titles.tax_' . $term->taxonomy . '_robots' );

		return in_array( 'noindex', (array) $robots, true ) ? false : true;
	}

	/**
	 * Is term indexable by meta.
	 *
	 * @param WP_Term $term Term to check.
	 *
	 * @return boolean
	 */
	private static function is_term_meta_indexable( $term ) {
		$robots = Helper::get_term_meta( 'robots', $term, $term->taxonomy );
		if ( empty( $robots ) || ! is_array( $robots ) ) {
			return '';
		}

		if ( in_array( 'index', $robots, true ) ) {
			return true;
		}

		return in_array( 'noindex', $robots, true ) ? false : '';
	}

	/**
	 * Check if taxonomy is indexable.
	 *
	 * @param string $taxonomy Taxonomy to check.
	 *
	 * @return bool
	 */
	public static function is_taxonomy_indexable( $taxonomy ) {
		if ( Helper::get_settings( 'titles.tax_' . $taxonomy . '_custom_robots' ) ) {
			if ( in_array( 'noindex', (array) Helper::get_settings( 'titles.tax_' . $taxonomy . '_robots' ), true ) ) {
				return false;
			}
		}

		return Helper::get_settings( 'sitemap.tax_' . $taxonomy . '_sitemap' );
	}

	/**
	 * Get the taxonomies that are public and not set to noindex.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array All the accessible taxonomies.
	 */
	public static function get_accessible_taxonomies() {
		static $accessible_taxonomies;

		if ( isset( $accessible_taxonomies ) && did_action( 'wp_loaded' ) ) {
			return $accessible_taxonomies;
		}

		$accessible_taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$accessible_taxonomies = self::filter_exclude_taxonomies( $accessible_taxonomies );

		if ( ! is_array( $accessible_taxonomies ) ) {
			$accessible_taxonomies = [];
		}

		return $accessible_taxonomies;
	}

	/**
	 * Get accessible taxonomies.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function get_allowed_taxonomies() {
		static $rank_math_allowed_taxonomies;

		if ( isset( $rank_math_allowed_taxonomies ) ) {
			return $rank_math_allowed_taxonomies;
		}

		$rank_math_allowed_taxonomies = [];
		foreach ( self::get_accessible_taxonomies() as $taxonomy => $object ) {
			if ( false === Helper::get_settings( 'titles.tax_' . $taxonomy . '_add_meta_box' ) ) {
				continue;
			}

			$rank_math_allowed_taxonomies[] = $taxonomy;
		}

		return $rank_math_allowed_taxonomies;
	}

	/**
	 * Get taxonomies attached to a post type.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string  $post_type Post type to get taxonomy data for.
	 * @param string  $output    (Optional) Output type can be `names`, `objects`, `choices`.
	 * @param boolean $filter    (Optional) Whether to filter taxonomies.
	 *
	 * @return boolean|array
	 */
	public static function get_object_taxonomies( $post_type, $output = 'choices', $filter = true ) {
		if ( 'names' === $output ) {
			return get_object_taxonomies( $post_type );
		}

		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		$taxonomies = self::filter_exclude_taxonomies( $taxonomies, $filter );

		if ( 'objects' === $output ) {
			return $taxonomies;
		}

		return empty( $taxonomies ) ? false : [ 'off' => esc_html__( 'None', 'rank-math' ) ] + wp_list_pluck( $taxonomies, 'label', 'name' );
	}

	/**
	 * Filter taxonomies using
	 *        `is_taxonomy_viewable` function
	 *        'rank_math_excluded_taxonomies' filter
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array|object $taxonomies Collection of taxonomies to filter.
	 * @param boolean      $filter     (Optional) Whether to filter taxonomies.
	 *
	 * @return array|object
	 */
	public static function filter_exclude_taxonomies( $taxonomies, $filter = true ) {
		$taxonomies = $filter ? array_filter( $taxonomies, [ __CLASS__, 'is_taxonomy_viewable' ] ) : $taxonomies;

		/**
		 * Filter: 'rank_math_excluded_taxonomies' - Allow changing the accessible taxonomies.
		 *
		 * @api array $taxonomies The public taxonomies.
		 */
		$taxonomies = apply_filters( 'rank_math/excluded_taxonomies', $taxonomies );

		return $taxonomies;
	}

	/**
	 * Determine whether a taxonomy is viewable.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string|WP_Taxonomy $taxonomy Taxonomy name or object.
	 *
	 * @return bool
	 */
	public static function is_taxonomy_viewable( $taxonomy ) {
		if ( is_scalar( $taxonomy ) ) {
			$taxonomy = get_taxonomy( $taxonomy );
			if ( ! $taxonomy ) {
				return false;
			}
		}

		if ( isset( $taxonomy->name ) && 'wp_pattern_category' === $taxonomy->name ) {
			return false;
		}

		/*
		 * For categories and tags, we check for the 'public' parameter.
		 * For others, we use the 'publicly_queryable' parameter.
		 */
		return $taxonomy->publicly_queryable || ( $taxonomy->_builtin && $taxonomy->public );
	}
}
