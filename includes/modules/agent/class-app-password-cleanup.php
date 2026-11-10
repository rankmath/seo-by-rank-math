<?php
/**
 * Removes the Assistant's Application Passwords when Rank Math is deactivated or uninstalled.
 *
 * @since      1.0.282
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Agent;

defined( 'ABSPATH' ) || exit;

/**
 * App_Password_Cleanup class.
 *
 * The password's 1-hour expiry and MCP-only scope are enforced by the WAP library
 * while Rank Math is active. Left behind, it is a permanent full-access credential
 * for its user, so it must go with the plugin.
 *
 * Application Passwords and user meta are network-wide, so run each method once per
 * network, not once per site.
 */
class App_Password_Cleanup {

	/**
	 * Product slug. Duplicates Agent::PRODUCT: uninstall.php loads this file without the autoloader.
	 */
	const PRODUCT = 'rankmath';

	/**
	 * Label prefix of every Application Password the Assistant creates, e.g. "WAP – Rank Math Support Agent".
	 *
	 * The separator is an en dash (–), not a hyphen: it must match the library's label byte for byte.
	 */
	const PASSWORD_NAME_PREFIX = 'WAP – Rank Math';

	/**
	 * User meta the WAP library keeps for the product.
	 */
	const UUID_META_KEY          = 'wap_app_password_uuid_rankmath';
	const CONSENT_META_KEY       = 'wap_client_consent_rankmath';
	const PERMISSION_META_PREFIX = 'wap_client_permission_rankmath_';

	/**
	 * Delete every user's Assistant password and reset their consent.
	 */
	public static function deactivate() {
		self::delete_credentials();
	}

	/**
	 * Remove whatever deactivation didn't, e.g. when the plugin was disabled over FTP.
	 *
	 * Called from uninstall.php.
	 *
	 * @param bool $clear_data Whether the site opted into removing all Rank Math data.
	 *                         Permission choices are settings, so they're kept otherwise.
	 */
	public static function uninstall( $clear_data = false ) {
		self::delete_credentials();

		if ( $clear_data ) {
			self::delete_permission_meta();
		}
	}

	/**
	 * Delete the Assistant's passwords and the meta that lets the library mint new ones.
	 *
	 * Without consent, the Assistant asks again before it creates the next password.
	 */
	private static function delete_credentials() {
		// Since wap-client 2.6.0; also covers the per-site uuid keys and WAP passwords no site tracks.
		if ( method_exists( 'WapClient', 'revoke_app_passwords' ) ) {
			\WapClient::revoke_app_passwords( self::PRODUCT );
		} else {
			self::delete_legacy_credentials();
		}

		// Object ID 0 with $delete_all removes the key for every user.
		delete_metadata( 'user', 0, self::CONSENT_META_KEY, '', true );
	}

	/**
	 * Fallback for a library without the helper: delete the passwords by name and the uuid that tracks them.
	 *
	 * Without the uuid, the library stops reusing its cached GRND, which carries the deleted password.
	 */
	private static function delete_legacy_credentials() {
		self::delete_passwords_by_name();
		delete_metadata( 'user', 0, self::UUID_META_KEY, '', true );
	}

	/**
	 * Delete every Application Password labelled as the Assistant's, whether or not it's on record.
	 */
	private static function delete_passwords_by_name() {
		global $wpdb;

		if ( ! class_exists( 'WP_Application_Passwords' ) ) {
			return;
		}

		// Narrows to only usermeta rows that can contain the password, so the
		// LIKE scan doesn't touch unrelated meta on large sites.
		$user_ids = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT DISTINCT user_id FROM %i WHERE meta_key = %s AND meta_value LIKE %s',
				$wpdb->usermeta,
				\WP_Application_Passwords::USERMETA_KEY_APPLICATION_PASSWORDS,
				'%' . $wpdb->esc_like( self::PASSWORD_NAME_PREFIX ) . '%'
			)
		);

		foreach ( $user_ids as $user_id ) {
			$passwords = \WP_Application_Passwords::get_user_application_passwords( (int) $user_id );
			foreach ( $passwords as $password ) {
				if ( 0 === strpos( $password['name'], self::PASSWORD_NAME_PREFIX ) ) {
					\WP_Application_Passwords::delete_application_password( (int) $user_id, $password['uuid'] );
				}
			}
		}
	}

	/**
	 * Delete every user's permission choices for the product.
	 */
	private static function delete_permission_meta() {
		global $wpdb;

		$meta_keys = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT DISTINCT meta_key FROM %i WHERE meta_key LIKE %s',
				$wpdb->usermeta,
				$wpdb->esc_like( self::PERMISSION_META_PREFIX ) . '%'
			)
		);

		foreach ( $meta_keys as $meta_key ) {
			delete_metadata( 'user', 0, $meta_key, '', true );
		}
	}
}
