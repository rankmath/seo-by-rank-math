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

use RankMath\Traits\Cache;
use RankMath\Helpers\DB as DB_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Summary class.
 *
 * @method get_cache_key()
 * @method get_intervals()
 * @method get_sql_date_intervals()
 * @method set_dimension_as_key()
 * @method extract_data_from_mixed()
 * @method get_merged_metrics()
 * @method get_merge_data_graph()
 * @method get_date_array()
 * @method get_graph_data_flat()
 */
class Summary {

	use Cache;

	/**
	 * Start date.
	 *
	 * @var string
	 */
	public $start_date;

	/**
	 * End date.
	 *
	 * @var string
	 */
	public $end_date;

	/**
	 * Compare start date.
	 *
	 * @var string
	 */
	public $compare_start_date;

	/**
	 * Compare end date.
	 *
	 * @var string
	 */
	public $compare_end_date;

	/**
	 * Days.
	 *
	 * @var int
	 */
	public $days;

	/**
	 * Get Widget.
	 *
	 * @return object
	 */
	public function get_widget() {
		global $wpdb;

		$cache_key = Stats::get()->get_cache_key( 'dashboard_stats_widget' );
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		$stats = DB::analytics()
			->selectSum( 'impressions', 'impressions' )
			->selectSum( 'clicks', 'clicks' )
			->selectAvg( 'position', 'position' )
			->whereBetween( 'created', [ Stats::get()->start_date, Stats::get()->end_date ] )
			->one();

		$old_stats = DB::analytics()
			->selectSum( 'impressions', 'impressions' )
			->selectSum( 'clicks', 'clicks' )
			->selectAvg( 'position', 'position' )
			->whereBetween( 'created', [ Stats::get()->compare_start_date, Stats::get()->compare_end_date ] )
			->one();

		if ( is_null( $stats ) ) {
			$stats = (object) [
				'clicks'      => 0,
				'impressions' => 0,
				'position'    => 0,
			];
		}

		if ( is_null( $old_stats ) ) {
			$old_stats = $stats;
		}

		$stats->clicks = [
			'total'      => (int) $stats->clicks,
			'previous'   => (int) $old_stats->clicks,
			'difference' => $stats->clicks - $old_stats->clicks,
		];

		$stats->impressions = [
			'total'      => (int) $stats->impressions,
			'previous'   => (int) $old_stats->impressions,
			'difference' => $stats->impressions - $old_stats->impressions,
		];

		$stats->position = [
			'total'      => (float) \number_format( $stats->position, 2 ),
			'previous'   => (float) \number_format( $old_stats->position, 2 ),
			'difference' => (float) \number_format( $stats->position - $old_stats->position, 2 ),
		];

		$stats->keywords = $this->get_keywords_summary();

		$stats = apply_filters( 'rank_math/analytics/get_widget', $stats );

		set_transient( $cache_key, $stats, DAY_IN_SECONDS * Stats::get()->days );

		return $stats;
	}

	/**
	 * Get Optimization stats.
	 *
	 * @param string $post_type Selected Post Type.
	 *
	 * @return object
	 */
	public function get_optimization_summary( $post_type = '' ) {
		global $wpdb;

		$cache_group = 'rank_math_optimization_summary';
		$hash_name   = $post_type ? $post_type : 'overall';
		$cache_key   = $this->generate_hash( $hash_name );
		$cache       = $this->get_cache( $cache_key, $cache_group );
		if ( false !== $cache ) {
			return $cache;
		}

		$stats = (object) [
			'good'    => 0,
			'ok'      => 0,
			'bad'     => 0,
			'noData'  => 0,
			'total'   => 0,
			'average' => 0,
		];

		$object_type_sql = $post_type ? ' AND object_subtype = "' . $post_type . '"' : '';
		$data            = DB_Helper::get_results(
			"SELECT COUNT(object_id) AS count,
				CASE
					WHEN seo_score BETWEEN 81 AND 100 THEN 'good'
					WHEN seo_score BETWEEN 51 AND 80 THEN 'ok'
					WHEN seo_score BETWEEN 1 AND 50 THEN 'bad'
					WHEN seo_score = 0 THEN 'noData'
					ELSE 'none'
				END AS type
			FROM {$wpdb->prefix}rank_math_analytics_objects
			WHERE is_indexable = 1
			{$object_type_sql}
			GROUP BY type"
		);

		$total = 0;
		foreach ( $data as $row ) {
			$total              += (int) $row->count;
			$stats->{$row->type} = (int) $row->count;
		}
		$stats->total   = $total;
		$stats->average = 0;

		// Average.
		$query = DB::objects()
		->selectCount( 'object_id', 'total' )
		->where( 'is_indexable', 1 )
		->selectSum( 'seo_score', 'score' );
		if ( $object_type_sql ) {
			$query->where( 'object_subtype', $post_type );
		}

		$average = $query->one();
		if ( $average && $average->total > 0 ) {
			$average->total += property_exists( $stats, 'noData' ) ? $stats->noData : 0; // phpcs:ignore
			$stats->average  = \round( $average->score / $average->total, 2 );
		}

		$this->set_cache( $cache_key, $stats, $cache_group, DAY_IN_SECONDS );

		return $stats;
	}

