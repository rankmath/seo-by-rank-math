<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.110
 *
 * @since      1.0.110
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Update the deprecated schema type Attorney and used it as LegalService.
 */
function rank_math_1_0_110_update_local_business_type() {
	$all_opts = rank_math()->settings->all_raw();
	$titles   = $all_opts['titles'];

	if ( 'Attorney' !== $titles['local_business_type'] ) {
		return;
	}

	$titles['local_business_type'] = 'LegalService';

	Helper::update_all_settings( null, $titles, null );
	rank_math()->settings->reset();
}
rank_math_1_0_110_update_local_business_type();
