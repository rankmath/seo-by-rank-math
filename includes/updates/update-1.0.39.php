<?php
/**
 * The Updates routine for version 1.0.39
 *
 * @since      1.0.39
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

/**
 * Convert old snippet variables to new one
 */
function rank_math_1_0_39_update_term_description() {
	$all_opts = rank_math()->settings->all_raw();
	$titles   = $all_opts['titles'];

	// Taxonomies.
	$taxonomies = Helper::get_accessible_taxonomies();

	foreach ( $taxonomies as $taxonomy => $object ) {
		if ( ! isset( $titles[ 'tax_' . $taxonomy . '_description' ] ) ) {
			$titles[ 'tax_' . $taxonomy . '_description' ] = '%term_description%';
		}
	}

	Helper::update_all_settings( null, $titles, null );
}
rank_math_1_0_39_update_term_description();