	/**
	 * Get analytics summary.
	 *
	 * @return object
	 */
	public function get_analytics_summary() {
		$args = [
			'start_date'         => $this->start_date,
			'end_date'           => $this->end_date,
			'compare_start_date' => $this->compare_start_date,
			'compare_end_date'   => $this->compare_end_date,
		];

		$cache_group = 'rank_math_analytics_summary';
		$cache_key   = $this->generate_hash( $args );
		$cache       = $this->get_cache( $cache_key, $cache_group );
		if ( false !== $cache ) {
			return $cache;
		}

		$stats = DB::analytics()
			->selectCount( 'DISTINCT(page)', 'posts' )
			->selectSum( 'impressions', 'impressions' )
			->selectSum( 'clicks', 'clicks' )
			->selectAvg( 'position', 'position' )
			->whereBetween( 'created', [ $this->start_date, $this->end_date ] )
			->one();

		$old_stats = DB::analytics()
			->selectCount( 'DISTINCT(page)', 'posts' )
			->selectSum( 'impressions', 'impressions' )
			->selectSum( 'clicks', 'clicks' )
			->selectAvg( 'position', 'position' )
			->whereBetween( 'created', [ $this->compare_start_date, $this->compare_end_date ] )
			->one();

		$total_ctr    = is_null( $stats->impressions ) ? 'n/a' : round( ( $stats->clicks / $stats->impressions ) * 100, 2 );
		$previous_ctr = is_null( $old_stats->impressions ) ? 'n/a' : ( 0 !== $old_stats->impressions && 'n/a' !== $old_stats->impressions ? round( ( $old_stats->clicks / $old_stats->impressions ) * 100, 2 ) : 0 );

		$stats->ctr = [
			'total'      => $total_ctr,
			'previous'   => $previous_ctr,
			'difference' => 'n/a' !== $total_ctr && 'n/a' !== $previous_ctr ? $total_ctr - $previous_ctr : 'n/a',
		];

		$stats->clicks = [
			'total'      => is_null( $stats->clicks ) ? 'n/a' : (int) $stats->clicks,
			'previous'   => is_null( $old_stats->clicks ) ? 'n/a' : (int) $old_stats->clicks,
			'difference' => is_null( $stats->clicks ) || is_null( $old_stats->clicks ) ? 'n/a' : $stats->clicks - $old_stats->clicks,
		];

		$stats->impressions = [
			'total'      => is_null( $stats->impressions ) ? 'n/a' : (int) $stats->impressions,
			'previous'   => is_null( $old_stats->impressions ) ? 'n/a' : (int) $old_stats->impressions,
			'difference' => is_null( $stats->impressions ) || is_null( $old_stats->impressions ) ? 'n/a' : $stats->impressions - $old_stats->impressions,
		];

		$stats->position = [
			'total'      => is_null( $stats->position ) ? 'n/a' : (float) \number_format( $stats->position, 2 ),
			'previous'   => is_null( $old_stats->position ) ? 'n/a' : (float) \number_format( $old_stats->position, 2 ),
			'difference' => is_null( $old_stats->position ) || is_null( $old_stats->position ) ? 'n/a' : (float) \number_format( $stats->position - $old_stats->position, 2 ),
		];
		$stats->keywords = $this->get_keywords_summary();
		$stats->graph    = $this->get_analytics_summary_graph();

		$stats = apply_filters( 'rank_math/analytics/summary', $stats );

		$stats = array_filter( (array) $stats );

		$this->set_cache( $cache_key, $stats, $cache_group, DAY_IN_SECONDS );

		return $stats;
	}

