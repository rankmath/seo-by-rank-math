<?php
/**
 * The Updates routine for version 1.0.55
 *
 * @since      1.0.55
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Reschedule analytics's daily tasks.
 *
 * @return void
 */
function rank_math_1_0_55_reschedule_analytics_daily_tasks() {

	if ( ! Helper::is_module_active( 'analytics' ) ) {
		return;
	}

	$daily_task_action = 'rank_math/analytics/daily_tasks';

	as_unschedule_all_actions( $daily_task_action );

	$fetch_gap = apply_filters( 'rank_math/analytics/fetch_gap', 7 );

	if ( false === as_next_scheduled_action( $daily_task_action ) ) {
		$schedule_in_minute = rand( 3, defined( 'RANK_MATH_PRO_FILE' ) ? 1380 : 4320 );
		$time_to_schedule   = ( strtotime( 'now' ) + ( $schedule_in_minute * MINUTE_IN_SECONDS ) );
		as_schedule_recurring_action(
			$time_to_schedule,
			DAY_IN_SECONDS * $fetch_gap,
			$daily_task_action,
			[],
			'rank-math'
		);
	}
}

rank_math_1_0_55_reschedule_analytics_daily_tasks();
