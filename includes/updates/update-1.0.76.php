<?php
/**
 * The Updates routine for version 1.0.76
 *
 * @since      1.0.76
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Add content_ai capability.
 */
function rank_math_1_0_76_add_content_ai_capability() {
	wp_roles();

	foreach ( WordPress::get_roles() as $slug => $role ) {
		$role = get_role( $slug );
		if ( ! $role ) {
			continue;
		}

		if ( $role->has_cap( 'manage_options' ) ) {
			$role->add_cap( 'rank_math_content_ai' );
		}
	}

	Helper::update_modules( [ 'content-ai' => 'on' ] );

	$all_opts = rank_math()->settings->all_raw();
	$general  = $all_opts['general'];

	// Post Types.
	$post_types = Helper::get_accessible_post_types();
	if ( isset( $post_types['attachment'] ) ) {
		unset( $post_types['attachment'] );
	}

	$general['content_ai_post_types'] = array_keys( $post_types );

	Helper::update_all_settings( $general, null, null );
	rank_math()->settings->reset();
}

rank_math_1_0_76_add_content_ai_capability();
