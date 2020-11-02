<?php
/**
 * Handles sitemaps caching and invalidation.
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
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Cache class.
 */
class Cache {

	/**
	 * Cache mode.
	 *
	 * @var string
	 */
	private $mode = 'db';

	/**
	 * The $wp_filesystem object.
	 *
	 * @var object WP_Filesystem
	 */
	private $wp_filesystem;

	/**
	 * Prefix of the filename for sitemap caches.
	 *
	 * @var string
	 */
	const STORAGE_KEY_PREFIX = 'rank_math_';

	/**
	 * The Constructor
	 */
	public function __construct() {
		$this->wp_filesystem = WordPress::get_filesystem();
		$this->mode          = $this->is_writable() ? 'file' : 'db';
	}

	/**
	 * Is the file writable?
	 *
	 * @return bool
	 */
	public function is_writable() {
		$directory_separator = '/';
		$folder_path         = $this->get_cache_directory();
		$test_file           = $folder_path . $this->get_storage_key();

		// If folder doesn't exist?
		if ( ! file_exists( $folder_path ) ) {
			// Can we create the folder?
			// returns true if yes and false if not.
			$permissions = ( defined( 'FS_CHMOD_DIR' ) ) ? FS_CHMOD_DIR : 0755;
			return $this->wp_filesystem->mkdir( $folder_path, $permissions );
		}

		// Does the file exist?
		// File exists. Is it writable?
		if ( file_exists( $test_file ) && ! $this->wp_filesystem->is_writable( $test_file ) ) {
			// Nope, it's not writable.
			return false;
		}

		// Folder exists, but is it actually writable?
		return $this->wp_filesystem->is_writable( $folder_path );
	}

	/**
	 * Get the sitemap that is cached.
	 *
	 * @param  string $type Sitemap type.
	 * @param  int    $page Page number to retrieve.
	 * @return false|string false on no cache found otherwise sitemap file.
	 */
	public function get_sitemap( $type, $page ) {
		$filename = $this->get_storage_key( $type, $page );
		if ( false === $filename ) {
			return false;
		}

		if ( 'file' === $this->mode ) {
			return $this->wp_filesystem->get_contents( self::get_cache_directory() . $filename );
		}

		$filename = "sitemap_{$type}_$filename";
		$sitemap  = get_transient( $filename );
		return maybe_unserialize( $sitemap );
	}

	/**
	 * Store the sitemap page from cache.
	 *
	 * @param  string $type    Sitemap type.
	 * @param  int    $page    Page number to store.
	 * @param  string $sitemap Sitemap body to store.
	 * @return boolean
	 */
	public function store_sitemap( $type, $page, $sitemap ) {
		$filename = $this->get_storage_key( $type, $page );
		if ( false === $filename ) {
			return false;
		}

		if ( 'file' === $this->mode ) {
			$stored = $this->wp_filesystem->put_contents( self::get_cache_directory() . $filename, $sitemap, FS_CHMOD_FILE );
			if ( true === $stored ) {
				self::cached_files( $filename, $type );
				return $stored;
			}
		}

		$filename = "sitemap_{$type}_$filename";
		return set_transient( $filename, maybe_serialize( $sitemap ), DAY_IN_SECONDS * 100 );
	}

	/**
	 * Get filename for sitemap
	 *
	 * @param  null|string $type The type to get the key for. Null or '1' for index cache.
	 * @param  int         $page The page of cache to get the key for.
	 * @return boolean|string The key where the cache is stored on. False if the key could not be generated.
	 */
	public function get_storage_key( $type = null, $page = 1 ) {
		$type = is_null( $type ) ? '1' : $type;

		$filename = self::STORAGE_KEY_PREFIX . md5( "{$type}_{$page}_" . home_url() ) . '.xml';

		return $filename;
	}

	/**
	 * Get cache directory
	 *
	 * @return string
	 */
	public static function get_cache_directory() {
		$dir     = wp_upload_dir();
		$default = $dir['basedir'] . '/rank-math';

		/**
		 * Filter XML sitemap cache directory.
		 *
		 * @param string $unsigned Default cache directory
		 */
		$filtered = apply_filters( 'rank_math/sitemap/cache_directory', $default );

		if ( ! is_string( $filtered ) || '' === $filtered ) {
			$filtered = $default;
		}

		return trailingslashit( $filtered );
	}

	/**
	 * Read/Write cached files.
	 *
	 * @param  mixed  $value Pass null to get option,
	 *                       Pass false to delete option,
	 *                       Pass value to update option.
	 * @param  string $type  Sitemap type.
	 * @return mixed
	 */
	public static function cached_files( $value = null, $type = '' ) {
		if ( '' !== $type ) {
			$options           = Helper::option( 'sitemap_cache_files' );
			$options[ $value ] = $type;
			return Helper::option( 'sitemap_cache_files', $options );
		}

		return Helper::option( 'sitemap_cache_files', $value );
	}

	/**
	 * Invalidate sitemap cache.
	 *
	 * @param null|string $type The type to get the key for. Null for all caches.
	 */
	public static function invalidate_storage( $type = null ) {
		$directory     = self::get_cache_directory();
		$wp_filesystem = WordPress::get_filesystem();

		if ( is_null( $type ) ) {
			$wp_filesystem->delete( $directory, true );
			wp_mkdir_p( $directory );
			self::clear_transients();
			self::cached_files( false );
			Helper::clear_cache();
			return;
		}

		$data  = [];
		$files = self::cached_files();
		foreach ( $files as $file => $sitemap_type ) {
			if ( $type !== $sitemap_type ) {
				$data[ $file ] = $sitemap_type;
				continue;
			}

			$wp_filesystem->delete( $directory . $file );
		}

		self::clear_transients( $type );
		self::cached_files( $data );
		Helper::clear_cache();
	}

	/**
	 * Reset ALL transient caches.
	 *
	 * @param null|string $type The type to get the key for. Null for all caches.
	 */
	private static function clear_transients( $type = null ) {
		global $wpdb;
		if ( is_null( $type ) ) {
			return $wpdb->delete(
				$wpdb->options,
				[ 'option_name' => $wpdb->esc_like( '_transient_sitemap_' ) . '%' ],
				[ '%s' ]
			);
		}

		return $wpdb->delete(
			$wpdb->options,
			[ 'option_name' => $wpdb->esc_like( '_transient_sitemap_' . $type ) . '%' ],
			[ '%s' ]
		);
	}
}
