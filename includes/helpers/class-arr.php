<?php
/**
 * The Array helpers.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath\Helpers;

use ArrayAccess;

/**
 * Arr class.
 */
class Arr {

	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool
	 */
	public static function accessible( $value ) {
		return is_array( $value ) || $value instanceof ArrayAccess;
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param ArrayAccess|array $arr Array to check key in.
	 * @param string|int        $key   Key to check for.
	 *
	 * @return bool
	 */
	public static function exists( $arr, $key ) {
		if ( $arr instanceof ArrayAccess ) {
			// @codeCoverageIgnoreStart
			return $arr->offsetExists( $key );
			// @codeCoverageIgnoreEnd
		}

		return array_key_exists( $key, $arr );
	}

	/**
	 * Check whether an array or [[\Traversable]] contains an element.
	 *
	 * This method does the same as the PHP function [in_array()](https://secure.php.net/manual/en/function.in-array.php)
	 * but additionally works for objects that implement the [[\Traversable]] interface.
	 *
	 * @throws \InvalidArgumentException If `$arr` is neither traversable nor an array.
	 *
	 * @param array|\Traversable $arr  The set of values to search.
	 * @param mixed              $search The value to look for.
	 * @param bool               $strict Whether to enable strict (`===`) comparison.
	 *
	 * @return bool `true` if `$search` was found in `$arr`, `false` otherwise.
	 */
	public static function includes( $arr, $search, $strict = true ) {
		if ( $arr instanceof \Traversable ) {
			return self::includes_traversable( $arr, $search, $strict );
		}

		$is_array = is_array( $arr );
		if ( ! $is_array ) {
			throw new \InvalidArgumentException( 'Argument $arr must be an array or implement Traversable' );
		}

		return $is_array ? in_array( $search, $arr, $strict ) : false; // phpcs:ignore
	}

	/**
	 * Check Traversable contains an element.
	 *
	 * @param \Traversable $arr  The set of values to search.
	 * @param mixed        $search The value to look for.
	 * @param bool         $strict Whether to enable strict (`===`) comparison.
	 *
	 * @return bool `true` if `$search` was found in `$arr`, `false` otherwise.
	 */
	private static function includes_traversable( $arr, $search, $strict = true ) {
		foreach ( $arr as $value ) {
			if ( ( $strict && $search === $value ) || $search == $value ) { // phpcs:ignore
				return true;
			}
		}

		return false;
	}

	/**
	 * Insert a single array item inside another array at a set position
	 *
	 * @param array $arr      Array to modify. Is passed by reference, and no return is needed.
	 * @param array $new_arr  New array to insert.
	 * @param int   $position Position in the main array to insert the new array.
	 */
	public static function insert( &$arr, $new_arr, $position ) {
		$before = array_slice( $arr, 0, $position - 1 );
		$after  = array_diff_key( $arr, $before );
		$arr    = array_merge( $before, $new_arr, $after );
	}

	/**
	 * Push an item onto the beginning of an array.
	 *
	 * @param array $arr Array to add.
	 * @param mixed $value Value to add.
	 * @param mixed $key   Add with this key.
	 */
	public static function prepend( &$arr, $value, $key = null ) {
		if ( is_null( $key ) ) {
			array_unshift( $arr, $value );
			return;
		}

		$arr = [ $key => $value ] + $arr;
	}

	/**
	 * Update array add or delete value
	 *
	 * @param array $arr Array to modify. Is passed by reference, and no return is needed.
	 * @param array $value Value to add or delete.
	 */
	public static function add_delete_value( &$arr, $value ) {
		if ( ( $key = array_search( $value, $arr ) ) !== false ) { // @codingStandardsIgnoreLine
			unset( $arr[ $key ] );
			return;
		}

		$arr[] = $value;
	}

	/**
	 * Create an array from string.
	 *
	 * @param string $value     The string to split.
	 * @param string $separator Specifies where to break the string.
	 *
	 * @return array Returns an array after applying the function to each one.
	 */
	public static function from_string( $value, $separator = ',' ) {
		return array_values( array_filter( array_map( 'trim', explode( $separator, $value ) ) ) );
	}
}
