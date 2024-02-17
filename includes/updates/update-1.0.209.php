<?php
/**
 * The Updates routine for version 1.0.209.
 *
 * @since      1.0.209
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Migrate Free Content AI users to a new server.
 */
function rank_math_1_0_209_content_ai_migrate_user() {
	// Early Bail if site doesn't have a Content AI plan.
	if ( ! Helper::get_content_ai_plan() ) {
		Helper::migrate_user_to_nest_js();
	}
}
rank_math_1_0_209_content_ai_migrate_user();
