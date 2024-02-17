<?php
/**
 * The Helper class that provides easy access to accessing params from $_GET, $_POST and $_REQUEST.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath\Helpers;

/**
 * Param class.
 */
class Param {

	/**
	 * Get field from query string.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function get( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return filter_has_var( INPUT_GET, $id ) ? filter_input( INPUT_GET, $id, $filter, $flag ) : $default;
	}

	/**
	 * Get field from FORM post.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function post( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return filter_has_var( INPUT_POST, $id ) ? filter_input( INPUT_POST, $id, $filter, $flag ) : $default;
	}

	/**
	 * Get field from request.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function request( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return isset( $_REQUEST[ $id ] ) ? filter_var( $_REQUEST[ $id ], $filter, $flag ) : $default;
	}

	/**
	 * Get field from FORM server.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function server( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return isset( $_SERVER[ $id ] ) ? filter_var( $_SERVER[ $id ], $filter, $flag ) : $default;
	}
}
