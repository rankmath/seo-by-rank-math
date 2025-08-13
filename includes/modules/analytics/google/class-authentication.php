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

use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Data_Encryption;
use RankMath\Helpers\Param;
use RankMath\Helpers\Security;

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
	 * Get or update token data.
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

		return $tokens['expire'] && time() > $tokens['expire'];
	}

	/**
	 * Get oauth url.
	 *
	 * @return string
	 */
	public static function get_auth_url() {
		$page = self::get_page_slug();

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
	 * Google custom app.
	 *
	 * @return string
	 */
	public static function get_auth_app_url() {
		return apply_filters( 'rank_math/analytics/app_url', 'https://oauth.rankmath.com' );
	}

	/**
	 * Get page slug according to request.
	 *
	 * @return string
	 */
	public static function get_page_slug() {
		$page = Param::get( 'page' );
		if ( ! empty( $page ) ) {
			switch ( $page ) {
				case 'rank-math-wizard':
					return 'rank-math-wizard&step=analytics';

				case 'rank-math-analytics':
					return 'rank-math-analytics';

				default:
					if ( Helper::is_react_enabled() ) {
						return 'rank-math-options-general&view=analytics';
					}

					return 'rank-math-options-general#setting-panel-analytics';
			}
		}

		$page = wp_get_referer();
		if ( ! empty( $page ) && Str::contains( 'wizard', $page ) ) {
			return 'rank-math-wizard&step=analytics';
		}

		return 'rank-math-options-general#setting-panel-analytics';
	}
}
