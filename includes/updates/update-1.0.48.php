<?php
/**
 * The Updates routine for version 1.0.48
 *
 * @since      1.0.48
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Tools\Schema_Converter;

defined( 'ABSPATH' ) || exit;

/**
 * Convert Existing Schema Data.
 */
function rank_math_1_0_48_convert_schema_data() {
	$posts = Schema_Converter::get()->find_posts();
	if ( ! empty( $posts['posts'] ) ) {
		Schema_Converter::get()->start( $posts['posts'] );
	}
}

rank_math_1_0_48_convert_schema_data();
