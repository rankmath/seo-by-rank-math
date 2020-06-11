<?php
/**
 * The compatibility functionality for 3rd party plugins.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility class.
 */
class Compatibility {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {

			if ( \defined( 'PIRATE_FORMS_VERSION' ) ) {
				$this->action( 'admin_enqueue_scripts', 'pirate_forms_dequeue_scripts' );
			}
		}

		$this->filter( 'rank_math/pre_simple_page_id', 'subscribe_to_comments_reloaded' );
		$this->filter( 'genesis_detect_seo_plugins', 'disable_genesis_seo' );
	}

	/**
	 * Subscribe to comments reloaded page ID.
	 *
	 * @param  int $page_id Change page id to real page.
	 * @return int
	 */
	public function subscribe_to_comments_reloaded( $page_id ) {
		if ( is_plugin_active( 'subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php' ) ) {
			$page_permalink = get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
			if ( function_exists( 'qtrans_convertURL' ) ) {
				$page_permalink = qtrans_convertURL( $page_permalink );
			}
			if ( ( strpos( $_SERVER['REQUEST_URI'], $page_permalink ) !== false ) ) {
				$this->action( 'rank_math/head', 'subscribe_to_comments_reloaded_remove_robots', 1 );
				return get_queried_object_id();
			}
		}

		return $page_id;
	}

	/**
	 * Remove robots for this plugin.
	 */
	public function subscribe_to_comments_reloaded_remove_robots() {
		remove_action( 'rank_math/frontend/robots', '__return_empty_array' );
	}

	/**
	 * Remove Pirate forms plugin scripts and styles from setting panels.
	 */
	public function pirate_forms_dequeue_scripts() {

		if ( ! wp_script_is( 'pirate_forms_pro_admin_scripts' ) ) {
			return;
		}

		$screen = get_current_screen();
		if (
			( ! Str::contains( 'rank-math', $screen->id ) && ! in_array( $screen->base, [ 'post', 'term', 'profile', 'user-edit' ], true ) ) ||
			'pf_form' === $screen->id
		) {
			return;
		}

		wp_dequeue_script( 'pirate_forms_pro_admin_scripts' );
		wp_dequeue_style( 'pirate_forms_pro_admin_styles' );
	}

	/**
	 * Disable Genesis SEO functionality.
	 *
	 * @param array $array Array hold disable info.
	 *
	 * @return array
	 */
	public function disable_genesis_seo( $array ) {
		$array['classes'][]   = '\RankMath\RankMath';
		$array['functions'][] = 'rank_math';

		return $array;
	}
}
