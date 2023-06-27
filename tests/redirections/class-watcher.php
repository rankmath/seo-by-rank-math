<?php
/**
 * The Redirections Watcher
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Watcher class.
 */
class Watcher {

	use Hooker;

	/**
	 * Hold updated posts permalinks.
	 *
	 * @var array
	 */
	private $updated_posts = [];

	/**
	 * Hook methods for invalidation on necessary events.
	 */
	public function __construct() {

		// Post.
		// Only monitor if permalinks enabled.
		if ( get_option( 'permalink_structure' ) ) {
			if ( Helper::get_settings( 'general.redirections_post_redirect' ) ) {
				$this->action( 'pre_post_update', 'pre_post_update' );
				$this->action( 'post_updated', 'handle_post_update', 10, 3 );
			} else {
				$this->action( 'wp_trash_post', 'display_suggestion' );
			}
		}
		$this->action( 'deleted_post', 'invalidate_post' );

		// Term.
		$this->action( 'edited_terms', 'invalidate_term', 10, 2 );
		$this->action( 'delete_term', 'invalidate_term', 10, 2 );

		// User.
		$this->action( 'delete_user', 'invalidate_author' );
		$this->action( 'profile_update', 'invalidate_author' );
	}

	/**
	 * Remember the previous post permalink
	 *
	 * @param integer $post_id Post ID.
	 */
	public function pre_post_update( $post_id ) {
		$this->updated_posts[ $post_id ] = get_permalink( $post_id );
	}

	/**
	 * Handles redirection when post is updated.
	 *
	 * @param integer $post_id Post ID.
	 * @param WP_Post $post    Post object after update.
	 * @param WP_Post $before  Post object before update.
	 */
	public function handle_post_update( $post_id, $post, $before ) {
		if ( ! in_array( $post->post_type, Helper::get_accessible_post_types() ) ) {
			return;
		}

		// Transitioning state of post.
		$transition = "{$before->post_status}_to_{$post->post_status}";

		// Both state permalink.
		$before_permalink = isset( $this->updated_posts[ $post_id ] ) ? $this->updated_posts[ $post_id ] : false;
		$after_permalink  = get_permalink( $post_id );

		// Check for permalink change.
		if ( 'publish_to_publish' === $transition && $this->has_permalink_changed( $before_permalink, $after_permalink ) ) {
			$redirection_id = $this->create_redirection( $before_permalink, $after_permalink, 301, $post );
			Helper::add_notification(
				sprintf(
					// translators: %1$s: post type label, %2$s: edit redirection URL.
					__( 'SEO Notice: you just changed the slug of a %1$s and Rank Math has automatically created a redirection. You can edit the redirection by <a href="%2$s">clicking here</a>.', 'rank-math' ),
					Helper::get_post_type_label( $post->post_type, true ), $this->get_edit_redirection_url( $redirection_id )
				),
				[ 'type' => 'warning' ]
			);

			$this->do_action( 'redirection/post_updated', $redirection_id );
			return;
		}
	}

	/**
	 * Create redirection
	 *
	 * @param  string  $from_url    Redirecting from url for cache.
	 * @param  string  $url_to      Destination url.
	 * @param  int     $header_code Response header code.
	 * @param  WP_Post $object      Post object.
	 * @return int Redirection id.
	 */
	private function create_redirection( $from_url, $url_to, $header_code, $object ) {
		// Early Bail!
		if ( empty( $from_url ) || empty( $url_to ) ) {
			return;
		}

		// Check for any existing redirection.
		// If found update that record.
		$redirection = $this->has_existing_redirection( $object->ID );
		if ( false === $redirection ) {
			$redirection = Redirection::from([
				'url_to'      => $url_to,
				'header_code' => $header_code,
			]);
		}

		$redirection->set_nocache( true );
		$redirection->add_source( $from_url, 'exact' );
		$redirection->save();

		// Perform Cache.
		Cache::purge_by_object_id( $object->ID, 'post' );
		if ( $from_url ) {
			$from_url = parse_url( $from_url, PHP_URL_PATH );
			Cache::add([
				'from_url'       => $from_url,
				'redirection_id' => $redirection->get_id(),
				'object_id'      => $object->ID,
			]);
		}

		return $redirection->get_id();
	}

