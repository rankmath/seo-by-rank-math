<?php
/**
 * The Updates routine for version 1.0.37
 *
 * @since      1.0.37
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Add Dashboard role to admins.
 */
function rank_math_1_0_37_add_new_caps() {
	update_option( 'rank_math_registration_skip', 1 );
}
rank_math_1_0_37_add_new_caps();

/**
 * Convert old snippet variables to new one
 */
function rank_math_1_0_37_convert_snippet_variables() {
	$all_opts = rank_math()->settings->all_raw();
	$titles   = $all_opts['titles'];

	// Post Types.
	$post_types   = Helper::get_accessible_post_types();
	$post_types[] = 'product';

	foreach ( $post_types as $post_type ) {
		if (
			isset( $titles[ 'pt_' . $post_type . '_default_snippet_name' ] ) &&
			'%title%' === $titles[ 'pt_' . $post_type . '_default_snippet_name' ]
		) {
			$titles[ 'pt_' . $post_type . '_default_snippet_name' ] = '%seo_title%';
		}

		if (
			isset( $titles[ 'pt_' . $post_type . '_default_snippet_desc' ] ) &&
			'%excerpt%' === $titles[ 'pt_' . $post_type . '_default_snippet_desc' ]
		) {
			$titles[ 'pt_' . $post_type . '_default_snippet_desc' ] = '%seo_description%';
		}
	}

	Helper::update_all_settings( null, $titles, null );
	rank_math()->settings->reset();
}
rank_math_1_0_37_convert_snippet_variables();
