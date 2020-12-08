<?php
/**
 * The Analytics Module
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use Exception;
use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Data_Fetcher class.
 */
class Data_Fetcher {

	use Hooker;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Data_Fetcher
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Data_Fetcher ) ) {
			$instance = new Data_Fetcher();
			$instance->hooks();
		}

		return $instance;
	}

	/**
	 * Hooks.
	 */
	private function hooks() {
		$this->action( 'rank_math/analytics/daily_tasks', 'daily_tasks' );
		$this->action( 'rank_math/analytics/daily_tasks', 'check_for_missing_dates' );
		$this->action( 'rank_math/analytics/flat_posts', 'process_posts' );
		$this->action( 'rank_math/analytics/get_analytics', 'get_analytics_data' );
		$this->action( 'rank_math/analytics/clear_cache', 'clear_cache' );
		add_action( 'rank_math/analytics/sync_sitemaps', [ Api::get(), 'sync_sitemaps' ] );
	}

	/**
	 * Start fetching process.
	 *
	 * @param integer $days Number of days to fetch from past.
	 */
	public function start_process( $days = 90, $action = 'get_analytics' ) {
		global $wpdb;

		$count    = $this->add_data_pull( $days + 2, $action );
		$time_gap = $this->do_filter( 'analytics/schedule_gap', 30 );

		// Clear cache.
		as_schedule_single_action( time() + ( $time_gap * ( $count + 1 ) ), 'rank_math/analytics/clear_cache' );

		// First pull notice.
		$first = get_option( 'rank_math_analytics_first_fetch' );
		if ( ! $first ) {
			$tables = $wpdb->query( "SHOW TABLES LIKE '%rank_math_analytics%'" );
			if ( $tables < 1 ) {
				( new Installer() )->install();
			}
			update_option( 'rank_math_analytics_first_fetch', 'fetching' );
		}

		if ( $first && $count > 0 ) {
			update_option( 'rank_math_analytics_first_fetch', 'fetching' );
		}
	}

	/**
	 * Add data pull jobs.
	 *
	 * @param integer $days Number of days to fetch from past.
	 */
	private function add_data_pull( $days, $action = 'get_analytics' ) {
		$count    = 1;
		$start    = Helper::get_midnight( time() + DAY_IN_SECONDS );
		$interval = $this->get_data_interval();
		$time_gap = $this->do_filter( 'analytics/schedule_gap', 30 );

		if ( 1 === $interval ) {
			for ( $current = 1; $current <= $days; $current++ ) {
				$date = date_i18n( 'Y-m-d', $start - ( DAY_IN_SECONDS * $current ) );
				if ( ! DB::date_exists( $date ) ) {
					$count++;
					as_schedule_single_action(
						time() + ( $time_gap * $count ),
						'rank_math/analytics/' . $action,
						[ $date ]
					);
				}
			}
		} else {
			for ( $current = 1; $current <= $days; $current = $current + $interval ) {
				for ( $j = 0; $j < $interval; $j++ ) {
					$date = date_i18n( 'Y-m-d', $start - ( DAY_IN_SECONDS * ( $current + $j ) ) );
					if ( ! DB::date_exists( $date ) ) {
						$count++;
						as_schedule_single_action(
							time() + ( $time_gap * $count ),
							'rank_math/analytics/' . $action,
							[ $date ]
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
	 * Flat posts
	 */
	public function flat_posts() {
		$done = \boolval( get_option( 'rank_math_flat_posts_done' ) );
		if ( $done ) {
			return;
		}

		$post_types = Helper::get_accessible_post_types();
		unset( $post_types['attachment'] );

		$ids = get_posts(
			[
				'post_type'      => array_keys( $post_types ),
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			]
		);

		$counter = 0;
		$chunks  = \array_chunk( $ids, 50 );
		foreach ( $chunks as $chunk ) {
			$counter++;
			as_schedule_single_action( time() + ( 60 * ( $counter / 2 ) ), 'rank_math/analytics/flat_posts', [ $chunk ] );
		}

		// Clear cache.
		as_schedule_single_action( time() + ( 60 * ( ( $counter + 1 ) / 2 ) ), 'rank_math/analytics/clear_cache' );

		update_option( 'rank_math_flat_posts_done', true );
	}

	/**
	 * Process posts.
	 *
	 * @param  array $ids Posts ids to process.
	 */
	public function process_posts( $ids ) {
		foreach ( $ids as $id ) {
			Watcher::get()->update_post_info( $id );
		}
	}

	/**
	 * Get analytics data.
	 *
	 * @param string $date Date to fetch data for.
	 */
	public function get_analytics_data( $date ) {
		set_time_limit( 300 );

		if ( DB::date_exists( $date ) ) {
			return;
		}

		$this->save_query_page( $date );

		do_action( 'rank_math/analytics/get_analytics_data', $date );

		update_option( 'rank_math_analytics_last_updated', time() );
	}

	/**
	 * Get page and keyword data and save it into database.
	 *
	 * @param string $date Date to fetch data for.
	 */
	public function save_query_page( $date ) {
		$rows = Api::get()->get_search_analytics( $date, $date, [ 'query', 'page' ] );
		if ( empty( $rows ) ) {
			return;
		}

		$rows = \array_map( [ $this, 'normalize_query_page_data' ], $rows );

		try {
			DB::add_query_page_bulk( $date, $rows );
		} catch ( Exception $e ) {} // phpcs:ignore
	}

	/**
	 * Delete all useless data from gsc and ga.
	 */
	public function delete_all_useless() {
		global $wpdb;
		$wpdb->get_results( "DELETE FROM {$wpdb->prefix}rank_math_analytics_gsc WHERE page NOT IN ( SELECT page from {$wpdb->prefix}rank_math_analytics_objects )" );
	}

	/**
	 * Normalize console data.
	 *
	 * @param array $row Single row item.
	 *
	 * @return array
	 */
	protected function normalize_data( $row ) {
		$row['ctr']      = round( $row['ctr'] * 100, 2 );
		$row['position'] = round( $row['position'], 2 );

		return $row;
	}

	/**
	 * Normalize console data.
	 *
	 * @param array $row Single row item.
	 *
	 * @return array
	 */
	protected function normalize_query_page_data( $row ) {
		$row          = $this->normalize_data( $row );
		$row['query'] = $row['keys'][0];
		$row['page']  = $row['keys'][1];

		unset( $row['keys'] );

		return $row;
	}

	/**
	 * Perform these tasks daily.
	 */
	public function daily_tasks() {
		$start = Helper::get_midnight( time() - DAY_IN_SECONDS );
		$date  = date_i18n( 'Y-m-d', $start - DAY_IN_SECONDS * 2 );
		$this->get_analytics_data( $date );
		$this->delete_all_useless();
		$this->calculate_stats();
		DB::delete_data_log();
	}

	/**
	 * Check for missing dates.
	 */
	public function check_for_missing_dates() {
		$count = 1;
		$start = Helper::get_midnight( time() + DAY_IN_SECONDS );

		for ( $current = 1; $current <= 90; $current++ ) {
			$date = date_i18n( 'Y-m-d', $start - ( DAY_IN_SECONDS * $current ) );
			if ( ! DB::date_exists( $date ) ) {
				$count++;
				as_schedule_single_action(
					time() + ( 60 * ( $count / 2 ) ),
					'rank_math/analytics/get_analytics',
					[ $date ]
				);
			}
		}

		// Clear cache.
		if ( $count > 1 ) {
			as_schedule_single_action( time() + ( 60 * ( ( $count + 1 ) / 2 ) ), 'rank_math/analytics/clear_cache' );
		}
	}

	/**
	 * Calculate stats.
	 */
	public function calculate_stats() {
		$ranges = [
			'-7 days',
			'-15 days',
			'-30 days',
			'-3 months',
			'-6 months',
			'-1 year',
		];

		foreach ( $ranges as $range ) {
			Stats::get()->set_date_range( $range );
			Stats::get()->get_top_keywords();
		}
	}

	/**
	 * Kill process.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public function kill_process() {
		\as_unschedule_all_actions( 'rank_math/analytics/get_analytics' );
	}

	/**
	 * Clear cache.
	 */
	public function clear_cache() {
		$this->delete_all_useless();
		DB::purge_cache();
	}
}
