<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.84
 *
 * @since      1.0.84
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Start the scheduled URL Inspections if Analytics module is enabled.
 */
function rank_math_1_0_84_init_url_inspections() {
	$active_modules = get_option( 'rank_math_modules', [] );
	if ( ! is_array( $active_modules ) || ! in_array( 'analytics', $active_modules, true ) ) {
		return;
	}

	// Start first fetch in 15 minutes.
	$start = time() + ( 15 * MINUTE_IN_SECONDS );
	as_schedule_single_action( $start, 'rank_math/analytics/workflow', [ 'inspections', 0, null, null ], 'rank-math' );
}

rank_math_1_0_84_init_url_inspections();

/**
 * Fix collations for the Analytics tables.
 */
function rank_math_1_0_84_check_analytics_collations() {
	$tables = [
		'rank_math_analytics_ga',
		'rank_math_analytics_gsc',
		'rank_math_analytics_keyword_manager',
	];

	$objects_coll = \RankMath\Helpers\DB::get_table_collation( 'rank_math_analytics_objects' );
	foreach ( $tables as $table ) {
		\RankMath\Helpers\DB::check_collation( $table, 'all', $objects_coll );
	}
}

/**
 * Run collation fixer on multisite or simple install.
 *
 * @return void
 */
function rank_math_1_0_84_check_collations() {
	if ( is_multisite() ) {
		foreach ( get_sites() as $site ) {
			switch_to_blog( $site->blog_id );
			rank_math_1_0_84_check_analytics_collations();
			restore_current_blog();
		}

		return;
	}

	rank_math_1_0_84_check_analytics_collations();
}

rank_math_1_0_84_check_collations();

/**
 * Enable the Index Status tab by default.
 */
function rank_math_1_0_84_update_analytics_options() {
	$active_modules = get_option( 'rank_math_modules', [] );
	if ( ! is_array( $active_modules ) || ! in_array( 'analytics', $active_modules, true ) ) {
		return;
	}

	$options = get_option( 'rank_math_google_analytic_profile' );
	if ( ! is_array( $options ) ) {
		return;
	}

	$options['enable_index_status'] = true;
	update_option( 'rank_math_google_analytic_profile', $options );
}

rank_math_1_0_84_update_analytics_options();
