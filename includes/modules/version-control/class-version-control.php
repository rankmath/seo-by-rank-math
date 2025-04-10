<?php
/**
 * The Version Control internal module.
 *
 * @package    RankMath
 * @subpackage RankMath\Version_Control
 */

namespace RankMath;

use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Version_Control class.
 */
class Version_Control {

	use Hooker;

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Module directory.
	 *
	 * @var string
	 */
	public $directory = '';

	/**
	 * Plugin info transient key.
	 *
	 * @var string
	 */
	const TRANSIENT = 'rank_math_wporg_plugin_info';

	/**
	 * WordPress.org plugins API URL.
	 *
	 * @var string
	 */
	const API_URL = 'https://api.wordpress.org/plugins/info/1.0/seo-by-rank-math.json';

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( Helper::is_heartbeat() ) {
			return;
		}

		if ( Helper::is_rest() ) {
			return;
		}

		$this->config(
			[
				'id'        => 'status',
				'directory' => __DIR__,
			]
		);

		$this->hooks();
	}

	/**
	 * Register version control hooks.
	 */
	public function hooks() {
		if ( Helper::get_settings( 'general.beta_optin' ) ) {
			$beta_optin = new Beta_Optin();
			$beta_optin->hooks();
		}

		if (
			Helper::is_advanced_mode() && (
				! Helper::is_plugin_active_for_network() ||
				current_user_can( 'setup_network' )
			)
		) {
			$this->filter( 'rank_math/tools/default_tab', 'change_default_tab' );
		}

		$this->filter( 'rank_math/admin/dashboard_view', 'network_admin_view', 10, 2 );
		$this->filter( 'rank_math/admin/dashboard_nav_links', 'network_admin_dashboard_tabs' );
	}

	/**
	 * Get localized JSON data based on the Page view.
	 */
	public static function get_json_data() {
		$versions = array_reverse( array_keys( Beta_Optin::get_available_versions( Helper::get_settings( 'general.beta_optin' ) ) ) );
		array_splice( $versions, 10 );
		return [
			'latestVersion'           => Beta_Optin::get_latest_version(),
			'isRollbackVersion'       => Rollback_Version::is_rollback_version(),
			'isPluginUpdateDisabled'  => Helper::is_plugin_update_disabled(),
			'availableVersions'       => $versions,
			'updateCoreUrl'           => self_admin_url( 'update-core.php' ),
			'rollbackVersion'         => get_option( 'rank_math_rollback_version', false ),
			'rollbackNonce'           => wp_create_nonce( 'rank-math-rollback' ),
			'betaOptin'               => boolval( Helper::get_settings( 'general.beta_optin' ) ),
			'autoUpdate'              => boolval( Helper::get_auto_update_setting() ),
			'updateNotificationEmail' => boolval( Helper::get_settings( 'general.update_notification_email' ) ),
		];
	}

	/**
	 * Replace Admin_Helper::get_view() output for the network admin tab.
	 *
	 * @param  string $file File path.
	 * @return string       New file path.
	 */
	public function network_admin_view( $file ) {
		if ( 'version_control' === Param::get( 'view' ) && is_network_admin() && Helper::is_plugin_active_for_network() ) {
			wp_enqueue_script( 'rank-math-status', rank_math()->plugin_url() . 'includes/modules/status/assets/js/status.js', [ 'lodash', 'rank-math-components' ], rank_math()->version, true );

			$data = array_merge(
				[
					'canUser' => [
						'manageOptions'  => current_user_can( 'manage_options' ),
						'setupNetwork'   => current_user_can( 'setup_network' ),
						'installPlugins' => current_user_can( 'install_plugins' ),
					],
				],
				$this->get_json_data(),
			);

			foreach ( $data as $key => $value ) {
				Helper::add_json( $key, $value );
			}
			return __DIR__ . '/display.php';
		}

		return $file;
	}

	/**
	 * Filter top nav links in the dashboard.
	 *
	 * @param  array $nav_links Nav links.
	 * @return array            New nav links.
	 */
	public function network_admin_dashboard_tabs( $nav_links ) {
		if ( ! is_network_admin() ) {
			return $nav_links;
		}

		if ( empty( $nav_links ) ) {
			$nav_links = [
				'help' => [
					'id'    => 'help',
					'url'   => '',
					'args'  => '',
					'cap'   => 'manage_options',
					'title' => esc_html__( 'Dashboard', 'rank-math' ),
				],
			];
		}

		$nav_links['version_control'] = [
			'id'    => 'version_control',
			'url'   => '',
			'args'  => 'view=version_control',
			'cap'   => 'manage_options',
			'title' => esc_html__( 'Version Control', 'rank-math' ),
		];
		return $nav_links;
	}

	/**
	 * Change default tab on the Status & Tools screen.
	 *
	 * @param string $default_value Default tab.
	 *
	 * @return string New default tab.
	 */
	public function change_default_tab( $default_value ) {
		if ( is_multisite() && ! current_user_can( 'setup_network' ) ) {
			return $default_value;
		}

		return 'version_control';
	}

	/**
	 * Get Rank Math plugin information.
	 *
	 * @return mixed Plugin information array or false on fail.
	 */
	public static function get_plugin_info() {
		$cache = get_transient( self::TRANSIENT );
		if ( $cache ) {
			return $cache;
		}

		$request = wp_remote_get( self::API_URL, [ 'timeout' => 20 ] );
		if ( ! is_wp_error( $request ) && is_array( $request ) ) {
			$response = json_decode( $request['body'], true );
			set_transient( self::TRANSIENT, $response, ( 12 * HOUR_IN_SECONDS ) );
			return $response;
		}

		return false;
	}

	/**
	 * Get plugin data to use in the `update_plugins` transient.
	 *
	 * @param  string $version New version.
	 * @param  string $package New version download URL.
	 * @return array           An array of plugin metadata.
	 */
	public static function get_plugin_data( $version, $package ) {
		return [
			'id'          => 'w.org/plugins/seo-by-rank-math',
			'slug'        => 'seo-by-rank-math',
			'plugin'      => 'seo-by-rank-math/rank-math.php',
			'new_version' => $version,
			'url'         => 'https://wordpress.org/plugins/seo-by-rank-math/',
			'package'     => $package,
			'icons'       =>
			[
				'2x'  => 'https://ps.w.org/seo-by-rank-math/assets/icon-256x256.png?rev=2034417',
				'1x'  => 'https://ps.w.org/seo-by-rank-math/assets/icon.svg?rev=2034417',
				'svg' => 'https://ps.w.org/seo-by-rank-math/assets/icon.svg?rev=2034417',
			],
			'banners'     =>
			[
				'2x' => 'https://ps.w.org/seo-by-rank-math/assets/banner-1544x500.png?rev=2034417',
				'1x' => 'https://ps.w.org/seo-by-rank-math/assets/banner-772x250.png?rev=2034417',
			],
			'banners_rtl' => [],
		];
	}
}
