<?php
/**
 * The Post Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use WP_Post;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Post class.
 */
class Post extends Metadata {

	/**
	 * Type of object metadata is for (e.g., comment, post, or user).
	 *
	 * @var string
	 */
	protected $meta_type = 'post';

	/**
	 * Retrieve Post instance.
	 *
	 * @param  WP_Post|object|int $post Post to get either (int) post id or (WP_Post|object) post.
	 * @return Post|false Post object, false otherwise.
	 */
	public static function get( $post = 0 ) {
		$post = self::get_post_id( $post );
		if ( false === $post ) {
			return null;
		}

		if ( isset( self::$objects[ $post ] ) && 'post' === self::$objects[ $post ]->meta_type ) {
			return self::$objects[ $post ];
		}

		$_post                  = new self( WP_Post::get_instance( $post ) );
		$_post->object_id       = $post;
		self::$objects[ $post ] = $_post;

		return $_post;
	}

	/**
	 * Get the post ID.
	 *
	 * @param  integer $post Post ID.
	 * @return integer
	 */
	private static function get_post_id( $post = 0 ) {
		if ( is_object( $post ) && isset( $post->ID ) ) {
			return $post->ID;
		}

		$post = absint( $post );
		if ( $post > 0 ) {
			return $post;
		}

		if ( 0 === $post ) {
			$post = get_post();
		}

		return ! is_null( $post ) ? $post->ID : false;
	}

	/**
	 * Get a post meta value.
	 *
	 * @param  string  $key     Value to get, without prefix.
	 * @param  integer $post_id ID of the post.
	 * @param  string  $default Default value to use when metadata does not exists.
	 * @return mixed
	 */
	public static function get_meta( $key, $post_id = 0, $default = '' ) {
		$post = self::get( $post_id );

		if ( is_null( $post ) || ! $post->is_found() || 'auto-draft' === $post->post_status ) {
			return '';
		}

		return $post->get_metadata( $key, $default );
	}

	/**
	 * Get the ID of the current page.
	 *
	 * @return int The ID of the page.
	 */
	public static function get_simple_page_id() {
		/**
		 * Filter: Allow changing the default page ID. Short-circuit if 3rd party set page ID.
		 *
		 * @param unsigned int $page_id The default page id.
		 */
		$page_id = apply_filters( 'rank_math/pre_simple_page_id', false );
		if ( false !== $page_id ) {
			return $page_id;
		}

		if ( \is_singular() ) {
			return get_the_ID();
		}

		if ( self::is_posts_page() ) {
			return get_option( 'page_for_posts' );
		}

		if ( self::is_shop_page() ) {
			return self::get_shop_page_id();
		}

		/**
		 * Filter: Allow changing the default page ID.
		 *
		 * @param unsigned int $page_id The default page ID.
		 */
		return apply_filters( 'rank_math/simple_page_id', 0 );
	}

	/**
	 * Returns the ID of the selected WooCommerce shop page.
	 *
	 * @return int The ID of the Shop page.
	 */
	public static function get_shop_page_id() {
		static $shop_page_id;
		if ( ! $shop_page_id ) {
			$shop_page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : ( -1 );
		}

		return $shop_page_id;
	}

	/**
	 * Checks if the current page is a simple page.
	 *
	 * @return bool Whether the current page is a simple page.
	 */
	public static function is_simple_page() {
		return self::get_simple_page_id() > 0;
	}

	/**
	 * Checks if the current page is the WooCommerce "Shop" page.
	 *
	 * @return bool Whether the current page is the shop page.
	 */
	public static function is_shop_page() {
		if ( function_exists( 'is_shop' ) && function_exists( 'wc_get_page_id' ) ) {
			return \is_shop() && ! \is_search();
		}

		return false;
	}

	/**
	 * Checks if the current page is one of the WooCommerce pages: Cart/Account/Checkout.
	 *
	 * @return bool Whether the current page is a WooCommerce page.
	 */
	public static function is_woocommerce_page() {
		if ( Conditional::is_woocommerce_active() ) {
			return \is_cart() || \is_checkout() || \is_account_page();
		}

		return false;
	}

	/**
	 * Check whether this is the homepage and if it shows the posts.
	 *
	 * @return bool
	 */
	public static function is_home_posts_page() {
		return ( \is_home() && 'posts' === get_option( 'show_on_front' ) );
	}

	/**
	 * Check whether this is the static frontpage.
	 *
	 * @return bool
	 */
	public static function is_home_static_page() {
		return ( \is_front_page() && 'page' === get_option( 'show_on_front' ) && \is_page( get_option( 'page_on_front' ) ) );
	}

	/**
	 * Check if this is the posts page and that it's not the frontpage.
	 *
	 * @return bool
	 */
	public static function is_posts_page() {
		return ( \is_home() && 'page' === get_option( 'show_on_front' ) );
	}
}
