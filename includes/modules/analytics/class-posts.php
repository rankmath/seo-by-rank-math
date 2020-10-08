<?php
/**
 * The Analytics Module
 *
 * @since      0.9.0
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
 * Posts class.
 */
class Posts extends Pageviews {

	/**
	 * Get post data.
	 *
	 * @param int $id Post id.
	 *
	 * @return object
	 */
	public function get_post( $id ) {
		$post = DB::objects()
			->where( 'id', $id )
			->one();

		$metrices = $this->get_analytics_data(
			[
				'pageview'  => true,
				'sub_where' => " AND page = '{$post->page}'",
				'pages'     => [ $post->page ],
			]
		);
		if ( ! empty( $metrices ) ) {
			$metrices = current( $metrices );
		}

		// Keywords.
		$keywords = DB::analytics()
			->distinct()
			->selectCount( 'query', 'keywords' )
			->whereLike( 'page', $post->page, '%', '' )
			->whereBetween( 'created', [ $this->start_date, $this->end_date ] )
			->where( 'clicks', '>', 0 )
			->getVar();

		$old_keywords = DB::analytics()
			->distinct()
			->selectCount( 'query', 'keywords' )
			->whereLike( 'page', $post->page, '%', '' )
			->where( 'clicks', '>', 0 )
			->whereBetween( 'created', [ $this->compare_start_date, $this->compare_end_date ] )
			->getVar();

		$post->keywords = [
			'total'      => (int) $keywords,
			'previous'   => (int) $old_keywords,
			'difference' => $keywords - $old_keywords,
		];

		$post->backlinks = [
			'total'      => 0,
			'previous'   => 0,
			'difference' => 0,
		];

		$post->badges = [
			'clicks'      => $this->get_position_for_badges( 'clicks', $post->page ),
			'traffic'     => $this->get_position_for_badges( 'traffic', $post->page ),
			'keywords'    => $this->get_position_for_badges( 'query', $post->page ),
			'impressions' => $this->get_position_for_badges( 'impressions', $post->page ),
		];

		return array_merge(
			(array) $post,
			(array) $metrices,
			$this->get_graph_data_for_post( $post->page ),
			$this->get_post_ranking_keywords( $post->page )
		);
	}

	/**
	 * Get positio for badges.
	 *
	 * @param  string $column Column name.
	 * @param  string $page   Page url.
	 * @return integer
	 */
	public function get_position_for_badges( $column, $page ) {
		$start = strtotime( '-30 days ', $this->end );
		if ( 'traffic' === $column ) {
			$rows = DB::traffic()
				->select( 'page' )
				->selectSum( 'pageviews', 'pageviews' )
				->whereBetween( 'created', [ $start, $this->end_date ] )
				->groupBy( 'page' )
				->orderBy( 'pageviews', 'DESC' )
				->limit( 5 );
		} else {
			$rows = DB::analytics()
				->select( 'page' )
				->where( 'clicks', '>', 0 )
				->whereBetween( 'created', [ $start, $this->end_date ] )
				->groupBy( 'page' )
				->orderBy( $column, 'DESC' )
				->limit( 5 );
		}

		if ( 'impressions' === $column || 'click' === $column ) {
			$rows->selectSum( $column, $column );
		}

		if ( 'query' === $column ) {
			$rows->selectCount( 'DISTINCT(query)', 'keywords' );
		}

		$rows = $rows->get( ARRAY_A );
		foreach ( $rows as $index => $row ) {
			if ( $page === $row['page'] ) {
				return $index + 1;
			}
		}

		return 99;
	}

	/**
	 * Get ranking keywords.
	 *
	 * @param string $page Page url.
	 *
	 * @return object
	 */
	public function get_post_ranking_keywords( $page ) {
		$data    = $this->get_analytics_data(
			[
				'dimension' => 'query',
				'sub_where' => "AND page LIKE '%{$page}'",
			]
		);
		$data    = $this->set_query_as_key( $data );
		$history = $this->get_graph_data_for_keywords( \array_keys( $data ) );
		$data    = $this->set_query_position( $data, $history );

		return [ 'rankingKeywords' => $data ];
	}

