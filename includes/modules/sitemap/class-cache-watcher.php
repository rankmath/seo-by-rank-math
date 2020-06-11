<?php
/**
 * The Cache Watcher.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap;

use RankMath\Helper;
use RankMath\Sitemap\Sitemap;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Cache_Watcher class.
 */
class Cache_Watcher {

	use Hooker;

	/**
	 * Holds the options that, when updated, should cause the cache to clear.
	 *
	 * @var array
	 */
	protected static $cache_clear = [];

	/**
	 * Holds the flag to clear all cache.
	 *
	 * @var boolean
	 */
	protected static $clear_all = false;

	/**
	 * Holds the array of types to clear.
	 *
	 * @var array
	 */
	protected static $clear_types = [];

	/**
	 * Hook methods for invalidation on necessary events.
	 */
	public function __construct() {
		$this->action( 'save_post', 'save_post' );
		$this->action( 'transition_post_status', 'status_transition', 10, 3 );
		$this->action( 'admin_footer', 'status_transition_bulk_finished' );

		$this->action( 'edited_terms', 'invalidate_term', 10, 2 );
		$this->action( 'clean_term_cache', 'invalidate_term', 10, 2 );
		$this->action( 'clean_object_term_cache', 'invalidate_term', 10, 2 );

		$this->action( 'delete_user', 'invalidate_author' );
		$this->action( 'user_register', 'invalidate_author' );
		$this->action( 'profile_update', 'invalidate_author' );

		$this->action( 'rank_math/sitemap/invalidate_object_type', 'invalidate_object_type', 10, 2 );

		add_action( 'shutdown', array( __CLASS__, 'clear_queued' ) );
		add_action( 'update_option', array( __CLASS__, 'clear_on_option_update' ) );
		add_action( 'deleted_term_relationships', array( __CLASS__, 'invalidate' ) );

		// Option on updatation of which clear cache.
		self::register_clear_on_option_update( 'home' );
		self::register_clear_on_option_update( 'permalink_structure' );
		self::register_clear_on_option_update( 'rank_math_modules' );
		self::register_clear_on_option_update( 'rank-math-options-titles' );
		self::register_clear_on_option_update( 'rank-math-options-general' );
		self::register_clear_on_option_update( 'rank-math-options-sitemap' );
	}

	/**
	 * Check for relevant post type before invalidation.
	 *
	 * @param int $post_id Post ID to possibly invalidate for.
	 */
	public function save_post( $post_id ) {
		if ( false === Sitemap::is_object_indexable( $post_id ) ) {
			return false;
		}

		$post = get_post( $post_id );
		if ( ! empty( $post->post_password ) ) {
			return false;
		}

		update_user_meta( $post->post_author, 'last_update', get_post_modified_time( 'U', false, $post ) );
		$this->invalidate_post( $post_id );
		$this->invalidate_author( $post->post_author );
	}

	/**
	 * Hooked into transition_post_status. Will initiate search engine pings
	 * if the post is being published, is a post type that a sitemap is built for
	 * and is a post that is included in sitemaps.
	 *
	 * @param string   $new_status New post status.
	 * @param string   $old_status Old post status.
	 * @param \WP_Post $post       Post object.
	 */
	public function status_transition( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status ) {
			return;
		}

		if ( defined( 'WP_IMPORTING' ) ) {
			$this->status_transition_bulk( $new_status, $old_status, $post );
			return;
		}

		if ( $this->can_exclude( $post ) ) {
			return;
		}

		if ( WP_CACHE ) {
			wp_schedule_single_event( ( time() + 300 ), 'rank_math/sitemap/hit_index' );
		}

		if ( ! Sitemap::can_ping() ) {
			return;
		}

