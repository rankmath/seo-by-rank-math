<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * @link    https://rankmath.com
 * @since   0.9.0
 * @package RANK_MATH
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clear cron.
wp_clear_scheduled_hook( 'rank_math_tracker_send_event' );
wp_clear_scheduled_hook( 'rank_math_search_console_get_analytics' );

// Set rank_math_clear_data_on_uninstall to TRUE to delete all data on uninstall.
if ( true === apply_filters( 'rank_math_clear_data_on_uninstall', false ) ) {

	if ( ! is_multisite() ) {
		rank_math_remove_data();
		return;
	}

	global $wpdb;

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'" );
	if ( ! empty( $blog_ids ) ) {
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			rank_math_remove_data();
			restore_current_blog();
		}
	}
}

/**
 * Removes ALL plugin data
 *
 * @since 1.0.35
 */
function rank_math_remove_data() {
	// Delete all options.
	rank_math_delete_options();

	// Delete meta for post, user and term.
	rank_math_delete_meta( 'post' );
	rank_math_delete_meta( 'user' );
	rank_math_delete_meta( 'term' );

	// Drop Tables.
	rank_math_drop_table( '404_logs' );
	rank_math_drop_table( 'redirections' );
	rank_math_drop_table( 'redirections_cache' );
	rank_math_drop_table( 'internal_links' );
	rank_math_drop_table( 'internal_meta' );
	rank_math_drop_table( 'analytics_gsc' );
	rank_math_drop_table( 'analytics_objects' );

	// Remove Capabilities.
	/**
	 * PSR-4 Autoload.
	 */
	include dirname( __FILE__ ) . '/vendor/autoload.php';

	\RankMath\Role_Manager\Capability_Manager::get()->remove_capabilities();

	// Clear any cached data that has been removed.
	wp_cache_flush();
}

/**
 * Delete options.
 *
 * @return void
 */
function rank_math_delete_options() {
	global $wpdb;

	$where = $wpdb->prepare( 'WHERE option_name LIKE %s OR option_name LIKE %s', '%' . $wpdb->esc_like( 'rank-math' ) . '%', '%' . $wpdb->esc_like( 'rank_math' ) . '%' );
	$wpdb->query( "DELETE FROM {$wpdb->prefix}options {$where}" ); // phpcs:ignore
}

/**
 * Delete post meta.
 *
 * @param string $table Table name.
 * @return void
 */
function rank_math_delete_meta( $table = 'post' ) {
	global $wpdb;

	$where = $wpdb->prepare( 'WHERE meta_key LIKE %s OR meta_key LIKE %s', '%' . $wpdb->esc_like( 'rank-math' ) . '%', '%' . $wpdb->esc_like( 'rank_math' ) . '%' );
	$wpdb->query( "DELETE FROM {$wpdb->prefix}{$table}meta {$where}" ); // phpcs:ignore
}

/**
 * Drop table from database
 *
 * @param string $name Name of table.
 * @return void
 */
function rank_math_drop_table( $name ) {
	global $wpdb;

	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rank_math_{$name}" ); // phpcs:ignore
}
