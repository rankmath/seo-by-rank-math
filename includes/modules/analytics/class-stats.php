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
use MyThemeShop\Helpers\Param;
use RankMathPro\Analytics\Pageviews;

defined( 'ABSPATH' ) || exit;

/**
 * Stats class.
 */
class Stats extends Keywords {

	use Hooker;

	/**
	 * Start date.
	 *
	 * @var string
	 */
	public $start_date = '';

	/**
	 * End date.
	 *
	 * @var string
	 */
	public $end_date = '';

	/**
	 * Compare Start date.
	 *
	 * @var string
	 */
	public $compare_start_date = '';

	/**
	 * Compare End date.
	 *
	 * @var string
	 */
	public $compare_end_date = '';

	/**
	 * Number of days.
	 *
	 * @var int
	 */
	public $days = 0;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Stats
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Stats ) ) {
			$instance = new Stats();
			$instance->set_date_range();
		}

		return $instance;
	}

	/**
	 * Date range.
	 *
	 * @param string $range Range of days.
	 */
	public function set_date_range( $range = false ) {
		// Dates.
		$subtract = DAY_IN_SECONDS * 3;
		$start    = strtotime( false !== $range ? $range : $this->get_date_from_cookie( 'date_range', '-30 days' ) ) - $subtract - ( DAY_IN_SECONDS * 5 );
		$end      = strtotime( $this->do_filter( 'analytics/end_date', 'today' ) ) - $subtract;

		// Timestamp.
		$this->end   = Helper::get_midnight( $end );
		$this->start = Helper::get_midnight( $start );

		// Period.
		$this->end_date   = date_i18n( 'Y-m-d 23:59:59', $end );
		$this->start_date = date_i18n( 'Y-m-d 00:00:01', $start );

		// Compare with.
		$this->days               = ceil( abs( $end - $start ) / DAY_IN_SECONDS );
		$this->compare_end_date   = $start - DAY_IN_SECONDS;
		$this->compare_start_date = $this->compare_end_date - ( $this->days * DAY_IN_SECONDS );
		$this->compare_end_date   = date_i18n( 'Y-m-d 23:59:59', $this->compare_end_date );
		$this->compare_start_date = date_i18n( 'Y-m-d 00:00:01', $this->compare_start_date );
	}

	/**
	 * Get sql range.
	 *
	 * @param string $column Column name.
	 *
	 * @return string
	 */
	public function get_sql_range( $column = 'date' ) {
		$range    = $this->get_date_from_cookie( 'date_range', '-30 days' );
		$interval = [
			'-30 days'  => 'WEEK(' . $column . ')',
			'-3 months' => 'WEEK(' . $column . ')',
			'-6 months' => 'MONTH(' . $column . ')',
			'-1 year'   => 'MONTH(' . $column . ')',
		];

		return isset( $interval[ $range ] ) ? $interval[ $range ] : $column;
	}

	/**
	 * Get intervals for graph.
	 *
	 * @return array
	 */
	public function get_intervals() {
		$range    = $this->get_date_from_cookie( 'date_range', '-30 days' );
		$interval = [
			'-7 days'   => '0 days',
			'-15 days'  => '-3 days',
			'-30 days'  => '-6 days',
			'-3 months' => '-6 days',
			'-6 months' => '-30 days',
			'-1 year'   => '-30 days',
		];

		$ticks = [
			'-7 days'   => 7,
			'-15 days'  => 5,
			'-30 days'  => 5,
			'-3 months' => 13,
			'-6 months' => 6,
			'-1 year'   => 12,
		];

		$addition = [
			'-7 days'   => 0,
			'-15 days'  => DAY_IN_SECONDS,
			'-30 days'  => DAY_IN_SECONDS,
			'-3 months' => -DAY_IN_SECONDS / 6,
			'-6 months' => DAY_IN_SECONDS / 2,
			'-1 year'   => 0,
		];

		$ticks    = $ticks[ $range ];
		$interval = $interval[ $range ];
		$addition = $addition[ $range ];

		$map   = [];
		$dates = [];

		$end   = $this->end;
		$start = strtotime( $interval, $end );

		for ( $i = 0; $i < $ticks; $i++ ) {
			$end_date   = date_i18n( 'Y-m-d', $end );
			$start_date = date_i18n( 'Y-m-d', $start );

			$dates[ $end_date ] = [
				'start'     => $start_date,
				'end'       => $end_date,
				'formatted' => $start_date === $end_date ?
					date_i18n( 'd M, Y', $end ) :
					date_i18n( 'd M', $start ) . ' - ' . date_i18n( 'd M, Y', $end ),
			];

			$map[ $start_date ] = $end_date;
			for ( $j = 1; $j < 32; $j++ ) {
				$date = date_i18n( 'Y-m-d', strtotime( $j . ' days', $start ) );
				if ( $start_date === $end_date ) {
					break;
				}

				if ( $date === $end_date ) {
					break;
				}

				$map[ $date ] = $end_date;
			}
			$map[ $end_date ] = $end_date;

			$end   = \strtotime( '-1 days', $start );
			$start = \strtotime( $interval, $end + $addition );
		}

		return [
			'map'   => $map,
			'dates' => \array_reverse( $dates ),
		];
	}

	/**
	 * Get date array
	 *
	 * @param  array $dates Dates.
	 * @param  array $default Default value.
	 * @return array
	 */
	public function get_date_array( $dates, $default ) {
		$data = [];
		foreach ( $dates as $date => $d ) {
			$data[ $date ]                  = $default;
			$data[ $date ]['date']          = $date;
			$data[ $date ]['dateFormatted'] = $d['formatted'];
		}

		return $data;
	}

	/**
	 * Convert data to proper type.
	 *
	 * @param  array $row Row to normalize.
	 * @return array
	 */
	public function normalize_graph_rows( $row ) {
		foreach ( $row as $col => $val ) {
			if ( in_array( $col, [ 'query', 'page', 'date', 'created', 'dateFormatted' ], true ) ) {
				continue;
			}

			if ( in_array( $col, [ 'ctr', 'position', 'earnings' ], true ) ) {
				$row->$col = round( $row->$col, 0 );
				continue;
			}

			$row->$col = absint( $row->$col );
		}

		return $row;
	}

	/**
	 * [get_merge_data_graph description]
	 *
	 * @param  array $rows Rows to merge.
	 * @param  array $data Data array.
	 * @param  array $map  Interval map.
	 * @return array
	 */
	public function get_merge_data_graph( $rows, $data, $map ) {
		foreach ( $rows as $row ) {
			if ( ! isset( $map[ $row->date ] ) ) {
				continue;
			}

			$date = $map[ $row->date ];

			foreach ( $row as $key => $value ) {
				if ( 'date' === $key || 'created' === $key ) {
					continue;
				}

				$data[ $date ][ $key ][] = $value;
			}
		}

		return $data;
	}

	/**
	 * Flat graph data.
	 *
	 * @param  array $rows Graph data.
	 * @return array
	 */
	public function get_graph_data_flat( $rows ) {
		foreach ( $rows as &$row ) {
			if ( isset( $row['clicks'] ) ) {
				$row['clicks'] = \array_sum( $row['clicks'] );
			}

			if ( isset( $row['impressions'] ) ) {
				$row['impressions'] = \array_sum( $row['impressions'] );
			}

			if ( isset( $row['earnings'] ) ) {
				$row['earnings'] = \array_sum( $row['earnings'] );
			}

			if ( isset( $row['pageviews'] ) ) {
				$row['pageviews'] = \array_sum( $row['pageviews'] );
			}

			if ( isset( $row['ctr'] ) ) {
				$row['ctr'] = empty( $row['ctr'] ) ? 0 : ceil( array_sum( $row['ctr'] ) / count( $row['ctr'] ) );
			}

			if ( isset( $row['position'] ) ) {
				$row['position'] = empty( $row['position'] ) ? 0 : ceil( array_sum( $row['position'] ) / count( $row['position'] ) );
			}

			if ( isset( $row['keywords'] ) ) {
				$row['keywords'] = empty( $row['keywords'] ) ? 0 : ceil( array_sum( $row['keywords'] ) / count( $row['keywords'] ) );
			}
		}

		return $rows;
	}

	/**
	 * Get filter data.
	 *
	 * @param string $filter  Filter key.
	 * @param string $default Filter default value.
	 *
	 * @return mixed
	 */
	public function get_date_from_cookie( $filter, $default ) {
		$cookie_key = 'rank_math_analytics_' . $filter;
		$new_value  = sanitize_title( Param::post( $filter ) );
		if ( $new_value ) {
			setcookie( $cookie_key, $new_value, time() + ( HOUR_IN_SECONDS * 30 ), COOKIEPATH, COOKIE_DOMAIN, false, true );
			return $new_value;
		}

		if ( ! empty( $_COOKIE[ $cookie_key ] ) ) {
			return $_COOKIE[ $cookie_key ];
		}

		return $default;
	}

	/**
	 * Get rows from analytics.
	 *
	 * @param  array $args Array of arguments.
	 * @return array
	 */
	public function get_analytics_data( $args = [] ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			[
				'dimension' => 'page',
				'order'     => 'DESC',
				'orderBy'   => 'diffPosition',
				'objects'   => false,
				'pageview'  => false,
				'where'     => '',
				'sub_where' => '',
				'dates'     => ' AND created BETWEEN %s AND %s',
				'limit'     => 'LIMIT 5',
				'pages'     => [],
			]
		);

		$where     = $args['where'];
		$limit     = $args['limit'];
		$dimension = $args['dimension'];
		$sub_where = $args['sub_where'];
		$dates     = $args['dates'];
		$created   = '';

		if ( 'date' === $args['orderBy'] ) {
			$created         = 'created, ';
			$args['orderBy'] = 't1.created';
		}
		$order = sprintf( 'ORDER BY %s %s', $args['orderBy'], $args['order'] );

		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT
				t1.{$dimension} as {$dimension}, t1.clicks, t1.impressions, ROUND( t1.position, 0 ) as position, t1.ctr,
				COALESCE( t1.clicks - t2.clicks, 0 ) as diffClicks,
				COALESCE( t1.impressions - t2.impressions, 0 ) as diffImpressions,
				COALESCE( ROUND( t1.position - t2.position, 0 ), 0 ) as diffPosition,
				COALESCE( t1.ctr - t2.ctr, 0 ) as diffCtr
			FROM
				( SELECT {$created}{$dimension}, SUM( clicks ) as clicks, SUM(impressions) as impressions, AVG(position) as position, AVG(ctr) as ctr FROM {$wpdb->prefix}rank_math_analytics_gsc WHERE 1 = 1{$dates}{$sub_where} GROUP BY {$dimension}) as t1
			LEFT JOIN
				( SELECT {$dimension}, SUM( clicks ) as clicks, SUM(impressions) as impressions, AVG(position) as position, AVG(ctr) as ctr FROM {$wpdb->prefix}rank_math_analytics_gsc WHERE 1 = 1{$dates}{$sub_where} GROUP BY {$dimension}) as t2
			ON t1.{$dimension} = t2.{$dimension}
			{$where}
			{$order}
			{$limit}",
			$this->start_date,
			$this->end_date,
			$this->compare_start_date,
			$this->compare_end_date
		);
		$data = $wpdb->get_results( $query, ARRAY_A );
		// phpcs:enable

		$rows      = 'page' === $dimension ? $this->set_page_as_key( $data ) : $data;
		$page_urls = \array_merge( \array_keys( $rows ), $args['pages'] );

		$pageviews = [];
		if ( \class_exists( 'RankMathPro\Analytics\Pageviews' ) && $args['pageview'] && ! empty( $page_urls ) ) {
			$pageviews = Pageviews::get_pageviews( [ 'pages' => $page_urls ] );
			$pageviews = $pageviews['rows'];
		}

		if ( $args['objects'] ) {
			$objects = $this->get_objects( $page_urls );
		}

		foreach ( $rows as $page => $row ) {
			$rows[ $page ]['pageviews'] = [
				'total'      => 0,
				'difference' => 0,
			];

			$rows[ $page ]['clicks'] = [
				'total'      => (int) $rows[ $page ]['clicks'],
				'difference' => (int) $rows[ $page ]['diffClicks'],
			];

			$rows[ $page ]['impressions'] = [
				'total'      => (int) $rows[ $page ]['impressions'],
				'difference' => (int) $rows[ $page ]['diffImpressions'],
			];

			$rows[ $page ]['position'] = [
				'total'      => (float) $rows[ $page ]['position'],
				'difference' => (float) $rows[ $page ]['diffPosition'],
			];

			$rows[ $page ]['ctr'] = [
				'total'      => (float) $rows[ $page ]['ctr'],
				'difference' => (float) $rows[ $page ]['diffCtr'],
			];

			unset(
				$rows[ $page ]['diffClicks'],
				$rows[ $page ]['diffImpressions'],
				$rows[ $page ]['diffPosition'],
				$rows[ $page ]['diffCtr']
			);
		}

		if ( $args['pageview'] && ! empty( $pageviews ) ) {
			foreach ( $pageviews as $pageview ) {
				$page = $pageview['page'];
				if ( ! isset( $rows[ $page ] ) ) {
					$rows[ $page ] = [];
				}

				$rows[ $page ]['pageviews'] = [
					'total'      => (int) $pageview['pageviews'],
					'difference' => (int) $pageview['difference'],
				];
			}
		}

		if ( $args['objects'] && ! empty( $objects ) ) {
			foreach ( $objects as $object ) {
				$page = $object['page'];
				if ( ! isset( $rows[ $page ] ) ) {
					$rows[ $page ] = [];
				}
				$rows[ $page ] = array_merge( $rows[ $page ], $object );
			}
		}

		return $rows;
	}

	/**
	 * Set page as key.
	 *
	 * @param array $data Rows to process.
	 * @return array
	 */
	public function set_page_as_key( $data ) {
		$rows = [];
		foreach ( $data as $row ) {
			$page          = $this->get_relative_url( $row['page'] );
			$rows[ $page ] = $row;
		}

		return $rows;
	}

	/**
	 * Set query as key.
	 *
	 * @param array $data Rows to process.
	 * @return array
	 */
	public function set_query_as_key( $data ) {
		$rows = [];
		foreach ( $data as $row ) {
			$rows[ $row['query'] ] = $row;
		}

		return $rows;
	}

	/**
	 * Set query position history.
	 *
	 * @param array $data    Rows to process.
	 * @param array $history Rows to process.
	 *
	 * @return array
	 */
	public function set_query_position( $data, $history ) {
		foreach ( $history as $row ) {
			if ( ! isset( $data[ $row->query ]['graph'] ) ) {
				$data[ $row->query ]['graph'] = [];
			}

			$data[ $row->query ]['graph'][] = $row;
		}

		return $data;
	}

	/**
	 * Set page position history.
	 *
	 * @param array $data    Rows to process.
	 * @param array $history Rows to process.
	 *
	 * @return array
	 */
	public function set_page_position_graph( $data, $history ) {
		foreach ( $history as $row ) {
			if ( ! isset( $data[ $row->page ]['graph'] ) ) {
				$data[ $row->page ]['graph'] = [];
			}

			$data[ $row->page ]['graph'][] = $row;
		}

		return $data;
	}

	/**
	 * Generate Cache Keys.
	 *
	 * @param string $what What for you need the key.
	 * @param mixed  $args more salt to add into key.
	 *
	 * @return string
	 */
	public function get_cache_key( $what, $args = [] ) {
		$key = 'rank_math_' . $what;

		if ( ! empty( $args ) ) {
			$key .= '_' . join( '_', (array) $args );
		}

		return $key;
	}

	/**
	 * Get relative url.
	 *
	 * @param  string $url Url to make relative.
	 * @return string
	 */
	public static function get_relative_url( $url ) {
		$home_url = home_url();

		$domain = strtolower( wp_parse_url( home_url(), PHP_URL_HOST ) );
		$domain = str_replace( [ 'www.', '.' ], [ '', '\.' ], $domain );
		$regex  = "/http[s]?:\/\/(www\.)?$domain/mU";
		$url    = strtolower( trim( $url ) );
		$url    = preg_replace( $regex, '', $url );

		return \str_replace( $home_url, '', $url );
	}
}
