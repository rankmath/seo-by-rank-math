<?php
/**
 * The Redirections Cache.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use MyThemeShop\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Cache class.
 */
class Cache {

	/**
	 * Get query builder.
	 *
	 * @return Query_Builder
	 */
	private static function table() {
		return Database::table( 'rank_math_redirections_cache' );
	}

	/**
	 * Get redirection by object ID.
	 *
	 * @param  integer $object_id   Object ID to look for.
	 * @param  string  $object_type Current objcect type.
	 * @return object
	 */
	public static function get_by_object_id( $object_id, $object_type ) {
		return self::table()->where( 'object_id', $object_id )->where( 'object_type', $object_type )->one();
	}

	/**
	 * Get redirection by URL.
	 *
	 * @param  integer $url URL to look for.
	 * @return object
	 */
	public static function get_by_url( $url ) {
		return empty( $url ) ? false : self::table()->where( 'BINARY from_url', $url )->one();
	}

	/**
	 * Add a new record.
	 *
	 * @param array $args Values to insert.
	 */
	public static function add( $args = [] ) {
		if ( empty( $args ) ) {
			return false;
		}

		$args = wp_parse_args(
			$args,
			[
				'from_url'       => '',
				'redirection_id' => '',
				'object_id'      => '',
				'object_type'    => 'post',
				'is_redirected'  => '1',
			]
		);

		return self::table()->insert( $args, [ '%s', '%d', '%d', '%s', '%d' ] );
	}

	/**
	 * Purge cache for a redirection.
	 *
	 * @param  integer $ids Redirection IDs to purge cache for.
	 * @return integer
	 */
	public static function purge( $ids ) {
		return self::table()->whereIn( 'redirection_id', (array) $ids )->delete();
	}

	/**
	 * Purge cache for an object.
	 *
	 * @param  integer $ids Object IDs to purge cache for.
	 * @param  string  $object_type Current object type.
	 * @return integer
	 */
	public static function purge_by_object_id( $ids, $object_type ) {
		return self::table()->whereIn( 'object_id', (array) $ids )
			->where( 'object_type', $object_type )->delete();
	}
}
