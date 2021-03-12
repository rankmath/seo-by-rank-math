<?php
/**
 * The Conditional helpers.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Helpers
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Helpers;

use MyThemeShop\Helpers\Param;

/**
 * Conditional class.
 */
class Conditional {

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
			defined( 'REST_REQUEST' ) && REST_REQUEST || // (#1)
			isset( $_GET['rest_route'] ) && // (#2)
			0 === strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 )
		) {
			return true;
		}

		// (#3)
		if ( null === $wp_rewrite ) {
			$wp_rewrite = new \WP_Rewrite;
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
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
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