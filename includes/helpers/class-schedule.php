<?php
/**
 * Schedule helpers.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Schedule class.
 */
class Schedule {

	/**
	 * Schedule a recurring action
	 *
	 * @param int    $timestamp When the first instance of the job will run.
	 * @param int    $interval_in_seconds How long to wait between runs.
	 * @param string $hook The hook to trigger.
	 * @param array  $args Arguments to pass when the hook triggers.
	 * @param string $group The group to assign this job to.
	 * @param bool   $unique Whether the action should be unique. It will not be scheduled if another pending or running action has the same hook and group parameters.
	 * @param int    $priority Lower values take precedence over higher values. Defaults to 10, with acceptable values falling in the range 0-255.
	 *
	 * @return int The action ID. Zero if there was an error scheduling the action.
	 */
	public static function recurring_action( $timestamp, $interval_in_seconds, $hook, $args = [], $group = '', $unique = false, $priority = 10 ) {
		$id = as_schedule_recurring_action( $timestamp, $interval_in_seconds, $hook, $args, $group, $unique, $priority );

		if ( ! $id ) {
			self::notify(
				esc_html__( 'There was an issue scheduling the async action required for Analytics; if the problem persists, please contact our support team.', 'rank-math' ),
				'recurring'
			);
		}

		return $id;
	}

	/**
	 * Schedule an action to run one time
	 *
	 * @param int    $timestamp When the job will run.
	 * @param string $hook The hook to trigger.
	 * @param array  $args Arguments to pass when the hook triggers.
	 * @param string $group The group to assign this job to.
	 * @param bool   $unique Whether the action should be unique. It will not be scheduled if another pending or running action has the same hook and group parameters.
	 * @param int    $priority Lower values take precedence over higher values. Defaults to 10, with acceptable values falling in the range 0-255.
	 *
	 * @return int The action ID. Zero if there was an error scheduling the action.
	 */
	public static function single_action( $timestamp, $hook, $args = [], $group = '', $unique = false, $priority = 10 ) {
		$id = as_schedule_single_action( $timestamp, $hook, $args, $group, $unique, $priority );

		if ( ! $id ) {
			self::notify(
				esc_html__( 'There was an issue scheduling a single action required for Analytics; if the problem persists, please contact our support team.', 'rank-math' ),
				'schedule'
			);
		}

		return $id;
	}

	/**
	 * Cancel the next occurrence of a scheduled action.
	 *
	 * While only the next instance of a recurring or cron action is unscheduled by this method, that will also prevent
	 * all future instances of that recurring or cron action from being run. Recurring and cron actions are scheduled in
	 * a sequence instead of all being scheduled at once. Each successive occurrence of a recurring action is scheduled
	 * only after the former action is run. If the next instance is never run, because it's unscheduled by this function,
	 * then the following instance will never be scheduled (or exist), which is effectively the same as being unscheduled
	 * by this method also.
	 *
	 * @param string $hook The hook that the job will trigger.
	 * @param array  $args Args that would have been passed to the job.
	 * @param string $group The group the job is assigned to.
	 *
	 * @return int|null The scheduled action ID if a scheduled action was found, or null if no matching action found.
	 */
	public static function unschedule_action( $hook, $args = [], $group = '' ) {
		$id = as_unschedule_action( $hook, $args, $group );

		if ( ! $id ) {
			self::notify(
				esc_html__( 'There was an issue scheduling the recurring action required for Analytics; if the problem persists, please contact our support team.', 'rank-math' ),
				'unschedule'
			);
		}

		return $id;
	}

	/**
	 * Enqueue an action to run one time, as soon as possible
	 *
	 * @param string $hook The hook to trigger.
	 * @param array  $args Arguments to pass when the hook triggers.
	 * @param string $group The group to assign this job to.
	 * @param bool   $unique Whether the action should be unique. It will not be scheduled if another pending or running action has the same hook and group parameters.
	 * @param int    $priority Lower values take precedence over higher values. Defaults to 10, with acceptable values falling in the range 0-255.
	 *
	 * @return int The action ID. Zero if there was an error scheduling the action.
	 */
	public static function async_action( $hook, $args = [], $group = '', $unique = false, $priority = 10 ) {
		$id = as_enqueue_async_action( $hook, $args, $group, $unique, $priority );

		if ( ! $id ) {
			self::notify(
				esc_html__( 'There was an issue unscheduling a background action related to Analytics; if the problem persists, please contact our support team.', 'rank-math' ),
				'async'
			);
		}

		return $id;
	}

	/**
	 * Notify
	 *
	 * @param string $message Message to display.
	 * @param string $type Type of notification.
	 */
	public static function notify( $message = '', $type = '' ) {
		Helper::add_notification(
			$message,
			[
				'type'   => 'error',
				'id'     => 'rank_math_action_scheduler_' . $type,
				'screen' => 'rank-math_page_rank-math-analytics',
			]
		);
		rank_math()->notification->update_storage();
	}
}
