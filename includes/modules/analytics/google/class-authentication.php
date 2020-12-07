<?php
/**
 *  Google Authentication wrapper.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Google;

use RankMath\Data_Encryption;
use RankMath\Helpers\Security;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;
use RankMath\Helper as GlobalHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Authentication class.
 */
class Authentication {

	/**
	 * API version.
	 *
	 * @var string
	 */
	protected static $api_version = '2.1';

	/**
	 * Get or update Search Console data.
	 *
	 * @param  bool|array $data Data to save.
	 * @return bool|array
	 */
	public static function tokens( $data = null ) {
		$key          = 'rank_math_google_oauth_tokens';
		$encrypt_keys = [
			'access_token',
			'refresh_token',
		];

		// Clear data.
		if ( false === $data ) {
			delete_option( $key );
			return false;
		}

		$saved = get_option( $key, [] );
		foreach ( $encrypt_keys as $enc_key ) {
			if ( isset( $saved[ $enc_key ] ) ) {
				$saved[ $enc_key ] = Data_Encryption::deep_decrypt( $saved[ $enc_key ] );
			}
		}

		// Getter.
		if ( is_null( $data ) ) {
			return wp_parse_args( $saved, [] );
		}

		// Setter.
		foreach ( $encrypt_keys as $enc_key ) {
			if ( isset( $saved[ $enc_key ] ) ) {
				$saved[ $enc_key ] = Data_Encryption::deep_encrypt( $saved[ $enc_key ] );
			}
			if ( isset( $data[ $enc_key ] ) ) {
				$data[ $enc_key ] = Data_Encryption::deep_encrypt( $data[ $enc_key ] );
			}
		}

		$data = wp_parse_args( $data, $saved );
		update_option( $key, $data );

		return $data;
	}

	/**
	 * Is google authorized.
	 *
	 * @return boolean
	 */
	public static function is_authorized() {
		$tokens = self::tokens();

		return isset( $tokens['access_token'] ) && isset( $tokens['refresh_token'] );
	}

	/**
	 * Check if token is expired.
	 *
	 * @return boolean
	 */
	public static function is_token_expired() {
		$tokens = self::tokens();

		return $tokens['expire'] && time() > ( $tokens['expire'] - 120 );
	}

	/**
	 * Get oauth url.
	 *
	 * @return string
	 */
	public static function get_auth_url() {

		$page = Param::get( 'page' );
		$page = 'rank-math-wizard' === $page ? 'rank-math-wizard&step=analytics' : 'rank-math-options-general#setting-panel-analytics';

		return Security::add_query_arg_raw(
			[
				'version'      => defined( 'RANK_MATH_PRO_VERSION' ) ? 'pro' : 'free',
				'api_version'  => static::$api_version,
				'redirect_uri' => rawurlencode( admin_url( 'admin.php?page=' . $page ) ),
				'security'     => wp_create_nonce( 'rank_math_oauth_token' ),
			],
			self::get_auth_app_url()
		);
	}

	/**
	 * Get access token after redirect.
	 */
	public static function get_tokens_from_server() {
		// Bail if the user is not authenticated at all yet.
		$id = Param::get( 'process_oauth', 0, FILTER_VALIDATE_INT );
		if ( $id < 1 ) {
			return;
		}

		$response = wp_remote_get( self::get_auth_app_url() . '/get.php?id=' . $id );
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$response = wp_remote_retrieve_body( $response );
		if ( empty( $response ) ) {
			return;
		}

		$response = \json_decode( $response, true );
		unset( $response['id'] );

		// Save new token.
		self::tokens( $response );

		$redirect = Security::remove_query_arg_raw( [ 'process_oauth', 'security' ] );
		if ( Str::contains( 'rank-math-options-general', $redirect ) ) {
			$redirect .= '#setting-panel-analytics';
		}

		GlobalHelper::remove_notification( 'rank_math_analytics_reauthenticate' );

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Google custom app.
	 *
	 * @return string
	 */
	public static function get_auth_app_url() {
		return apply_filters( 'rank_math/analytics/app_url', 'https://oauth.rankmath.com' );
	}
}
