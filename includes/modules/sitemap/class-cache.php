<?php
/**
 * Handle sitemap caching and invalidation.
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
use RankMath\Admin\Database\Database;

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
	 * The constructor.
	 */
	public function __construct() {
		$this->wp_filesystem = Helper::get_filesystem();
		$this->mode          = $this->is_writable() ? 'file' : 'db';

		/**
		 * Change sitemap caching mode (can be "file" or "db").
		 */
		$this->mode = apply_filters( 'rank_math/sitemap/cache_mode', $this->mode );
	}

	/**
	 * Is the file writable?
	 *
	 * @return bool
	 */
	public function is_writable() {
		if ( is_null( $this->wp_filesystem ) || ! Helper::is_filesystem_direct() ) {
			return false;
		}

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
	 * @param  bool   $html Is HTML sitemap.
	 * @return false|string false on no cache found otherwise sitemap file.
	 */
	public function get_sitemap( $type, $page, $html = false ) {
		$filename = $this->get_storage_key( $type, $page, $html );
		if ( false === $filename || is_null( $this->wp_filesystem ) ) {
			return false;
		}

		$path = self::get_cache_directory() . $filename;
		if ( 'file' === $this->mode
			&& is_a( $this->wp_filesystem, 'WP_Filesystem_Direct' )
			&& $this->wp_filesystem->exists( $path ) ) {
			return $this->wp_filesystem->get_contents( $path );
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
	 * @param  bool   $html    Is HTML sitemap.
	 * @return boolean
	 */
	public function store_sitemap( $type, $page, $sitemap, $html = false ) {
		$filename = $this->get_storage_key( $type, $page, $html );
		if ( false === $filename || is_null( $this->wp_filesystem ) ) {
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
	 * Get filename for sitemap.
	 *
	 * @param  null|string $type The type to get the key for. Null or '1' for index cache.
	 * @param  int         $page The page of cache to get the key for.
	 * @param  boolean     $html Whether to add html extension.
	 * @return boolean|string The key where the cache is stored on. False if the key could not be generated.
	 */
	private function get_storage_key( $type = null, $page = 1, $html = false ) {
		$type = is_null( $type ) ? '1' : $type;

		$filename = self::STORAGE_KEY_PREFIX . md5( "{$type}_{$page}_" . home_url() ) . '.' . ( $html ? 'html' : 'xml' );

		return $filename;
	}

	/**
	 * Get cache directory.
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
		/**
		 * Filter: 'rank_math/sitemap/invalidate_storage' - Allow developers to disable sitemap cache invalidation.
		 */
		if ( ! apply_filters( 'rank_math/sitemap/invalidate_storage', true, $type ) ) {
			return;
		}

		$wp_filesystem = Helper::get_filesystem();
		if ( is_null( $wp_filesystem ) ) {
			return;
		}

		$directory = self::get_cache_directory();

		if ( is_null( $type ) ) {
			$wp_filesystem->delete( $directory, true );
			wp_mkdir_p( $directory );
			self::clear_transients();
			self::cached_files( false );
			Helper::clear_cache( 'sitemap' );
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
		Helper::clear_cache( 'sitemap/' . $type );

		/**
		 * Action: 'rank_math/sitemap/invalidated_storage' - Runs after sitemap cache invalidation.
		 */
		do_action( 'rank_math/sitemap/invalidated_storage', $type );
	}

	/**
	 * Reset ALL transient caches.
	 *
	 * @param null|string $type The type to get the key for. Null for all caches.
	 */
	private static function clear_transients( $type = null ) {

		if ( is_null( $type ) ) {
			return Database::table( 'options' )
				->whereLike( 'option_name', '_transient_sitemap_' )
				->delete();
		}

		return Database::table( 'options' )
			->whereLike( 'option_name', '_transient_sitemap_' . $type )
			->delete();
	}
}
