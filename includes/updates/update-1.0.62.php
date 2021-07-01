<?php
/**
 * The Updates routine for version 1.0.62
 *
 * @since      1.0.62
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

use RankMath\Helper;

/**
 * Remove duplicated data_fetch tasks.
 *
 * @return void
 */
function rank_math_1_0_62_remove_duplicated_data_fetch_tasks() {
	if ( ! Helper::is_module_active( 'analytics' ) ) {
		return;
	}

	$task_name = 'rank_math/analytics/data_fetch';
	$actions   = as_get_scheduled_actions(
		[
			'hook'    => $task_name,
			'status'  => 'pending',
			'orderby' => 'date',
			'order'   => 'DESC',
		],
		ARRAY_A
	);

	// Run cleaner only when two or more actions are scheduled.
	if ( count( $actions ) <= 1 ) {
		return;
	}

	$timestamp = as_next_scheduled_action( $task_name ); // Get first action timestamp.
	as_unschedule_all_actions( 'rank_math/analytics/data_fetch' );

	if ( false !== $timestamp ) {
		$fetch_gap = apply_filters( 'rank_math/analytics/fetch_gap', 7 );
		as_schedule_recurring_action(
			$timestamp,
			DAY_IN_SECONDS * $fetch_gap,
			$task_name,
			[],
			'rank-math'
		);
	}
}
rank_math_1_0_62_remove_duplicated_data_fetch_tasks();

/**
 * Reindex all posts to apply new schemas title feature.
 */
function rank_math_1_0_62_reindex_all_posts() {
	apply_filters( 'rank_math/tools/analytics_reindex_posts', 'Something went wrong.' );
}
rank_math_1_0_62_reindex_all_posts();
