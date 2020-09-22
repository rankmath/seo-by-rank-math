<?php
/**
 * The Link Classifier.
 *
 * Determines of a link is an outbound or internal one.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Classifier class.
 */
class Classifier {

	const TYPE_EXTERNAL = 'external';
	const TYPE_INTERNAL = 'internal';

	/**
	 * Base host.
	 *
	 * @var string
	 */
	protected $base_host = '';

	/**
	 * Base path.
	 *
	 * @var string
	 */
	protected $base_path = '';

	/**
	 * Constructor setting the base url
	 *
	 * @param string $base_url The base url to set.
	 */
	public function __construct( $base_url ) {

		$this->base_host = Helper::get_url_part( $base_url, 'host' );

		$base_path = Helper::get_url_part( $base_url, 'path' );
		if ( $base_path ) {
			$this->base_path = trailingslashit( $base_path );
		}
	}

	/**
	 * Determines if the given link is an outbound or an internal link.
	 *
	 * @param  string $link The link to classify.
	 * @return string Returns outbound or internal.
	 */
	public function classify( $link ) {
		$url_parts = wp_parse_url( $link );

		// Because parse_url may return false.
		if ( ! is_array( $url_parts ) ) {
			$url_parts = [];
		}

		// Short-circuit if filter returns non-null.
		$filtered = apply_filters( 'rank_math/links/is_external', null, $url_parts );
		if ( null !== $filtered ) {
			return $filtered ? self::TYPE_EXTERNAL : self::TYPE_INTERNAL;
		}

		if ( $this->contains_protocol( $url_parts ) && $this->is_external_link( $url_parts ) ) {
			return self::TYPE_EXTERNAL;
		}

		return self::TYPE_INTERNAL;
	}

	/**
	 * Returns true when the link starts with https:// or http://
	 *
	 * @param  array $url_parts The URL parts to use.
	 * @return bool True if the URL starts with a protocol.
	 */
	protected function contains_protocol( array $url_parts ) {
		return isset( $url_parts['scheme'] ) && null !== $url_parts['scheme'];
	}

	/**
	 * Checks if the link contains the `home_url`. Returns true if this isn't the case.
	 *
	 * @param  array $url_parts The URL parts to use.
	 * @return bool True when the link doesn't contain the home url.
	 */
	protected function is_external_link( array $url_parts ) {
		if ( $this->has_valid_scheme( $url_parts ) || $this->has_different_host( $url_parts ) ) {
			return true;
		}

		// There is no base path.
		if ( empty( $this->base_path ) ) {
			return false;
		}

		// When there is a path.
		if ( isset( $url_parts['path'] ) ) {
			return ( strpos( $url_parts['path'], $this->base_path ) === false );
		}

		return true;
	}

	/**
	 * Checks if the link contains valid scheme
	 *
	 * @param  array $url_parts The URL parts to use.
	 * @return bool
	 */
	private function has_valid_scheme( array $url_parts ) {
		return isset( $url_parts['scheme'] ) && ! in_array( $url_parts['scheme'], [ 'http', 'https' ], true );
	}

	/**
	 * Checks if the base host is equal to the host
	 *
	 * @param  array $url_parts The URL parts to use.
	 * @return bool
	 */
	private function has_different_host( array $url_parts ) {
		return isset( $url_parts['host'] ) && $url_parts['host'] !== $this->base_host;
	}
}
