<?php
/**
 * The Updates routine for version 1.0.36.1
 *
 * @since      1.0.36.1
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

/**
 * Clear notice about disconnection.
 */
function rank_math_1_0_36_1_reset_options() {
	rank_math()->notification->remove_by_id( 'connect_data_cleared' );
}
rank_math_1_0_36_1_reset_options();
