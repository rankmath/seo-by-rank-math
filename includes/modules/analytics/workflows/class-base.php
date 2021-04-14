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
use function has_filter;
use RankMath\Analytics\DB;
use RankMath\Traits\Hooker;
use function as_schedule_single_action;

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
		$count    = $this->add_data_pull( $days + 2, $action );
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
					$count++;
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
						$count++;
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
	 * @param string $prev Previous profile data.
	 * @param string $new  New posted profile data.
	 *
	 * @return boolean
	 */
	public function is_profile_updated( $param, $prev, $new ) {
		if (
			! is_null( $prev ) &&
			! is_null( $new ) &&
			isset( $prev[ $param ] ) &&
			isset( $new[ $param ] ) &&
			$prev[ $param ] === $new[ $param ]
		) {
			return false;
		}

		return true;
	}
}
