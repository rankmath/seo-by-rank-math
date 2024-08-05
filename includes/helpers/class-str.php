<?php
/**
 * The String helpers.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath\Helpers;

/**
 * Str class.
 */
class Str {

	/**
	 * Validates whether the passed variable is a empty string.
	 *
	 * @param mixed $variable The variable to validate.
	 *
	 * @return bool Whether or not the passed value is a non-empty string.
	 */
	public static function is_empty( $variable ) {
		return empty( $variable ) || ! is_string( $variable );
	}

	/**
	 * Validates whether the passed variable is a non-empty string.
	 *
	 * @param mixed $variable The variable to validate.
	 *
	 * @return bool Whether or not the passed value is a non-empty string.
	 */
	public static function is_non_empty( $variable ) {
		return is_string( $variable ) && '' !== $variable;
	}

	/**
	 * Check if the string contains the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function contains( $needle, $haystack ) {
		return self::is_non_empty( $needle ) ? strpos( $haystack, $needle ) !== false : false;
	}

	/**
	 * Check if the string begins with the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function starts_with( $needle, $haystack ) {
		return '' === $needle || substr( $haystack, 0, strlen( $needle ) ) === (string) $needle;
	}

	/**
	 * Check if the string end with the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function ends_with( $needle, $haystack ) {
		return '' === $needle || substr( $haystack, -strlen( $needle ) ) === (string) $needle;
	}

	/**
	 * Check the string for desired comparison.
	 *
	 * @param string $needle     The sub-string to search for.
	 * @param string $haystack   The string to search.
	 * @param string $comparison The type of comparison.
	 *
	 * @return bool
	 */
	public static function comparison( $needle, $haystack, $comparison = '' ) {

		$hash = [
			'regex'    => 'preg_match',
			'end'      => [ __CLASS__, 'ends_with' ],
			'start'    => [ __CLASS__, 'starts_with' ],
			'contains' => [ __CLASS__, 'contains' ],
		];

		if ( $comparison && isset( $hash[ $comparison ] ) ) {
			return call_user_func( $hash[ $comparison ], $needle, $haystack );
		}

		// Exact.
		return $needle === $haystack;
	}

	/**
	 * Convert string to array with defined seprator.
	 *
	 * @param string $str String to convert.
	 * @param string $sep Seprator.
	 *
	 * @return bool|array
	 */
	public static function to_arr( $str, $sep = ',' ) {
		$parts = explode( $sep, trim( $str ) );

		return empty( $parts ) ? false : $parts;
	}

	/**
	 * Convert string to array, weed out empty elements and whitespaces.
	 *
	 * @param string $str         User-defined list.
	 * @param string $sep_pattern Separator pattern for regex.
	 *
	 * @return array
	 */
	public static function to_arr_no_empty( $str, $sep_pattern = '\r\n|[\r\n]' ) {
		$array = empty( $str ) ? [] : preg_split( '/' . $sep_pattern . '/', $str, -1, PREG_SPLIT_NO_EMPTY );
		$array = array_filter( array_map( 'trim', $array ) );

		return $array;
	}

	/**
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
	 *
	 * @param string $size The size.
	 *
	 * @return int
	 */
	public static function let_to_num( $size ) {
		$char = substr( $size, -1 );
		$ret  = substr( $size, 0, -1 );

		// @codingStandardsIgnoreStart
		switch ( strtoupper( $char ) ) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
		}
		// @codingStandardsIgnoreEnd

		return $ret;
	}

	/**
	 * Convert a number to K, M, B, etc.
	 *
	 * @param int|double $number    Number which to convert to pretty string.
	 * @param int        $precision Decimal places in the human-readable format.
	 *
	 * @return string
	 */
	public static function human_number( $number, $precision = 1 ) {

		if ( ! is_numeric( $number ) ) {
			return 0;
		}

		$negative = '';
		if ( abs( $number ) != $number ) {
			$negative = '-';
			$number   = abs( $number );
		}

		if ( $number < 1000 ) {
			return $negative ? -1 * $number : $number;
		}

		$unit  = intval( log( $number, 1000 ) );
		$units = [ '', 'K', 'M', 'B', 'T', 'Q' ];

		if ( array_key_exists( $unit, $units ) ) {
			return sprintf( '%s%s%s', $negative, rtrim( number_format( $number / pow( 1000, $unit ), $precision ), '.0' ), $units[ $unit ] );
		}

		return $number;
	}

	/**
	 * Truncate text for given length.
	 *
	 * @param {string} $str    Text to truncate.
	 * @param {number} $length Length to truncate for.
	 * @param {string} $append Append to the end if string is truncated.
	 *
	 * @return {string} Truncated text.
	 */
	public static function truncate( $str, $length = 110, $append = '' ) {
		$str     = wp_strip_all_tags( $str, true );
		$strlen  = mb_strlen( $str );
		$excerpt = mb_substr( $str, 0, $length );

		// Remove part of an entity at the end.
		$excerpt = preg_replace( '/&[^;\s]{0,6}$/', '', $excerpt );
		if ( $str !== $excerpt ) {
			$strrpos = function_exists( 'mb_strrpos' ) ? 'mb_strrpos' : 'strrpos';
			$excerpt = mb_substr( $str, 0, $strrpos( trim( $excerpt ), ' ' ) );
		}

		if ( $strlen > $length ) {
			$excerpt .= $append;
		}

		return $excerpt;
	}

	/**
	 * Multibyte ucwords.
	 *
	 * @param string $string String to convert.
	 */
	public static function mb_ucwords( $string ) {
		if ( ! function_exists( 'mb_convert_case' ) || ! function_exists( 'mb_detect_encoding' ) || mb_detect_encoding( $string ) !== 'UTF-8' ) {
			return ucwords( $string );
		}

		$words   = preg_split( '/([\s]+)/u', $string, -1, PREG_SPLIT_DELIM_CAPTURE );
		$ucwords = '';
		foreach ( $words as $word ) {
			if ( is_numeric( $word ) ) {
				$ucwords .= $word;
				continue;
			}

			if ( isset( $word[0] ) ) {
				$ucwords .= preg_match( '/[\p{L}]/u', $word[0] ) ? mb_strtoupper( $word[0], 'UTF-8' ) . mb_substr( $word, 1, mb_strlen( $word ), 'UTF-8' ) : $word;
			}
		}

		return $ucwords;
	}
}
