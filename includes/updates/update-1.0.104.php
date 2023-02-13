<?php
/**
 * The Updates routine for version 1.0.104
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Update TOC block settings on existing sites.
 */
function rank_math_1_0_104_toc_block_settings() {
	$all_opts                        = rank_math()->settings->all_raw();
	$general                         = $all_opts['general'];
	$general['toc_block_title']      = 'Table of Contents';
	$general['toc_block_list_style'] = 'ul';

	Helper::update_all_settings( $general, null, null );
	rank_math()->settings->reset();
}
rank_math_1_0_104_toc_block_settings();
