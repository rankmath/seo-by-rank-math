<?php
/**
 * The Updates routine for version 1.0.202.
 *
 * @since      1.0.202
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Convert English Language stored in Content AI settings to US English.
 */
function rank_math_1_0_202_content_ai_convert_english_language() {
	$all_opts = rank_math()->settings->all_raw();
	$general  = $all_opts['general'];
	if ( ! empty( $general['content_ai_language'] ) && 'English' === $general['content_ai_language'] ) {
		$general['content_ai_language'] = 'US English';
	}

	$general['cotnent_ai_enable_grammarly'] = 'on';

	Helper::update_all_settings( $general, null, null );
	rank_math()->settings->reset();
}
rank_math_1_0_202_content_ai_convert_english_language();
