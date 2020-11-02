<?php
/**
 * The Post_Type helpers.
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
 * Post Type class.
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */
trait Post_Type {

	/**
	 * Check if post is indexable.
	 *
	 * @param int $post_id Post ID to check.
	 *
	 * @return boolean
	 */
	public static function is_post_indexable( $post_id ) {
		if ( true === self::is_post_excluded( $post_id ) ) {
			return false;
		}

		$robots = self::is_post_meta_indexable( $post_id );
		if ( is_bool( $robots ) ) {
			return $robots;
		}

		$post_type = get_post_type( $post_id );
		$robots    = Helper::get_settings( 'titles.pt_' . $post_type . '_custom_robots' );
		$robots    = false === $robots ? Helper::get_settings( 'titles.robots_global' ) : Helper::get_settings( 'titles.pt_' . $post_type . '_robots' );

		return in_array( 'noindex', (array) $robots, true ) ? false : true;
	}

	/**
	 * Check if post is indexable by meta.
	 *
	 * @param int $post_id Post ID to check.
	 *
	 * @return boolean
	 */
	private static function is_post_meta_indexable( $post_id ) {
		$robots = Helper::get_post_meta( 'robots', $post_id );
		if ( empty( $robots ) || ! is_array( $robots ) ) {
			return '';
		}

		if ( in_array( 'index', $robots, true ) ) {
			return true;
		}

		return in_array( 'noindex', $robots, true ) ? false : '';
	}

	/**
	 * Check if post is explicitly excluded.
	 *
	 * @param  int $post_id Post ID to check.
	 * @return bool
	 */
	public static function is_post_excluded( $post_id ) {
		static $posts_to_exclude;

		if ( ! isset( $posts_to_exclude ) ) {
			$posts_to_exclude = wp_parse_id_list( Helper::get_settings( 'sitemap.exclude_posts' ) );
			$posts_to_exclude = apply_filters( 'rank_math/sitemap/posts_to_exclude', $posts_to_exclude );
		}

		return in_array( $post_id, $posts_to_exclude, true );
	}

	/**
	 * Check if post type is indexable.
	 *
	 * @param  string $post_type Post type to check.
	 * @return bool
	 */
	public static function is_post_type_indexable( $post_type ) {
		if ( Helper::get_settings( 'titles.pt_' . $post_type . '_custom_robots' ) ) {
			if ( in_array( 'noindex', (array) Helper::get_settings( 'titles.pt_' . $post_type . '_robots' ), true ) ) {
				return false;
			}
		}

		return Helper::get_settings( 'sitemap.pt_' . $post_type . '_sitemap' );
	}

	/**
	 * Check if post type is accessible.
	 *
	 * @param  string $post_type Post type to check.
	 * @return bool
	 */
	public static function is_post_type_accessible( $post_type ) {
		return in_array( $post_type, self::get_allowed_post_types(), true );
	}

	/**
	 * Get the post type label.
	 *
	 * @param  string $post_type Post type name.
	 * @param  bool   $singular  Get singular label.
	 * @return string|false
	 */
	public static function get_post_type_label( $post_type, $singular = false ) {
		$object = get_post_type_object( $post_type );
		if ( ! $object ) {
			return false;
		}
		return ! $singular ? $object->labels->name : $object->labels->singular_name;
	}

	/**
	 * Get post types that are public and not set to noindex.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array All the accessible post types.
	 */
	public static function get_accessible_post_types() {
		static $accessible_post_types;

		if ( isset( $accessible_post_types ) && did_action( 'wp_loaded' ) ) {
			return $accessible_post_types;
		}

		$accessible_post_types = get_post_types( [ 'public' => true ] );
		$accessible_post_types = array_filter( $accessible_post_types, 'is_post_type_viewable' );

		/**
		 * Changing the list of accessible post types.
		 *
		 * @api array $accessible_post_types The post types.
		 */
		$accessible_post_types = apply_filters( 'rank_math/excluded_post_types', $accessible_post_types );

		if ( ! is_array( $accessible_post_types ) ) {
			$accessible_post_types = [];
		}

		return $accessible_post_types;
	}

	/**
	 * Get accessible post types.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function get_allowed_post_types() {
		static $rank_math_allowed_post_types;

		if ( isset( $rank_math_allowed_post_types ) ) {
			return $rank_math_allowed_post_types;
		}

		$rank_math_allowed_post_types = [];
		foreach ( self::get_accessible_post_types() as $post_type ) {
			if ( false === apply_filters( 'rank_math/metabox/add_seo_metabox', Helper::get_settings( 'titles.pt_' . $post_type . '_add_meta_box', true ) ) ) {
				continue;
			}

			$rank_math_allowed_post_types[] = $post_type;
		}

		return $rank_math_allowed_post_types;
	}

	/**
	 * Whether to use default schema.
	 *
	 * @param  int $post_id Post ID.
	 * @return bool
	 */
	public static function can_use_default_schema( $post_id ) {
		$pages = array_map(
			'absint',
			array_filter(
				[
					Helper::get_settings( 'titles.local_seo_about_page' ),
					Helper::get_settings( 'titles.local_seo_contact_page' ),
					get_option( 'page_for_posts' ),
				]
			)
		);

		return ! in_array( (int) $post_id, $pages, true );
	}

	/**
	 * Whether to use default Product schema on WooCommerce pages.
	 *
	 * @return bool
	 */
	public static function can_use_default_product_schema() {
		return apply_filters( 'rank_math/schema/use_default_product', true );
	}
}
