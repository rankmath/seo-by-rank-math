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

		$directory = dirname( __FILE__ );
		$this->config(
			[
				'id'        => 'status',
				'directory' => $directory,
			]
		);

		$this->hooks();

		$this->maybe_save_beta_optin();
		$this->maybe_save_auto_update();
	}

	/**
	 * Change beta_optin setting.
	 *
	 * @return bool Change successful.
	 */
	public function maybe_save_beta_optin() {
		if ( ! Param::post( 'beta_optin' ) || ! Param::post( '_wpnonce' ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'rank-math-beta-optin' ) ) {
			return false;
		}

		// Sanitize input.
		$new_value = Param::post( 'beta_optin' ) === 'on' ? 'on' : 'off';

		$settings               = get_option( 'rank-math-options-general', [] );
		$settings['beta_optin'] = $new_value;
		rank_math()->settings->set( 'general', 'beta_optin', 'on' === $new_value ? true : false );
		update_option( 'rank-math-options-general', $settings );

		return true;
	}

	/**
	 * Change enable_auto_update setting.
	 *
	 * @return bool Change successful.
	 */
	public function maybe_save_auto_update() {
		if ( ! ( Param::post( 'enable_auto_update' ) || Param::post( 'enable_update_notification_email' ) ) && ! Param::post( '_wpnonce' ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'rank-math-auto-update' ) ) {
			return false;
		}

		if ( Param::post( 'enable_auto_update' ) ) {
			$new_value = Param::post( 'enable_auto_update' ) === 'on' ? 'on' : 'off';
			Helper::toggle_auto_update_setting( $new_value );
		}

		if ( Param::post( 'enable_update_notification_email' ) ) {
			$enable_notifications = Param::post( 'enable_update_notification_email' ) === 'on' ? 'on' : 'off';
			$settings             = get_option( 'rank-math-options-general', [] );

			$settings['update_notification_email'] = $enable_notifications;
			rank_math()->settings->set( 'general', 'update_notification_email', 'on' === $enable_notifications ? true : false );
			update_option( 'rank-math-options-general', $settings );
		}

		return true;
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
			$this->filter( 'rank_math/tools/pages', 'add_status_page' );
			$this->filter( 'rank_math/tools/default_tab', 'change_default_tab' );
		}

		$this->filter( 'rank_math/admin/dashboard_view', 'network_admin_view', 10, 2 );
		$this->filter( 'rank_math/admin/dashboard_nav_links', 'network_admin_dashboard_tabs' );
		$this->action( 'admin_enqueue_scripts', 'enqueue', 20 );

		if ( $this->should_add_json() ) {
			/* translators: Placeholder is version number. */
			Helper::add_json( 'rollbackConfirm', esc_html__( 'Are you sure you want to install version %s?', 'rank-math' ) );
		}
	}

	/**
	 * Check if JSON for confirmation l10n needs to be added.
	 *
	 * @return bool Whether the data needs to be added.
	 */
	private function should_add_json() {
		if ( ! is_admin() ) {
			return false;
		}

		if ( is_network_admin() && Helper::is_plugin_active_for_network() ) {
			return Param::get( 'page' ) === 'rank-math';
		}

		return Param::get( 'page' ) === 'rank-math-status';
	}

	/**
	 * Check if assets should be enqueued on current admin page.
	 *
	 * @param  string $hook Page hook name.
	 * @return bool         Whether we should proceed with the enqueue functions.
	 */
	private function should_enqueue( $hook ) {
		if ( is_network_admin() && Helper::is_plugin_active_for_network() ) {
			return 'toplevel_page_rank-math' === $hook;
		}

		return 'rank-math_page_rank-math-status' === $hook;
	}

	/**
	 * Replace Admin_Helper::get_view() output for the network admin tab.
	 *
	 * @param  string $file File path.
	 * @param  string $view Requested view.
	 * @return string       New file path.
	 */
	public function network_admin_view( $file, $view ) {
		if ( 'version_control' === Param::get( 'view' ) && is_network_admin() && Helper::is_plugin_active_for_network() ) {
			return dirname( __FILE__ ) . '/display.php';
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
	 * Add subpage to Status & Tools screen.
	 *
	 * @param array $pages Pages.
	 * @return array       New pages.
	 */
	public function add_status_page( $pages ) {
		$pages['version_control'] = [
			'url'   => 'status',
			'args'  => 'view=version_control',
			'cap'   => 'install_plugins',
			'title' => __( 'Version Control', 'rank-math' ),
			'class' => '\\RankMath\\Version_Control',
		];

		return $pages;
	}

	/**
	 * Change default tab on the Status & Tools screen.
	 *
	 * @param string $default Default tab.
	 * @return string         New default tab.
	 */
	public function change_default_tab( $default ) {
		if ( is_multisite() && ! current_user_can( 'setup_network' ) ) {
			return $default;
		}
		return 'version_control';
	}

	/**
	 * Enqueue CSS & JS.
	 *
	 * @param string $hook Page hook name.
	 * @return void
	 */
	public function enqueue( $hook ) {
		if ( ! $this->should_enqueue( $hook ) ) {
			return;
		}
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );
		wp_enqueue_style( 'rank-math-cmb2' );
		wp_enqueue_style( 'rank-math-version-control', $uri . '/assets/css/version-control.css', [], rank_math()->version );
		wp_enqueue_script( 'rank-math-version-control', $uri . '/assets/js/version-control.js', [ 'jquery' ], rank_math()->version, true );
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

	/**
	 * Display forms.
	 */
	public function display() {
		$directory = dirname( __FILE__ );
		include_once $directory . '/display.php';
	}

}
