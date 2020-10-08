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
use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Pageviews class.
 */
class Pageviews extends Summary {

	/**
	 * Get page views for pages.
	 *
	 * @param array $args Array of urls.
	 *
	 * @return array
	 */
	public function get_pageviews( $args = [] ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			[
				'order'     => 'DESC',
				'orderBy'   => 't1.pageviews',
				'where'     => '',
				'sub_where' => '',
				'dates'     => ' AND created BETWEEN %s AND %s',
				'limit'     => '',
				'pages'     => '',
			]
		);

		if ( ! empty( $args['pages'] ) ) {
			$args['pages'] = ' AND page IN (\'' . join( '\', \'', $args['pages'] ) . '\')';
		}

		$pages     = $args['pages'];
		$where     = $args['where'];
		$limit     = $args['limit'];
		$dates     = $args['dates'];
		$sub_where = $args['sub_where'];
		$order     = sprintf( 'ORDER BY %s %s', $args['orderBy'], $args['order'] );

		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS t1.page as page, COALESCE( t1.pageviews, 0 ) as pageviews, COALESCE( t2.pageviews - t1.pageviews, 0 ) as difference
			FROM
				( SELECT page, SUM(pageviews) as pageviews FROM {$wpdb->prefix}rank_math_analytics_ga WHERE 1=1{$pages}{$dates}{$sub_where} GROUP BY page ) as t1
			LEFT JOIN
				( SELECT page, SUM(pageviews) as pageviews FROM {$wpdb->prefix}rank_math_analytics_ga WHERE 1=1{$pages}{$dates}{$sub_where} GROUP BY page ) as t2
			ON t1.page = t2.page
			{$where}
			{$order}
			{$limit}",
			$this->start_date,
			$this->end_date,
			$this->compare_start_date,
			$this->compare_end_date
		);
		$rows      = $wpdb->get_results( $query, ARRAY_A );
		$rowsFound = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		return \compact( 'rows', 'rowsFound' );
	}

	/**
	 * Get page views for pages.
	 *
	 * @param array $args Array of urls.
	 *
	 * @return array
	 */
	public function get_pageviews_with_object( $args = [] ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			[
				'dates' => ' AND created BETWEEN %s AND %s',
				'limit' => '',
			]
		);

		$limit = $args['limit'];
		$dates = $args['dates'];

		// phpcs:disable
		$query = $wpdb->prepare(
			"WITH traffic AS (
			    SELECT t1.page as page, COALESCE( t1.pageviews, 0 ) as pageviews, COALESCE( t2.pageviews - t1.pageviews, 0 ) as difference
				FROM
			    	( SELECT page, SUM(pageviews) as pageviews FROM {$wpdb->prefix}rank_math_analytics_ga WHERE 1=1{$dates} GROUP BY page ) as t1
				LEFT JOIN
			    	( SELECT page, SUM(pageviews) as pageviews FROM {$wpdb->prefix}rank_math_analytics_ga WHERE 1=1{$dates} GROUP BY page ) as t2
				ON t1.page = t2.page
			)
			SELECT SQL_CALC_FOUND_ROWS o.*, COALESCE( t.pageviews, 0 ) as pageviews, COALESCE( t.difference, 0 ) as difference
			FROM {$wpdb->prefix}rank_math_analytics_objects as o
			LEFT JOIN traffic as t ON o.page = t.page
			ORDER BY pageviews DESC
			{$limit}",
			$this->start_date,
			$this->end_date,
			$this->compare_start_date,
			$this->compare_end_date
		);
		$rows      = $wpdb->get_results( $query, ARRAY_A );
		$rowsFound = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		return \compact( 'rows', 'rowsFound' );
	}
}
