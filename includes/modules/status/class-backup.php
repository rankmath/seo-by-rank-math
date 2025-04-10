<?php
/**
 * The functionality related to the Backup.
 *
 * @since      1.0.240
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Status;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Backup class.
 */
class Backup {
	/**
	 * Get backups from the database.
	 *
	 * @param {bool} $return_object Whether to retrun the backup data or only the keys with formatted date.
	 */
	public static function get_backups( $return_object = false ) {
		$backups = get_option( 'rank_math_backups', [] );
		if ( empty( $backups ) ) {
			$backups = [];
		} elseif ( ! is_array( $backups ) ) {
			$backups = (array) $backups;
		}

		if ( empty( $backups ) || $return_object ) {
			return $backups;
		}

		$data = [];
		foreach ( array_keys( $backups ) as $backup ) {
			$data[ $backup ] = esc_html( date_i18n( 'M jS Y, H:i a', $backup ) );
		}

		return $data;
	}

	/**
	 * Create Backup.
	 */
	public static function create_backup() {
		$key = Helper::get_current_time();
		if ( is_null( $key ) ) {
			return [
				'type'    => 'error',
				'message' => esc_html__( 'Unable to create backup this time.', 'rank-math' ),
			];
		}

		$backups = self::get_backups( true );
		$backups = [ $key => Import_Export_Settings::get_export_data() ] + $backups;
		update_option( 'rank_math_backups', $backups, false );

		return [
			'type'    => 'success',
			'message' => esc_html__( 'Backup created successfully.', 'rank-math' ),
			'backups' => self::get_backups(),
		];
	}

	/**
	 * Create Backup.
	 *
	 * @param string $key Backup key to be restored.
	 */
	public static function restore_backup( $key ) {
		$backups = self::get_backups( true );
		if ( ! array_key_exists( $key, $backups ) ) {
			return [
				'type'    => 'error',
				'message' => esc_html__( 'Backup does not exist.', 'rank-math' ),
			];
		}

		Import_Export_Settings::do_import_data( $backups[ $key ], true );

		return [
			'type'    => 'success',
			'message' => esc_html__( 'Backup restored successfully.', 'rank-math' ),
		];
	}

	/**
	 * Delete Backup.
	 *
	 * @param string $key Backup key to be restored.
	 */
	public static function delete_backup( $key ) {
		$backups = self::get_backups();
		if ( ! isset( $backups[ $key ] ) ) {
			return [
				'type'    => 'error',
				'message' => esc_html__( 'No backup key found to delete.', 'rank-math' ),
				'backups' => self::get_backups(),
			];
		}

		unset( $backups[ $key ] );
		update_option( 'rank_math_backups', $backups, false );

		return [
			'type'    => 'success',
			'message' => esc_html__( 'Backup successfully deleted.', 'rank-math' ),
			'backups' => self::get_backups(),
		];
	}
}
