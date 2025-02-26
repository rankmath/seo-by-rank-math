<?php
/**
 * The Analytics module database operations
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Google\Console;
use RankMath\Helpers\Str;
use RankMath\Helpers\DB as DB_Helper;
use RankMath\Admin\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * DB class.
 */
class DB {

	/**
	 * Get any table.
	 *
	 * @param string $table_name Table name.
	 *
	 * @return \RankMath\Admin\Database\Query_Builder
	 */
	public static function table( $table_name ) {
		return Database::table( $table_name );
	}

	/**
	 * Get console data table.
	 *
	 * @return \RankMath\Admin\Database\Query_Builder
	 */
	public static function analytics() {
		return Database::table( 'rank_math_analytics_gsc' );
	}

	/**
	 * Get objects table.
	 *
	 * @return \RankMath\Admin\Database\Query_Builder
	 */
	public static function objects() {
		return Database::table( 'rank_math_analytics_objects' );
	}

	/**
	 * Get inspections table.
	 *
	 * @return \RankMath\Admin\Database\Query_Builder
	 */
	public static function inspections() {
		return Database::table( 'rank_math_analytics_inspections' );
	}

	/**
	 * Delete a record.
	 *
	 * @param  int $days Decide whether to delete all or delete 90 days data.
	 */
	public static function delete_by_days( $days ) {
		// Delete console data.
		if ( Console::is_console_connected() ) {
			if ( -1 === $days ) {
				self::analytics()->truncate();
			} else {
				$start = date_i18n( 'Y-m-d H:i:s', strtotime( '-1 days' ) );
				$end   = date_i18n( 'Y-m-d H:i:s', strtotime( '-' . $days . ' days' ) );

				self::analytics()->whereBetween( 'created', [ $end, $start ] )->delete();
			}
		}

		// Delete analytics, adsense data.
		do_action( 'rank_math/analytics/delete_by_days', $days );
		self::purge_cache();

		return true;
	}

	/**
	 * Delete record for comparison.
	 */
	public static function delete_data_log() {
		$days = Helper::get_settings( 'general.console_caching_control', 90 );

		// Delete old console data more than 2 times ago of specified number of days to keep the data.
		$start = date_i18n( 'Y-m-d H:i:s', strtotime( '-' . ( $days * 2 ) . ' days' ) );

		self::analytics()->where( 'created', '<', $start )->delete();

		// Delete old analytics and adsense data.
		do_action( 'rank_math/analytics/delete_data_log', $start );
	}

	/**
	 * Purge SC transient
	 */
	public static function purge_cache() {
		$table = Database::table( 'options' );
		$table->whereLike( 'option_name', 'top_keywords' )->delete();
		$table->whereLike( 'option_name', 'posts_summary' )->delete();
		$table->whereLike( 'option_name', 'top_keywords_graph' )->delete();
		$table->whereLike( 'option_name', 'dashboard_stats_widget' )->delete();
		$table->whereLike( 'option_name', 'rank_math_analytics_data_info' )->delete();

		do_action( 'rank_math/analytics/purge_cache', $table );

		wp_cache_flush();
	}

	/**
	 * Get search console table info.
	 *
	 * @return array
	 */
	public static function info() {
		global $wpdb;

		if ( ! Api::get()->is_console_connected() ) {
			return [];
		}

		$key  = 'rank_math_analytics_data_info';
		$data = get_transient( $key );
		if ( false !== $data ) {
			return $data;
		}

		$days = self::analytics()
			->selectCount( 'DISTINCT(created)', 'days' )
			->getVar();

		$rows = self::analytics()
			->selectCount( 'id' )
			->getVar();

		$size = $wpdb->get_var( "SELECT SUM((data_length + index_length)) AS size FROM information_schema.TABLES WHERE table_schema='" . $wpdb->dbname . "' AND (table_name='" . $wpdb->prefix . "rank_math_analytics_gsc')" ); // phpcs:ignore
		$data = compact( 'days', 'rows', 'size' );

		$data = apply_filters( 'rank_math/analytics/analytics_tables_info', $data );

		set_transient( $key, $data, DAY_IN_SECONDS );

		return $data;
	}

	/**
	 * Has data pulled.
	 *
	 * @return boolean
	 */
	public static function has_data() {
		static $rank_math_gsc_has_data;
		if ( isset( $rank_math_gsc_has_data ) ) {
			return $rank_math_gsc_has_data;
		}

		$id = self::objects()
		->select( 'id' )
		->limit( 1 )
		->getVar();

		$rank_math_gsc_has_data = $id > 0 ? true : false;
		return $rank_math_gsc_has_data;
	}

