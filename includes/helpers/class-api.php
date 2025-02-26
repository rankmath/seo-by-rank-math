<?php
/**
 * The API helpers.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * API class.
 */
trait Api {

	/**
	 * Add notification.
	 *
	 * @param string $message Message string.
	 * @param array  $options Set of options.
	 */
	public static function add_notification( $message, $options = [] ) {
		$options['classes'] = ! empty( $options['classes'] ) ? $options['classes'] . ' rank-math-notice' : 'rank-math-notice';
		$notification       = compact( 'message', 'options' );

		/**
		 * Filter notification message & arguments before adding.
		 * Pass a falsy value to stop the notification from getting added.
		 */
		apply_filters( 'rank_math/admin/add_notification', $notification );

		if ( empty( $notification ) || ! is_array( $notification ) ) {
			return;
		}

		rank_math()->notification->add( $notification['message'], $notification['options'] );
	}

	/**
	 * Remove notification.
	 *
	 * @param string $notification_id Notification id.
	 */
	public static function remove_notification( $notification_id ) {
		rank_math()->notification->remove_by_id( $notification_id );
	}

	/**
	 * Check if notification exists.
	 *
	 * @param string $notification_id Notification id.
	 */
	public static function has_notification( $notification_id ) {
		return rank_math()->notification->has_notification( $notification_id );
	}

	/**
	 * Get Setting.
	 *
	 * @param  string $field_id      The field id to get value for.
	 * @param  mixed  $default_value The default value if no field found.
	 * @return mixed
	 */
	public static function get_settings( $field_id = '', $default_value = false ) {
		return rank_math()->settings->get( $field_id, $default_value );
	}

	/**
	 * Get Auto update setting status.
	 *
	 * @return bool
	 */
	public static function get_auto_update_setting() {
		return in_array( 'seo-by-rank-math/rank-math.php', (array) get_site_option( 'auto_update_plugins', [] ), true );
	}

	/**
	 * Toggle auto updates option.
	 *
	 * @param string $toggle       New status.
	 * @return void
	 */
	public static function toggle_auto_update_setting( $toggle ) {
		do_action( 'rank_math/settings/toggle_auto_update', $toggle );

		$auto_updates = (array) get_site_option( 'auto_update_plugins', [] );
		if ( ! empty( $toggle ) && 'off' !== $toggle ) {
			$auto_updates[] = 'seo-by-rank-math/rank-math.php';
			update_site_option( 'auto_update_plugins', array_unique( $auto_updates ) );
			return;
		}

		update_site_option( 'auto_update_plugins', array_diff( $auto_updates, [ 'seo-by-rank-math/rank-math.php' ] ) );
	}

	/**
	 * Add something to the JSON object.
	 *
	 * @param string $key         Unique identifier.
	 * @param mixed  $value       The data itself can be either a single or an array.
	 * @param string $object_name Name for the JavaScript object. Passed directly, so it should be qualified JS variable.
	 */
	public static function add_json( $key, $value, $object_name = 'rankMath' ) {
		rank_math()->json->add( $key, $value, $object_name );
	}

	/**
	 * Remove something from the JSON object.
	 *
	 * @param string $key         Unique identifier.
	 * @param string $object_name Name for the JavaScript object. Passed directly, so it should be qualified JS variable.
	 */
	public static function remove_json( $key, $object_name = 'rankMath' ) {
		rank_math()->json->remove( $key, $object_name );
	}
}
