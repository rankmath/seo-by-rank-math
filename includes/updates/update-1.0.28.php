<?php
/**
 * The Updates routine for version 1.0.28.
 *
 * @since      1.0.28
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clear SEO Analysis result.
 */
function rank_math_1_0_28_reset_options() {
	delete_option( 'rank_math_seo_analysis_results' );
}
rank_math_1_0_28_reset_options();
