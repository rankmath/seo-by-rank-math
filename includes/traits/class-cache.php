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
	 * @param string|array|object $object Object for that hash need to generate.
	 *
	 * @return string Hash value of provided object.
	 */
	public function generate_hash( $object ) {

		if ( empty( $object ) ) {
			return '';
		}

		if ( is_object( $object ) ) {
			$object = (array) $object;
		}

		if ( is_array( $object ) ) {
			ksort( $object );
			$object = wp_json_encode( $object );
		}

		$object = trim( $object );
		$hash   = hash( 'sha256', $object );

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
		if ( false === wp_using_ext_object_cache() ) {
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
		if ( false === wp_using_ext_object_cache() ) {
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
		if ( false === wp_using_ext_object_cache() ) {
			return false;
		}

		global $wp_object_cache;
		if ( ! isset( $wp_object_cache->cache[ $group ] ) ) {
			return;
		}

		$wp_object_cache->delete_multiple( array_keys( $wp_object_cache->cache[ $group ] ), $group );

		return true;
	}
}
