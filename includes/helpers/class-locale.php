<?php
/**
 * The Locale helpers.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Locale class.
 */
class Locale {

	/**
	 * Get site language.
	 *
	 * @return string
	 */
	public static function get_site_language() {
		return self::get_language( get_locale() );
	}

	/**
	 * Get the language part of a given locale, defaults to english when the $locale is empty.
	 *
	 * @param string $locale The locale to get the language of.
	 *
	 * @return string The language part of the locale.
	 */
	public static function get_language( $locale = null ) {
		$language = 'en';

		if ( empty( $locale ) || ! is_string( $locale ) ) {
			return $language;
		}

		$locale_parts = explode( '_', $locale );

		if ( ! empty( $locale_parts[0] ) ) {
			$language = $locale_parts[0];
		}

		return $language;
	}
}
