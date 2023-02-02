<?php
/**
 * The Updates routine for version 1.0.103.1
 *
 * @since      1.0.103.1
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Enable author sitemap.
 */
function rank_math_1_0_103_1_update_html_sitemap() {
	$all_opts = rank_math()->settings->all_raw();
	$sitemap_settings = $all_opts['sitemap'];
	if ( isset( $sitemap_settings['authors_sitemap'] ) ) {
		return;
	}

	$sitemap_settings['authors_sitemap'] = 'on';

	Helper::update_all_settings( null, null, $sitemap_settings );
	rank_math()->settings->reset();
}
rank_math_1_0_103_1_update_html_sitemap();
