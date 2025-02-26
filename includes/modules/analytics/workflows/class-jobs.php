<?php
/**
 * Jobs.
 *
 * @since      1.0.54
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics\Workflow;

use Exception;
use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Google\Console;
use RankMath\Google\Url_Inspection;
use RankMath\Analytics\DB;
use RankMath\Traits\Cache;
use RankMath\Traits\Hooker;
use RankMath\Analytics\Stats;
use RankMath\Analytics\Watcher;

defined( 'ABSPATH' ) || exit;

/**
 * Jobs class.
 */
class Jobs {

	use Hooker;
	use Cache;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Jobs
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Jobs ) ) {
			$instance = new Jobs();
			$instance->hooks();
		}

		return $instance;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		$this->action( 'rank_math/analytics/flat_posts', 'do_flat_posts' );
		$this->action( 'rank_math/analytics/flat_posts_completed', 'flat_posts_completed' );
		add_action( 'rank_math/analytics/sync_sitemaps', [ Api::get(), 'sync_sitemaps' ] );

		if ( Console::is_console_connected() ) {
			$this->action( 'rank_math/analytics/clear_cache', 'clear_cache', 99 );

			// Fetch missing google data action.
			$this->action( 'rank_math/analytics/data_fetch', 'data_fetch' );

			// Console data fetch.
			$this->filter( 'rank_math/analytics/get_console_days', 'get_console_days' );
			$this->action( 'rank_math/analytics/get_console_data', 'get_console_data' );
			$this->action( 'rank_math/analytics/handle_console_response', 'handle_console_response' );

			// Inspections data fetch.
			$this->action( 'rank_math/analytics/get_inspections_data', 'get_inspections_data' );
		}
	}

	/**
	 * Fetch missing console data.
	 */
	public function data_fetch() {
		$this->check_for_missing_dates( 'console' );
	}

	/**
	 * Perform post check.
	 */
	public function flat_posts_completed() {
		$rows = DB::objects()
			->selectCount( 'id' )
			->getVar();

		Workflow::kill_workflows();
	}

	/**
	 * Add/update posts info from objects table.
	 *
	 * @param  array $ids Posts ids to process.
	 */
	public function do_flat_posts( $ids ) {
		Inspections::kill_jobs();

		foreach ( $ids as $id ) {
			Watcher::get()->update_post_info( $id );
		}
	}

	/**
	 * Clear cache.
	 */
	public function clear_cache() {
		global $wpdb;

		// Delete all useless data from console data table.
		$wpdb->get_results( "DELETE FROM {$wpdb->prefix}rank_math_analytics_gsc WHERE page NOT IN ( SELECT page from {$wpdb->prefix}rank_math_analytics_objects )" );

		// Delete useless data from inspections table too.
		$wpdb->get_results( "DELETE FROM {$wpdb->prefix}rank_math_analytics_inspections WHERE page NOT IN ( SELECT page from {$wpdb->prefix}rank_math_analytics_objects )" );

		delete_transient( 'rank_math_analytics_data_info' );
		DB::purge_cache();
		DB::delete_data_log();
		$this->calculate_stats();

		update_option( 'rank_math_analytics_last_updated', time() );

		Workflow::do_workflow( 'inspections' );
	}

	/**
	 * Set the console start and end dates.
	 *
	 * @param array $args Args containing start and end date.
	 */
	public function get_console_days( $args = [] ) {
		set_time_limit( 300 );

		$rows = Api::get()->get_search_analytics(
			[
				'start_date' => $args['start_date'],
				'end_date'   => $args['end_date'],
				'dimensions' => [ 'date' ],
			]
		);

		if ( empty( $rows ) || is_wp_error( $rows ) ) {
			return [];
		}

		$empty_dates = get_option( 'rank_math_console_empty_dates', [] );

		$dates = [];

		foreach ( $rows as $row ) {

			// Have at least few impressions.
			if ( $row['impressions'] ) {
				$date = $row['keys'][0];

				if ( ! DB::date_exists( $date, 'console' ) && ! in_array( $date, $empty_dates, true ) ) {
					$dates[] = [
						'start_date' => $date,
						'end_date'   => $date,
					];
				}
			}
		}

		return $dates;
	}

	/**
	 * Get console data.
	 *
	 * @param string $date Date to fetch data for.
	 */
	public function get_console_data( $date ) {
		set_time_limit( 300 );

		$rows = Api::get()->get_search_analytics(
			[
				'start_date' => $date,
				'end_date'   => $date,
				'dimensions' => [ 'query', 'page' ],
			]
		);

		if ( empty( $rows ) || is_wp_error( $rows ) ) {
			return;
		}

		$rows = \array_map( [ $this, 'normalize_query_page_data' ], $rows );

		try {
			DB::add_query_page_bulk( $date, $rows );

			// Clear the cache here.
			$this->cache_flush_group( 'rank_math_rest_keywords_rows' );
			$this->cache_flush_group( 'rank_math_posts_rows_by_objects' );
			$this->cache_flush_group( 'rank_math_analytics_summary' );

			return $rows;
		} catch ( Exception $e ) {} // phpcs:ignore
	}

	/**
	 * Handlle console response.
	 *
	 * @param array $data API request and response data.
	 */
	public function handle_console_response( $data = [] ) {
		if ( 200 !== $data['code'] ) {
			return;
		}

		if ( isset( $data['formatted_response']['rows'] ) && ! empty( $data['formatted_response']['rows'] ) ) {
			return;
		}

		if ( ! isset( $data['args']['startDate'] ) ) {
			return;
		}

		$dates = get_option( 'rank_math_console_empty_dates', [] );
		if ( ! $dates ) {
			$dates = [];
		}

		$dates[] = $data['args']['startDate'];
		$dates[] = $data['args']['endDate'];

		$dates = array_unique( $dates );

		update_option( 'rank_math_console_empty_dates', $dates );
	}

	/**
	 * Get inspection results from the API and store them in the database.
	 *
	 * @param string $page URI to fetch data for.
	 */
	public function get_inspections_data( $page ) {
		// If the option is disabled, don't fetch data.
		if ( ! \RankMath\Analytics\Url_Inspection::is_enabled() ) {
			return;
		}

		$inspection = Url_Inspection::get()->get_inspection_data( $page );
		if ( empty( $inspection ) ) {
			return;
		}

		try {
			DB::store_inspection( $inspection );
		} catch ( Exception $e ) {} // phpcs:ignore
	}

	/**
	 * Check for missing dates.
	 *
	 * @param string $action Action to perform.
	 */
	public function check_for_missing_dates( $action ) {
		$days = Helper::get_settings( 'general.console_caching_control', 90 );

		Workflow::do_workflow(
			$action,
			$days,
			null,
			null
		);
	}

	/**
	 * Calculate stats.
	 */
	private function calculate_stats() {
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
	 * Normalize console data.
	 *
	 * @param array $row Single row item.
	 *
	 * @return array
	 */
	private function normalize_data( $row ) {
		$row['ctr']      = round( $row['ctr'] * 100, 2 );
		$row['position'] = round( $row['position'], 2 );

		return $row;
	}
}
