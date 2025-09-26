<?php
/**
 * The Sitemap module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap;

use RankMath\Helper;
use RankMath\Helpers\DB as DB_Helper;
use RankMath\Helpers\Sitepress;
use RankMath\Traits\Hooker;
use RankMath\Sitemap\Html\Sitemap as Html_Sitemap;

defined( 'ABSPATH' ) || exit;

/**
 * Sitemap class.
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */
class Sitemap {

	use Hooker;

	/**
	 * Sitemap Index object.
	 *
	 * @var Sitemap_Index
	 */
	public $index;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			new Admin();
		}

		if ( is_admin() || wp_doing_cron() ) {
			new Cache_Watcher();
		}

		new Router();
		$this->index = new Sitemap_Index();
		$this->index->hooks();
		new Redirect_Core_Sitemaps();
		new Html_Sitemap();

		add_action( 'rank_math/sitemap/hit_index', [ __CLASS__, 'hit_index' ] );

		$this->filter( 'rank_math/admin/notice/new_post_type', 'new_post_type_notice', 10, 2 );

		if ( class_exists( 'SitePress' ) ) {
			$this->filter( 'rank_math/sitemap/build_type', 'rank_math_build_sitemap_filter' );
			$this->filter( 'rank_math/sitemap/entry', 'exclude_hidden_language_posts', 10, 3 );
		}

		$this->action( 'rank_math/settings/after_save', 'clear_cache' );
	}

	/**
	 * Exclude posts under hidden language.
	 *
	 * @since 1.0.5
	 *
	 * @param string $url  Post URL.
	 * @param string $type URL type.
	 * @param object $post Object with some post information.
	 *
	 * @return string
	 */
	public function exclude_hidden_language_posts( $url, $type, $post ) {
		if ( 'post' !== $type ) {
			return $url;
		}

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
		if ( Sitepress::get()->is_per_domain() ) {
			return $type;
		}

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
	 * Add new CPT notice.
	 *
	 * @param  string $notice New CPT notice.
	 * @param  int    $count  Count of new post types detected.
	 * @return string
	 */
	public function new_post_type_notice( $notice, $count ) {
		/* Translators: placeholder is the post type name. */
		$notice = __( 'Rank Math has detected a new post type: %1$s. You may want to check the settings of the <a href="%2$s">Titles &amp; Meta page</a> and <a href="%3$s">the Sitemap</a>.', 'rank-math' );

		if ( $count > 1 ) {
			/* Translators: placeholder is the post type names separated with commas. */
			$notice = __( 'Rank Math has detected new post types: %1$s. You may want to check the settings of the <a href="%2$s">Titles &amp; Meta page</a> and <a href="%3$s">the Sitemap</a>.', 'rank-math' );
		}

		return $notice;
	}

	/**
	 * Hit sitemap index to pre-generate the cache.
	 */
	public static function hit_index() {
		wp_remote_get( Router::get_base_url( self::get_sitemap_index_slug() . '.xml' ) );
	}

	/**
	 * Exclude object from sitemap.
	 *
	 * @param  int     $object_id   Object id.
	 * @param  string  $object_type Object type. Accetps: post, term, user.
	 * @param  boolean $is_include  Add or Remove object.
	 */
	public static function exclude_object( $object_id, $object_type, $is_include ) {
		$field_id = "exclude_{$object_type}s";
		$ids      = Helper::get_settings( 'sitemap.' . $field_id );

		if ( empty( $ids ) ) {
			$ids = $object_id;
		} else {
			$ids = array_filter( wp_parse_id_list( $ids ) );

			// Add object.
			if ( $is_include && ! in_array( $object_id, $ids, true ) ) {
				$ids[] = $object_id;
			}

			// Remove object.
			if ( ! $is_include && in_array( $object_id, $ids, true ) ) {
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
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
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
				SELECT post_type, MAX( GREATEST( p.post_modified_gmt, p.post_date_gmt ) ) AS date
				FROM $wpdb->posts as p
				LEFT JOIN {$wpdb->postmeta} AS pm ON ( p.ID = pm.post_id AND pm.meta_key = 'rank_math_robots')
				WHERE (
					( pm.meta_key = 'rank_math_robots' AND pm.meta_value NOT LIKE '%noindex%' ) OR
				    pm.post_id IS NULL
				)
				AND p.post_status IN ( 'publish','inherit' )
					AND p.post_type IN ('" . implode( "','", $post_type_names ) . "')
				GROUP BY p.post_type
				ORDER BY p.post_modified_gmt DESC";

				foreach ( DB_Helper::get_results( $sql ) as $obj ) {
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
	 * Check if cache is enabled.
	 *
	 * @return boolean
	 */
	public static function is_cache_enabled() {
		static $xml_sitemap_caching;
		if ( isset( $xml_sitemap_caching ) ) {
			return $xml_sitemap_caching;
		}

		/**
		 * Filter to enable/disable XML sitemap caching.
		 *
		 * @param boolean $true Enable or disable caching.
		 */
		$xml_sitemap_caching = apply_filters( 'rank_math/sitemap/enable_caching', true );
		return $xml_sitemap_caching;
	}

	/**
	 * Check if `object` is indexable.
	 *
	 * @param int/object $data_object Post|Term Object.
	 * @param string     $type        Object Type.
	 *
	 * @return boolean
	 */
	public static function is_object_indexable( $data_object, $type = 'post' ) {
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

		return Helper::$method( $data_object );
	}

	/**
	 * Redirect duplicate sitemaps.
	 *
	 * @param int $count       Total number of entries.
	 * @param int $max_entries Entries per sitemap.
	 */
	public static function maybe_redirect( $count, $max_entries ) {
		$current_page = (int) get_query_var( 'sitemap_n' );
		if ( ! $current_page && $count > $max_entries ) {
			Helper::redirect( preg_replace( '/\.xml$/', '1.xml', Helper::get_current_page_url() ) );
			die();
		}

		if ( $count < $max_entries && $current_page ) {
			Helper::redirect( preg_replace( '/' . preg_quote( $current_page, '/' ) . '\.xml$/', '.xml', Helper::get_current_page_url() ) );
			die();
		}
	}

	/**
	 * Get the sitemap index slug.
	 */
	public static function get_sitemap_index_slug() {
		/**
		 * Filter: 'rank_math/sitemap/index_slug' - Modify the sitemap index slug.
		 *
		 * @param string $slug Sitemap index slug.
		 *
		 * @return string
		 */
		return apply_filters( 'rank_math/sitemap/index/slug', 'sitemap_index' );
	}

	/**
	 * Ensure sitemap cache is invalidated when settings change.
	 */
	public function clear_cache() {
		Cache::invalidate_storage();
	}
}
