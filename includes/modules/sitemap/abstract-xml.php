<?php
/**
 * The Sitemap xml and stylesheet contract
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
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
	 * Send file headers
	 *
	 * @param array $headers Array of headers.
	 */
	protected function send_headers( $headers = [] ) {
		$expires  = gmdate( 'D, d M Y H:i:s', ( time() + YEAR_IN_SECONDS ) );
		$defaults = array(
			'X-Robots-Tag'  => 'noindex',
			'Content-Type'  => 'text/xml; charset=' . $this->get_output_charset(),
			'Pragma'        => 'public',
			'Cache-Control' => 'maxage=' . YEAR_IN_SECONDS,
			'Expires'       => $expires . ' GMT',
			'Etag'          => md5( $expires . $this->type ),
		);

		$headers = wp_parse_args( $headers, $defaults );

		header( $this->get_protocol() . ' 200 OK', true, 200 );

		foreach ( $headers as $header => $value ) {
			header( $header . ': ' . $value );
		}
	}

	/**
	 * Get HTTP protocol
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
	 * Get charset for the output.
	 *
	 * @return string
	 */
	protected function get_output_charset() {
		return $this->output_charset;
	}
}
