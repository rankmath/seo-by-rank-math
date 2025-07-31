<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.250
 *
 * @since      1.0.250
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Update LLMS Post types on existing sites.
 */
function rank_math_1_0_250_update_llms_post_types() {
	$post_types = Helper::get_accessible_post_types();
	if ( isset( $post_types['attachment'] ) ) {
		unset( $post_types['attachment'] );
	}

	if ( empty( $post_types ) ) {
		return;
	}

	$all_opts                   = rank_math()->settings->all_raw();
	$general                    = $all_opts['general'];
	$general['llms_post_types'] = array_keys( $post_types );

	Helper::update_all_settings( $general, null, null );
	rank_math()->settings->reset();
}
rank_math_1_0_250_update_llms_post_types();
