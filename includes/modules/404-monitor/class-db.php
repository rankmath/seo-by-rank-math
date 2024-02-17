<?php
/**
 * The database operations for the 404 Monitor module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Monitor
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Monitor;

use RankMath\Helper;
use RankMath\Admin\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * DB class.
 */
class DB {

	/**
	 * Get query builder.
	 *
	 * @return Query_Builder
	 */
	private static function table() {
		return Database::table( 'rank_math_404_logs' );
	}

	/**
	 * Get error log items.
	 *
	 * @param array $args Array of arguments.
	 *
	 * @return array
	 */
	public static function get_logs( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'orderby' => 'id',
				'order'   => 'DESC',
				'limit'   => 10,
				'paged'   => 1,
				'search'  => '',
				'ids'     => [],
				'uri'     => '',
			]
		);

		$args = apply_filters( 'rank_math/404_monitor/get_logs_args', $args );

		$table = self::table()->found_rows()->page( $args['paged'] - 1, $args['limit'] );

		if ( ! empty( $args['search'] ) ) {
			$table->whereLike( 'uri', rawurlencode( $args['search'] ) );
		}

		if ( ! empty( $args['ids'] ) ) {
			$table->whereIn( 'id', (array) $args['ids'] );
		}

		if ( ! empty( $args['uri'] ) ) {
			$table->where( 'uri', $args['uri'] );
		}

		if ( ! empty( $args['orderby'] ) && in_array( $args['orderby'], [ 'id', 'uri', 'accessed', 'times_accessed' ], true ) ) {
			$table->orderBy( $args['orderby'], $args['order'] );
		}

		return [
			'logs'  => $table->get( ARRAY_A ),
			'count' => $table->get_found_rows(),
		];
	}

	/**
	 * Add a record.
	 *
	 * @param array $args Values to insert.
	 */
	public static function add( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'uri'            => '',
				'accessed'       => current_time( 'mysql' ),
				'times_accessed' => '1',
				'referer'        => '',
				'user_agent'     => '',
			]
		);

		// Maybe delete logs if record exceed defined limit.
		$limit = absint( Helper::get_settings( 'general.404_monitor_limit' ) );
		if ( $limit && self::get_count() >= $limit ) {
			self::clear_logs();
		}

		return self::table()->insert( $args, [ '%s', '%s', '%d', '%s', '%s', '%s' ] );
	}

	/**
	 * Update a record.
	 *
	 * @param array $args Values to update.
	 */
	public static function update( $args ) {
		$row = self::table()->where( 'uri', $args['uri'] )->one( ARRAY_A );

		if ( $row ) {
			return self::update_counter( $row );
		}

		return self::add( $args );
	}

	/**
	 * Delete a record.
	 *
	 * @param array $ids Array of IDs to delete.
	 *
	 * @return int Number of records deleted.
	 */
	public static function delete_log( $ids ) {
		return self::table()->whereIn( 'id', (array) $ids )->delete();
	}

	/**
	 * Get total number of log items (number of rows in the DB table).
	 *
	 * @return int
	 */
	public static function get_count() {
		return self::table()->selectCount()->getVar();
	}

	/**
	 * Clear logs completely.
	 *
	 * @return int
	 */
	public static function clear_logs() {
		return self::table()->truncate();
	}

	/**
	 * Get stats for dashboard widget.
	 *
	 * @return array
	 */
	public static function get_stats() {
		return self::table()->selectCount( '*', 'total' )->selectSum( 'times_accessed', 'hits' )->one();
	}

	/**
	 * Update if URL is matched and hit.
	 *
	 * @param object $row Record to update.
	 *
	 * @return int|false The number of rows updated, or false on error.
	 */
	private static function update_counter( $row ) {
		$update_data = [
			'accessed'       => current_time( 'mysql' ),
			'times_accessed' => absint( $row['times_accessed'] ) + 1,
		];

		return self::table()->set( $update_data )->where( 'id', absint( $row['id'] ) )->update();
	}
}
