<?php
/**
 * The Updates routine for version 1.0.52
 *
 * @since      1.0.52
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enable the new Analytis module
 */
function rank_math_1_0_52_reindex_all_posts() {
	apply_filters( 'rank_math/tools/analytics_reindex_posts', 'Something went wrong.' );
}

rank_math_1_0_52_reindex_all_posts();