	/**
	 * Check for any existing redirection.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return boolean|int
	 */
	private function has_existing_redirection( $post_id ) {
		$cache = Cache::get_by_object_id( $post_id, 'post' );
		if ( ! $cache ) {
			return false;
		}

		return Redirection::create( $cache->redirection_id );
	}

	/**
	 * Changed if permalinks are different and the before wasn't the site url (we don't want to redirect the site URL)
	 *
	 * @param  WP_Post $before Post object before update.
	 * @param  WP_Post $after  Post object after update.
	 * @return boolean
	 */
	private function has_permalink_changed( $before, $after ) {
		$before = parse_url( $before, PHP_URL_PATH );
		$after  = parse_url( $after, PHP_URL_PATH );

		// Are the URLs the same?
		if ( $before === $after ) {
			return false;
		}

		// Check it's not redirecting from the root.
		if ( $this->get_site_path() === $before || '/' === $before ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets edit redirection URL.
	 *
	 * @param  int $redirection_id Redirection ID.
	 * @return string
	 */
	private function get_edit_redirection_url( $redirection_id ) {
		return Helper::get_admin_url( 'redirections', [
			'redirection' => $redirection_id,
			'security'    => wp_create_nonce( 'redirection_list_action' ),
		]);
	}

	/**
	 * Get site path.
	 *
	 * @return string
	 */
	private function get_site_path() {
		$path = parse_url( get_site_url(), PHP_URL_PATH );
		if ( $path ) {
			return rtrim( $path, '/' ) . '/';
		}

		return '/';
	}

	/**
	 * Display notice after a post has been deleted
	 *
	 * @param int $post_id Deleted post ID.
	 */
	public function display_suggestion( $post_id ) {
		$post = get_post( $post_id );

		if ( $this->can_display_suggestion( $post ) ) {

			$url       = get_permalink( $post_id );
			$admin_url = Helper::get_admin_url( 'redirections', [ 'url' => trim( set_url_scheme( $url, 'relative' ), '/' ) ] );

			/* translators: 1. url to new screen, 2. old trashed post permalink */
			$message = sprintf( wp_kses_post( __( '<strong>SEO Notice:</strong> A previously published post has been moved to trash. You may redirect it <code>%2$s</code> to <a href="%1$s">new url</a>.', 'rank-math' ) ), $admin_url, $url );
			Helper::add_notification( $message, [ 'type' => 'warning' ] );
		}
	}

	/**
	 * Can display ay_suggestion
	 *
	 * @param  WP_Post $post Current post.
	 * @return bool
	 */
	private function can_display_suggestion( $post ) {
		if ( 'publish' !== $post->post_status ) {
			return false;
		}

		return Helper::is_post_type_accessible( $post->post_type );
	}

	/**
	 * Invalidate redirection cache for the post.
	 * Don't invalidate for revisions.
	 *
	 * @param int $post_id Post ID to invalidate type for.
	 */
	public function invalidate_post( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		Cache::purge_by_object_id( $post_id, 'post' );
	}

	/**
	 * Invalidate redirection cache for taxonomies.
	 *
	 * @param int|WP_Term $term Term ID or Term object.
	 */
	public function invalidate_term( $term ) {
		if ( is_a( $term, 'WP_Term' ) ) {
			$term = $term->term_id;
		}
		Cache::purge_by_object_id( $term, 'term' );
	}

	/**
	 * Invalidate redirection cache for authors.
	 *
	 * @param int $user_id User ID.
	 */
	public function invalidate_author( $user_id ) {
		Cache::purge_by_object_id( $user_id, 'user' );
	}
}
