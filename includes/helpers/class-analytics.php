<?php
/**
 * The Analytics helpers.
 *
 * @since      1.0.86.2
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Helper;
use RankMath\Google\Authentication;
use RankMath\Google\Console;
use RankMath\Helpers\Schedule;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics class.
 */
trait Analytics {

	/**
	 * Can add Analytics Frontend stats.
	 *
	 * @return bool
	 */
	public static function can_add_frontend_stats() {
		return Authentication::is_authorized() &&
			Console::is_console_connected() &&
			Helper::has_cap( 'analytics' ) &&
			apply_filters( 'rank_math/analytics/frontend_stats', Helper::get_settings( 'general.analytics_stats' ) );
	}

	/**
	 * Can add Index Status tab on Analytics page.
	 *
	 * @return bool
	 */
	public static function can_add_index_status() {
		$profile = get_option( 'rank_math_google_analytic_profile', [] );
		if ( is_array( $profile ) && isset( $profile['enable_index_status'] ) ) {
			return $profile['enable_index_status'];
		}

		return true;
	}

	/**
	 * Schedule data fetch.
	 *
	 * @param int $fetch_gap The gap in days to fetch data.
	 */
	public static function schedule_data_fetch( $fetch_gap = 3 ) {
		$task_name = 'rank_math/analytics/data_fetch';

		$action_id = Schedule::unschedule_action( $task_name );

		// Delete cancel ID.
		if ( $action_id ) {
			global $wpdb;

			$wpdb->delete(
				$wpdb->actionscheduler_actions,
				[
					'action_id' => $action_id,
				]
			);

			$wpdb->delete(
				$wpdb->actionscheduler_logs,
				[
					'action_id' => $action_id,
				]
			);
		}

		$schedule_in_minute = wp_rand( 3, defined( 'RANK_MATH_PRO_FILE' ) ? 1380 : 4320 );
		$time_to_schedule   = ( strtotime( 'tomorrow' ) + ( $schedule_in_minute * MINUTE_IN_SECONDS ) );

		Schedule::recurring_action(
			$time_to_schedule,
			DAY_IN_SECONDS * $fetch_gap,
			$task_name,
			[],
			'rank-math'
		);
	}
}
