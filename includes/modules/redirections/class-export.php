<?php
/**
 * The Redirections Export.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Export class.
 *
 * @codeCoverageIgnore
 */
class Export {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'admin_init', 'export' );
	}

	/**
	 * Export redirections.
	 */
	public function export() {
		$server = Param::get( 'export' );
		if ( ! $server || ! in_array( $server, [ 'apache', 'nginx' ], true ) ) {
			return;
		}

		if ( ! Helper::has_cap( 'general' ) ) {
			return;
		}

		check_admin_referer( 'rank-math-export-redirections' );

		$filename = "rank-math-redirections-{$server}-" . date_i18n( 'Y-m-d-H-i-s' ) . ( 'apache' === $server ? '.htaccess' : '.conf' );

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$items = DB::get_redirections(
			[
				'limit'  => 1000,
				'status' => 'active',
			]
		);

		if ( 0 === $items['count'] ) {
			return;
		}

		$text[] = '# Created by Rank Math';
		$text[] = '# ' . date_i18n( 'r' );
		$text[] = '# Rank Math ' . trim( rank_math()->version ) . ' - https://rankmath.com/';
		$text[] = '';

		$text = array_merge( $text, $this->$server( $items['redirections'] ) );

		$text[] = '';
		$text[] = '# Rank Math Redirections END';

		echo implode( PHP_EOL, $text ) . PHP_EOL;
		exit;
	}

	/**
	 * Apache rewrite rules.
	 *
	 * @param array $items Array of DB items.
	 *
	 * @return string
	 */
	private function apache( $items ) {
		$output[] = '<IfModule mod_rewrite.c>';

		foreach ( $items as $item ) {
			$this->apache_item( $item, $output );
		}

		$output[] = '</IfModule>';

		return $output;
	}

	/**
	 * Format Apache single item.
	 *
	 * @param array $item   Single item.
	 * @param array $output Output array.
	 */
	private function apache_item( $item, &$output ) {
		$target  = '410' === $item['header_code'] ? '- [G]' : sprintf( '%s [R=%d,L]', $this->encode2nd( $item['url_to'] ), $item['header_code'] );
		$sources = maybe_unserialize( $item['sources'] );

		foreach ( $sources as $from ) {
			$url = $from['pattern'];
			if ( 'regex' !== $from['comparison'] && strpos( $url, '?' ) !== false || strpos( $url, '&' ) !== false ) {
				$url_parts = parse_url( $url );
				$url       = $url_parts['path'];
				$output[]  = sprintf( 'RewriteCond %%{QUERY_STRING} ^%s$', preg_quote( $url_parts['query'] ) );
			}

			// Get rewrite string.
			$output[] = sprintf( '%sRewriteRule %s %s', ( $this->is_valid_regex( $from ) ? '' : '# ' ), $this->get_comparison( $url, $from ), $target );
		}
	}

	/**
	 * Nginx rewrite rules.
	 *
	 * @param array $items Array of db items.
	 *
	 * @return string
	 */
	private function nginx( $items ) {
		$output[] = 'server {';

		foreach ( $items as $item ) {
			$this->nginx_item( $item, $output );
		}

		$output[] = '}';

		return $output;
	}

	/**
	 * Format nginx single item.
	 *
	 * @param array $item   Single item.
	 * @param array $output Output array.
	 */
	private function nginx_item( $item, &$output ) {
		$target      = $this->encode2nd( $item['url_to'] );
		$sources     = maybe_unserialize( $item['sources'] );
		$header_code = '301' === $item['header_code'] ? 'permanent' : 'redirect';

		foreach ( $sources as $from ) {
			if ( ! $this->is_valid_regex( $from ) ) {
				continue;
			}

			$output[] = $this->normalize_nginx_redirect( $this->get_comparison( $from['pattern'], $from ), $target, $header_code );
		}
	}

	/**
	 * Check if it's a valid pattern.
	 *
	 * So we don't break the site when it's inserted in the .htaccess.
	 *
	 * @param array $source Source array.
	 *
	 * @return string
	 */
	private function is_valid_regex( $source ) {
		if ( 'regex' == $source['comparison'] && @preg_match( $source['pattern'], null ) === false ) { // phpcs:ignore
			return false;
		}

		return true;
	}

	/**
	 * Normalize redirect data.
	 *
	 * @param string $source      Matching pattern.
	 * @param string $target      Target where to redirect.
	 * @param string $header_code Response header code.
	 *
	 * @return string
	 */
	private function normalize_nginx_redirect( $source, $target, $header_code ) {
		$source = preg_replace( "/[\r\n\t].*?$/s", '', $source );
		$source = preg_replace( '/[^\PC\s]/u', '', $source );
		$target = preg_replace( "/[\r\n\t].*?$/s", '', $target );
		$target = preg_replace( '/[^\PC\s]/u', '', $target );

		return "    rewrite {$source} {$target} {$header_code};";
	}

	/**
	 * Get comparison pattern.
	 *
	 * @param string $url  URL for comparison.
	 * @param array  $from Comparison type and URL.
	 *
	 * @return string
	 */
	private function get_comparison( $url, $from ) {
		$comparison = $from['comparison'];
		if ( 'regex' === $comparison ) {
			return $this->encode_regex( $from['pattern'] );
		}

		$hash = [
			'exact'    => '^{url}/?$',
			'contains' => '^(.*){url}(.*)$',
			'start'    => '^{url}',
			'end'      => '{url}/?$',
		];

		$url = preg_quote( $url );
		return isset( $hash[ $comparison ] ) ? str_replace( '{url}', $url, $hash[ $comparison ] ) : $url;
	}

	/**
	 * Encode URL.
	 *
	 * @param string $url URL to encode.
	 *
	 * @return string
	 */
	private function encode2nd( $url ) {
		$url = urlencode( $url );
		$url = str_replace( '%2F', '/', $url );
		$url = str_replace( '%3F', '?', $url );
		$url = str_replace( '%3A', ':', $url );
		$url = str_replace( '%3D', '=', $url );
		$url = str_replace( '%26', '&', $url );
		$url = str_replace( '%25', '%', $url );
		$url = str_replace( '+', '%20', $url );
		$url = str_replace( '%24', '$', $url );
		return $url;
	}

	/**
	 * Encode regex.
	 *
	 * @param string $url URL to encode.
	 *
	 * @return string
	 */
	private function encode_regex( $url ) {
		$url = preg_replace( '/[^a-zA-Z0-9\s](.*)[^a-zA-Z0-9\s][imsxeADSUXJu]*/', '$1', $url ); // Strip delimiters.
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url ); // Remove newlines.
		$url = preg_replace( '/[^\PC\s]/u', '', $url ); // Remove any invalid characters.
		$url = str_replace( ' ', '%20', $url ); // Make sure spaces are quoted.
		$url = str_replace( '%24', '$', $url );
		$url = ltrim( $url, '/' ); // No leading slash.
		$url = preg_replace( '@^\^/@', '^', $url ); // If pattern has a ^ at the start then ensure we don't have a slash immediately.

		return $url;
	}
}
