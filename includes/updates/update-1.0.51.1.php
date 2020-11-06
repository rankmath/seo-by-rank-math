<?php
/**
 * The Updates routine for version 1.0.51.1
 *
 * @since      1.0.51.1
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;
use MyThemeShop\Helpers\WordPress;
use MyThemeShop\Helpers\Conditional;

/**
 * Enable the new Analytis module
 */
function rank_math_1_0_51_1_reindex_all_posts() {
	apply_filters( 'rank_math/tools/analytics_reindex_posts', 'Something went wrong.' );
}

rank_math_1_0_51_1_reindex_all_posts();
