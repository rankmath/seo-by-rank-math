<?php
/**
 * Helper Functions.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Helpers\Api;
use RankMath\Helpers\Attachment;
use RankMath\Helpers\Conditional;
use RankMath\Helpers\Choices;
use RankMath\Helpers\Post_Type;
use RankMath\Helpers\Options;
use RankMath\Helpers\Taxonomy;
use RankMath\Helpers\WordPress;
use RankMath\Helpers\Schema;
use RankMath\Helpers\Analytics;
use RankMath\Helpers\DB;
use RankMath\Replace_Variables\Replacer;

defined( 'ABSPATH' ) || exit;

/**
 * Helper class.
 */
class Helper {

	use Api, Attachment, Conditional, Choices, Post_Type, Options, Taxonomy, WordPress, Schema, DB, Analytics;

	/**
	 * Replace `%variables%` with context-dependent value.
	 *
	 * @param string $content The string containing the %variables%.
	 * @param array  $args    Context object, can be post, taxonomy or term.
	 * @param array  $exclude Excluded variables won't be replaced.
	 *
	 * @return string
	 */
	public static function replace_vars( $content, $args = [], $exclude = [] ) {
		return ( new Replacer() )->replace( $content, $args, $exclude );
	}

	/**
	 * Replace `%variables%` with context-dependent value in SEO fields.
	 *
	 * @param string $content The string containing the %variables%.
	 * @param object $post    Context object, can be post, taxonomy or term.
	 *
	 * @return string
	 */
	public static function replace_seo_fields( $content, $post ) {
		if ( empty( $post ) || ! in_array( $content, [ '%seo_title%', '%seo_description%', '%url%' ], true ) ) {
			return self::replace_vars( $content, $post );
		}

		if ( '%seo_title%' === $content ) {
			$default = self::get_settings( "titles.pt_{$post->post_type}_title", '%title% %sep% %sitename%' );
			$title   = self::get_post_meta( 'title', $post->ID, $default );

			return self::replace_vars( $title, $post );
		}

		if ( '%seo_description%' === $content ) {
			$default = self::get_settings( "titles.pt_{$post->post_type}_description", '%excerpt%' );
			$desc    = self::get_post_meta( 'description', $post->ID, $default );

			return self::replace_vars( $desc, $post );
		}

		return self::get_post_meta( 'canonical', $post->ID, get_the_permalink( $post->ID ) );
	}

	/**
	 * Register extra %variables%. For developers.
	 *
	 * @codeCoverageIgnore
	 *
	 * @deprecated 1.0.34 Use rank_math_register_var_replacement()
	 * @see rank_math_register_var_replacement()
	 *
	 * @param  string $var       Variable name, for example %custom%. '%' signs are optional.
	 * @param  mixed  $callback  Replacement callback. Should return value, not output it.
	 * @param  array  $args      Array with additional title, description and example values for the variable.
	 *
	 * @return bool Replacement was registered successfully or not.
	 */
	public static function register_var_replacement( $var, $callback, $args = [] ) {
		_deprecated_function( 'RankMath\Helper::register_var_replacement()', '1.0.34', 'rank_math_register_var_replacement()' );
		$args['description'] = isset( $args['desc'] ) ? $args['desc'] : '';
		$args['variable']    = $var;
		return rank_math_register_var_replacement( $var, $args, $callback );
	}

	/**
	 * Get midnight time for the date variables.
	 *
	 * @param  int $time Timestamp of date.
	 * @return int
	 */
	public static function get_midnight( $time ) {
		$org_time = $time;
		if ( is_numeric( $time ) ) {
			$time = self::get_date( 'Y-m-d H:i:s', $time, false, true );
		}

		// Early bail if time format is invalid.
		if ( false === strtotime( $time ) ) {
			return $org_time;
		}

		$date = new \DateTime( $time );
		$date->setTime( 0, 0, 0 );

		return $date->getTimestamp();
	}

	/**
	 * Extract URL part.
	 *
	 * @param  string $url  The URL to parse.
	 * @param  string $part The URL part to retrieve.
	 *
	 * @return string The extracted URL part.
	 */
	public static function get_url_part( $url, $part ) {
		$url_parts = wp_parse_url( $url );

		return $url_parts[ $part ] ?? '';
	}

	/**
	 * Get current page URL.
	 *
	 * @param  bool $ignore_qs Ignore query string.
	 *
	 * @return string
	 */
	public static function get_current_page_url( $ignore_qs = false ) {
		$link = '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$link = ( is_ssl() ? 'https' : 'http' ) . $link;

		if ( $ignore_qs ) {
			$link = explode( '?', $link );
			$link = $link[0];
		}

		return $link;
	}

	/**
	 * Get module by ID.
	 *
	 * @param  string $id ID to get module.
	 *
	 * @return object Module class object.
	 */
	public static function get_module( $id ) {
		return rank_math()->manager->get_module( $id );
	}

