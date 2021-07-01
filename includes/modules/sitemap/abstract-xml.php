<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * The Sitemap xml and stylesheet abstract class.
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

defined( 'ABSPATH' ) || exit;

/**
 * XML.
 */
abstract class XML {

	/**
	 * HTTP protocol to use in headers.
	 *
	 * @var string
	 */
	protected $http_protocol = null;

	/**
	 * Holds charset of output, might be converted.
	 *
	 * @var string
	 */
	protected $output_charset = 'UTF-8';

	/**
	 * Send file headers.
	 *
	 * @param array $headers Array of headers.
	 * @param bool  $is_xsl True if sending headers are for XSL.
	 */
	protected function send_headers( $headers = [], $is_xsl = false ) {
		$defaults = [
			'X-Robots-Tag'  => 'noindex',
			'Content-Type'  => 'text/xml; charset=' . $this->get_output_charset(),
			'Pragma'        => 'public',
			'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
			'Expires'       => 0,
		];

		$headers = wp_parse_args( $headers, $defaults );

		/**
		 * Filter the sitemap HTTP headers.
		 *
		 * @param array $headers HTTP headers.
		 * @param bool  $is_xsl Whether these headers are for XSL.
		 */
		$headers = $this->do_filter( 'sitemap/http_headers', $headers, $is_xsl );

		header( $this->get_protocol() . ' 200 OK', true, 200 );

		foreach ( $headers as $header => $value ) {
			header( $header . ': ' . $value );
		}
	}

	/**
	 * Get HTTP protocol.
	 *
	 * @return string
	 */
	protected function get_protocol() {
		if ( ! is_null( $this->http_protocol ) ) {
			return $this->http_protocol;
		}

		$this->http_protocol = ! empty( $_SERVER['SERVER_PROTOCOL'] ) ? sanitize_text_field( $_SERVER['SERVER_PROTOCOL'] ) : 'HTTP/1.1';
		return $this->http_protocol;
	}

	/**
	 * Get `charset` for the output.
	 *
	 * @return string
	 */
	protected function get_output_charset() {
		return $this->output_charset;
	}
}
