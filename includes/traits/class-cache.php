<?php
/**
 * The Cache Trait.
 *
 * @since      1.0.99
 * @package    RankMath
 * @subpackage RankMath\Traits
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Cache class.
 */
trait Cache {

	/**
	 * To generate hash of object.
	 *
	 * @param string|array|object $value Object for that hash need to generate.
	 *
	 * @return string Hash value of provided object.
	 */
	public function generate_hash( $value ) {

		if ( empty( $value ) ) {
			return '';
		}

		if ( is_object( $value ) ) {
			$value = (array) $value;
		}

		if ( is_array( $value ) ) {
			ksort( $value );
			$value = wp_json_encode( $value );
		}

		$value = apply_filters( 'rank_math/cache/generate_hash', trim( $value ) );
		$hash  = hash( 'sha256', $value );

		return $hash;
	}

	/**
	 * Sets a value in cache.
	 *
	 * The value is set whether or not this key already exists in Redis.
	 *
	 * @param string $key    The key under which to store the value.
	 * @param mixed  $data   The value to store.
	 * @param string $group  The group value appended to the $key.
	 * @param int    $expire The expiration time, defaults to 0.
	 *
	 * @return bool              Returns TRUE on success or FALSE on failure.
	 */
	public function set_cache( $key, $data, $group = '', $expire = 0 ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		return wp_cache_set( $key, $data, $group, $expire );
	}

	/**
	 * Retrieve object from cache.
	 *
	 * Gets an object from cache based on $key and $group.
	 *
	 * @param string $key   The key under which to store the value.
	 * @param string $group The group value appended to the $key.
	 *
	 * @return bool|mixed Cached object value.
	 */
	public function get_cache( $key, $group ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		return wp_cache_get( $key, $group );
	}

	/**
	 * Removes all cache items in a group, if the object cache implementation supports it.
	 *
	 * @param string $group Name of group to remove from cache.
	 *
	 * @return bool True if group was flushed, false otherwise.
	 */
	public function cache_flush_group( $group ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		global $wp_object_cache;
		if ( ! isset( $wp_object_cache->cache[ $group ] ) ) {
			return;
		}

		$wp_object_cache->delete_multiple( array_keys( $wp_object_cache->cache[ $group ] ), $group );

		return true;
	}

	/**
	 * Check if cache is enabled.
	 */
	public function is_enabled() {
		if ( wp_using_ext_object_cache() === false ) {
			return false;
		}

		return apply_filters( 'rank_math/cache/enabled', true );
	}
}
