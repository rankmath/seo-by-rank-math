<?php
/**
 * The Updates routine for version 1.0.47
 *
 * @since      1.0.47
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use MyThemeShop\Helpers\DB;

defined( 'ABSPATH' ) || exit;

/**
 * The update routine file functiont to add primary key in rank_math_internal_meta table.
 */
function rank_math_1_0_47_update_internal_meta_table() {
	if ( ! is_multisite() ) {
		rank_math_1_0_47_add_primary_key_to_internal_meta_table();
		return;
	}

	global $wpdb;
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'" );
	if ( ! empty( $blog_ids ) ) {
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			rank_math_1_0_47_add_primary_key_to_internal_meta_table();
			restore_current_blog();
		}
	}
}

/**
 * Add Primary key to internal_meta table.
 */
function rank_math_1_0_47_add_primary_key_to_internal_meta_table() {
	if ( ! DB::check_table_exists( 'rank_math_internal_meta' ) ) {
		return;
	}

	global $wpdb;
	$row = $wpdb->get_results( "SHOW INDEXES FROM {$wpdb->prefix}rank_math_internal_meta WHERE Key_name = 'PRIMARY'" );
	if ( empty( $row ) ) {
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}rank_math_internal_meta DROP INDEX object_id, ADD PRIMARY KEY(object_id);" );
	}
}

rank_math_1_0_47_update_internal_meta_table();
