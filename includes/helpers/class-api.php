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
	 * @param  string $field_id The field id to get value for.
	 * @param  mixed  $default  The default value if no field found.
	 * @return mixed
	 */
	public static function get_settings( $field_id = '', $default = false ) {
		return rank_math()->settings->get( $field_id, $default );
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

	/**
	 * Get the Content AI Credits.
	 *
	 * @param bool $force_update Whether to send a request to API to get the new Credits value.
	 */
	public static function get_content_ai_credits( $force_update = false ) {
		$registered = Admin_Helper::get_registration_data();
		if ( empty( $registered ) ) {
			return 0;
		}

		$credits = get_option( 'rank_math_ca_credits' );
		if ( $credits && ! $force_update ) {
			return $credits;
		}

		$args = [
			'username' => rawurlencode( $registered['username'] ),
			'api_key'  => rawurlencode( $registered['api_key'] ),
			'site_url' => rawurlencode( self::get_home_url() ),
		];

		$url = add_query_arg(
			$args,
			'https://rankmath.com/wp-json/rankmath/v1/contentAiCredits'
		);

		$data = wp_remote_get(
			$url,
			[
				'timeout' => 60,
			]
		);

		$response_code = wp_remote_retrieve_response_code( $data );
		if ( 200 !== $response_code ) {
			return 0;
		}

		$data = wp_remote_retrieve_body( $data );
		$data = json_decode( $data, true );

		$credits = ! empty( $data['remaining_credits'] ) ? $data['remaining_credits'] : 0;
		update_option( 'rank_math_ca_credits', $credits, false );
		return $credits;
	}
}
