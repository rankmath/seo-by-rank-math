<?php
/**
 * Show Analytics stats on frontend.
 *
 * @since      1.0.86
 * @package    RankMath
 * @subpackage RankMath\Analytics
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use RankMath\Helper;
use RankMath\KB;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics_Stats class.
 */
class Analytics_Stats {

	use Hooker;

	/**
	 * The Constructor
	 */
	public function __construct() {
		if ( ! Helper::can_add_frontend_stats() ) {
			return;
		}

		$this->action( 'wp_enqueue_scripts', 'enqueue' );
	}

	/**
	 * Enqueue Styles and Scripts
	 */
	public function enqueue() {
		if ( ! is_singular() || is_admin() || is_preview() || Helper::is_divi_frontend_editor() ) {
			return;
		}

		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );
		wp_enqueue_style( 'rank-math-analytics-stats', $uri . '/assets/css/admin-bar.css', null, rank_math()->version );
		wp_enqueue_script( 'rank-math-analytics-stats', $uri . '/assets/js/admin-bar.js', [ 'jquery', 'wp-api-fetch', 'wp-element', 'wp-components' ], rank_math()->version, true );

		Helper::add_json( 'isAnalyticsConnected', \RankMath\Google\Analytics::is_analytics_connected() );
		Helper::add_json( 'hideFrontendStats', get_user_meta( get_current_user_id(), 'rank_math_hide_frontend_stats', true ) );

		Helper::add_json( 'links', KB::get_links() );
	}
}
