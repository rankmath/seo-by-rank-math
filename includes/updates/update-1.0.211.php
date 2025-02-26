<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.211.
 *
 * @since      1.0.211
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add value in options table to show a notification in Dashboard menu on existing setup.
 */
function rank_math_1_0_211_content_ai_store_notification_option() {
	update_option( 'rank_math_view_modules', true, false );
}
rank_math_1_0_211_content_ai_store_notification_option();
