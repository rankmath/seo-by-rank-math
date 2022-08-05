<?php
/**
 * Get URL Inspection data.
 *
 * @since      1.0.84
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use Exception;
use MyThemeShop\Helpers\DB as DB_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Url_Inspection class.
 */
class Url_Inspection {

	/**
	 * Holds the singleton instance of this class.
	 *
	 * @var Url_Inspection
	 */
	private static $instance;

	/**
	 * Singleton
	 */
	public static function get() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Schedule a new inspection for an object ID.
	 *
	 * @param string $page       URL to inspect (relative).
	 * @param string $reschedule What to do if the job already exists: reschedule for new time, or skip and keep old time.
	 * @param int    $delay      Number of seconds to delay the inspection from now.
	 */
	public function schedule_inspection( $page, $reschedule = true, $delay = 0 ) {
		$delay = absint( $delay );
		if ( $reschedule ) {
			as_unschedule_action( 'rank_math/analytics/get_inspections_data', [ $page ], 'rank_math/analytics/get_inspections_data' );
		} elseif ( as_has_scheduled_action( 'rank_math/analytics/get_inspections_data', [ $page ], 'rank_math/analytics/get_inspections_data' ) ) {
			// Already scheduled and reschedule = false.
			return;
		}

		if ( 0 === $delay ) {
			as_enqueue_async_action( 'rank_math/analytics/get_inspections_data', [ $page ], 'rank_math/analytics/get_inspections_data' );
			return;
		}

		$time = time() + $delay;
		as_schedule_single_action( $time, 'rank_math/analytics/get_inspections_data', [ $page ], 'rank_math/analytics/get_inspections_data' );
	}

	/**
	 * Fetch the inspection data for a URL, store it, and return it.
	 *
	 * @param string $page URL to inspect.
	 */
	public function inspect( $page ) {
		$inspection = \RankMath\Google\Url_Inspection::get()->get_inspection_data( $page );

		if ( empty( $inspection ) ) {
			return [];
		}

		DB::store_inspection( $inspection );

		return wp_parse_args( $inspection, DB::get_inspection_defaults() );
	}

	/**
	 * Get latest inspection results for each page.
	 *
	 * @param array $params   Parameters.
	 * @param int   $per_page Number of items per page.
	 */
	public function get_inspections( $params, $per_page ) {
		// Early Bail!!
		if ( ! DB_Helper::check_table_exists( 'rank_math_analytics_inspections' ) ) {
			return;
		}

		return DB::get_inspections( $params, $per_page );
	}

	/**
	 * Check if the "Enable Index Status Tab" option is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$profile = get_option( 'rank_math_google_analytic_profile', [] );
		if ( empty( $profile) || ! is_array( $profile ) ) {
			return false;
		}

		$enable_index_status = true;
		if ( isset( $profile['enable_index_status'] ) ) {
			$enable_index_status = $profile['enable_index_status'];
		}

		return $enable_index_status;
	}
}
