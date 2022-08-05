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
		'1.0.49'   => 'updates/update-1.0.49.php',
		'1.0.50'   => 'updates/update-1.0.50.php',
		'1.0.52'   => 'updates/update-1.0.52.php',
		'1.0.54'   => 'updates/update-1.0.54.php',
		'1.0.55'   => 'updates/update-1.0.55.php',
		'1.0.56'   => 'updates/update-1.0.56.php',
		'1.0.62'   => 'updates/update-1.0.62.php',
		'1.0.63'   => 'updates/update-1.0.63.php',
		'1.0.65'   => 'updates/update-1.0.65.php',
		'1.0.67'   => 'updates/update-1.0.67.php',
		'1.0.76'   => 'updates/update-1.0.76.php',
		'1.0.79'   => 'updates/update-1.0.79.php',
		'1.0.84'   => 'updates/update-1.0.84.php',
		'1.0.86'   => 'updates/update-1.0.86.php',
		'1.0.89'   => 'updates/update-1.0.89.php',
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
			update_option( 'rank_math_install_date', current_time( 'timestamp' ) ); // phpcs:ignore
		}

		// Clear rollback option if necessary.
		if ( rank_math()->version !== get_option( 'rank_math_rollback_version' ) ) {
			delete_option( 'rank_math_rollback_version' );
		}

		update_option( 'rank_math_version', rank_math()->version );
		update_option( 'rank_math_db_version', rank_math()->db_version );
	}
}
