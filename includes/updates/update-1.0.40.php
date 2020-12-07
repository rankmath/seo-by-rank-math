<?php
/**
 * The Updates routine for version 1.0.40
 *
 * @since      1.0.40
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Set Elementor Library Add metabox value to false.
 */
function rank_math_1_0_40_update_titles_settings() {
	$all_opts = rank_math()->settings->all_raw();
	$titles   = $all_opts['titles'];

	if ( isset( $titles['pt_elementor_library_add_meta_box'] ) ) {
		$titles['pt_elementor_library_add_meta_box'] = 'off';
	}

	Helper::update_all_settings( null, $titles, null );
}
rank_math_1_0_40_update_titles_settings();
