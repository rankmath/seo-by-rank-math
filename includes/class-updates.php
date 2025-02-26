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

use RankMath\Helper;
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
		'1.0.84'    => 'updates/update-1.0.84.php',
		'1.0.86'    => 'updates/update-1.0.86.php',
		'1.0.89'    => 'updates/update-1.0.89.php',
		'1.0.98'    => 'updates/update-1.0.98.php',
		'1.0.103.1' => 'updates/update-1.0.103.1.php',
		'1.0.104'   => 'updates/update-1.0.104.php',
		'1.0.107.3' => 'updates/update-1.0.107.3.php',
		'1.0.110'   => 'updates/update-1.0.110.php',
		'1.0.201'   => 'updates/update-1.0.201.php',
		'1.0.201.1' => 'updates/update-1.0.201.1.php',
		'1.0.202'   => 'updates/update-1.0.202.php',
		'1.0.211'   => 'updates/update-1.0.211.php',
		'1.0.232'   => 'updates/update-1.0.232.php',
		'1.0.237'   => 'updates/update-1.0.237.php',
		'1.0.238'   => 'updates/update-1.0.238.php',
		'1.0.239'   => 'updates/update-1.0.239.php',
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
			update_option( 'rank_math_install_date', Helper::get_current_time() );
		}

		// Clear rollback option if necessary.
		if ( rank_math()->version !== get_option( 'rank_math_rollback_version' ) ) {
			delete_option( 'rank_math_rollback_version' );
		}

		update_option( 'rank_math_version', rank_math()->version, false );
		update_option( 'rank_math_db_version', rank_math()->db_version, false );
	}
}
