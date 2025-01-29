<?php
/**
 *  Workflow Base.
 *
 * @since      1.0.54
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics\Workflow;

use RankMath\Helper;
use RankMath\Analytics\DB;
use RankMath\Traits\Hooker;
use function as_schedule_single_action;
use function has_filter;

defined( 'ABSPATH' ) || exit;

/**
 * Base class.
 */
abstract class Base {

	use Hooker;

	/**
	 * Start fetching process.
	 *
	 * @param  integer $days   Number of days to fetch from past.
	 * @param  string  $action Action to perform.
	 * @return integer
	 */
	public function create_jobs( $days = 90, $action = 'console' ) {
		$count    = $this->add_data_pull( $days + 3, $action );
		$time_gap = $this->get_schedule_gap();

		Workflow::add_clear_cache( time() + ( $time_gap * ( $count + 1 ) ) );

		update_option( 'rank_math_analytics_first_fetch', 'fetching' );

		return $count;
	}

	/**
	 * Add data pull jobs.
	 *
	 * @param integer $days Number of days to fetch from past.
	 * @param  string  $action Action to perform.
	 * @return integer
	 */
	private function add_data_pull( $days, $action = 'console' ) {
		$count    = 1;
		$start    = Helper::get_midnight( time() + DAY_IN_SECONDS );
		$interval = $this->get_data_interval();
		$time_gap = $this->get_schedule_gap();

		$hook = "get_{$action}_data";
		if ( 1 === $interval ) {
			for ( $current = 1; $current <= $days; $current++ ) {
				$date = date_i18n( 'Y-m-d', $start - ( DAY_IN_SECONDS * $current ) );
				if ( ! DB::date_exists( $date, $action ) ) {
					++$count;
					as_schedule_single_action(
						time() + ( $time_gap * $count ),
						'rank_math/analytics/' . $hook,
						[ $date ],
						'rank-math'
					);
				}
			}
		} else {
			for ( $current = 1; $current <= $days; $current = $current + $interval ) {
				for ( $j = 0; $j < $interval; $j++ ) {
					$date = date_i18n( 'Y-m-d', $start - ( DAY_IN_SECONDS * ( $current + $j ) ) );
					if ( ! DB::date_exists( $date, $action ) ) {
						++$count;
						as_schedule_single_action(
							time() + ( $time_gap * $count ),
							'rank_math/analytics/' . $hook,
							[ $date ],
							'rank-math'
						);
					}
				}
			}
		}

		return $count;
	}

	/**
	 * Get data interval.
	 *
	 * @return int
	 */
	private function get_data_interval() {
		$is_custom = has_filter( 'rank_math/analytics/app_url' );

		return $is_custom ? $this->do_filter( 'analytics/data_interval', 7 ) : 7;
	}

	/**
	 * Get schedule gap.
	 *
	 * @return int
	 */
	private function get_schedule_gap() {
		return $this->do_filter( 'analytics/schedule_gap', 30 );
	}

	/**
	 * Check if google profile is updated.
	 *
	 * @param string $param Google profile param name.
	 * @param string $previous_value Previous profile data.
	 * @param string $new_value  New posted profile data.
	 *
	 * @return boolean
	 */
	public function is_profile_updated( $param, $previous_value, $new_value ) {
		if (
			! is_null( $previous_value ) &&
			! is_null( $new_value ) &&
			isset( $previous_value[ $param ] ) &&
			isset( $new_value[ $param ] ) &&
			$previous_value[ $param ] === $new_value[ $param ]
		) {
			return false;
		}

		return true;
	}

	/**
	 * Function to get the dates.
	 *
	 * @param int $days Number of days.
	 *
	 * @return array
	 */
	public static function get_dates( $days = 90 ) {
		$end   = Helper::get_midnight( strtotime( '-1 day', time() ) );
		$start = strtotime( '-' . $days . ' day', $end );

		return [
			'start_date' => date_i18n( 'Y-m-d', $start ),
			'end_date'   => date_i18n( 'Y-m-d', $end ),
		];
	}

	/**
	 * Schedule single action
	 *
	 * @param int     $days   Number of days.
	 * @param string  $action Name of the action hook.
	 * @param array   $args   Arguments to pass to callbacks when the hook triggers.
	 * @param string  $group  The group to assign this job to.
	 * @param boolean $unique Whether the action should be unique.
	 */
	public function schedule_single_action( $days = 90, $action = '', $args = [], $group = 'rank-math', $unique = false ) {
		$timestamp = get_option( 'rank_math_analytics_last_single_action_schedule_time', time() );
		$time_gap  = $this->get_schedule_gap();

		$dates = self::get_dates( $days );

		// Get the analytics dates in which analytics data is actually available.
		$days = apply_filters(
			'rank_math/analytics/get_' . $action . '_days',
			[
				'start_date' => $dates['start_date'],
				'end_date'   => $dates['end_date'],
			]
		);

		// No days then don't schedule the action.
		if ( empty( $days ) ) {
			return;
		}

		foreach ( $days as $day ) {

			// Next schedule time.
			$timestamp = $timestamp + $time_gap;

			$args = wp_parse_args(
				[
					'start_date' => $day['start_date'],
					'end_date'   => $day['end_date'],
				],
				$args
			);

			as_schedule_single_action(
				$timestamp,
				'rank_math/analytics/get_' . $action . '_data',
				$args,
				$group,
				$unique
			);

		}

		Workflow::add_clear_cache( $timestamp );

		// Update timestamp.
		update_option( 'rank_math_analytics_last_single_action_schedule_time', $timestamp );
	}
}
