<?php
/**
 * The Redirections Watcher.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;

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
	 * Hold updated terms permalinks.
	 *
	 * @var array
	 */
	private $updated_terms = [];

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
				$this->action( 'edit_terms', 'pre_term_update', 10, 2 );
				$this->action( 'edited_term', 'handle_term_update', 10, 3 );
			}
			$this->action( 'wp_trash_post', 'display_suggestion' );
		}
		$this->action( 'deleted_post', 'invalidate_post' );

		// Term.
		$this->action( 'pre_delete_term', 'invalidate_term', 10, 2 );

		// User.
		$this->action( 'delete_user', 'invalidate_author' );
		$this->action( 'profile_update', 'invalidate_author' );
	}

	/**
	 * Remember the previous post permalink.
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
		if ( ! in_array( $post->post_type, Helper::get_accessible_post_types(), true ) ) {
			return;
		}

		// Transitioning state of post.
		$transition = "{$before->post_status}_to_{$post->post_status}";

		// Both state permalink.
		$before_permalink = isset( $this->updated_posts[ $post_id ] ) ? $this->updated_posts[ $post_id ] : false;
		$after_permalink  = get_permalink( $post_id );

		// Check for permalink change.
		if ( 'publish_to_publish' === $transition && $this->has_permalink_changed( $before_permalink, $after_permalink ) ) {
			$redirection_id = $this->create_redirection( $before_permalink, $after_permalink, 301, $post->ID, 'post' );

			$message = sprintf(
				// translators: %1$s: post type label, %2$s: edit redirection URL.
				__( 'SEO Notice: you just changed the slug of a %1$s and Rank Math has automatically created a redirection. You can edit the redirection by <a href="%2$s">clicking here</a>.', 'rank-math' ),
				Helper::get_post_type_label( $post->post_type, true ),
				$this->get_edit_redirection_url( $redirection_id )
			);
			$this->add_notification( $message, true );

			// Update the meta value as well.
			if ( 'edit-post' === Param::post( 'screen' ) ) {
				update_post_meta( $post_id, 'rank_math_permalink', $post->post_name );
			}

			$this->do_action( 'redirection/post_updated', $redirection_id, $post_id );
			return;
		}
	}

	/**
	 * Remember the previous term permalink.
	 *
	 * @param integer $term_id  Term ID.
	 * @param string  $taxonomy Taxonomy slug of the related term.
	 */
	public function pre_term_update( $term_id, $taxonomy ) {
		$this->updated_terms[ $term_id ] = get_term_link( $term_id, $taxonomy );
	}

	/**
	 * Detect if the slug changed, hooked into 'post_updated'.
	 *
	 * @param integer $term_id  ID of the term edited.
	 * @param integer $tt_id    The term taxonomy id.
	 * @param string  $taxonomy Taxonomy slug of the related term.
	 *
	 * @return bool
	 */
	public function handle_term_update( $term_id, $tt_id, $taxonomy ) {
		if ( ! in_array( $taxonomy, array_keys( Helper::get_accessible_taxonomies() ), true ) ) {
			return;
		}

		// Both state permalink.
		$before_permalink = isset( $this->updated_terms[ $term_id ] ) ? $this->updated_terms[ $term_id ] : false;
		$after_permalink  = get_term_link( $term_id );

		if ( $before_permalink !== $after_permalink ) {
			$term           = get_term_by( 'id', $term_id, $taxonomy );
			$redirection_id = $this->create_redirection( $before_permalink, $after_permalink, 301, $term->term_id, 'term' );

			$message = sprintf(
				// translators: %1$s: term name, %2$s: edit redirection URL.
				__( 'SEO Notice: you just changed the slug of a %1$s and Rank Math has automatically created a redirection. You can edit the redirection by <a href="%2$s">clicking here</a>.', 'rank-math' ),
				$term->name,
				$this->get_edit_redirection_url( $redirection_id )
			);
			$this->add_notification( $message, true );

			$this->do_action( 'redirection/term_updated', $redirection_id, $term_id );
		}
	}

	/**
	 * Create redirection.
	 *
	 * @param string $from_url    Redirecting from url for cache.
	 * @param string $url_to      Destination url.
	 * @param int    $header_code Response header code.
	 * @param int    $object_id   Object id.
	 * @param string $type        Object type.
	 *
	 * @return int Redirection id.
	 */
	private function create_redirection( $from_url, $url_to, $header_code, $object_id, $type ) {
		// Early bail.
		if ( empty( $from_url ) || empty( $url_to ) ) {
			return;
		}

		// Check for any existing redirection.
		// If found update that record.
		$redirection = $this->has_existing_redirection( $object_id, $type );
		if ( false === $redirection ) {
			$redirection = Redirection::from(
				[
					'url_to'      => $url_to,
					'header_code' => $header_code,
				]
			);
		}

		$redirection->set_nocache( true );
		$redirection->add_source( $from_url, 'exact' );
		$redirection->add_destination( $url_to );
		$redirection->save();

		// Perform Cache.
		Cache::purge_by_object_id( $object_id, $type );
		if ( $from_url ) {
			$from_url = wp_parse_url( $from_url, PHP_URL_PATH );
			$from_url = Redirection::strip_subdirectory( $from_url );
			Cache::add(
				[
					'from_url'       => $from_url,
					'redirection_id' => $redirection->get_id(),
					'object_id'      => $object_id,
					'object_type'    => $type,
				]
			);
		}

		return $redirection->get_id();
	}

	/**
	 * Check for any existing redirection.
	 *
	 * @param int    $object_id Object id.
	 * @param string $type      Object type.
	 *
	 * @return boolean|int
	 */
	private function has_existing_redirection( $object_id, $type ) {
		$cache = Cache::get_by_object_id( $object_id, $type );
		if ( ! $cache ) {
			return false;
		}

		return Redirection::create( $cache->redirection_id );
	}

	/**
	 * Check if permalinks are different and if the before isn't the site URL.
	 *
	 * @param  WP_Post $before Post object before update.
	 * @param  WP_Post $after  Post object after update.
	 * @return boolean
	 */
	private function has_permalink_changed( $before, $after ) {
		$before = wp_parse_url( $before, PHP_URL_PATH );
		$after  = wp_parse_url( $after, PHP_URL_PATH );

		// Do the URLs the match?
		if ( $before === $after ) {
			return false;
		}

		// Is the $before the site URL?
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
		return Helper::get_admin_url(
			'redirections',
			[
				'redirection' => $redirection_id,
				'security'    => wp_create_nonce( 'redirection_list_action' ),
			]
		);
	}

	/**
	 * Get site path.
	 *
	 * @return string
	 */
	private function get_site_path() {
		$path = wp_parse_url( get_home_url(), PHP_URL_PATH );
		if ( $path ) {
			return rtrim( $path, '/' ) . '/';
		}

		return '/';
	}

	/**
	 * Display a suggestion notice after a post has been deleted.
	 *
	 * @param int $post_id Deleted post ID.
	 */
	public function display_suggestion( $post_id ) {
		$post = get_post( $post_id );

		if ( $this->can_display_suggestion( $post ) ) {
			$url = get_permalink( $post_id );
			$this->add_invalid_notification( $url, 'post' );
		}
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
	 * @param int $term Term ID.
	 */
	public function invalidate_term( $term ) {
		$url = get_term_link( $term );
		$this->add_invalid_notification( $url, 'term' );
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

	/**
	 * Check if notice can be displayed or not.
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
	 * Show Delete Post/Term notification.
	 *
	 * @param url    $url  Deleted object url.
	 * @param string $type Deleted object type.
	 */
	private function add_invalid_notification( $url, $type ) {
		$admin_url = Helper::get_admin_url( 'redirections', [ 'url' => trim( set_url_scheme( $url, 'relative' ), '/' ) ] );

		/* translators: 1. url to new screen, 2. old trashed post permalink */
		$message = sprintf( wp_kses_post( __( '<strong>SEO Notice:</strong> A previously published %1$s has been moved to trash. You may redirect <code>%2$s</code> to <a href="%3$s">a new url</a>.', 'rank-math' ) ), $type, $url, $admin_url );

		$this->add_notification( $message, true );
	}

	/**
	 * Show Delete Post/Term notification.
	 *
	 * @param string  $message        Notification message.
	 * @param boolean $is_dismissible Is notification dismissible.
	 */
	private function add_notification( $message, $is_dismissible = false ) {
		if ( ! Helper::has_cap( 'redirections' ) ) {
			return;
		}

		Helper::add_notification(
			$message,
			[
				'type'    => 'warning',
				'id'      => 'auto_post_redirection',
				'classes' => $is_dismissible ? 'is-dismissible' : '',
			]
		);
	}
}
