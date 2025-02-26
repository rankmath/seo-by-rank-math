<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.107.3
 *
 * @since      1.0.107.3
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Replace %searchphrase% variable to %search_query%
 */
function rank_math_1_0_107_3_replace_search_variable() {
	$all_opts = rank_math()->settings->all_raw();
	$titles   = $all_opts['titles'];

	$titles['search_title'] = str_replace( '%searchphrase%', '%search_query%', $titles['search_title'] );

	RankMath\Helper::update_all_settings( null, $titles, null );
	rank_math()->settings->reset();
}
rank_math_1_0_107_3_replace_search_variable();
