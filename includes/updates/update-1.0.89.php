<?php
/**
 * The Updates routine for version 1.0.89
 *
 * @since      1.0.89
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Convert keywords used in Content AI to lower case on existing sites.
 */
function rank_math_1_0_89_update_contentai_data() {
	$data = get_option( 'rank_math_ca_data' );
	if ( empty( $data ) ) {
		return;
	}

	foreach ( $data as $country => $keywords ) {
		if ( empty( $keywords ) ) {
			continue;
		}

		$ret = [];
		foreach ( $keywords as $key => $keyword ) {
			$ret[ mb_strtolower( $key ) ] = $keyword;
		}

		if ( ! empty( $ret ) ) {
			$data[ $country ] = $ret;
		}
	}

	update_option( 'rank_math_ca_data', $data );
}

rank_math_1_0_89_update_contentai_data();