	/**
	 * Get posts summary.
	 *
	 * @param string $post_type Selected Post Type.
	 *
	 * @return object
	 */
	public function get_posts_summary( $post_type = '' ) {
		$cache_key = $this->get_cache_key( 'posts_summary', $this->days . 'days' );
		$cache     = ! $post_type ? get_transient( $cache_key ) : false;

		if ( false !== $cache ) {
			return $cache;
		}

		global $wpdb;
		$query   = DB::analytics()
			->selectCount( 'DISTINCT(' . $wpdb->prefix . 'rank_math_analytics_gsc.page)', 'posts' )
			->selectSum( 'impressions', 'impressions' )
			->selectSum( 'clicks', 'clicks' )
			->selectAvg( 'ctr', 'ctr' )
			->whereBetween( $wpdb->prefix . 'rank_math_analytics_gsc.created', [ $this->start_date, $this->end_date ] );
		$summary = $query->one();
		$summary = apply_filters( 'rank_math/analytics/posts_summary', $summary, $post_type, $query );
		$summary = wp_parse_args(
			array_filter( (array) $summary ),
			[
				'ctr'         => 'n/a',
				'posts'       => 'n/a',
				'clicks'      => 'n/a',
				'pageviews'   => 'n/a',
				'impressions' => 'n/a',
			]
		);

		set_transient( $cache_key, $summary, DAY_IN_SECONDS );

		return $summary;
	}

	/**
	 * Get keywords summary.
	 *
	 * @return array
	 */
	public function get_keywords_summary() {
		global $wpdb;

		// Get Total Keywords Counts.
		$keywords_count = DB_Helper::get_var(
			$wpdb->prepare(
				"SELECT NULLIF(COUNT(DISTINCT(query)), 0)
				FROM {$wpdb->prefix}rank_math_analytics_gsc
				WHERE created BETWEEN %s AND %s",
				$this->start_date,
				$this->end_date
			)
		);

		$old_keywords_count = DB_Helper::get_var(
			$wpdb->prepare(
				"SELECT NULLIF(COUNT(DISTINCT(query)), 0)
				FROM {$wpdb->prefix}rank_math_analytics_gsc
				WHERE created BETWEEN %s AND %s",
				$this->compare_start_date,
				$this->compare_end_date
			)
		);

		$keywords = [
			'total'      => is_null( $keywords_count ) ? 'n/a' : (int) $keywords_count,
			'previous'   => is_null( $old_keywords_count ) ? 'n/a' : (int) $old_keywords_count,
			'difference' => is_null( $keywords_count ) || is_null( $old_keywords_count ) ? 'n/a' : (int) $keywords_count - (int) $old_keywords_count,
		];

		return $keywords;
	}

	/**
	 * Get analytics graph data.
	 *
	 * @return array
	 */
	public function get_analytics_summary_graph() {
		global $wpdb;

		$data = new \stdClass();

		// Step1. Get split date intervals for graph within selected date range.
		$intervals     = $this->get_intervals();
		$sql_daterange = $this->get_sql_date_intervals( $intervals );

		// Step2. Get current analytics data by split date intervals.
		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT DATE_FORMAT( created, '%%Y-%%m-%%d') as date, SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(position) as position, AVG(ctr) as ctr, {$sql_daterange}
			FROM {$wpdb->prefix}rank_math_analytics_gsc
			WHERE created BETWEEN %s AND %s
			GROUP BY range_group",
			$this->start_date,
			$this->end_date
		);
		$analytics = DB_Helper::get_results( $query );
		$analytics = $this->set_dimension_as_key( $analytics, 'range_group' );
		// phpcs:enable

		// Step2. Get current keyword data by split date intervals. Keyword count should be calculated as total count of most recent date for each split date intervals.
		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT t.range_group, MAX(CONCAT(t.range_group, ':', t.date, ':', t.keywords )) as mixed FROM
				(SELECT COUNT(DISTINCT(query)) as keywords, Date(created) as date, {$sql_daterange}
				FROM {$wpdb->prefix}rank_math_analytics_gsc
				WHERE created BETWEEN %s AND %s
				GROUP BY range_group, Date(created)) AS t
			GROUP BY t.range_group",
			$this->start_date,
			$this->end_date
		);
		$keywords = DB_Helper::get_results( $query );
		// phpcs:enable

		$keywords = $this->extract_data_from_mixed( $keywords, 'mixed', ':', [ 'keywords', 'date' ] );
		$keywords = $this->set_dimension_as_key( $keywords, 'range_group' );

		// merge metrics data.
		$data->analytics = [];
		$data->analytics = $this->get_merged_metrics( $analytics, $keywords, true );

		$data->merged = $this->get_date_array(
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

		// Convert types.
		$data->analytics = array_map( [ $this, 'normalize_graph_rows' ], $data->analytics );

		// Merge for performance.
		$data->merged = $this->get_merge_data_graph( $data->analytics, $data->merged, $intervals['map'] );

		// For developers.
		$data = apply_filters( 'rank_math/analytics/analytics_summary_graph', $data, $intervals );

		$data->merged = $this->get_graph_data_flat( $data->merged );
		$data->merged = array_values( $data->merged );

		return $data;
	}
}
