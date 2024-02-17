<?php
/**
 * The Version Rollback Class.
 *
 * @package    RankMath
 * @subpackage RankMath\Version_Control
 */

namespace RankMath;

use RankMath\Traits\Hooker;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Rollback_Version class.
 */
class Rollback_Version {

	use Hooker;

	/**
	 * Rollback version option key.
	 *
	 * @var string
	 */
	const ROLLBACK_VERSION_OPTION = 'rank_math_rollback_version';

	/**
	 * Check if currently installed version is a rollback version.
	 *
	 * @return boolean Whether it is rollback or not.
	 */
	public static function is_rollback_version() {
		$is_rollback = boolval( get_option( self::ROLLBACK_VERSION_OPTION, false ) );
		if ( ! $is_rollback ) {
			return false;
		}

		$current_version = rank_math()->version;
		$latest_version  = Beta_Optin::get_latest_version();
		if ( $current_version === $latest_version ) {
			delete_option( self::ROLLBACK_VERSION_OPTION );
			return false;
		}

		return true;
	}

	/**
	 * Check if we should roll back in this request or not.
	 */
	public static function should_rollback() {
		if ( ! Param::post( 'rm_rollback_version' ) ) {
			return false;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'rank-math-rollback' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Reinstall previous version.
	 *
	 * @return boolean Whether the installation was successful.
	 */
	public function rollback() {
		$title        = __( 'Rollback Plugin', 'rank-math' );
		$parent_file  = 'plugins.php';
		$submenu_file = 'plugins.php';
		$new_version  = Param::post( 'rm_rollback_version' );

		wp_enqueue_script( 'updates' );
		$plugin = 'seo-by-rank-math/rank-math.php';
		$nonce  = 'upgrade-plugin_' . $plugin;
		$url    = 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $plugin );
		if ( ! class_exists( '\Plugin_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		update_option( self::ROLLBACK_VERSION_OPTION, $new_version );
		// Downgrade version number if necessary.
		if ( version_compare( rank_math()->version, $new_version, '>' ) ) {
			update_option( 'rank_math_version', $new_version );
		}

		add_filter( 'pre_site_transient_update_plugins', [ $this, 'pre_transient_update_plugins' ], 20 );
		add_filter( 'gettext', [ $this, 'change_updater_strings' ], 20, 3 );
		$upgrader = new \Plugin_Upgrader( new \Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) ) );
		echo '<div class="rank-math-rollback-status">';
		$upgrader->upgrade( $plugin );
		echo '</div>';
		remove_filter( 'pre_site_transient_update_plugins', [ $this, 'pre_transient_update_plugins' ], 20 );
		remove_filter( 'gettext', [ $this, 'change_updater_strings' ], 20 );

		return true;
	}

	/**
	 * Inject old version in the `update_plugins` transient for downgrading.
	 *
	 * @param  boolean $false False. Pass truthy value to short-circuit the get_site_transient().
	 * @return object         New `update_plugins` data object.
	 */
	public function pre_transient_update_plugins( $false ) {
		$versions       = Beta_Optin::get_available_versions( true );
		$selected       = Param::post( 'rm_rollback_version' );
		$package        = $versions[ $selected ];
		$data           = new \stdClass();
		$data->response = [];
		$data->response['seo-by-rank-math/rank-math.php'] = new \stdClass();

		$plugin_data = Version_Control::get_plugin_data( $selected, $package );
		foreach ( $plugin_data as $prop_key => $prop_value ) {
			$data->response['seo-by-rank-math/rank-math.php']->{$prop_key} = $prop_value;
		}
		return $data;
	}

	/**
	 * Hooked to gettext filter to change strings in the Updater for the rollback process.
	 *
	 * @param  string $translation Translated text.
	 * @param  string $text        Original text.
	 * @param  string $domain      Text-domain.
	 *
	 * @return string New translated text.
	 */
	public function change_updater_strings( $translation, $text, $domain ) {
		if ( 'Plugin updated successfully.' === $text ) {
			return __( 'Plugin rollback successful.', 'rank-math' );
		}

		if ( 'Installing the latest version&#8230;' === $text ) {
			return __( 'Installing the rollback version&#8230;', 'rank-math' );
		}

		return $translation;
	}
}
