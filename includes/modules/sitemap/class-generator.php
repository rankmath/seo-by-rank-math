<?php
/**
 * The Sitemap Generator
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

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Generator class.
 */
class Generator extends XML {

	use Hooker;

	/**
	 * XSL stylesheet for styling a sitemap for web browsers.
	 *
	 * @var string
	 */
	protected $stylesheet = '';

	/**
	 * Holds the get_bloginfo( 'charset' ) value to reuse for performance.
	 *
	 * @var string
	 */
	protected $charset = 'UTF-8';

	/**
	 * If data encoding needs to be converted for output.
	 *
	 * @var boolean
	 */
	protected $needs_conversion = false;

	/**
	 * Timezone.
	 *
	 * @var Timezone
	 */
	public $timezone;

	/**
	 * Providers array.
	 *
	 * @var Provider
	 */
	public $providers = [];

	/**
	 * The maximum number of entries per sitemap page.
	 *
	 * @var int
	 */
	private $max_entries;

	/**
	 * Set up object properties.
	 */
	public function __construct() {

		$this->stylesheet     = preg_replace( '/(^http[s]?:)/', '', Router::get_base_url( 'main-sitemap.xsl' ) );
		$this->stylesheet     = '<?xml-stylesheet type="text/xsl" href="' . $this->stylesheet . '"?>';
		$this->charset        = get_bloginfo( 'charset' );
		$this->output_charset = $this->charset;
		$this->timezone       = new Timezone();

		if (
			'UTF-8' !== $this->charset
			&& function_exists( 'mb_list_encodings' )
			&& in_array( $this->charset, mb_list_encodings(), true )
		) {
			$this->output_charset = 'UTF-8';
		}

		$this->needs_conversion = $this->output_charset !== $this->charset;
		$this->instantiate();
	}

	/**
	 * Instantiate required objects.
	 */
	private function instantiate() {
		// Initialize sitemap providers classes.
		$this->providers = [
			new \RankMath\Sitemap\Providers\Post_Type(),
			new \RankMath\Sitemap\Providers\Taxonomy(),
		];

		// Author Provider.
		if ( true === Helper::is_author_archive_indexable() ) {
			$this->providers[] = new \RankMath\Sitemap\Providers\Author();
		}

		$external_providers = $this->do_filter( 'sitemap/providers', [] );
		foreach ( $external_providers as $provider ) {
			if ( is_object( $provider ) ) {
				$this->providers[] = $provider;
			}
		}
	}

	/**
	 * Produce final XML output with debug information.
	 *
	 * @param  string $type Sitemap type.
	 * @param  int    $page Page number to retrieve.
	 * @return string
	 */
	public function get_output( $type, $page ) {
		$output = '<?xml version="1.0" encoding="' . esc_attr( $this->get_output_charset() ) . '"?>';

		if ( $this->stylesheet ) {
			/**
			 * Filter the stylesheet URL for the XML sitemap.
			 *
			 * @param string $stylesheet Stylesheet URL.
			 */
			$output .= $this->do_filter( "sitemap/{$type}_stylesheet_url", $this->stylesheet ) . "\n";
		}

		$content = $this->build_sitemap( $type, $page );

		if ( '' !== $content ) {
			return $output . $content;
		}

		return '';
	}

	/**
	 * Attempts to build the requested sitemap.
	 *
	 * @param  string $type Sitemap type.
	 * @param  int    $page Page number to retrieve.
	 * @return string
	 */
	public function build_sitemap( $type, $page ) {
		$this->max_entries = absint( Helper::get_settings( 'sitemap.items_per_page', 100 ) );

		/**
		 * Filter the type of sitemap to build.
		 *
		 * @param string $type Sitemap type, determined by the request.
		 */
		$type = $this->do_filter( 'sitemap/build_type', $type );
		if ( '1' === $type ) {
			return $this->build_root_map();
		}

		foreach ( $this->providers as $provider ) {
			if ( ! $provider->handles_type( $type ) ) {
				continue;
			}

			$links = $provider->get_sitemap_links( $type, $this->max_entries, $page );
			return $this->get_sitemap( $links, $type, $page );
		}

		return $this->do_filter( "sitemap/{$type}/content", '' );
	}

