<?php
/**
 * Sitemap date format class.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\Sitemap;

use DateTime;
use Exception;
use DateTimeZone;

defined( 'ABSPATH' ) || exit;

/**
 * Timezone.
 */
class Timezone {

	/**
	 * Format arbitrary UTC datetime string to desired form in site's time zone.
	 *
	 * @param string $datetime_string The input datetime string in UTC time zone.
	 * @param string $format          Date format to use.
	 *
	 * @return string
	 */
	public function format_date( $datetime_string, $format = DATE_W3C ) {
		if ( empty( $datetime_string ) || false === $this->is_valid_datetime( $datetime_string ) ) {
			return '';
		}

		$date_time = new DateTime( $datetime_string, new DateTimeZone( 'UTC' ) );

		return false === $date_time ? '' : $date_time->format( $format );
	}

	/**
	 * Check if a string is a valid datetime.
	 *
	 * @param  string $datetime String input to check as valid input for DateTime class.
	 * @return boolean
	 */
	private function is_valid_datetime( $datetime ) {
		if ( substr( $datetime, 0, 1 ) === '-' ) {
			return false;
		}

		try {
			return new DateTime( $datetime ) !== false;
		} catch ( Exception $exc ) {
			return false;
		}
	}
}
