<?php
/**
 * The Updates routine for version 1.0.14.
 *
 * @since      1.0.14
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

/**
 * Delete previous notices.
 */
function rank_math_1_0_14_clear_old_notices() {
	delete_option( 'rank_math_notifications' );
}

rank_math_1_0_14_clear_old_notices();