	/**
	 * Check if console data exists at specified date.
	 *
	 * @param  string $date   Date to check data existence.
	 * @param  string $action Action name to filter data type.
	 * @return boolean
	 */
	public static function date_exists( $date, $action = 'console' ) {
		$tables['console'] = DB_Helper::check_table_exists( 'rank_math_analytics_gsc' ) ? 'rank_math_analytics_gsc' : '';

		/**
		 * Filter: 'rank_math/analytics/date_exists_tables' - Allow developers to add more tables to check.
		 */
		$tables = apply_filters( 'rank_math/analytics/date_exists_tables', $tables, $date, $action );

		if ( empty( $tables[ $action ] ) ) {
			return true; // Should return true to avoid further data fetch action.
		}

		$table = self::table( $tables[ $action ] );

		$id = $table
			->select( 'id' )
			->where( 'DATE(created)', $date )
			->getVar();

		return $id > 0 ? true : false;
	}

	/**
	 * Add a new record into objects table.
	 *
	 * @param array $args Values to insert.
	 *
	 * @return bool|int
	 */
	public static function add_object( $args = [] ) {
		if ( empty( $args ) ) {
			return false;
		}

		unset( $args['id'] );

		$args = wp_parse_args(
			$args,
			[
				'created'        => current_time( 'mysql' ),
				'page'           => '',
				'object_type'    => 'post',
				'object_subtype' => 'post',
				'object_id'      => 0,
				'primary_key'    => '',
				'seo_score'      => 0,
				'page_score'     => 0,
				'is_indexable'   => false,
				'schemas_in_use' => '',
			]
		);

		return self::objects()->insert( $args, [ '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d', '%s' ] );
	}

	/**
	 * Add new record in the inspections table.
	 *
	 * @param array $args Values to insert.
	 *
	 * @return bool|int
	 */
	public static function store_inspection( $args = [] ) {
		if ( empty( $args ) || empty( $args['page'] ) ) {
			return false;
		}

		unset( $args['id'] );

		$defaults = self::get_inspection_defaults();

		// Only keep $args items that are in $defaults.
		$args = array_intersect_key( $args, $defaults );

		// Apply defaults.
		$args = wp_parse_args( $args, $defaults );

		// We only have strings: placeholders will be '%s'.
		$format = array_fill( 0, count( $args ), '%s' );

		// Check if we have an existing record, based on 'page'.
		$id = self::inspections()
			->select( 'id' )
			->where( 'page', $args['page'] )
			->getVar();

		if ( $id ) {
			return self::inspections()
				->set( $args )
				->where( 'id', $id )
				->update();
		}

		return self::inspections()->insert( $args, $format );
	}

	/**
	 * Get inspection defaults.
	 *
	 * @return array
	 */
	public static function get_inspection_defaults() {
		$defaults = [
			'created'              => current_time( 'mysql' ),
			'page'                 => '',
			'index_verdict'        => 'VERDICT_UNSPECIFIED',
			'indexing_state'       => 'INDEXING_STATE_UNSPECIFIED',
			'coverage_state'       => '',
			'page_fetch_state'     => 'PAGE_FETCH_STATE_UNSPECIFIED',
			'robots_txt_state'     => 'ROBOTS_TXT_STATE_UNSPECIFIED',
			'rich_results_verdict' => 'VERDICT_UNSPECIFIED',
			'rich_results_items'   => '',
			'last_crawl_time'      => '',
			'crawled_as'           => 'CRAWLING_USER_AGENT_UNSPECIFIED',
			'google_canonical'     => '',
			'user_canonical'       => '',
			'sitemap'              => '',
			'referring_urls'       => '',
			'raw_api_response'     => '',
		];

		return apply_filters( 'rank_math/analytics/inspection_defaults', $defaults );
	}

	/**
	 * Add/Update a record into/from objects table.
	 *
	 * @param array $args Values to update.
	 *
	 * @return bool|int
	 */
	public static function update_object( $args = [] ) {
		if ( empty( $args ) ) {
			return false;
		}

		// If object exists, try to update.
		$old_id = absint( $args['id'] );
		if ( ! empty( $old_id ) ) {
			unset( $args['id'] );

			$updated = self::objects()->set( $args )
				->where( 'id', $old_id )
				->where( 'object_id', absint( $args['object_id'] ) )
				->update();

			if ( ! empty( $updated ) ) {
				return $old_id;
			}
		}

		// In case of new object or failed to update, try to add.
		return self::add_object( $args );
	}

