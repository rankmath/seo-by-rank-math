<?php
/**
 * The Updates routine for version 1.0.86
 *
 * @since      1.0.86
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Update Analytics Stats on existing sites.
 */
function rank_math_1_0_86_update_analytics_stats() {
	$all_opts                   = rank_math()->settings->all_raw();
	$general                    = $all_opts['general'];
	$general['analytics_stats'] = 'on';

	Helper::update_all_settings( $general, null, null );
	rank_math()->settings->reset();
}
rank_math_1_0_86_update_analytics_stats();