	/**
	 * Get graph data.
	 *
	 * @param string $page Page url.
	 *
	 * @return array
	 */
	public function get_graph_data_for_post( $page ) {
		global $wpdb;

		$data     = new \stdClass();
		$interval = $this->get_sql_range( 'created' );

		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT
				DATE_FORMAT( created,'%%Y-%%m-%%d') as date, SUM( clicks ) as clicks, SUM(impressions) as impressions, ROUND( AVG(position), 0 ) as position, ROUND( AVG(ctr), 2 ) as ctr
			FROM
				{$wpdb->prefix}rank_math_analytics_gsc
			WHERE clicks > 0 AND created BETWEEN %s AND %s AND page LIKE '%{$page}'
			GROUP BY {$interval}
			ORDER BY created ASC",
			$this->start_date,
			$this->end_date
		);
		$analytics = $wpdb->get_results( $query );
		// phpcs:enable

		$traffic = DB::traffic()
			->select( 'DATE_FORMAT( created,\'%Y-%m-%d\') as date' )
			->selectSum( 'pageviews', 'pageviews' )
			->where( 'page', $page )
			->whereBetween( 'created', [ $this->start_date, $this->end_date ] )
			->groupBy( $interval )
			->orderBy( 'created', 'ASC' )
			->get();

		$keywords = DB::analytics()
			->distinct()
			->select( 'DATE_FORMAT( created,\'%Y-%m-%d\') as date' )
			->selectCount( 'query', 'keywords' )
			->whereLike( 'page', $page )
			->where( 'clicks', '>', 0 )
			->whereBetween( 'created', [ $this->start_date, $this->end_date ] )
			->groupBy( $interval )
			->orderBy( 'created', 'ASC' )
			->get();

		// Convert types.
		$analytics = array_map( [ $this, 'normalize_graph_rows' ], $analytics );
		$traffic   = array_map( [ $this, 'normalize_graph_rows' ], $traffic );
		$keywords  = array_map( [ $this, 'normalize_graph_rows' ], $keywords );

		$intervals = $this->get_intervals();

		// Merge for performance.
		$data = $this->get_date_array(
			$intervals['dates'],
			[
				'clicks'      => [],
				'impressions' => [],
				'position'    => [],
				'ctr'         => [],
				'keywords'    => [],
				'pageviews'   => [],
			]
		);
		$data = $this->get_merge_data_graph( $analytics, $data, $intervals['map'] );
		$data = $this->get_merge_data_graph( $traffic, $data, $intervals['map'] );
		$data = $this->get_merge_data_graph( $keywords, $data, $intervals['map'] );
		$data = $this->get_graph_data_flat( $data );
		$data = array_values( $data );

