<?php
/**
 * The Conditional helpers.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Conditional class.
 */
trait Conditional {

	/**
	 * Check if whitelabel filter is active.
	 *
	 * @return boolean
	 */
	public static function is_whitelabel() {
		/**
		 * Enable whitelabel.
		 *
		 * @param bool $whitelabel Enable whitelabel.
		 */
		return apply_filters( 'rank_math/whitelabel', false );
	}

	/**
	 * Check if module is active.
	 *
	 * @param  string  $id               Module ID.
	 * @param  boolean $check_registered Whether to check if module is among registered modules or not.
	 * @return boolean
	 */
	public static function is_module_active( $id, $check_registered = true ) {
		$active_modules = get_option( 'rank_math_modules', [] );
		if ( ! is_array( $active_modules ) || ( $check_registered && ! self::is_plugin_ready() ) ) {
			return false;
		}

		return in_array( $id, $active_modules, true ) && ( ! $check_registered || array_key_exists( $id, rank_math()->manager->modules ) );
	}

	/**
	 * Check if Rank Math manager is ready.
	 *
	 * @return boolean
	 */
	public static function is_plugin_ready() {
		return ( isset( rank_math()->manager ) && ! is_null( rank_math()->manager ) );
	}

	/**
	 * Checks if the plugin is configured.
	 *
	 * @param bool $value If this param is set, the option will be updated.
	 * @return bool Return the option value if param is not set.
	 */
	public static function is_configured( $value = null ) {
		$key = 'rank_math_is_configured';
		if ( is_null( $value ) ) {
			$value = get_option( $key );
			return ! empty( $value );
		}
		Helper::schedule_flush_rewrite();
		update_option( $key, $value, false );

		return $value;
	}

	/**
	 * Check if the site is connected to the Rank Math API.
	 *
	 * @return bool
	 */
	public static function is_site_connected() {
		$registered = Admin_Helper::get_registration_data();

		return false !== $registered && ! empty( $registered['connected'] ) && ! empty( $registered['api_key'] );
	}

	/**
	 * Check that the plugin is licensed properly.
	 *
	 * @return bool
	 */
	public static function is_invalid_registration() {
		if ( defined( 'RANK_MATH_REGISTRATION_SKIP' ) && RANK_MATH_REGISTRATION_SKIP ) {
			return false;
		}

		$is_skipped = Helper::is_plugin_active_for_network() ? get_blog_option( get_main_site_id(), 'rank_math_registration_skip' ) : get_option( 'rank_math_registration_skip' );
		if ( true === boolval( $is_skipped ) ) {
			return false;
		}

		return ! self::is_site_connected();
	}

