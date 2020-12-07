<?php
/**
 * The Updates routine for version 1.0.37.3
 *
 * @since      1.0.37.3
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Update Internal links.
 */
function rank_math_1_0_37_3_execute_internal_links_cron() {
	do_action( 'rank_math/links/internal_links' );
}
rank_math_1_0_37_3_execute_internal_links_cron();
