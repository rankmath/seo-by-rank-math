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
use WP_REST_Request;
use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Keywords class.
 */
class Keywords extends Posts {

	/**
	 * Get keywords.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_keywords_rows( WP_REST_Request $request ) {
		$per_page = 25;
		$offset   = ( $request->get_param( 'page' ) - 1 ) * $per_page;
		$rows     = $this->get_analytics_data(
			[
				'dimension' => 'query',
				'objects'   => false,
				'pageview'  => false,
				'orderBy'   => 't1.impressions',
				'limit'     => "LIMIT {$offset}, {$per_page}",
			]
		);

		return apply_filters( 'rank_math/analytics/keywords', $this->set_query_as_key( $rows ) );
	}

	/**
	 * Get top 50 keywords.
	 *
	 * @return object
	 */
	public function get_top_keywords() {
		global $wpdb;

		$cache_key = $this->get_cache_key( 'top_keywords', $this->days . 'days' );
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		$data = $wpdb->get_results( // phpcs:ignore
			$wpdb->prepare(
				"SELECT query, ROUND( AVG(position), 0 ) as position FROM {$wpdb->prefix}rank_math_analytics_gsc WHERE created BETWEEN %s AND %s GROUP BY query",
				$this->start_date,
				$this->end_date
			)
		);

		$compare = $wpdb->get_results( // phpcs:ignore
			$wpdb->prepare(
				"SELECT query, ROUND( AVG(position), 0 ) as position FROM {$wpdb->prefix}rank_math_analytics_gsc WHERE created BETWEEN %s AND %s GROUP BY query",
				$this->compare_start_date,
				$this->compare_end_date
			)
		);

		$positions = [
			'top3'          => [
				'total'      => 0,
				'difference' => 0,
			],
			'top10'         => [
				'total'      => 0,
				'difference' => 0,
			],
			'top50'         => [
				'total'      => 0,
				'difference' => 0,
			],
			'top100'        => [
				'total'      => 0,
				'difference' => 0,
			],
			'ctr'           => 0,
			'ctrDifference' => 0,
		];

		$positions = $this->get_top_position_total( $positions, $data, 'total' );
		$positions = $this->get_top_position_total( $positions, $compare, 'difference' );

		// CTR.
		$positions['ctr'] = DB::analytics()
			->selectAvg( 'ctr', 'ctr' )
			->whereBetween( 'created', [ $this->start_date, $this->end_date ] )
			->getVar();

		$positions['ctrDifference'] = DB::analytics()
			->selectAvg( 'ctr', 'ctr' )
			->whereBetween( 'created', [ $this->compare_start_date, $this->compare_end_date ] )
			->getVar();

		$positions['ctr']           = empty( $positions['ctr'] ) ? 0 : $positions['ctr'];
		$positions['ctrDifference'] = empty( $positions['ctrDifference'] ) ? 0 : $positions['ctrDifference'];
		$positions['ctrDifference'] = $positions['ctr'] - $positions['ctrDifference'];

		set_transient( $cache_key, $positions, DAY_IN_SECONDS );

		return $positions;
	}

	/**
	 * Get position graph
	 *
	 * @return array
	 */
	public function get_top_position_graph() {
		$cache_key = $this->get_cache_key( 'top_keywords_graph', $this->days . 'days' );
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		$intervals = $this->get_intervals();

		// Data.
		$data = $this->get_date_array(
			$intervals['dates'],
			[
				'top3'   => [],
				'top10'  => [],
				'top50'  => [],
				'top100' => [],
			]
		);
		$data = $this->get_postion_graph_data( 'top3', $data, $intervals['map'] );
		$data = $this->get_postion_graph_data( 'top10', $data, $intervals['map'] );
		$data = $this->get_postion_graph_data( 'top50', $data, $intervals['map'] );
		$data = $this->get_postion_graph_data( 'top100', $data, $intervals['map'] );

		foreach ( $data as &$item ) {
			$item['top3']   = empty( $item['top3'] ) ? 0 : ceil( array_sum( $item['top3'] ) / count( $item['top3'] ) );
			$item['top10']  = empty( $item['top10'] ) ? 0 : ceil( array_sum( $item['top10'] ) / count( $item['top10'] ) );
			$item['top50']  = empty( $item['top50'] ) ? 0 : ceil( array_sum( $item['top50'] ) / count( $item['top50'] ) );
			$item['top100'] = empty( $item['top100'] ) ? 0 : ceil( array_sum( $item['top100'] ) / count( $item['top100'] ) );
		}

		$data = array_values( $data );
		set_transient( $cache_key, $data, DAY_IN_SECONDS );

		return $data;
	}

	/**
	 * Get graph data.
	 *
	 * @param  string $position Position for which data required.
	 * @param  array  $data     Data array.
	 * @param  array  $map      Interval map.
	 * @return array
	 */
	private function get_postion_graph_data( $position, $data, $map ) {
		global $wpdb;

		$positions = [
			'top3'   => '1 AND 3',
			'top10'  => '4 AND 10',
			'top50'  => '11 AND 50',
			'top100' => '51 AND 100',
		];
		$range     = $positions[ $position ];

		// phpcs:disable
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT( created,'%%Y-%%m-%%d') as date, COUNT(query) as total
				FROM {$wpdb->prefix}rank_math_analytics_gsc
				WHERE position BETWEEN {$range} AND created BETWEEN %s AND %s
				GROUP BY created
				ORDER BY created ASC",
				$this->start_date,
				$this->end_date
			)
		);
		// phpcs:enable

		foreach ( $rows as $row ) {
			if ( ! isset( $map[ $row->date ] ) ) {
				continue;
			}

			$date = $map[ $row->date ];

			$data[ $date ][ $position ][] = absint( $row->total );
		}

		return $data;
	}

	/**
	 * Get top position total.
	 *
	 * @param  array  $positions Position array.
	 * @param  array  $rows      Data to process.
	 * @param  string $where     What data to get total.
	 *
	 * @return array
	 */
	private function get_top_position_total( $positions, $rows, $where ) {
		foreach ( $rows as $row ) {
			$position = $row->position;
			if ( $position > 0 && $position <= 3 ) {
				$key = 'top3';
			}

			if ( $position >= 4 && $position <= 10 ) {
				$key = 'top10';
			}

			if ( $position >= 11 && $position <= 50 ) {
				$key = 'top50';
			}

			if ( $position > 50 ) {
				$key = 'top100';
			}

			$positions[ $key ][ $where ] += 1;
		}

		if ( 'difference' === $where ) {
			$positions['top3']['difference']   = $positions['top3']['total'] - $positions['top3']['difference'];
			$positions['top10']['difference']  = $positions['top10']['total'] - $positions['top10']['difference'];
			$positions['top50']['difference']  = $positions['top50']['total'] - $positions['top50']['difference'];
			$positions['top100']['difference'] = $positions['top100']['total'] - $positions['top100']['difference'];
		}

		return $positions;
	}
}
