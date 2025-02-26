<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.237.
 *
 * @since      1.0.237
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Alter the redirections table structure.
 */
function rank_math_1_0_237_update_redirection_structure() {
	// Early Bail if redirections table doesn't exist.
	if ( ! \RankMath\Helpers\DB::check_table_exists( 'rank_math_redirections' ) ) {
		return;
	}

	global $wpdb;
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}rank_math_redirections MODIFY COLUMN sources LONGTEXT NOT NULL" );
}
rank_math_1_0_237_update_redirection_structure();
