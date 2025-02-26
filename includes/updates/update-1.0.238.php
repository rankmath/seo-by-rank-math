<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.238.
 *
 * @since      1.0.238
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;
defined( 'ABSPATH' ) || exit;

/**
 * Flush rewrite rules.
 */
function rank_math_1_0_238_flush_rules() {
	if (
		Helper::is_woocommerce_active() &&
		Helper::is_module_active( 'woocommerce' ) && (
		Helper::get_settings( 'general.wc_remove_category_base' ) ||
		Helper::get_settings( 'general.wc_remove_category_parent_slugs' ) )
	) {
		flush_rewrite_rules( true );
	}
}

rank_math_1_0_238_flush_rules();
