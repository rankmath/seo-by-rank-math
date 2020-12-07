<?php
/**
 * The Updates routine for version 1.0.46
 *
 * @since      1.0.46
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Enable the core auto update option if it's set in RM.
 */
function rank_math_1_0_46_maybe_enable_auto_update() {
	if ( Helper::get_settings( 'general.enable_auto_update' ) ) {
		Helper::toggle_auto_update_setting( 'on' );
	}
}
rank_math_1_0_46_maybe_enable_auto_update();
