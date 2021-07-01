<?php
/**
 * The WordPress helpers.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Helpers
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Helpers;

use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;

/**
 * WordPress class.
 */
class WordPress {

	/**
	 * Get roles.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $output How to return roles.
	 *
	 * @return array
	 */
	public static function get_roles( $output = 'names' ) {
		$wp_roles = wp_roles();

		if ( 'names' !== $output ) {
			return $wp_roles->roles;
		}

		return $wp_roles->get_names();
	}

	/**
	 * Retrieves the sitename.
	 *
	 * @return string
	 */
	public static function get_site_name() {
		return wp_strip_all_tags( get_bloginfo( 'name' ), true );
	}

	/**
	 * Get action from request.
	 *
	 * @return bool|string
	 */
	public static function get_request_action() {
		if ( empty( $_REQUEST['action'] ) ) {
			return false;
		}

		if ( '-1' === $_REQUEST['action'] && ! empty( $_REQUEST['action2'] ) ) {
			$_REQUEST['action'] = $_REQUEST['action2'];
		}

		return sanitize_key( $_REQUEST['action'] );
	}

	/**
	 * Instantiates the WordPress filesystem for use.
	 *
	 * @return object
	 */
	public static function get_filesystem() {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	/**
	 * Get current post type.
	 *
	 * This function has some fallback strategies to get the current screen post type.
	 *
	 * @return string|bool
	 */
	public static function get_post_type() {
		global $pagenow;

		$post_type = self::post_type_from_globals();
		if ( false !== $post_type ) {
			return $post_type;
		}

		$post_type = self::post_type_from_request();
		if ( false !== $post_type ) {
			return $post_type;
		}

		return 'post-new.php' === $pagenow ? 'post' : false;
	}

	/**
	 * Get post type from global variables
	 *
	 * @return string|bool
	 */
	private static function post_type_from_globals() {
		global $post, $typenow, $current_screen;

		if ( $post && $post->post_type ) {
			return $post->post_type;
		}

		if ( $typenow ) {
			return $typenow;
		}

		if ( $current_screen && $current_screen->post_type ) {
			return $current_screen->post_type;
		}

		return false;
	}

	/**
	 * Get post type from request variables
	 *
	 * @return string|bool
	 */
	private static function post_type_from_request() {

		if ( $post_type = Param::request( 'post_type' ) ) { // phpcs:ignore
			return sanitize_key( $post_type );
		}

		if ( $post_id = Param::request( 'post_ID' ) ) { // phpcs:ignore
			return get_post_type( $post_id );
		}

		// @codeCoverageIgnoreStart
		if ( $post = Param::get( 'post' ) ) { // phpcs:ignore
			return get_post_type( $post );
		}
		// @codeCoverageIgnoreEnd

		return false;
	}

	/**
	 * Strip all shortcodes active or orphan.
	 *
	 * @param string $content Content to remove shortcodes from.
	 *
	 * @return string
	 */
	public static function strip_shortcodes( $content ) {
		if ( ! Str::contains( '[', $content ) ) {
			return $content;
		}

		// Remove Caption shortcode.
		$content = \preg_replace( '#\s*\[caption[^]]*\].*?\[/caption\]\s*#is', '', $content );

		return preg_replace( '~\[\/?.*?\]~s', '', $content );
	}
}
