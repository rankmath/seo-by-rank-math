<?php
/**
 * Functions and actions related to updates.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Traits\Hooker;


defined( 'ABSPATH' ) || exit;

/**
 * Updates class
 */
class Updates implements Runner {

	use Hooker;

	/**
	 * Updates that need to be run
	 *
	 * @var array
	 */
	private static $updates = [
		'0.9.8'    => 'updates/update-0.9.8.php',
		'0.10.0'   => 'updates/update-0.10.0.php',
		'1.0.14'   => 'updates/update-1.0.14.php',
		'1.0.15'   => 'updates/update-1.0.15.php',
		'1.0.18'   => 'updates/update-1.0.18.php',
		'1.0.24'   => 'updates/update-1.0.24.php',
		'1.0.28'   => 'updates/update-1.0.28.php',
		'1.0.30'   => 'updates/update-1.0.30.php',
		'1.0.36'   => 'updates/update-1.0.36.php',
		'1.0.36.1' => 'updates/update-1.0.36.1.php',
		'1.0.37'   => 'updates/update-1.0.37.php',
		'1.0.37.3' => 'updates/update-1.0.37.3.php',
		'1.0.39'   => 'updates/update-1.0.39.php',
		'1.0.40'   => 'updates/update-1.0.40.php',
		'1.0.42'   => 'updates/update-1.0.42.php',
		'1.0.43'   => 'updates/update-1.0.43.php',
		'1.0.46'   => 'updates/update-1.0.46.php',
		'1.0.47'   => 'updates/update-1.0.47.php',
		'1.0.48'   => 'updates/update-1.0.48.php',
		'1.0.49'   => 'updates/update-1.0.49.php',
		'1.0.50'   => 'updates/update-1.0.50.php',
		'1.0.52'   => 'updates/update-1.0.52.php',
		'1.0.54'   => 'updates/update-1.0.54.php',
	];

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'admin_init', 'do_updates' );
	}

	/**
	 * Check if any update is required.
	 */
	public function do_updates() {
		$installed_version = get_option( 'rank_math_version', '1.0.0' );

		// Maybe it's the first install.
		if ( ! $installed_version ) {
			return;
		}

		if ( version_compare( $installed_version, rank_math()->version, '<' ) ) {
			$this->perform_updates();
		}
	}

	/**
	 * Perform all updates.
	 */
	public function perform_updates() {
		$installed_version = get_option( 'rank_math_version', '1.0.0' );

		foreach ( self::$updates as $version => $path ) {
			if ( version_compare( $installed_version, $version, '<' ) ) {
				include $path;
				update_option( 'rank_math_version', $version );
			}
		}

		// Save install date.
		if ( false === boolval( get_option( 'rank_math_install_date' ) ) ) {
			update_option( 'rank_math_install_date', current_time( 'timestamp' ) );
		}

		// Clear rollback option if necessary.
		if ( rank_math()->version !== get_option( 'rank_math_rollback_version' ) ) {
			delete_option( 'rank_math_rollback_version' );
		}

		update_option( 'rank_math_version', rank_math()->version );
		update_option( 'rank_math_db_version', rank_math()->db_version );
	}
}