		if ( ! Helper::is_post_excluded( $post->ID ) && ! wp_next_scheduled( 'rank_math/sitemap/ping_search_engines' ) ) {
			wp_schedule_single_event( ( time() + 300 ), 'rank_math/sitemap/ping_search_engines' );
		}
	}

	/**
	 * Can exclude post type.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return bool
	 */
	private function can_exclude( $post ) {
		$post_type = get_post_type( $post );
		wp_cache_delete( 'lastpostmodified:gmt:' . $post_type, 'timeinfo' );

		// None of our interest..
		// If the post type is excluded in options, we can stop.
		return 'nav_menu_item' === $post_type || ! Sitemap::is_object_indexable( $post->ID );
	}

	/**
	 * While bulk importing, just save unique post_types.
	 *
	 * When importing is done, if we have a post_type that is saved in the sitemap
	 * try to ping the search engines
	 *
	 * @param string   $new_status New post status.
	 * @param string   $old_status Old post status.
	 * @param \WP_Post $post       Post object.
	 */
	private function status_transition_bulk( $new_status, $old_status, $post ) {
		$this->importing_post_types[] = get_post_type( $post );
		$this->importing_post_types   = array_unique( $this->importing_post_types );
	}

	/**
	 * After import finished, walk through imported post_types and update info.
	 */
	public function status_transition_bulk_finished() {
		if ( ! defined( 'WP_IMPORTING' ) || empty( $this->importing_post_types ) ) {
			return;
		}

		if ( false === $this->maybe_ping_search_engines() ) {
			return;
		}

		if ( WP_CACHE ) {
			do_action( 'rank_math/sitemap/hit_index' );
		}

		Sitemap::ping_search_engines();
	}

	/**
	 * Check if we can ping search engines.
	 *
	 * @return bool
	 */
	private function maybe_ping_search_engines() {
		$ping = false;
		foreach ( $this->importing_post_types as $post_type ) {
			wp_cache_delete( 'lastpostmodified:gmt:' . $post_type, 'timeinfo' );

			// Just have the cache deleted for nav_menu_item.
			if ( 'nav_menu_item' === $post_type ) {
				continue;
			}
		}

		return $ping;
	}

	/**
	 * Helper to invalidate in hooks where type is passed as second argument.
	 *
	 * @param int    $unused    Unused term ID value.
	 * @param string $taxonomy  Taxonomy to invalidate.
	 */
	public function invalidate_term( $unused, $taxonomy ) {
		if ( false !== Helper::get_settings( 'sitemap.tax_' . $taxonomy . '_sitemap' ) ) {
			self::invalidate( $taxonomy );
		}
	}

	/**
	 * Delete cache transients for index and specific type.
	 *
	 * Always deletes the main index sitemaps cache, as that's always invalidated by any other change.
	 *
	 * @param string $type Sitemap type to invalidate.
	 */
	public static function invalidate( $type ) {
		self::clear( array( $type ) );
	}

	/**
	 * Invalidate sitemap cache for the post type of a post.
	 * Don't invalidate for revisions.
	 *
	 * @param int $post_id Post ID to invalidate type for.
	 */
	public static function invalidate_post( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		self::invalidate( get_post_type( $post_id ) );
	}

	/**
	 * Invalidate sitemap cache for authors.
	 *
	 * @param int $user_id User ID.
	 */
	public static function invalidate_author( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( 'user_register' === current_action() || 'profile_update' === current_action() ) {
			update_user_meta( $user_id, 'last_update', time() );
		}

		if ( $user && ! is_null( $user->roles ) && ! in_array( 'subscriber', $user->roles, true ) ) {
			self::invalidate( 'author' );
		}
	}

	/**
	 * Function to clear Sitemap Cache.
	 *
	 * @param string $object_type Object type for destination where to save.
	 * @param int    $object_id   Object id for destination where to save.
	 *
	 * @return void
	 */
	public static function invalidate_object_type( $object_type, $object_id ) {
		if ( 'post' === $object_type ) {
			self::invalidate_post( $object_id );
			return;
		}

		if ( 'user' === $object_type ) {
			self::invalidate_author( $object_id );
			return;
		}

		if ( 'term' === $object_type ) {
			$term = get_term( $object_id );
			self::invalidate_term( $object_id, $term->taxonomy );
		}
	}

	/**
	 * Delete cache transients for given sitemaps types or all by default.
	 *
	 * @param array $types Set of sitemap types to delete cache transients for.
	 */
	public static function clear( $types = [] ) {
		if ( ! Sitemap::is_cache_enabled() ) {
			return;
		}

		// No types provided, clear all.
		if ( empty( $types ) ) {
			self::$clear_all = true;
			return;
		}

		// Always invalidate the index sitemap as well.
		if ( ! in_array( '1', $types, true ) ) {
			array_unshift( $types, '1' );
		}

		foreach ( $types as $type ) {
			if ( ! in_array( $type, self::$clear_types, true ) ) {
				self::$clear_types[] = $type;
			}
		}
	}

	/**
	 * Invalidate storage for cache types queued to clear.
	 */
	public static function clear_queued() {
		if ( self::$clear_all ) {
			Cache::invalidate_storage();
			self::$clear_all   = false;
			self::$clear_types = [];
			return;
		}

		foreach ( self::$clear_types as $type ) {
			Cache::invalidate_storage( $type );
		}

		self::$clear_types = [];
	}

	/**
	 * Adds a hook that when given option is updated, the cache is cleared.
	 *
	 * @param string $option Option name.
	 * @param string $type   Sitemap type.
	 */
	public static function register_clear_on_option_update( $option, $type = '' ) {
		self::$cache_clear[ $option ] = $type;
	}

	/**
	 * Clears the transient cache when a given option is updated, if that option has been registered before.
	 *
	 * @param string $option The option name that's being updated.
	 */
	public static function clear_on_option_update( $option ) {
		if ( ! array_key_exists( $option, self::$cache_clear ) ) {
			return;
		}

		// Clear all caches.
		if ( empty( self::$cache_clear[ $option ] ) ) {
			self::clear();
			return;
		}

		// Clear specific provided type(s).
		$types = (array) self::$cache_clear[ $option ];
		self::clear( $types );
	}
}