	/**
	 * Modify module status.
	 *
	 * @param array $modules Modules to modify.
	 */
	public static function update_modules( $modules ) {
		$stored = get_option( 'rank_math_modules', [] );

		foreach ( $modules as $module => $action ) {
			if ( 'off' === $action ) {
				if ( in_array( $module, $stored, true ) ) {
					$stored = array_diff( $stored, [ $module ] );
				}
				continue;
			}

			$stored[] = $module;
			Installer::create_tables( [ $module ] );
		}

		update_option( 'rank_math_modules', array_unique( $stored ) );
	}

	/**
	 * Get list of currently active modules.
	 *
	 * @return array
	 */
	public static function get_active_modules() {
		$registered_modules = rank_math()->manager->modules;
		$stored             = array_values( get_option( 'rank_math_modules', [] ) );
		foreach ( $stored as $key => $value ) {
			if (
				! isset( $registered_modules[ $value ] )
				|| ! is_object( $registered_modules[ $value ] )
				|| ! method_exists( $registered_modules[ $value ], 'is_disabled' )
				|| $registered_modules[ $value ]->is_disabled()
			) {
				unset( $stored[ $key ] );
			}
		}

		return $stored;
	}

	/**
	 * Clear cache from:
	 *  - WordPress Total Cache
	 *  - W3 Total Cache
	 *  - WP Super Cache
	 *  - SG CachePress
	 *  - WPEngine
	 *  - Varnish
	 *
	 * @param string $context Context for cache to clear.
	 */
	public static function clear_cache( $context = '' ) {

		/**
		 * Filter: 'rank_math/pre_clear_cache' - Allow developers to extend/override cache clearing.
		 * Pass a truthy value to override the cache clearing.
		 */
		if ( apply_filters( 'rank_math/pre_clear_cache', false, $context ) ) {
			return;
		}

		// Clean WordPress cache.
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}

		// If W3 Total Cache is being used, clear the cache.
		if ( function_exists( 'w3tc_pgcache_flush' ) ) {
			w3tc_pgcache_flush();
		}

		// If WP Super Cache is being used, clear the cache.
		if ( function_exists( 'wp_cache_clean_cache' ) ) {
			global $file_prefix;
			wp_cache_clean_cache( $file_prefix );
		}

		// If SG CachePress is installed, reset its caches.
		if ( class_exists( 'SG_CachePress_Supercacher' ) && is_callable( [ 'SG_CachePress_Supercacher', 'purge_cache' ] ) ) {
			\SG_CachePress_Supercacher::purge_cache();
		}

		// Clear caches on WPEngine-hosted sites.
		if ( class_exists( 'WpeCommon' ) ) {
			\WpeCommon::purge_memcached();
			\WpeCommon::clear_maxcdn_cache();
			\WpeCommon::purge_varnish_cache();
		}

		// Clear Varnish caches.
		self::clear_varnish_cache();
	}

	/**
	 * Clear varnish cache for the dynamic files.
	 * Credit @davidbarratt: https://github.com/davidbarratt/varnish-http-purge
	 */
	private static function clear_varnish_cache() {
		// Parse the URL for proxy proxies.
		$parsed_url = wp_parse_url( home_url() );

		// Build a varniship.
		$varniship = get_option( 'vhp_varnish_ip' );
		if ( defined( 'VHP_VARNISH_IP' ) && false !== VHP_VARNISH_IP ) {
			$varniship = VHP_VARNISH_IP;
		}

		// If we made varniship, let it sail.
		$purgeme = ( isset( $varniship ) && null !== $varniship ) ? $varniship : $parsed_url['host'];
		wp_remote_request(
			'http://' . $purgeme,
			[
				'method'   => 'PURGE',
				'blocking' => false,
				'headers'  => [
					'host'           => $parsed_url['host'],
					'X-Purge-Method' => 'default',
				],
			]
		);
	}

	/**
	 * Check if current environment is a localhost.
	 *
	 * @return boolean
	 */
	public static function is_localhost() {
		$whitelist = [
			'127.0.0.1', // IPv4 address.
			'::1', // IPv6 address.
		];

		$ip = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );

		return in_array( $ip, $whitelist, true );
	}

	/**
	 * Get date using date_i18n() or date().
	 *
	 * @param string      $format Format to display the date.
	 * @param int|boolean $timestamp_with_offset A sum of Unix timestamp and timezone offset in seconds.
	 * @param boolean     $gmt Whether to use GMT timezone. Only applies if timestamp is not provided.
	 * @param boolean     $mode Whether to use date() or date_i18n().
	 * @return mixin
	 */
	public static function get_date( $format, $timestamp_with_offset = false, $gmt = false, $mode = false ) {
		if ( true === $mode ) {
			return date( $format, $timestamp_with_offset ); // phpcs:ignore
		}
		return date_i18n( $format, $timestamp_with_offset, $gmt );
	}

	/**
	 * Check for valid image url.
	 *
	 * @param string $image_url The image url.
	 * @return boolean
	 */
	public static function is_image_url( $image_url ) {
		return filter_var( $image_url, FILTER_VALIDATE_URL ) && preg_match( '/\.(jpg|jpeg|png|gif|webp)$/i', $image_url );
	}
}
