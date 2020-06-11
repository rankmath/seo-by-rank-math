<?php
/**
 * The Search Console Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Search_Console;

use Exception;
use RankMath\Helper;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Data_Fetcher class.
 */
class Data_Fetcher extends \WP_Background_Process {

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected $action = 'fetch_search_console_data';

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
			$instance = new Data_Fetcher;
		}

		return $instance;
	}

	/**
	 * Clean the previous slate.
	 */
	public function clean_start() {
		$this->kill_process();
		DB::delete( -1 );
		DB::purge_cache();
		$this->start_process( 90 );
	}

	/**
	 * Start fetching process.
	 *
	 * @param integer $days Number of days to fetch from past.
	 */
	public function start_process( $days = 90 ) {
		$start = Helper::get_midnight( time() - DAY_IN_SECONDS );
		for ( $current = 1; $current <= $days; $current++ ) {
			$this->push_to_queue( date_i18n( 'Y-m-d', $start - ( DAY_IN_SECONDS * $current ) ) );
		}

		$this->save()->dispatch();
	}

	/**
	 * Task to perform
	 *
	 * @param string $item Item to process.
	 *
	 * @return bool
	 */
	protected function task( $item ) {
		try {
			if ( Str::is_non_empty( $item ) ) {
				$this->get_analytics_data( $item );
			}
			return false;
		} catch ( Exception $error ) {
			return true;
		}
	}

	/**
	 * Get analytics data.
	 *
	 * @param string $current Date to fetch data for.
	 */
	private function get_analytics_data( $current ) {
		set_time_limit( 300 );
		if ( DB::date_exists( $current ) ) {
			return true;
		}

		foreach ( [ 'page', 'query', 'date' ] as $metric ) {
			$rows = $this->query_analytics_data( $current, $current, $metric );
			foreach ( $rows as $row ) {
				DB::insert( $row, $current, $metric );
			}
		}

		DB::purge_cache();

		// Sleep to not hit 5 QPS Limit.
		sleep( 2 );

		return true;
	}

	/**
	 * Query analytics data from google client api.
	 *
	 * @param string  $start_date Start date.
	 * @param string  $end_date   End date.
	 * @param string  $dimension  Dimension of data.
	 * @param integer $limit      Number of rows.
	 *
	 * @return array
	 */
	private function query_analytics_data( $start_date, $end_date, $dimension, $limit = 5000 ) {
		$api      = Client::get()->get_google_client();
		$response = $api->post(
			'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( Client::get()->profile ) . '/searchAnalytics/query',
			[
				'startDate'  => $start_date,
				'endDate'    => $end_date,
				'rowLimit'   => $limit,
				'dimensions' => [ $dimension ],
			]
		);

		$rows = false;
		if ( $api->is_success() ) {
			if ( isset( $response['rows'] ) ) {
				$rows = $response['rows'];
				$rows = $this->normalize_analytics_data( $rows );
			}
		}

		return $rows ? $rows : [];
	}

	/**
	 * Normalize analytics data.
	 *
	 * @param array $rows Array of rows.
	 *
	 * @return array
	 */
	private function normalize_analytics_data( $rows ) {
		foreach ( $rows as &$row ) {
			$row['ctr']      = round( $row['ctr'] * 100, 2 );
			$row['position'] = round( $row['position'], 2 );
		}

		return $rows;
	}

	/**
	 * Is queue empty
	 *
	 * @return bool
	 */
	public function is_empty() {
		return $this->is_queue_empty();
	}

	/**
	 * Kill process.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public function kill_process() {
		$this->cancel_process();
	}
}
