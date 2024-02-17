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
	 * @param ArrayAccess|array $array Array to check key in.
	 * @param string|int        $key   Key to check for.
	 *
	 * @return bool
	 */
	public static function exists( $array, $key ) {
		if ( $array instanceof ArrayAccess ) {
			// @codeCoverageIgnoreStart
			return $array->offsetExists( $key );
			// @codeCoverageIgnoreEnd
		}

		return array_key_exists( $key, $array );
	}

	/**
	 * Check whether an array or [[\Traversable]] contains an element.
	 *
	 * This method does the same as the PHP function [in_array()](https://secure.php.net/manual/en/function.in-array.php)
	 * but additionally works for objects that implement the [[\Traversable]] interface.
	 *
	 * @throws \InvalidArgumentException If `$array` is neither traversable nor an array.
	 *
	 * @param array|\Traversable $array  The set of values to search.
	 * @param mixed              $search The value to look for.
	 * @param bool               $strict Whether to enable strict (`===`) comparison.
	 *
	 * @return bool `true` if `$search` was found in `$array`, `false` otherwise.
	 */
	public static function includes( $array, $search, $strict = true ) {
		if ( $array instanceof \Traversable ) {
			return self::includes_traversable( $array, $search, $strict );
		}

		$is_array = is_array( $array );
		if ( ! $is_array ) {
			throw new \InvalidArgumentException( 'Argument $array must be an array or implement Traversable' );
		}

		return $is_array ? in_array( $search, $array, $strict ) : false; // phpcs:ignore
	}

	/**
	 * Check Traversable contains an element.
	 *
	 * @param \Traversable $array  The set of values to search.
	 * @param mixed        $search The value to look for.
	 * @param bool         $strict Whether to enable strict (`===`) comparison.
	 *
	 * @return bool `true` if `$search` was found in `$array`, `false` otherwise.
	 */
	private static function includes_traversable( $array, $search, $strict = true ) {
		foreach ( $array as $value ) {
			if ( ( $strict && $search === $value ) || $search == $value ) { // phpcs:ignore
				return true;
			}
		}

		return false;
	}

	/**
	 * Insert a single array item inside another array at a set position
	 *
	 * @param array $array    Array to modify. Is passed by reference, and no return is needed.
	 * @param array $new      New array to insert.
	 * @param int   $position Position in the main array to insert the new array.
	 */
	public static function insert( &$array, $new, $position ) {
		$before = array_slice( $array, 0, $position - 1 );
		$after  = array_diff_key( $array, $before );
		$array  = array_merge( $before, $new, $after );
	}

	/**
	 * Push an item onto the beginning of an array.
	 *
	 * @param array $array Array to add.
	 * @param mixed $value Value to add.
	 * @param mixed $key   Add with this key.
	 */
	public static function prepend( &$array, $value, $key = null ) {
		if ( is_null( $key ) ) {
			array_unshift( $array, $value );
			return;
		}

		$array = [ $key => $value ] + $array;
	}

	/**
	 * Update array add or delete value
	 *
	 * @param array $array Array to modify. Is passed by reference, and no return is needed.
	 * @param array $value Value to add or delete.
	 */
	public static function add_delete_value( &$array, $value ) {
		if ( ( $key = array_search( $value, $array ) ) !== false ) { // @codingStandardsIgnoreLine
			unset( $array[ $key ] );
			return;
		}

		$array[] = $value;
	}

	/**
	 * Create an array from string.
	 *
	 * @param string $string    The string to split.
	 * @param string $separator Specifies where to break the string.
	 *
	 * @return array Returns an array after applying the function to each one.
	 */
	public static function from_string( $string, $separator = ',' ) {
		return array_values( array_filter( array_map( 'trim', explode( $separator, $string ) ) ) );
	}
}