	/**
	 * Check if author archives are indexable.
	 *
	 * @return bool
	 */
	public static function is_author_archive_indexable() {
		if ( true === Helper::get_settings( 'titles.disable_author_archives' ) ) {
			return false;
		}

		if ( Helper::get_settings( 'titles.author_custom_robots' ) && in_array( 'noindex', (array) Helper::get_settings( 'titles.author_robots' ), true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the AMP module is active.
	 *
	 * @since 1.0.24
	 *
	 * @return bool
	 */
	public static function is_amp_active() {
		if ( ! self::is_module_active( 'amp' ) ) {
			return false;
		}

		if ( function_exists( 'ampforwp_get_setting' ) && 'rank_math' === ampforwp_get_setting( 'ampforwp-seo-selection' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if editing the file is allowed.
	 *
	 * @since 1.0.32
	 *
	 * @return bool
	 */
	public static function is_edit_allowed() {
		/**
		 * Allow editing the robots.txt & htaccess data.
		 *
		 * @param bool $can_edit Can edit the robots & htacess data.
		 */
		return apply_filters(
			'rank_math/can_edit_file',
			( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) &&
			( ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS )
		);
	}

	/**
	 * Check whether to show SEO score.
	 *
	 * @since 1.0.32
	 *
	 * @return boolean
	 */
	public static function is_score_enabled() {
		/**
		 * Enable SEO Score.
		 *
		 * @param bool $score_enabled Enable SEO Score.
		 */
		return apply_filters( 'rank_math/show_score', true );
	}

	/**
	 * Is on elementor editor.
	 *
	 * @since 1.0.37
	 *
	 * @return boolean
	 */
	public static function is_elementor_editor() {
		return 'elementor' === Param::get( 'action' );
	}

	/**
	 * Is UX Builder (used in Flatsome theme).
	 *
	 * @since 1.0.60
	 *
	 * @return boolean
	 */
	public static function is_ux_builder() {
		return 'uxbuilder' === Param::get( 'app' ) && ! empty( Param::get( 'type' ) );
	}

	/**
	 * Is on Divi frontend editor.
	 *
	 * @since 1.0.63
	 *
	 * @return boolean
	 */
	public static function is_divi_frontend_editor() {
		return function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled();
	}

	/**
	 * Get current editor, or false if we're not editing.
	 *
	 * @since 1.0.67
	 *
	 * @return mixed
	 */
	public static function get_current_editor() {
		if ( self::is_elementor_editor() ) {
			return 'elementor';
		}

		if ( self::is_divi_frontend_editor() ) {
			return 'divi';
		}

		if ( self::is_block_editor() && \rank_math_is_gutenberg() ) {
			return 'gutenberg';
		}

		if ( self::is_ux_builder() ) {
			return 'uxbuilder';
		}

		if ( Admin_Helper::is_post_edit() ) {
			return 'classic';
		}

		return false;
	}

	/**
	 * Is Advanced Mode.
	 *
	 * @since 1.0.43
	 *
	 * @return boolean
	 */
	public static function is_advanced_mode() {
		return 'advanced' === apply_filters( 'rank_math/setup_mode', Helper::get_settings( 'general.setup_mode', 'advanced' ) );
	}

	/**
	 * Is React Mode.
	 *
	 * @since 1.0.250
	 *
	 * @return boolean
	 */
	public static function is_react_enabled() {
		$is_react_enabled = get_option( 'rank_math_react_settings_ui', 'on' );
		return apply_filters( 'rank_math/is_react_enabled', $is_react_enabled === 'on' );
	}

	/**
	 * Is Breadcrumbs Enabled.
	 *
	 * @since 1.0.64
	 *
	 * @return boolean
	 */
	public static function is_breadcrumbs_enabled() {
		return \current_theme_supports( 'rank-math-breadcrumbs' ) || Helper::get_settings( 'general.breadcrumbs' );
	}

	/**
	 * Is Wizard page.
	 *
	 * @since 1.0.64
	 *
	 * @return boolean
	 */
	public static function is_wizard() {
		return ( filter_input( INPUT_GET, 'page' ) === 'rank-math-wizard' || filter_input( INPUT_POST, 'action' ) === 'rank_math_save_wizard' );
	}

	/**
	 * Is filesystem method direct.
	 *
	 * @since 1.0.71.1
	 *
	 * @return boolean
	 */
	public static function is_filesystem_direct() {
		if ( ! function_exists( 'get_filesystem_method' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php'; // @phpstan-ignore-line
		}

		return 'direct' === get_filesystem_method();
	}

	/**
	 * Is AJAX request
	 *
	 * @return bool Returns true when the page is loaded via ajax.
	 */
	public static function is_ajax() {
		return function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * Is CRON request
	 *
	 * @return bool Returns true when the page is loaded via cron.
	 */
	public static function is_cron() {
		return function_exists( 'wp_doing_cron' ) ? wp_doing_cron() : defined( 'DOING_CRON' ) && DOING_CRON;
	}

	/**
	 * Checks if WP-Cron is enabled and functional.
	 *
	 * @return bool True if WP-Cron is usable; false otherwise.
	 */
	public static function is_cron_enabled() {
		// Check if WP-Cron is disabled in the wp-config.php file.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			return false;
		}

		// Early bail if usable cron transient is set to true.
		if ( get_transient( 'rank_math_wp_cron_usable' ) ) {
			return true;
		}

		// Attempt a loopback request to wp-cron.php to check if it is blocked by the server.
		$response = wp_remote_post(
			site_url( 'wp-cron.php' ),
			[
				'timeout'   => 5,
				'blocking'  => true,
				'sslverify' => apply_filters( 'https_local_ssl_verify', true ),
			]
		);

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}

		set_transient( 'rank_math_wp_cron_usable', 1, HOUR_IN_SECONDS );

		return true;
	}

	/**
	 * Is auto-saving
	 *
	 * @return bool Returns true when the page is loaded for auto-saving.
	 */
	public static function is_autosave() {
		return defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	}

	/**
	 * Is REST request
	 *
	 * @link https://wordpress.stackexchange.com/questions/221202/does-something-like-is-rest-exist/221289
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings
	 * Case #3: It can happen that WP_Rewrite is not yet initialized,
	 *          so do this (wp-settings.php)
	 * Case #4: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @return bool
	 */
	public static function is_rest() {
		global $wp_rewrite;

		$prefix = rest_get_url_prefix();
		if (
			( defined( 'REST_REQUEST' ) && REST_REQUEST ) || // (#1)
			// phpcs:ignore= WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification -- Nonce verification is not needed here as this is only used to verify the imported file.
			( isset( $_GET['rest_route'] ) && 0 === strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 ) ) // (#2)
		) {
			return true;
		}

		// (#3)
		if ( null === $wp_rewrite ) {
			$wp_rewrite = new \WP_Rewrite();  //phpcs:ignore
		}

		// (#4)
		$rest_url    = wp_parse_url( trailingslashit( rest_url() ) );
		$current_url = wp_parse_url( add_query_arg( [] ) );

		if ( ! isset( $current_url['path'] ) || ! isset( $rest_url['path'] ) ) {
			return false;
		}

		return 0 === strpos( $current_url['path'], $rest_url['path'], 0 );
	}

	/**
	 * Check if the request is heartbeat.
	 *
	 * @return bool
	 */
	public static function is_heartbeat() {
		return 'heartbeat' === Param::post( 'action' );
	}

	/**
	 * Check if the request is from frontend.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return bool
	 */
	public function is_frontend() {
		return ! is_admin();
	}

	/**
	 * Is WooCommerce Installed
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active() {
		// @codeCoverageIgnoreStart
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php'; // @phpstan-ignore-line
		}
		// @codeCoverageIgnoreEnd
		return is_plugin_active( 'woocommerce/woocommerce.php' ) && function_exists( 'is_woocommerce' );
	}

	/**
	 * Is EDD Installed
	 *
	 * @return bool
	 */
	public static function is_edd_active() {
		return class_exists( 'Easy_Digital_Downloads' );
	}
}
