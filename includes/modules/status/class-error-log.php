<?php
/**
 * Locate, retrieve, and display the server's error log.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Status;

use RankMath\Helper;
use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Error_Log class.
 */
class Error_Log {

	/**
	 * Log path.
	 *
	 * @var string|bool
	 */
	private static $log_path = null;

	/**
	 * File content.
	 *
	 * @var array
	 */
	private static $contents = null;

	/**
	 * Get Error Log JSON data to be used on the System Status page.
	 *
	 * @return array Error Log data.
	 */
	public static function get_error_log_localized_data() {
		$error_log_load_error = self::error_log_load_error();
		// Early bail if Error log file could not be loaded.
		if ( $error_log_load_error ) {
			return [
				'errorLogError' => $error_log_load_error,
			];
		}

		return [
			'errorLog'     => self::get_error_log_rows( 100 ),
			'errorLogPath' => esc_html( basename( self::get_log_path() ) ),
			'errorLogSize' => is_array( self::$contents ) ? esc_html( Str::human_number( strlen( join( '', self::$contents ) ) ) ) : '0',
		];
	}

	/**
	 * Get last x rows from the error log.
	 *
	 * @param  integer $limit Max number of rows to return.
	 *
	 * @return string[]       Array of rows of text.
	 */
	private static function get_error_log_rows( $limit = -1 ) {
		if ( is_null( self::$contents ) ) {
			$wp_filesystem  = Helper::get_filesystem();
			self::$contents = $wp_filesystem->get_contents_array( self::get_log_path() );
		}

		if ( -1 === $limit ) {
			return join( '', self::$contents );
		}

		return is_array( self::$contents ) ? join( '', array_slice( self::$contents, -$limit ) ) : '';
	}

	/**
	 * Show error if the log cannot be loaded.
	 */
	private static function error_log_load_error() {
		$log_file      = self::get_log_path();
		$wp_filesystem = Helper::get_filesystem();

		if (
			empty( $log_file ) ||
			is_null( $wp_filesystem ) ||
			! Helper::is_filesystem_direct() ||
			! $wp_filesystem->exists( $log_file ) ||
			! $wp_filesystem->is_readable( $log_file )
		) {
			return esc_html__( 'The error log cannot be retrieved.', 'rank-math' );
		}

		// Error log must be smaller than 100 MB.
		$size = $wp_filesystem->size( $log_file );
		if ( $size > 100000000 ) {
			return esc_html__( 'The error log cannot be retrieved: Error log file is too large.', 'rank-math' );
		}

		return false;
	}

	/**
	 * Get error log file location.
	 *
	 * @return string Path to log file.
	 */
	private static function get_log_path() {
		if ( is_null( self::$log_path ) ) {
			self::$log_path = ini_get( 'error_log' );
		}

		return self::$log_path;
	}
}