	/**
	 * Build the root sitemap (example.com/sitemap_index.xml) which lists sub-sitemaps for other content types.
	 */
	public function build_root_map() {
		$links = [];
		foreach ( $this->providers as $provider ) {
			$links = array_merge( $links, $provider->get_index_links( $this->max_entries ) );
		}

		if ( empty( $links ) ) {
			return '';
		}

		return $this->get_index( $links );
	}

	/**
	 * Produce XML output for sitemap index.
	 *
	 * @param  array $links Set of sitemaps index links.
	 * @return string
	 */
	public function get_index( $links ) {

		$xml = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		foreach ( $links as $link ) {
			$xml .= $this->sitemap_index_url( $link );
		}

		/**
		 * Filter to append sitemaps to the index.
		 *
		 * @param string $index String to append to sitemaps index, defaults to empty.
		 */
		$xml .= $this->do_filter( 'sitemap/index', '' );
		$xml .= '</sitemapindex>';

		return $xml;
	}

	/**
	 * Produce XML output for urlset.
	 *
	 * @param  array  $links        Set of sitemap links.
	 * @param  string $type         Sitemap type.
	 * @param  int    $current_page Current sitemap page number.
	 * @return string
	 */
	public function get_sitemap( $links, $type, $current_page ) {

		$urlset = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
			. 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd '
			. 'http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" '
			. 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		/**
		 * Filters the `urlset` for a sitemap by type.
		 *
		 * @param string $urlset The output for the sitemap's `urlset`.
		 */
		$xml = $this->do_filter( "sitemap/{$type}_urlset", $urlset );

		foreach ( $links as $url ) {
			$method = $type . '_sitemap_url';
			$xml   .= has_filter( "rank_math/sitemap/{$method}" ) ? $this->do_filter( "sitemap/{$method}", $url, $this ) : $this->sitemap_url( $url );
		}

		/**
		 * Filter to add extra URLs to the XML sitemap by type.
		 *
		 * Only runs for the first page, not on all.
		 *
		 * @param string $content String content to add, defaults to empty.
		 */
		if ( 1 === $current_page ) {
			$xml .= $this->do_filter( "sitemap/{$type}_content", '' );
		}

		$xml .= '</urlset>';

		return $xml;
	}

	/**
	 * Build the `<sitemap>` tag for a given URL.
	 *
	 * @param  array $url Array of parts that make up this entry.
	 * @return string
	 */
	protected function sitemap_index_url( $url ) {

		$date = null;
		if ( ! empty( $url['lastmod'] ) ) {
			$date = $this->timezone->format_date( $url['lastmod'] );
		}

		$output  = $this->newline( '<sitemap>', 1 );
		$output .= $this->newline( '<loc>' . htmlspecialchars( $url['loc'] ) . '</loc>', 2 );
		$output .= empty( $date ) ? '' : $this->newline( '<lastmod>' . htmlspecialchars( $date ) . '</lastmod>', 2 );
		$output .= $this->newline( '</sitemap>', 1 );

		return $output;
	}

	/**
	 * Build the `<url>` tag for a given URL.
	 *
	 * Public access for backwards compatibility reasons.
	 *
	 * @param  array $url Array of parts that make up this entry.
	 * @return string
	 */
	public function sitemap_url( $url ) {

		$date = null;
		if ( ! empty( $url['mod'] ) ) {
			// Create a DateTime object date in the correct timezone.
			$date = $this->timezone->format_date( $url['mod'] );
		}

		$output  = $this->newline( '<url>', 1 );
		$output .= $this->newline( '<loc>' . $this->encode_url_rfc3986( htmlspecialchars( $url['loc'] ) ) . '</loc>', 2 );
		$output .= empty( $date ) ? '' : $this->newline( '<lastmod>' . htmlspecialchars( $date ) . '</lastmod>', 2 );
		if ( ! empty( $url['images'] ) ) {
			$output .= $this->sitemap_images( $url );
		}
		$output .= $this->newline( '</url>', 1 );

		/**
		 * Filters the output for the sitemap url tag.
		 *
		 * @param string $output The output for the sitemap url tag.
		 * @param array  $url    The sitemap url array on which the output is based.
		 */
		return $this->do_filter( 'sitemap/url', $output, $url );
	}

