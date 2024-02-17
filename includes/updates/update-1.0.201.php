<?php
/**
 * The Updates routine for version 1.0.201
 *
 * @since      1.0.201
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Add default values for the new Content AI settings.
 */
function rank_math_1_0_201_content_ai_settings() {
	$all_opts                               = rank_math()->settings->all_raw();
	$general                                = $all_opts['general'];
	$general['content_ai_country']          = 'all';
	$general['content_ai_tone']             = 'Formal';
	$general['content_ai_audience']         = 'General Audience';
	$general['content_ai_language']         = Helper::content_ai_default_language();

	Helper::update_all_settings( $general, null, null );
	rank_math()->settings->reset();

	// Update credits to array format. This is needed in case the call to credits endpoint on server fails for some reason.
	Helper::update_credits( get_option( 'rank_math_ca_credits' ) );

	// Fetch credits, plan & refresh date.
	if ( Helper::is_site_connected() ) {
		Helper::get_content_ai_credits( true );
	}
}
rank_math_1_0_201_content_ai_settings();
