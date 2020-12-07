<?php
/**
 * The Updates routine for version 1.0.30
 *
 * @since      1.0.30
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

/**
 * Clear SEO Analysis result.
 */
function rank_math_1_0_30_reset_options() {
	global $wpdb;

	$table_schema = [
		"ALTER TABLE {$wpdb->prefix}rank_math_redirections MODIFY sources TEXT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->charset}_bin NOT NULL;",
		"ALTER TABLE {$wpdb->prefix}rank_math_redirections_cache MODIFY from_url TEXT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->charset}_bin NOT NULL;",
	];

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	foreach ( $table_schema as $table ) {
		dbDelta( $table );
	}

	delete_option( 'rank_math_wc_category_base_redirection' );
}
rank_math_1_0_30_reset_options();
