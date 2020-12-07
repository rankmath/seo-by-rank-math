<?php
/**
 * The Updates routine for version 1.0.18.
 *
 * @since      1.0.18
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Change of authentication end-point notice.
 */
function rank_math_1_0_18_authentication_change() {
	$is_skipped = Helper::is_plugin_active_for_network() ? get_blog_option( get_main_site_id(), 'rank_math_registration_skip' ) : get_option( 'rank_math_registration_skip' );

	Helper::add_notification(
		sprintf(
			'Rank Math has a new home. Please <a href="%s">Register for FREE</a> and <a href="%s">CONNECT your account</a> again.',
			'https://rankmath.com/#signup',
			true === boolval( $is_skipped ) ? admin_url( 'admin.php?page=rank-math&view=help' ) : admin_url( 'admin.php?page=rank-math-registration' )
		),
		[
			'type' => 'error',
			'id'   => 'rank_math_authentication_change',
		]
	);
}

rank_math_1_0_18_authentication_change();
