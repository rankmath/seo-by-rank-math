<?php
/**
 * The WooCommerce module's product redirection features.
 *
 * @since      1.0.32
 * @package    RankMath
 * @subpackage RankMath\WooCommerce
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\WooCommerce;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Sitepress;
use RankMath\Helpers\Param;
use RankMath\Helpers\Str;
use RankMath\Redirections\Redirection;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce class.
 */
class Product_Redirection {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( Helper::is_module_active( 'redirections' ) ) {
			$this->filter( 'rank_math/redirection/pre_search', 'pre_redirection', 10, 3 );
			return;
		}

		$this->action( 'wp', 'redirect' );
	}

	/**
	 * Pre-filter the redirection.
	 *
	 * @param string $check    Check.
	 * @param string $uri      Current URL.
	 * @param string $full_uri Full URL.
	 *
	 * @return string|array
	 */
	public function pre_redirection( $check, $uri, $full_uri ) {
		if ( $new_link = $this->get_redirection_url() ) { // phpcs:ignore
			return [
				'url_to'      => $new_link,
				'header_code' => 301,
			];
		}

		return $check;
	}

	/**
	 * Redirect product with base to the new link.
	 */
	public function redirect() {
		if ( $link = $this->get_redirection_url() ) { // phpcs:ignore
			Helper::redirect( $link, 301 );
			exit;
		}
	}

	/**
	 * Get Product URL.
	 *
	 * @return string Modified URL
	 */
	private function get_redirection_url() {
		if ( ! $this->can_redirect() ) {
			return false;
		}

		$url                 = $this->get_source_url();
		$is_product          = is_product();
		$permalink_structure = wc_get_permalink_structure();
		$base                = $is_product ? $permalink_structure['product_base'] : $permalink_structure['category_base'];

		$base     = explode( '/', ltrim( $base, '/' ) );
		$new_link = $url;

		// Early Bail if new_link length is less then the base.
		if ( count( explode( '/', $new_link ) ) <= count( $base ) ) {
			return false;
		}

		// On Single product page redirect base with shop and product.
		if ( $is_product ) {
			$base[]   = 'product';
			$base[]   = 'shop';
			$new_link = $this->remove_base_from_url( $new_link );
		}

		foreach ( array_unique( $base ) as $remove ) {
			if ( '%product_cat%' === $remove ) {
				continue;
			}

			$new_link = ! Str::starts_with( '/', $new_link ) ? '/' . $new_link : $new_link;
			$new_link = preg_replace( "#/{$remove}/#i", '', $new_link, 1 );
		}

		$new_link = implode( '/', array_map( 'rawurlencode', explode( '/', ltrim( $new_link, '/' ) ) ) ); // encode everything but slashes.

		return $new_link === $this->strip_ignored_parts( $url ) ? false : trailingslashit( home_url( strtolower( $new_link ) ) );
	}

	/**
	 * Remove all bases from the product link.
	 *
	 * @param  string $link Product link.
	 * @return string Modified URL
	 */
	private function remove_base_from_url( $link ) {
		if ( is_feed() ) {
			return $link;
		}

		if ( Sitepress::get()->is_active() ) {
			global $sitepress_settings;

			// Early bail if auto-translation is enabled in WPML.
			if (
				isset( $sitepress_settings['custom_posts_sync_option'] ) &&
				isset( $sitepress_settings['custom_posts_sync_option']['product'] ) &&
				0 !== (int) $sitepress_settings['custom_posts_sync_option']['product']
			) {
				return $link;
			}
		}

		$link = trim( str_replace( Helper::get_home_url(), '', get_permalink() ), '/' );

		return $link;
	}

	/**
	 * Get source URL.
	 *
	 * @return string
	 */
	private function get_source_url() {
		global $wp;
		$url = defined( 'TRP_PLUGIN_DIR' ) ? $wp->request : Param::server( 'REQUEST_URI' );
		$url = str_replace( home_url( '/' ), '', $url );
		$url = urldecode( $url );
		$url = trim( Redirection::strip_subdirectory( $url ), '/' );

		$url = explode( '?', $url );
		$url = trim( $url[0], '/' );

		if ( $this->is_amp_endpoint() ) {
			$url = \str_replace( '/' . \amp_get_slug(), '', $url );
		}

		return $url;
	}

	/**
	 * Is AMP url.
	 *
	 * @return bool
	 */
	private function is_amp_endpoint() {
		return \function_exists( 'is_amp_endpoint' ) && \function_exists( 'amp_is_canonical' ) && is_amp_endpoint() && ! amp_is_canonical();
	}

	/**
	 * Remove unneeded parts from the URI.
	 *
	 * @param string $uri Original URI.
	 *
	 * @return string
	 */
	private function strip_ignored_parts( $uri ) {
		$ignore_url_parts = [
			'#/comment-page-([0-9]{1,})$#',
		];

		$ignore_url_parts = $this->do_filter( 'woocommerce/product_redirection_ignore_url_parts', $ignore_url_parts );
		foreach ( $ignore_url_parts as $pattern ) {
			$uri = preg_replace( $pattern, '', $uri );
		}

		return implode( '/', array_map( 'rawurlencode', explode( '/', $uri ) ) );
	}

	/**
	 * Can redirect to the new product link.
	 *
	 * @return bool
	 */
	private function can_redirect() {
		global $wp_query;

		if (
			$this->do_filter( 'woocommerce/product_redirection', true ) &&
			! isset( $_GET['elementor-preview'] ) && // phpcs:ignore
			! isset( $wp_query->query_vars['schema-preview'] ) &&
			( ( Helper::get_settings( 'general.wc_remove_product_base' ) && is_product() ) ||
			( Helper::get_settings( 'general.wc_remove_category_base' ) && is_product_category() ) )
		) {
			return true;
		}

		return false;
	}
}
