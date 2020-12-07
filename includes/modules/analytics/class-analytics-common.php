<?php
/**
 * Methods for frontend and backend in admin-only module
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;
use RankMath\Google\Console;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics class.
 */
class Analytics_Common {

	use Hooker;

	/**
	 * The Constructor
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() ) {
			return;
		}

		if ( Helper::has_cap( 'analytics' ) ) {
			$this->action( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		}

		new GTag();
		Data_Fetcher::get();

		$this->action( 'rest_api_init', 'init_rest_api' );
		$this->filter( 'rank_math/webmaster/google_verify', 'add_site_verification' );

		$this->filter( 'rank_math/tools/analytics_clear_caches', 'analytics_clear_caches' );
		$this->filter( 'rank_math/tools/analytics_reindex_posts', 'analytics_reindex_posts' );
	}

	/**
	 * Add site verification code.
	 *
	 * @param string $content If any code from setting.
	 *
	 * @return string
	 */
	public function add_site_verification( $content ) {
		$code = get_transient( 'rank_math_google_site_verification' );

		return ! empty( $code ) ? $code : $content;
	}

	/**
	 * Load the REST API endpoints.
	 */
	public function init_rest_api() {
		$controllers = [
			new Rest(),
		];

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}
	}

	/**
	 * Add admin bar item.
	 *
	 * @param Admin_Bar_Menu $menu Menu class instance.
	 */
	public function admin_bar_items( $menu ) {
		$dot_color = '#ed5e5e';
		if ( Console::is_console_connected() ) {
			$dot_color = '#11ac84';
		}

		$menu->add_sub_menu(
			'analytics',
			[
				'title'    => esc_html__( 'Analytics', 'rank-math' ) . '<span class="rm-menu-new update-plugins" style="background: ' . $dot_color . ';margin-left: 5px;min-width: 10px;height: 10px;margin-bottom: -1px;display: inline-block;border-radius: 5px;"><span class="plugin-count"></span></span>',
				'href'     => Helper::get_admin_url( 'analytics' ),
				'meta'     => [ 'title' => esc_html__( 'Review analytics and sitemaps', 'rank-math' ) ],
				'priority' => 20,
			]
		);
	}

	/**
	 * Purge cache.
	 *
	 * @return string
	 */
	public function analytics_clear_caches() {
		DB::purge_cache();
		return __( 'Analytics cache cleared.', 'rank-math' );
	}

	/**
	 * ReIndex posts.
	 *
	 * @return string
	 */
	public function analytics_reindex_posts() {
		DB::objects()->truncate();
		DB::table( 'postmeta' )->where( 'meta_key', 'rank_math_analytic_object_id' )->delete();
		delete_option( 'rank_math_flat_posts_done' );
		Data_Fetcher::get()->flat_posts();
		return __( 'Post re-index in progress.', 'rank-math' );
	}
}