	/**
	 * Sitemap Images.
	 *
	 * @param  array $url Array of parts that make up this entry.
	 * @return string
	 */
	public function sitemap_images( $url ) {
		$output = '';
		foreach ( $url['images'] as $img ) {

			if ( empty( $img['src'] ) ) {
				continue;
			}

			$output .= $this->newline( '<image:image>', 2 );
			$output .= $this->newline( '<image:loc>' . esc_html( $this->encode_url_rfc3986( $img['src'] ) ) . '</image:loc>', 3 );

			if ( ! empty( $img['title'] ) ) {
				$output .= $this->add_cdata( $img['title'], 'image:title', 3 );
			}
			if ( ! empty( $img['alt'] ) ) {
				$output .= $this->add_cdata( $img['alt'], 'image:caption', 3 );
			}

			$output .= $this->newline( '</image:image>', 2 );
		}

		return $output;
	}

	/**
	 * Convret encoding if needed.
	 *
	 * @param string  $data   Data to be added.
	 * @param string  $tag    Tag to create CDATA for.
	 * @param integer $indent Tab indent count.
	 */
	public function add_cdata( $data, $tag, $indent = 0 ) {
		if ( $this->needs_conversion ) {
			$data = mb_convert_encoding( $data, $this->output_charset, $this->charset );
		}

		$data = _wp_specialchars( html_entity_decode( $data, ENT_QUOTES, $this->output_charset ) );

		return $this->newline( "<{$tag}><![CDATA[{$data}]]></{$tag}>", $indent );
	}

	/**
	 * Apply some best effort conversion to comply with RFC3986.
	 *
	 * @param  string $url URL to encode.
	 * @return string
	 */
	public function encode_url_rfc3986( $url ) {
		if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return $url;
		}

		$url = $this->encode_url_path( $url );
		$url = $this->encode_url_query( $url );

		return $url;
	}

	/**
	 * Apply some best effort conversion to comply with RFC3986.
	 *
	 * @param  string $url URL to encode.
	 * @return string
	 */
	private function encode_url_path( $url ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( empty( $path ) || '/' === $path ) {
			return $url;
		}

		$encoded_path = explode( '/', $path );

		// First decode the path, to prevent double encoding.
		$encoded_path = array_map( 'rawurldecode', $encoded_path );

		$encoded_path = array_map( 'rawurlencode', $encoded_path );
		$encoded_path = implode( '/', $encoded_path );
		$encoded_path = str_replace( '%7E', '~', $encoded_path ); // PHP <5.3.

		return str_replace( $path, $encoded_path, $url );
	}

	/**
	 * Apply some best effort conversion to comply with RFC3986.
	 *
	 * @param  string $url URL to encode.
	 * @return string
	 */
	private function encode_url_query( $url ) {
		$query = wp_parse_url( $url, PHP_URL_QUERY );
		if ( empty( $query ) ) {
			return $url;
		}

		parse_str( $query, $parsed_query );

		if ( defined( 'PHP_QUERY_RFC3986' ) ) { // PHP 5.4+.
			$parsed_query = http_build_query( $parsed_query, null, '&amp;', PHP_QUERY_RFC3986 );
		} else {
			$parsed_query = http_build_query( $parsed_query, null, '&amp;' );
			$parsed_query = str_replace( '+', '%20', $parsed_query );
			$parsed_query = str_replace( '%7E', '~', $parsed_query );
		}

		return str_replace( $query, $parsed_query, $url );
	}

	/**
	 * Write a newline with indent count.
	 *
	 * @param  string  $content Content to write.
	 * @param  integer $indent  Count of indent.
	 * @return string
	 */
	public function newline( $content, $indent = 0 ) {
		return str_repeat( "\t", $indent ) . $content . "\n";
	}
}
