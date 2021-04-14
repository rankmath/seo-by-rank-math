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

	/**
	 * Log every failed API call.
	 * And kill all next scheduled event if failed count is more then three.
	 *
	 * @param array  $response   Response from api.
	 * @param string $action     Action performing.
	 * @param string $start_date Start date fetching for.
	 * @param array  $args       Array of arguments.
	 */
	public function log_failed_request( $response, $action, $start_date, $args ) {
		if ( $this->is_success() ) {
			return;
		}

		// Number of allowed attempt.
		$allow_fail_attempt          = 3;
		$option_key                  = 'rankmath_google_api_failed_attempts_data';
		$reconnect_google_option_key = 'rankmath_google_api_reconnect';

		if ( ! empty( $response['error'] ) && is_array( $response['error'] ) ) {

			// Limit maximum 10 failed attempt data to log.
			$failed_attempts   = get_option( $option_key, [] );
			$failed_attempts   = ( ! empty( $failed_attempts ) && is_array( $failed_attempts ) ) ? array_slice( $failed_attempts, -9, 9 ) : [];
			$failed_attempts[] = [
				'args'  => $args,
				'error' => $response['error'],
			];

			update_option( $option_key, $failed_attempts, false );

			if ( $allow_fail_attempt < count( $failed_attempts ) ) {
				update_option( $reconnect_google_option_key, 'search_analytics_query' );
			} else {
				as_schedule_single_action(
					time() + 60,
					"rank_math/analytics/get_{$action}_data",
					[ $start_date ],
					'rank-math'
				);
			}
		} else {
			delete_option( $option_key );
			delete_option( $reconnect_google_option_key );
		}
	}
}
