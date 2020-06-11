<?php
/**
 * The Security wrappers.
 *
 * @since      1.0.41.3
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Security class.
 */
class Security {

	/**
	 * Add query arg
	 *
	 * @param mixed ...$args Array of arguments.
	 *
	 * @return string
	 */
	public static function add_query_arg( ...$args ) {
		return esc_url( add_query_arg( ...$args ) );
	}

	/**
	 * Add query arg
	 *
	 * @param mixed ...$args Array of arguments.
	 *
	 * @return string
	 */
	public static function add_query_arg_raw( ...$args ) {
		return esc_url_raw( add_query_arg( ...$args ) );
	}

	/**
	 * Removes an item or items from a query string.
	 *
	 * @param string|array   $key    (Required) Query key or keys to remove.
	 * @param string|boolean $query  When false uses the current URL.
	 *
	 * @return string
	 */
	public static function remove_query_arg( $key, $query = false ) {
		return esc_url( remove_query_arg( $key, $query ) );
	}

	/**
	 * Removes an item or items from a query string.
	 *
	 * @param string|array   $key    (Required) Query key or keys to remove.
	 * @param string|boolean $query  When false uses the current URL.
	 *
	 * @return string
	 */
	public static function remove_query_arg_raw( $key, $query = false ) {
		return esc_url_raw( remove_query_arg( $key, $query ) );
	}
}
