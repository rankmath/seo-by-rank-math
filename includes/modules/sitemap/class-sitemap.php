<?php
/**
 * The Sitemap Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap;

use RankMath\Helper;
use RankMath\Helpers\Sitepress;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Sitemap class.
 */
class Sitemap {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			new Admin();
			new Cache_Watcher();
		}

		new Router();
		$this->index = new Sitemap_Index();
		$this->index->hooks();

		add_action( 'rank_math/sitemap/hit_index', [ __CLASS__, 'hit_sitemap_index' ] );
		add_action( 'rank_math/sitemap/ping_search_engines', [ __CLASS__, 'ping_search_engines' ] );

		$this->filter( 'rank_math/admin/notice/new_post_type', 'new_post_type_notice' );

		if ( class_exists( 'SitePress' ) ) {
			$this->filter( 'rank_math/sitemap/build_type', 'rank_math_build_sitemap_filter' );
			$this->filter( 'rank_math/sitemap/xml_post_url', 'exclude_hidden_language_posts', 10, 2 );
		}

		/**
		 * Disable the WP core XML sitemaps.
		 */
		add_filter( 'wp_sitemaps_enabled', '__return_false' );
	}

	/**
	 * Exclude posts under hidden language.
	 *
	 * @since 1.0.5
	 *
	 * @param string $url  Post URL.
	 * @param object $post Object with some post information.
	 *
	 * @return string
	 */
	public function exclude_hidden_language_posts( $url, $post ) {
		global $sitepress;
		// Check that at least ID is set in post object.
		if ( ! isset( $post->ID ) ) {
			return $url;
		}

		// Get list of hidden languages.
		$hidden_languages = $sitepress->get_setting( 'hidden_languages', [] );

		// If there are no hidden languages return original URL.
		if ( empty( $hidden_languages ) ) {
			return $url;
		}

		// Get language information for post.
		$language_info = $sitepress->post_translations()->get_element_lang_code( $post->ID );

		// If language code is one of the hidden languages return empty string to skip the post.
		if ( in_array( $language_info, $hidden_languages, true ) ) {
			return '';
		}

		return $url;
	}

	/**
	 * Prevent get_permalink from translating and remove filter added by WPML to get terms in current language.
	 *
	 * @since 1.0.5
	 *
	 * @param string $type Sitemap type.
	 *
	 * @return string
	 */
	public function rank_math_build_sitemap_filter( $type ) {
		global $sitepress_settings;
		// Before to build the sitemap and as we are on front-end just make sure the links won't be translated. The setting should not be updated in DB.
		$sitepress_settings['auto_adjust_ids'] = 0;

		/**
		 * Remove WPML filters while getting terms, to get all languages
		 */
		Sitepress::get()->remove_term_filters();

		return $type;
	}

	/**
	 * Add New CPT Notice
	 *
	 * @param  string $notice New CPT Notice.
	 * @return string
	 */
	public function new_post_type_notice( $notice ) {
		/* translators: post names */
		$notice = __( 'We detected new post type(s) (%1$s), and you would want to check the settings of <a href="%2$s">Titles &amp; Meta page</a> and <a href="%3$s">the Sitemap</a>.', 'rank-math' );

		return $notice;
	}

	/**
	 * Make a request for the sitemap index so as to cache it before the arrival of the search engines.
	 */
	public static function hit_sitemap_index() {
		wp_remote_get( Router::get_base_url( 'sitemap_index.xml' ) );
	}

	/**
	 * Notify search engines of the updated sitemap.
	 *
	 * @param string|null $url Optional URL to make the ping for.
	 */
	public static function ping_search_engines( $url = null ) {

		if ( ! self::can_ping() ) {
			return;
		}

		if ( empty( $url ) ) {
			$url = rawurlencode( Router::get_base_url( 'sitemap_index.xml' ) );
		}

		// Ping Google and Bing.
		wp_remote_get( 'http://www.google.com/webmasters/tools/ping?sitemap=' . $url, [ 'blocking' => false ] );

		if ( Router::get_base_url( 'geo-sitemap.xml' ) !== $url ) {
			wp_remote_get( 'http://www.google.com/ping?sitemap=' . $url, [ 'blocking' => false ] );
			wp_remote_get( 'http://www.bing.com/ping?sitemap=' . $url, [ 'blocking' => false ] );
		}
	}

	/**
	 * Check if we can ping search engines.
	 *
	 * @return bool
	 */
	public static function can_ping() {
		if ( false === Helper::get_settings( 'sitemap.ping_search_engines' ) ) {
			return false;
		}

		// Don't ping if blog is not public.
		if ( '0' === get_option( 'blog_public' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Exclude object frmofrom sitemap.
	 *
	 * @param  int     $object_id   Object id.
	 * @param  string  $object_type Object type. Accetps: post, term, user.
	 * @param  boolean $include     Add or Remove object.
	 */
	public static function exclude_object( $object_id, $object_type, $include ) {
		$field_id = "exclude_{$object_type}s";
		$ids      = Helper::get_settings( 'sitemap.' . $field_id );

		if ( empty( $ids ) ) {
			$ids = $object_id;
		} else {
			$ids = array_filter( wp_parse_id_list( $ids ) );

			// Add object.
			if ( $include && ! in_array( $object_id, $ids, true ) ) {
				$ids[] = $object_id;
			}

			// Remove object.
			if ( ! $include && in_array( $object_id, $ids, true ) ) {
				$ids = array_diff( $ids, [ $object_id ] );
			}

			$ids = implode( ',', $ids );
		}

		$opt = cmb2_options( 'rank-math-options-sitemap' );
		$opt->update( $field_id, $ids, true );
	}

	/**
	 * Get the GMT modification date for the last modified post in the post type.
	 *
	 * @param  string|array $post_types Post type or array of types.
	 * @param  boolean      $return_all Flag to return array of values.
	 * @return string|array|false
	 */
	public static function get_last_modified_gmt( $post_types, $return_all = false ) {
		global $wpdb;

		if ( empty( $post_types ) ) {
			return false;
		}

		static $post_type_dates = null;
		if ( ! is_array( $post_types ) ) {
			$post_types = [ $post_types ];
		}

		foreach ( $post_types as $post_type ) {
			if ( ! isset( $post_type_dates[ $post_type ] ) ) { // If we hadn't seen post type before. R.
				$post_type_dates = null;
				break;
			}
		}

		if ( is_null( $post_type_dates ) ) {
			$post_type_dates = [];
			$post_type_names = get_post_types( [ 'public' => true ] );

			if ( ! empty( $post_type_names ) ) {
				$sql = "
				SELECT post_type, MAX(post_modified_gmt) AS date
				FROM $wpdb->posts
				WHERE post_status IN ('publish','inherit')
					AND post_type IN ('" . implode( "','", $post_type_names ) . "')
				GROUP BY post_type
				ORDER BY post_modified_gmt DESC";

				foreach ( $wpdb->get_results( $sql ) as $obj ) { // phpcs:ignore
					$post_type_dates[ $obj->post_type ] = $obj->date;
				}
			}
		}

		$dates = array_intersect_key( $post_type_dates, array_flip( $post_types ) );
		if ( count( $dates ) > 0 ) {
			return $return_all ? $dates : max( $dates );
		}

		return false;
	}

	/**
	 * If cache is enabled.
	 *
	 * @return boolean
	 */
	public static function is_cache_enabled() {
		static $xml_sitemap_caching;
		if ( isset( $xml_sitemap_caching ) ) {
			return $xml_sitemap_caching;
		}

		/**
		 * Filter if XML sitemap transient cache is enabled.
		 *
		 * @param boolean $unsigned Enable cache or not, defaults to true
		 */
		$xml_sitemap_caching = apply_filters( 'rank_math/sitemap/enable_caching', true );
		return $xml_sitemap_caching;
	}

	/**
	 * Check if Object is indexable.
	 *
	 * @param int/object $object Post|Term Object.
	 * @param string     $type   Object Type.
	 *
	 * @return boolean
	 */
	public static function is_object_indexable( $object, $type = 'post' ) {
		/**
		 * Filter: 'rank_math/sitemap/include_noindex' - Include noindex data in Sitemap.
		 *
		 * @param bool   $value Whether to include noindex terms in Sitemap.
		 * @param string $type  Object Type.
		 *
		 * @return boolean
		 */
		if ( apply_filters( 'rank_math/sitemap/include_noindex', false, $type ) ) {
			return true;
		}

		$method = 'post' === $type ? 'is_post_indexable' : 'is_term_indexable';

		return Helper::$method( $object );
	}
}
