<?php
/**
 * The Updates routine for version 1.0.208.
 *
 * @since      1.0.208
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Migrate users to a new server.
 */
function rank_math_1_0_208_content_ai_migrate_user() {
	// Early Bail if site is not connected or doesn't have a Content AI plan.
	$registered = Admin_Helper::get_registration_data();
	if (
		! Helper::get_content_ai_plan() ||
		empty( $registered ) ||
		empty( $registered['connected'] ) ||
		empty( $registered['api_key'] ) ||
		empty( $registered['username'] )
	) {
		return;
	}

	Helper::migrate_user_to_nest_js( $registered['username'] );

	set_site_transient( 'rank_math_content_ai_migrating_user', true, 300 ); // Set transient to show Error CTA on Content AI page for 5 minutes to complete the migration.
}
rank_math_1_0_208_content_ai_migrate_user();
