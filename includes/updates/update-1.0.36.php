<?php
/**
 * The Updates routine for version 1.0.36
 *
 * @since      1.0.36
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Clear SEO Analysis result.
 */
function rank_math_1_0_36_reset_options() {
	Admin_Helper::get_registration_data( false );
}
rank_math_1_0_36_reset_options();
