<?php
/**
 * The class handles redirection of attachment & archive pages
 *
 * @since      1.0.216
 * @package    RankMath
 * @subpackage RankMath\Frontend
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Frontend;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Add Link_Attributes class.
 */
class Redirection {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( Helper::is_module_active( 'redirections' ) ) {
			$this->filter( 'rank_math/redirection/pre_search', 'pre_redirection', 10, 3 );
			return;
		}

		$this->action( 'wp', 'redirect' );
	}

	/**
	 * Pre-filter the redirection.
	 *
	 * @param string $check    Check.
	 * @param string $uri      Current URL.
	 * @param string $full_uri Full URL.
	 *
	 * @return string|array
	 */
	public function pre_redirection( $check, $uri, $full_uri ) {
		if ( $new_link = $this->get_redirection_url() ) { // phpcs:ignore
			return [
				'url_to'      => $new_link,
				'header_code' => 301,
			];
		}

		return $check;
	}

	/**
	 * Redirect product with base to the new link.
	 */
	public function redirect() {
		if ( $link = $this->get_redirection_url() ) { // phpcs:ignore
			Helper::redirect( $link, 301 );
			exit;
		}
	}

	/**
	 * Get Redirection URL.
	 *
	 * @return string Modified URL
	 */
	private function get_redirection_url() {
		// Redirect attachment page to parent post.
		if ( is_attachment() && Helper::get_settings( 'general.attachment_redirect_urls', true ) ) {
			global $post;

			$redirect = ! empty( $post->post_parent ) ? get_permalink( $post->post_parent ) : Helper::get_settings( 'general.attachment_redirect_default' );
			if ( ! $redirect ) {
				return;
			}

			/**
			 * Redirect attachment to its parent post.
			 *
			 * @param string  $redirect URL as calculated for redirection.
			 * @param WP_Post $post     Current post instance.
			 */
			return $this->do_filter( 'frontend/attachment/redirect_url', $redirect, $post );
		}

		// Redirect archives.
		global $wp_query;
		if (
			( Helper::get_settings( 'titles.disable_date_archives' ) && $wp_query->is_date ) ||
			( true === Helper::get_settings( 'titles.disable_author_archives' ) && $wp_query->is_author )
		) {
			return get_bloginfo( 'url' );
		}

		return false;
	}
}
