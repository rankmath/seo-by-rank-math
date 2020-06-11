<?php
/**
 * Date format class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap;

use DateTime;
use Exception;
use DateTimeZone;

defined( 'ABSPATH' ) || exit;

/**
 * Timezone
 */
class Timezone {

	/**
	 * Holds the timezone string value to reuse for performance.
	 *
	 * @var string
	 */
	private $timezone_string = '';

	/**
	 * Format arbitrary UTC datetime string to desired form in site's time zone.
	 *
	 * @param string $datetime_string The input datetime string in UTC time zone.
	 * @param string $format          Date format to use.
	 *
	 * @return string
	 */
	public function format_date( $datetime_string, $format = 'c' ) {
		$date_time = $this->get_datetime_with_timezone( $datetime_string );

		return is_null( $date_time ) ? '' : $date_time->format( $format );
	}

	/**
	 * Get the datetime object, in site's time zone, if the datetime string was valid.
	 *
	 * @param string $datetime_string The datetime string in UTC time zone, that needs to be converted to a DateTime object.
	 *
	 * @return DateTime|null in site's time zone
	 */
	public function get_datetime_with_timezone( $datetime_string ) {
		static $utc_timezone, $local_timezone;

		if ( ! isset( $utc_timezone ) ) {
			$utc_timezone   = new DateTimeZone( 'UTC' );
			$local_timezone = new DateTimeZone( $this->get_timezone_string() );
		}

		if ( ! empty( $datetime_string ) && $this->is_valid_datetime( $datetime_string ) ) {
			$datetime = new DateTime( $datetime_string, $utc_timezone );
			$datetime->setTimezone( $local_timezone );

			return $datetime;
		}

		return null;
	}

	/**
	 * Returns the correct timezone string.
	 *
	 * @return string
	 */
	private function get_timezone_string() {
		if ( '' === $this->timezone_string ) {
			$this->timezone_string = $this->determine_timezone_string();
		}

		return $this->timezone_string;
	}

	/**
	 * Returns the timezone string for a site, even if it's set to a UTC offset.
	 *
	 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
	 *
	 * @return string valid PHP timezone string
	 */
	private function determine_timezone_string() {
		// If site timezone string exists, return it.
		$timezone = get_option( 'timezone_string' );
		if ( ! empty( $timezone ) ) {
			return $timezone;
		}

		// Get UTC offset, if it isn't set then return UTC.
		$utc_offset = (int) get_option( 'gmt_offset', 0 );
		if ( 0 === $utc_offset ) {
			return 'UTC';
		}

		// Adjust UTC offset from hours to seconds.
		$utc_offset *= HOUR_IN_SECONDS;

		// Attempt to guess the timezone string from the UTC offset.
		$timezone = timezone_name_from_abbr( '', $utc_offset );

		if ( false !== $timezone ) {
			return $timezone;
		}

		$timezone = $this->determine_timezone_manually( $utc_offset );

		return false !== $timezone ? $timezone : 'UTC';
	}

	/**
	 * Determine timezone manually
	 *
	 * @param int $offset UTC Offset.
	 *
	 * @return string|bool
	 */
	private function determine_timezone_manually( $offset ) {
		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['offset'] === $offset ) {
					return $city['timezone_id'];
				}
			}
		}

		return false;
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
