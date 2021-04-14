<?php
/**
 * The Redirection module database operations.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use MyThemeShop\Helpers\Str;
use MyThemeShop\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * DB class.
 */
class DB {

	/**
	 * Get query builder object.
	 *
	 * @return Query_Builder
	 */
	private static function table() {
		return Database::table( 'rank_math_redirections' );
	}

	/**
	 * Get counts of records grouped by active and inactive.
	 *
	 * @return array
	 */
	public static function get_counts() {
		static $redirction_counts;
		if ( ! is_null( $redirction_counts ) ) {
			return $redirction_counts;
		}

		$redirction_counts = self::table()
			->selectSum( 'status = "active"', 'active' )
			->selectSum( 'status = "inactive"', 'inactive' )
			->selectSum( 'status = "trashed"', 'trashed' )
			->one( ARRAY_A );

		$redirction_counts['all'] = $redirction_counts['active'] + $redirction_counts['inactive'];

		return $redirction_counts;
	}

	/**
	 * Get redirections.
	 *
	 * @param array $args Array of filters apply to query.
	 *
	 * @return array
	 */
	public static function get_redirections( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'orderby' => 'id',
				'order'   => 'DESC',
				'limit'   => 10,
				'paged'   => 1,
				'search'  => '',
				'status'  => 'any',
			]
		);

		$status = self::is_valid_status( $args['status'] ) ? [ $args['status'], null ] : [ '!=', 'trashed' ];

		$table = self::table()
			->found_rows()
			->page( $args['paged'] - 1, $args['limit'] )
			->where( 'status', $status[0], $status[1] );

		if ( ! empty( $args['search'] ) ) {
			$table->whereLike( 'sources', $args['search'] );
			$table->orWhereLike( 'url_to', $args['search'] );
		}

		if ( ! empty( $args['orderby'] ) && in_array( $args['orderby'], [ 'id', 'url_to', 'header_code', 'hits', 'last_accessed' ], true ) ) {
			$table->orderBy( $args['orderby'], $args['order'] );
		}

		do_action_ref_array( 'rank_math/redirection/get_redirections_query', [ &$table, $args ] );

		$redirections = $table->get( ARRAY_A );
		$count        = $table->get_found_rows();

		return compact( 'redirections', 'count' );
	}

	/**
	 * Match redirections for URI.
	 *
	 * @param string $uri Current URI to match.
	 * @param bool   $all Get All.
	 *
	 * @return object
	 */
	public static function match_redirections( $uri, $all = false ) {
		if ( empty( $uri ) ) {
			return false;
		}

		// If nothing found than go for all.
		if ( $all ) {
			$redirections = self::table()
				->where( 'status', 'active' )
				->orderby( 'updated', 'desc' )
				->get( ARRAY_A );

			return self::compare_redirections( $redirections, $uri );
		}

		$table = self::table()->where( 'status', 'active' )->orderby( 'updated', 'desc' );

		// Generate words.
		$words = str_replace( '/', '-', $uri );
		$words = str_replace( '.', '-', $words );
		$words = explode( '-', $words );

		// Generate where clause.
		$where  = [];
		$source = maybe_serialize(
			[
				'pattern'    => $uri,
				'comparison' => 'exact',
			]
		);

		$where[] = [ 'sources', 'like', $table->esc_like( $source ) ];
		foreach ( $words as $word ) {
			$where[] = [ 'sources', 'like', $table->esc_like( $word ) ];
		}

		$redirections = $table->where( $where, 'or' )->get( ARRAY_A );
		$redirection  = self::compare_redirections( $redirections, $uri );
		if ( false === $redirection ) {
			return self::match_redirections( $uri, true );
		}

		return $redirection;
	}

	/**
	 * Compare given redirections.
	 *
	 * @param array  $redirections Array of redirection matched.
	 * @param string $uri          URI to compare with.
	 *
	 * @return array|bool
	 */
	private static function compare_redirections( $redirections, $uri ) {
		foreach ( $redirections as $redirection ) {
			$redirection['sources'] = maybe_unserialize( $redirection['sources'] );
			if ( ! empty( $redirection['sources'] ) && self::compare_sources( $redirection['sources'], $uri ) ) {
				return $redirection;
			}
		}

		return false;
	}

	/**
	 * Compare sources.
	 *
	 * @param array  $sources Array of sources.
	 * @param string $uri     URI to compare with.
	 *
	 * @return bool
	 */
	public static function compare_sources( $sources, $uri ) {
		if ( ! is_array( $sources ) || empty( $sources ) ) {
			return false;
		}

		foreach ( $sources as $source ) {
			if ( 'exact' === $source['comparison'] && isset( $source['ignore'] ) && 'case' === $source['ignore'] && strtolower( $source['pattern'] ) === strtolower( $uri ) ) {
				return true;
			}

			if ( Str::comparison( self::get_clean_pattern( $source['pattern'], $source['comparison'] ), $uri, $source['comparison'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Match redirections for a source.
	 *
	 * @param string $source Current source to match.
	 *
	 * @return array
	 */
	public static function match_redirections_source( $source ) {
		if ( empty( $source ) ) {
			return false;
		}

		$table = self::table();

		return $table->found_rows()
			->where( 'status', 'active' )
			->whereLike( 'sources', $source )
			->orderby( 'updated', 'desc' )
			->page( 0, 1 )
			->get( ARRAY_A );
	}

	/**
	 * Get clean pattern for testing.
	 *
	 * @param string $pattern    Pattern to clean.
	 * @param string $comparison Comparison type.
	 *
	 * @return string
	 */
	public static function get_clean_pattern( $pattern, $comparison ) {
		$pattern = trim( $pattern, '/' );
		return 'regex' === $comparison ? ( '@' . stripslashes( $pattern ) . '@' ) : $pattern;
	}

	/**
	 *  Get source by ID.
	 *
	 * @param int    $id     ID of the record to search for.
	 * @param string $status Status to filter with.
	 *
	 * @return bool|array
	 */
	public static function get_redirection_by_id( $id, $status = 'all' ) {
		$table = self::table()->where( 'id', $id );

		if ( 'all' !== $status ) {
			$table->where( 'status', $status );
		}

		$item = $table->one( ARRAY_A );
		if ( ! isset( $item['sources'] ) ) {
			return false;
		}

		$item['sources'] = maybe_unserialize( $item['sources'] );
		return $item;
	}

	/**
	 * Get stats for dashboard widget.
	 *
	 * @return int
	 */
	public static function get_stats() {
		return self::table()->selectCount( '*', 'total' )->selectSum( 'hits', 'hits' )->one();
	}

	/**
	 * Add a new record.
	 *
	 * @param array $args Values to insert.
	 *
	 * @return bool|int
	 */
	public static function add( $args = [] ) {
		if ( empty( $args ) ) {
			return false;
		}

		$args = wp_parse_args(
			$args,
			[
				'sources'     => '',
				'url_to'      => '',
				'header_code' => '301',
				'hits'        => '0',
				'status'      => 'active',
				'created'     => current_time( 'mysql' ),
				'updated'     => current_time( 'mysql' ),
			]
		);

		if ( in_array( $args['header_code'], [ 410, 451 ], true ) ) {
			$args['url_to'] = '';
		}

		$args['sources'] = maybe_serialize( $args['sources'] );

		return self::table()->insert( $args, [ '%s', '%s', '%d', '%d', '%s', '%s', '%s' ] );
	}

	/**
	 * Update a record.
	 *
	 * @param array $args Values to update.
	 *
	 * @return bool|int
	 */
	public static function update( $args = [] ) {
		if ( empty( $args ) ) {
			return false;
		}

		$args = wp_parse_args(
			$args,
			[
				'id'          => '',
				'sources'     => '',
				'url_to'      => '',
				'header_code' => '301',
				'status'      => 'active',
				'updated'     => current_time( 'mysql' ),
			]
		);

		$id = absint( $args['id'] );
		if ( 0 === $id ) {
			return false;
		}

		$args['sources'] = maybe_serialize( $args['sources'] );
		unset( $args['id'] );

		if ( in_array( $args['header_code'], [ 410, 451 ], true ) ) {
			$args['url_to'] = '';
		}

		Cache::purge( $id );
		return self::table()->set( $args )->where( 'id', $id )->update();
	}

	/**
	 * Add or Update record.
	 *
	 * @param array $redirection Single redirection item.
	 *
	 * @return int
	 */
	public static function update_iff( $redirection ) {
		// Update record.
		if ( isset( $redirection['id'] ) && ! empty( $redirection['id'] ) ) {
			self::update( $redirection );
			return $redirection['id'];
		}

		$existing_redirection = self::match_redirections_source( maybe_serialize( $redirection['sources'] ) );
		if ( ! empty( $existing_redirection ) && isset( $existing_redirection[0]['id'] ) ) {
			$redirection['id'] = $existing_redirection[0]['id'];
			self::update( $redirection );
			return $redirection['id'];
		}

		// Add record.
		return self::add( $redirection );
	}

	/**
	 * Update counter for redirection.
	 *
	 * @param object $redirection Record to update.
	 *
	 * @return int|false The number of rows updated, or false on error.
	 */
	public static function update_access( $redirection = false ) {
		if ( empty( $redirection ) ) {
			return false;
		}

		$args['hits']          = absint( $redirection['hits'] ) + 1;
		$args['last_accessed'] = current_time( 'mysql' );

		self::table()->set( $args )->where( 'id', $redirection['id'] )->update();
	}

	/**
	 * Delete multiple records.
	 *
	 * @param array $ids Array of ids to delete.
	 *
	 * @return int Number of records deleted.
	 */
	public static function delete( $ids ) {
		Cache::purge( $ids );
		return self::table()->whereIn( 'id', (array) $ids )->delete();
	}

	/**
	 * Change record status to active or inactive.
	 *
	 * @param array $ids     Array of ids.
	 * @param bool  $status Active=1, Inactive=0.
	 *
	 * @return int Number of records updated.
	 */
	public static function change_status( $ids, $status ) {
		if ( ! self::is_valid_status( $status ) ) {
			return false;
		}

		return self::table()->set( 'status', $status )
			->set( 'updated', current_time( 'mysql' ) )
			->whereIn( 'id', (array) $ids )->update();
	}

	/**
	 * Clean trashed redirects after 30 days.
	 *
	 * @return int Number of records deleted.
	 */
	public static function periodic_clean_trash() {
		$ids = self::table()->select( 'id' )->where( 'status', 'trashed' )->where( 'updated', '<=', date_i18n( 'Y-m-d', strtotime( '30 days ago' ) ) )->get( ARRAY_A );
		if ( empty( $ids ) ) {
			return 0;
		}

		return self::delete( wp_list_pluck( $ids, 'id' ) );
	}

	/**
	 * Delete all trashed redirections and associated sources.
	 *
	 * @return int Number of records deleted.
	 */
	public static function clear_trashed() {
		$ids = self::table()->select( 'id' )->where( 'status', 'trashed' )->get();
		if ( empty( $ids ) ) {
			return 0;
		}

		return self::delete( wp_list_pluck( $ids, 'id' ) );
	}

	/**
	 * Check if status is valid.
	 *
	 * @param string $status Status to validate.
	 *
	 * @return bool
	 */
	private static function is_valid_status( $status ) {
		$allowed = [ 'active', 'inactive', 'trashed' ];
		return in_array( $status, $allowed, true );
	}
}
