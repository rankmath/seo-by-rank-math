<?php
/**
 * The Updates routine for version 1.0.50
 *
 * @since      1.0.50
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Enable the new Analytis module
 */
function rank_math_1_0_50_delete_analytic_tables() {
	global $wpdb;

	if ( defined( 'RANK_MATH_PRO_FILE' ) ) {
		return;
	}

	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_analytics_ga" ); // phpcs:ignore
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_analytics_adsense" ); // phpcs:ignore
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_analytics_object_links" ); // phpcs:ignore
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_analytics_keyword_manager" ); // phpcs:ignore

	// Old tables.
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_links" ); // phpcs:ignore
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_redirections_0_9_17" ); // phpcs:ignore
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_redirection_sources_0_9_17" ); // phpcs:ignore
}

/**
 * Recreate table if not exits.
 */
function rank_math_1_0_50_recreate_as() {
	global $wpdb;

	if ( Conditional::is_woocommerce_active() ) {
		return;
	}

	$table_list = [
		'actionscheduler_actions',
		'actionscheduler_logs',
		'actionscheduler_groups',
		'actionscheduler_claims',
	];

	$found_tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}actionscheduler%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	foreach ( $table_list as $table_name ) {
		if ( ! in_array( $wpdb->prefix . $table_name, $found_tables, true ) ) {
			rank_math_1_0_50_recreate_tables();
			return;
		}
	}
}

/**
 * Force the data store schema updates.
 */
function rank_math_1_0_50_recreate_tables() {
	$store = new ActionScheduler_HybridStore();
	add_action( 'action_scheduler/created_table', [ $store, 'set_autoincrement' ], 10, 2 );

	$store_schema  = new ActionScheduler_StoreSchema();
	$logger_schema = new ActionScheduler_LoggerSchema();
	$store_schema->register_tables( true );
	$logger_schema->register_tables( true );

	remove_action( 'action_scheduler/created_table', [ $store, 'set_autoincrement' ], 10 );
}

rank_math_1_0_50_recreate_as();
rank_math_1_0_50_delete_analytic_tables();
