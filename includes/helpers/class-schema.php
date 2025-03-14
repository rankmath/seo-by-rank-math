<?php
/**
 * The Schema helpers.
 *
 * @since      1.0.62
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Schema class.
 */
trait Schema {
	/**
	 * Function to get Default Schema type by post_type.
	 *
	 * @param int     $post_id      Post ID.
	 * @param boolean $return_valid Whether to return valid schema type which can be used on the frontend.
	 * @param boolean $sanitize     Return sanitized Schema type.
	 *
	 * @return string Default Schema Type.
	 */
	public static function get_default_schema_type( $post_id, $return_valid = false, $sanitize = false ) {
		if ( metadata_exists( 'post', $post_id, 'rank_math_rich_snippet' ) || ! self::can_use_default_schema( $post_id ) ) {
			return false;
		}

		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, Helper::get_accessible_post_types(), true ) ) {
			return false;
		}

		$schema = Helper::get_settings( "titles.pt_{$post_type}_default_rich_snippet" );
		if ( ! $schema ) {
			return false;
		}

		if ( 'article' === $schema ) {
			/**
			 * Filter: Allow changing the default schema type.
			 *
			 * @param string $schema    Schema type.
			 * @param string $post_type Post type.
			 * @param int    $post_id   Post ID.
			 */
			$schema = apply_filters(
				'rank_math/schema/default_type',
				Helper::get_settings( "titles.pt_{$post_type}_default_article_type" ),
				$post_type,
				$post_id
			);
		}

		if ( class_exists( 'WooCommerce' ) && 'product' === $post_type ) {
			$schema = 'WooCommerceProduct';
		}

		if ( class_exists( 'Easy_Digital_Downloads' ) && 'download' === $post_type ) {
			$schema = 'EDDProduct';
		}

		if ( $return_valid && ! in_array( $schema, [ 'Article', 'NewsArticle', 'BlogPosting', 'WooCommerceProduct', 'EDDProduct' ], true ) ) {
			return false;
		}

		return $sanitize ? self::sanitize_schema_title( $schema ) : $schema;
	}

	/**
	 * Sanitize schema title.
	 *
	 * @param  string  $schema    Schema.
	 * @param  boolean $translate Whether to return the translated string.
	 * @return string
	 */
	public static function sanitize_schema_title( $schema, $translate = true ) {
		if ( in_array( $schema, [ 'BlogPosting', 'NewsArticle' ], true ) ) {
			return $translate ? esc_html__( 'Article', 'rank-math' ) : esc_html( 'Article' );
		}

		if ( 'WooCommerceProduct' === $schema ) {
			return $translate ? esc_html__( 'WooCommerce Product', 'rank-math' ) : esc_html( 'WooCommerce Product' );
		}

		if ( 'EDDProduct' === $schema ) {
			return $translate ? esc_html__( 'EDD Product', 'rank-math' ) : esc_html( 'EDD Product' );
		}

		if ( 'VideoObject' === $schema ) {
			return $translate ? esc_html__( 'Video', 'rank-math' ) : esc_html( 'Video' );
		}

		if ( 'JobPosting' === $schema ) {
			return $translate ? esc_html__( 'Job Posting', 'rank-math' ) : esc_html( 'Job Posting' );
		}

		if ( 'SoftwareApplication' === $schema ) {
			return $translate ? esc_html__( 'Software Application', 'rank-math' ) : esc_html( 'Software Application' );
		}

		if ( 'MusicGroup' === $schema || 'MusicAlbum' === $schema ) {
			return $translate ? esc_html__( 'Music', 'rank-math' ) : esc_html( 'Music' );
		}

		return $schema;
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
