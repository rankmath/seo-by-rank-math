<?php
/**
 * Minimal Google API wrapper.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Google;

use RankMath\Helpers\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Api
 */
class Api extends Console {

	/**
	 * Access token.
	 *
	 * @var array
	 */
	public $token = [];

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Api
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Api ) ) {
			$instance = new Api();
			$instance->setup();
		}

		return $instance;
	}

	/**
	 * Setup token.
	 */
	private function setup() {
		if ( ! Authentication::is_authorized() ) {
			return;
		}

		$tokens      = Authentication::tokens();
		$this->token = $tokens['access_token'];
	}

	/**
	 * Refresh access token when user login.
	 */
	public function refresh_token() {
		// Bail if the user is not authenticated at all yet.
		if ( ! Authentication::is_authorized() || ! Authentication::is_token_expired() ) {
			return true;
		}

		$tokens = Authentication::tokens();
		if ( empty( $tokens['refresh_token'] ) ) {
			return false;
		}

		$response = wp_remote_get( Authentication::get_auth_app_url() . '/refresh.php?code=' . $tokens['refresh_token'] );
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$response = wp_remote_retrieve_body( $response );
		if ( empty( $response ) ) {
			return false;
		}

		// Save new token.
		$this->token            = $response;
		$tokens['expire']       = time() + 3600;
		$tokens['access_token'] = $response;
		Authentication::tokens( $tokens );

		return true;
	}

	/**
	 * Revoke an OAuth2 access token or refresh token. This method will revoke the current access
	 * token, if a token isn't provided.
	 *
	 * @return boolean Returns True if the revocation was successful, otherwise False.
	 */
	public function revoke_token() {
		$tokens = Authentication::tokens();
		$this->http_post(
			Security::add_query_arg_raw( [ 'token' => $tokens['access_token'] ], 'https://oauth2.googleapis.com/revoke' )
		);

		Authentication::tokens( false );
		delete_option( 'rank_math_google_analytic_profile' );
		delete_option( 'rank_math_google_analytic_options' );
		delete_option( 'rankmath_google_api_failed_attempts_data' );
		delete_option( 'rankmath_google_api_reconnect' );

		return $this->is_success();
	}

	/**
	 * Get row limit.
	 *
	 * @return int
	 */
	public function get_row_limit() {
		return apply_filters( 'rank_math/analytics/row_limit', 1000 );
	}
}
