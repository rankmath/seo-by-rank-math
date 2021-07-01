<?php
/**
 * The Updates routine for version 1.0.63
 *
 * @since      1.0.63
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Delete rank_math_sc_analytics table.
 */
function rank_math_1_0_63_delete_old_analytics_table() {
	global $wpdb;
	if ( ! is_multisite() ) {
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_sc_analytics" );
		return;
	}

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'" );
	if ( ! empty( $blog_ids ) ) {
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_sc_analytics" );
			restore_current_blog();
		}
	}
}
rank_math_1_0_63_delete_old_analytics_table();
