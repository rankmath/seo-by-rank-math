<?php
/**
 * The URL helpers.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Helpers
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Helpers;

use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;

/**
 * Url class.
 */
class Url {

	/**
	 * Simple check for validating a URL, it must start with http:// or https://.
	 * and pass FILTER_VALIDATE_URL validation.
	 *
	 * @param string $url to check.
	 *
	 * @return bool
	 */
	public static function is_url( $url ) {
		if ( ! is_string( $url ) ) {
			return false;
		}

		// Must start with http:// or https://.
		if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) && 0 !== strpos( $url, '//' ) ) {
			return false;
		}

		// Check for scheme first, if it's missing then add it.
		if ( 0 === strpos( $url, '//' ) ) {
			$url = 'http:' . $url;
		}

		// Must pass validation.
		return false !== filter_var( trailingslashit( $url ), FILTER_VALIDATE_URL ) ? true : false;
	}

	/**
	 * Check whether a url is relative.
	 *
	 * @param string $url URL string to check.
	 *
	 * @return bool
	 */
	public static function is_relative( $url ) {
		return ( 0 !== strpos( $url, 'http' ) && 0 !== strpos( $url, '//' ) );
	}

	/**
	 * Checks whether a url is external.
	 *
	 * @param string $url    URL string to check. This should be a absolute URL.
	 * @param string $domain If wants to use some other domain not home_url().
	 *
	 * @return bool
	 */
	public static function is_external( $url, $domain = false ) {
		if ( empty( $url ) || '#' === $url[0] || '/' === $url[0] ) { // Link to current page or relative link.
			return false;
		}

		$domain = self::get_domain( $domain ? $domain : home_url() );
		if ( Str::contains( $domain, $url ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get current url.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return string
	 */
	public static function get_current_url() {
		return self::get_scheme() . '://' . self::get_host() . self::get_port() . Param::server( 'REQUEST_URI' );
	}

	/**
	 * Get url scheme.
	 *
	 * @return string
	 */
	public static function get_scheme() {
		return is_ssl() ? 'https' : 'http';
	}

	/**
	 * Some setups like HTTP_HOST, some like SERVER_NAME, it's complicated.
	 *
	 * @link http://stackoverflow.com/questions/2297403/http-host-vs-server-name
	 *
	 * @codeCoverageIgnore
	 *
	 * @return string the HTTP_HOST or SERVER_NAME
	 */
	public static function get_host() {
		$host = Param::server( 'HTTP_HOST' );
		if ( false !== $host ) {
			return $host;
		}

		$name = Param::server( 'SERVER_NAME' );
		if ( false !== $name ) {
			return $name;
		}

		return '';
	}

	/**
	 * Get current request port.
	 *
	 * @return string
	 */
	public static function get_port() {
		$port     = Param::server( 'SERVER_PORT' );
		$has_port = $port && ! in_array( $port, [ '80', '443' ], true );
		return $has_port ? ':' . $port : '';
	}

	/**
	 * Get parent domain
	 *
	 * @param string $url Url to parse.
	 *
	 * @return string
	 */
	public static function get_domain( $url ) {
		$pieces = wp_parse_url( $url );
		$domain = isset( $pieces['host'] ) ? $pieces['host'] : '';

		if ( Str::contains( 'localhost', $domain ) ) {
			return 'localhost';
		}

		if ( preg_match( '/(?P<domain>[a-zÀ-ž0-9][a-zÀ-ž0-9\-]{1,63}\.[a-zÀ-ž\.]{2,15})$/ui', $domain, $regs ) ) {
			return $regs['domain'];
		}

		return false;
	}
}
