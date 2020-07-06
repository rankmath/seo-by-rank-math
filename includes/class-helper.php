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
use RankMath\Replace_Variables\Replacer;

defined( 'ABSPATH' ) || exit;

/**
 * Helper class.
 */
class Helper {

	use Api, Attachment, Conditional, Choices, Post_Type, Options, Taxonomy, WordPress;

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
		$replace = new Replacer();
		return $replace->replace( $content, $args, $exclude );
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
		if ( is_numeric( $time ) ) {
			$time = date_i18n( 'Y-m-d H:i:s', $time );
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
	 * @return string The extracted URL part.
	 */
	public static function get_url_part( $url, $part ) {
		$url_parts = wp_parse_url( $url );

		if ( isset( $url_parts[ $part ] ) ) {
			return $url_parts[ $part ];
		}

		return '';
	}

	/**
	 * Get current page URL.
	 *
	 * @param  bool $ignore_qs Ignore query string.
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
	 * Get Search Console auth url.
	 *
	 * @return string
	 */
	public static function get_console_auth_url() {
		return \RankMath\Search_Console\Client::get()->get_auth_url();
	}

	/**
	 * Get or update Search Console data.
	 *
	 * @param  bool|array $data Data to save.
	 * @return bool|array
	 */
	public static function search_console_data( $data = null ) {
		$key          = 'rank_math_search_console_data';
		$encrypt_keys = [
			'access_token',
			'refresh_token',
			'profiles',
		];

		// Clear data.
		if ( false === $data ) {
			delete_option( $key );
			return false;
		}

		$saved = get_option( $key, [] );
		foreach ( $encrypt_keys as $enc_key ) {
			if ( isset( $saved[ $enc_key ] ) ) {
				$saved[ $enc_key ] = Data_Encryption::deep_decrypt( $saved[ $enc_key ] );
			}
		}

		// Getter.
		if ( is_null( $data ) ) {
			return wp_parse_args(
				$saved,
				[
					'authorized' => false,
					'profiles'   => [],
				]
			);
		}

		// Setter.
		foreach ( $encrypt_keys as $enc_key ) {
			if ( isset( $saved[ $enc_key ] ) ) {
				$saved[ $enc_key ] = Data_Encryption::deep_encrypt( $saved[ $enc_key ] );
			}
			if ( isset( $data[ $enc_key ] ) ) {
				$data[ $enc_key ] = Data_Encryption::deep_encrypt( $data[ $enc_key ] );
			}
		}

		$data = wp_parse_args( $data, $saved );
		update_option( $key, $data );

		return $data;
	}

	/**
	 * Get search console module object.
	 *
	 * @return object
	 */
	public static function search_console() {
		return self::get_module( 'search-console' );
	}

	/**
	 * Get module by ID.
	 *
	 * @param  string $id ID to get module.
	 * @return object Module class object.
	 */
	public static function get_module( $id ) {
		return rank_math()->manager->get_module( $id );
	}

	/**
	 * Modify module status.
	 *
	 * @param string $modules Modules to modify.
	 */
	public static function update_modules( $modules ) {
		$stored = get_option( 'rank_math_modules' );

		foreach ( $modules as $module => $action ) {
			if ( 'off' === $action ) {
				if ( in_array( $module, $stored, true ) ) {
					$stored = array_diff( $stored, [ $module ] );
				}
				continue;
			}

			$stored[] = $module;
		}

		update_option( 'rank_math_modules', array_unique( $stored ) );
	}

	/**
	 * Clear cache from:
	 *  - WordPress Total Cache
	 *  - W3 Total Cache
	 *  - WP Super Cache
	 *  - SG CachePress
	 *  - WPEngine
	 *  - Varnish
	 */
	public static function clear_cache() {
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
				'method'  => 'PURGE',
				'headers' => [
					'host'           => $parsed_url['host'],
					'X-Purge-Method' => 'default',
				],
			]
		);
	}
}