	/**
	 * Add console records.
	 *
	 * @param string $date Date of creation.
	 * @param array  $rows Data rows to insert.
	 */
	public static function add_query_page_bulk( $date, $rows ) {
		$chunks = array_chunk( $rows, 50 );

		foreach ( $chunks as $chunk ) {
			self::bulk_insert_query_page_data( $date . ' 00:00:00', $chunk );
		}
	}

	/**
	 * Bulk inserts records into a console table using WPDB.  All rows must contain the same keys.
	 *
	 * @param  string $date        Date.
	 * @param  array  $rows        Rows to insert.
	 */
	public static function bulk_insert_query_page_data( $date, $rows ) {
		global $wpdb;

		$data         = [];
		$placeholders = [];
		$columns      = [
			'created',
			'query',
			'page',
			'clicks',
			'impressions',
			'position',
			'ctr',
		];
		$columns      = '`' . implode( '`, `', $columns ) . '`';
		$placeholder  = [
			'%s',
			'%s',
			'%s',
			'%d',
			'%d',
			'%d',
			'%d',
		];

		// Start building SQL, initialise data and placeholder arrays.
		$sql = "INSERT INTO `{$wpdb->prefix}rank_math_analytics_gsc` ( $columns ) VALUES\n";

		// Build placeholders for each row, and add values to data array.
		foreach ( $rows as $row ) {
			if (
				$row['position'] > self::get_position_filter() ||
				Str::contains( '?', $row['page'] )
			) {
				continue;
			}

			$data[] = $date;
			$data[] = $row['query'];
			$data[] = str_replace( Helper::get_home_url(), '', self::remove_hash( urldecode( $row['page'] ) ) );
			$data[] = $row['clicks'];
			$data[] = $row['impressions'];
			$data[] = $row['position'];
			$data[] = $row['ctr'];

			$placeholders[] = '(' . implode( ', ', $placeholder ) . ')';
		}

		// Don't run insert with empty dataset, return 0 since no rows affected.
		if ( empty( $data ) ) {
			return 0;
		}

		// Stitch all rows together.
		$sql .= implode( ",\n", $placeholders );

		// Run the query.  Returns number of affected rows.
		return $wpdb->query( $wpdb->prepare( $sql, $data ) ); // phpcs:ignore
	}

	/**
	 * Remove hash part from Url.
	 *
	 * @param  string $url Url to process.
	 * @return string
	 */
	public static function remove_hash( $url ) {
		if ( ! Str::contains( '#', $url ) ) {
			return $url;
		}

		$url = \explode( '#', $url );
		return $url[0];
	}

	/**
	 * Get position filter.
	 *
	 * @return int
	 */
	private static function get_position_filter() {
		$number = apply_filters( 'rank_math/analytics/position_limit', false );
		if ( false === $number ) {
			return 100;
		}

		return $number;
	}

	/**
	 * Get all inspections.
	 *
	 * @param array $params   REST Parameters.
	 * @param int   $per_page Limit.
	 */
	public static function get_inspections( $params, $per_page ) {
		$page     = ! empty( $params['page'] ) ? absint( $params['page'] ) : 1;
		$per_page = absint( $per_page );
		$offset   = ( $page - 1 ) * $per_page;

		$inspections = self::inspections()->table;
		$objects     = self::objects()->table;

		$query = self::inspections()
			->select( [ "$inspections.*", "$objects.title", "$objects.object_id" ] )
			->leftJoin( $objects, "$inspections.page", "$objects.page" )
			->where( "$objects.page", '!=', '' )
			->orderBy( 'id', 'DESC' )
			->limit( $per_page, $offset );

		do_action_ref_array( 'rank_math/analytics/get_inspections_query', [ &$query, $params ] );

		$results = $query->get();

		return apply_filters( 'rank_math/analytics/get_inspections_results', $results );
	}

	/**
	 * Get inspections count.
	 *
	 * @param array $params   REST Parameters.
	 *
	 * @return int
	 */
	public static function get_inspections_count( $params ) {
		$pages = self::objects()->select( 'page' )->get( ARRAY_A );
		$pages = array_unique( wp_list_pluck( $pages, 'page' ) );
		$query = self::inspections()->selectCount( 'id', 'total' )->whereIn( 'page', $pages );

		do_action_ref_array( 'rank_math/analytics/get_inspections_count_query', [ &$query, $params ] );

		return $query->getVar();
	}
}