		return [ 'graph' => $data ];
	}

	/**
	 * Get posts by objects.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_posts_rows_by_objects( WP_REST_Request $request ) {
		// Pagination.
		$per_page = 25;
		$offset   = ( $request->get_param( 'page' ) - 1 ) * $per_page;

		$objects   = $this->get_objects_by_score( $request );
		$objects   = $this->get_links_by_objects( $objects );
		$pages     = \array_keys( $objects['rows'] );
		$pageviews = $this->get_pageviews( [ 'pages' => $pages ] );
		$pageviews = $this->set_page_as_key( $pageviews['rows'] );
		$console   = $this->get_analytics_data(
			[
				'limit'     => "LIMIT {$offset}, {$per_page}",
				'sub_where' => " AND page IN ('" . join( "', '", $pages ) . "')",
			]
		);

		$new_rows = [];
		foreach ( $objects['rows'] as $object ) {
			$page = $object['page'];

			if ( isset( $pageviews[ $page ] ) ) {
				$object['pageviews'] = [
					'total'      => $pageviews[ $page ]['pageviews'],
					'difference' => $pageviews[ $page ]['difference'],
				];
			}

			if ( isset( $console[ $page ] ) ) {
				$object = \array_merge( $console[ $page ], $object );
			}

			$new_rows[ $page ] = $object;
		}

		$history  = $this->get_graph_data_for_pages( $pages );
		$new_rows = $this->set_page_position_graph( $new_rows, $history );

		return [
			'rows'      => $new_rows,
			'rowsFound' => $objects['rowsFound'],
		];
	}

	/**
	 * Get posts summary.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_posts_rows_by_pageviews( WP_REST_Request $request ) {
		global $wpdb;

		// Pagination.
		$per_page  = 25;
		$offset    = ( $request->get_param( 'page' ) - 1 ) * $per_page;
		$data      = $this->get_pageviews_with_object( [ 'limit' => "LIMIT {$offset}, {$per_page}" ] );
		$pageviews = $this->set_page_as_key( $data['rows'] );
		$pages     = \array_keys( $pageviews );
		$console   = $this->get_analytics_data(
			[
				'limit'     => 'LIMIT 100',
				'objects'   => false,
				'sub_where' => " AND page IN ('" . join( "', '", $pages ) . "')",
			]
		);

		foreach ( $pageviews as $page => &$pageview ) {
			$pageview['pageviews'] = [
				'total'      => $pageview['pageviews'],
				'difference' => $pageview['difference'],
			];

			if ( isset( $console[ $page ] ) ) {
				unset( $console[ $page ]['pageviews'] );
				$pageview = \array_merge( $pageview, $console[ $page ] );
			}
		}

		$history   = $this->get_graph_data_for_pages( $pages );
		$pageviews = $this->set_page_position_graph( $pageviews, $history );

		$data['rows'] = $pageviews;
		return $data;
	}

	/**
	 * Get winning posts.
	 *
	 * @return object
	 */
	public function get_winning_posts() {
		global $wpdb;

		$cache_key = $this->get_cache_key( 'winning_posts', $this->days . 'days' );
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		$rows = $this->get_analytics_data(
			[
				'objects'  => true,
				'pageview' => true,
				'where'    => 'WHERE COALESCE( ROUND( t2.position - t1.position, 0 ), 0 ) > 0',
			]
		);

		$history = $this->get_graph_data_for_pages( \array_keys( $rows ) );
		$rows    = $this->set_page_position_graph( $rows, $history );

		set_transient( $cache_key, $rows, DAY_IN_SECONDS );

		return $rows;
	}

	/**
	 * Get losing posts.
	 *
	 * @return object
	 */
	public function get_losing_posts() {
		global $wpdb;

		$cache_key = $this->get_cache_key( 'losing_posts', $this->days . 'days' );
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		$rows = $this->get_analytics_data(
			[
				'order'    => 'ASC',
				'objects'  => true,
				'pageview' => true,
				'where'    => 'WHERE COALESCE( ROUND( t2.position - t1.position, 0 ), 0 ) < 0',
			]
		);

		$history = $this->get_graph_data_for_pages( \array_keys( $rows ) );
		$rows    = $this->set_page_position_graph( $rows, $history );

		set_transient( $cache_key, $rows, DAY_IN_SECONDS );

		return $rows;
	}

	/**
	 * Get graph data.
	 *
	 * @param array $pages Pages to get data for.
	 *
	 * @return array
	 */
	public function get_graph_data_for_pages( $pages ) {
		global $wpdb;

		$interval = $this->get_sql_range( 'created' );
		$pages    = \array_map( 'esc_sql', $pages );
		$pages    = '(\'' . join( '\', \'', $pages ) . '\')';

		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT
				page, DATE_FORMAT( created,'%%Y-%%m-%%d') as date, ROUND( AVG(position), 0 ) as position
			FROM
				{$wpdb->prefix}rank_math_analytics_gsc
			WHERE clicks > 0 AND page IN {$pages} AND created BETWEEN %s AND %s
			GROUP BY page, {$interval}
			ORDER BY created ASC",
			$this->start_date,
			$this->end_date
		);

		$data = $wpdb->get_results( $query );
		// phpcs:enable

		return array_map( [ $this, 'normalize_graph_rows' ], $data );
	}
}
