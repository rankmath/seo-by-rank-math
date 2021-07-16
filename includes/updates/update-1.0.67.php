<?php
/**
 * The Updates routine for version 1.0.67
 *
 * @since      1.0.67
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use MyThemeShop\Helpers\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Delete the IP column in the 404 logs table.
 */
function rank_math_1_0_67_alter_404_logs_table() {
	global $wpdb;

	$table_schema = "ALTER TABLE {$wpdb->prefix}rank_math_404_logs DROP COLUMN ip;";

	if ( ! is_multisite() ) {
		if ( DB::check_table_exists( 'rank_math_404_logs' ) ) {
			$wpdb->query( $table_schema ); // phpcs:ignore
		}
		return;
	}

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'" );
	if ( empty( $blog_ids ) ) {
		return;
	}

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );

		if ( DB::check_table_exists( 'rank_math_404_logs' ) ) {
			$wpdb->query( $table_schema ); // phpcs:ignore
		}

		restore_current_blog();
	}
}
rank_math_1_0_67_alter_404_logs_table();
