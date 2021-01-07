<?php
/**
 * The Updates routine for version 1.0.56
 *
 * @since      1.0.56
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;
use RankMath\Google\Permissions;

defined( 'ABSPATH' ) || exit;

/**
 * Reschedule analytics's daily tasks.
 *
 * @return void
 */
function rank_math_1_0_56_analytics_changes() {
	if ( ! Helper::is_module_active( 'analytics' ) ) {
		return;
	}

	// Get old task.
	$actions = as_get_scheduled_actions( [ 'hook' => 'rank_math/analytics/daily_tasks' ] );
	if ( ! empty( $actions ) ) {
		$action           = current( $actions );
		$schedule         = $action->get_schedule();
		$time_to_schedule = $schedule->get_date()->getTimestamp();
	} else {
		$schedule_in_minute = wp_rand( 3, defined( 'RANK_MATH_PRO_FILE' ) ? 1380 : 4320 );
		$time_to_schedule   = ( strtotime( 'now' ) + ( $schedule_in_minute * MINUTE_IN_SECONDS ) );
	}

	// Clear old task.
	as_unschedule_all_actions( 'rank_math/analytics/daily_tasks' );

	// Add new action for scheduler.
	$task_name = 'rank_math/analytics/data_fetch';
	$fetch_gap = apply_filters( 'rank_math/analytics/fetch_gap', 7 );

	if ( false === as_next_scheduled_action( $task_name ) ) {
		as_schedule_recurring_action(
			$time_to_schedule,
			DAY_IN_SECONDS * $fetch_gap,
			$task_name,
			[],
			'rank-math'
		);
	}

	// Fetch permission data.
	Permissions::fetch();
}

rank_math_1_0_56_analytics_changes();
