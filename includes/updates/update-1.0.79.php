<?php
/**
 * The Updates routine for version 1.0.79
 *
 * @since      1.0.79
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Set defaults for the new options added in this version.
 */
function rank_math_1_0_79_default_options() {
	$all_opts = rank_math()->settings->all_raw();
	$titles   = $all_opts['titles'];

	$titles['pt_post_slack_enhanced_sharing']    = 'on';
	$titles['pt_page_slack_enhanced_sharing']    = 'on';
	$titles['pt_product_slack_enhanced_sharing'] = 'on';
	$titles['author_slack_enhanced_sharing']     = 'on';
	foreach ( Helper::get_accessible_taxonomies() as $taxonomy => $object ) {
		$titles[ 'tax_' . $taxonomy . '_slack_enhanced_sharing' ] = 'on';
	}

	Helper::update_all_settings( null, $titles, null );
	rank_math()->settings->reset();
}

rank_math_1_0_79_default_options();
