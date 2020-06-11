<?php
/**
 * Minimal Google API wrapper.
 *
 * @since      1.0.34
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Search_Console;

use RankMath\Helpers\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Google Api
 */
class Google_Api {

	/**
	 * Was the last request successful.
	 *
	 * @var bool
	 */
	private $is_success = false;

	/**
	 * Last error.
	 *
	 * @var string
	 */
	private $last_error = '';

	/**
	 * Last response.
	 *
	 * @var array
	 */
	private $last_response = [];

	/**
	 * Last response header code.
	 *
	 * @var int
	 */
	private $last_code = 0;

	/**
	 * Access token.
	 *
	 * @var string
	 */
	private $token = '';

	/**
	 * Set access token.
	 *
	 * @param string $token Access token.
	 */
	public function set_token( $token ) {
		$this->token = $token;
	}

	/**
	 * Get Search Console auth url.
	 *
	 * @return string
	 */
	public function get_auth_url() {
		$config = $this->get_config();

		return Security::add_query_arg_raw(
			[
				'response_type' => 'code',
				'client_id'     => $config['client_id'],
				'redirect_uri'  => $config['redirect_uri'],
				'scope'         => implode( ' ', $config['scopes'] ),
				// 'access_type'   => 'offline',
			],
			'https://accounts.google.com/o/oauth2/v2/auth'
		);
	}

	/**
	 * Get profiles.
	 *
	 * @return array
	 */
	public function get_profiles() {
		$profiles = [];
		$response = $this->get( 'https://www.googleapis.com/webmasters/v3/sites' );
		if ( ! $this->is_success() ) {
			return $profiles;
		}

		foreach ( $response['siteEntry'] as $site ) {
			$profiles[ $site['siteUrl'] ] = $site['siteUrl'];
		}

		return $profiles;
	}

	/**
	 * Attempt to exchange a code for an valid authentication token.
	 * Helper wrapped around the OAuth 2.0 implementation.
	 *
	 * @param string $code Authorize code from accounts.google.com.
	 *
	 * @return array access token
	 */
	public function get_access_token( $code ) {
		$config = $this->get_config();

		return $this->post(
			'https://www.googleapis.com/oauth2/v4/token',
			[
				'code'          => $code,
				'client_id'     => $config['client_id'],
				'client_secret' => $config['client_secret'],
				'redirect_uri'  => $config['redirect_uri'],
				'grant_type'    => 'authorization_code',
			],
			15
		);
	}

	/**
	 * Attempt to refresh thhe access code.
	 * Helper wrapped around the OAuth 2.0 implementation.
	 *
	 * @param string|array $token The token (access token or a refresh token) that should be revoked.
	 *
	 * @return array access token
	 */
	public function refresh_token( $token ) {
		$config = $this->get_config();

		return $this->post(
			'https://www.googleapis.com/oauth2/v4/token',
			[
				'refresh_token' => $token['refresh_token'],
				'client_id'     => $config['client_id'],
				'client_secret' => $config['client_secret'],
				'grant_type'    => 'refresh_token',
			],
			15
		);
	}

	/**
	 * Revoke an OAuth2 access token or refresh token. This method will revoke the current access
	 * token, if a token isn't provided.
	 *
	 * @param string|array $token The token (access token or a refresh token) that should be revoked.
	 *
	 * @return boolean Returns True if the revocation was successful, otherwise False.
	 */
	public function revoke_token( $token ) {
		if ( is_array( $token ) ) {
			$token = isset( $token['refresh_token'] ) ? $token['refresh_token'] : $token['access_token'];
		}

		$this->post(
			Security::add_query_arg_raw( [ 'token' => $token ], 'https://oauth2.googleapis.com/revoke' )
		);

		return $this->is_success();
	}

	/**
	 * Was the last request successful?
	 *
	 * @return bool  True for success, false for failure
	 */
	public function is_success() {
		return $this->is_success;
	}

	/**
	 * Get the last error returned by either the network transport, or by the API.
	 * If something didn't work, this should contain the string describing the problem.
	 *
	 * @return  array|false  describing the error
	 */
	public function get_error() {
		return $this->last_error ? $this->last_error : false;
	}

	/**
	 * Get an array containing the HTTP headers and the body of the API response.
	 *
	 * @return array  Assoc array with keys 'headers' and 'body'
	 */
	public function get_response() {
		return $this->last_response;
	}

	/**
	 * Make an HTTP GET request - for retrieving data.
	 *
	 * @param string $url     URL to do request.
	 * @param array  $args    Assoc array of arguments (usually your data).
	 * @param int    $timeout Timeout limit for request in seconds.
	 *
	 * @return array|false     Assoc array of API response, decoded from JSON.
	 */
	public function get( $url, $args = [], $timeout = 10 ) {
		return $this->make_request( 'GET', $url, $args, $timeout );
	}

	/**
	 * Make an HTTP POST request - for creating and updating items.
	 *
	 * @param string $url     URL to do request.
	 * @param array  $args    Assoc array of arguments (usually your data).
	 * @param int    $timeout Timeout limit for request in seconds.
	 *
	 * @return array|false     Assoc array of API response, decoded from JSON.
	 */
	public function post( $url, $args = [], $timeout = 10 ) {
		return $this->make_request( 'POST', $url, $args, $timeout );
	}

	/**
	 * Make an HTTP PUT request - for creating new items.
	 *
	 * @param string $url     URL to do request.
	 * @param array  $args    Assoc array of arguments (usually your data).
	 * @param int    $timeout Timeout limit for request in seconds.
	 *
	 * @return array|false     Assoc array of API response, decoded from JSON.
	 */
	public function put( $url, $args = [], $timeout = 10 ) {
		return $this->make_request( 'PUT', $url, $args, $timeout );
	}

	/**
	 * Make an HTTP DELETE request - for deleting data.
	 *
	 * @param string $url     URL to do request.
	 * @param array  $args    Assoc array of arguments (usually your data).
	 * @param int    $timeout Timeout limit for request in seconds.
	 *
	 * @return array|false     Assoc array of API response, decoded from JSON.
	 */
	public function delete( $url, $args = [], $timeout = 10 ) {
		return $this->make_request( 'DELETE', $url, $args, $timeout );
	}

	/**
	 * Performs the underlying HTTP request. Not very exciting.
	 *
	 * @param string $http_verb The HTTP verb to use: get, post, put, patch, delete.
	 * @param string $url       URL to do request.
	 * @param array  $args       Assoc array of parameters to be passed.
	 * @param int    $timeout    Timeout limit for request in seconds.
	 *
	 * @return array|false Assoc array of decoded result.
	 */
	private function make_request( $http_verb, $url, $args = [], $timeout = 10 ) {
		$params = [
			'timeout' => $timeout,
			'method'  => $http_verb,
		];

		$params['headers'] = [ 'Authorization' => 'Bearer ' . $this->token ];

		if ( 'DELETE' === $http_verb || 'PUT' === $http_verb ) {
			$params['headers']['Content-Length'] = '0';
		} elseif ( 'POST' === $http_verb && ! empty( $args ) && is_array( $args ) ) {
			$params['body']                    = wp_json_encode( $args );
			$params['headers']['Content-Type'] = 'application/json';
		}

		$this->reset();
		$response           = wp_remote_request( $url, $params );
		$formatted_response = $this->format_response( $response );
		$this->determine_success( $response, $formatted_response );

		return $formatted_response;
	}

	/**
	 * Decode the response and format any error messages for debugging
	 *
	 * @param array $response The response from the curl request.
	 *
	 * @return array|false The JSON decoded into an array
	 */
	private function format_response( $response ) {
		$this->last_response = $response;

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( ! empty( $response['body'] ) ) {
			return json_decode( $response['body'], true );
		}

		return false;
	}

	/**
	 * Check if the response was successful or a failure. If it failed, store the error.
	 *
	 * @param array       $response           The response from the curl request.
	 * @param array|false $formatted_response The response body payload from the curl request.
	 */
	private function determine_success( $response, $formatted_response ) {
		if ( is_wp_error( $response ) ) {
			$this->last_error = 'WP_Error: ' . $response->get_error_message();
			return;
		}

		$this->last_code = wp_remote_retrieve_response_code( $response );
		if ( in_array( $this->last_code, [ 200, 204 ], true ) ) {
			$this->is_success = true;
			return;
		}

		if ( isset( $formatted_response['error_description'] ) ) {
			$this->last_error = 'Bad Request' === $formatted_response['error_description'] ?
				esc_html__( 'Bad request. Please check the code.', 'rank-math' ) : $formatted_response['error_description'];
			return;
		}

		$this->last_error = 'Unknown error, call getLastResponse() to find out what happened.';
	}

	/**
	 * Get RM Search Console API config.
	 *
	 * @return array
	 */
	private function get_config() {
		/**
		 * Filter: 'rank_math/search_console/alternate_app' - Allows filtering GSC application data.
		 *
		 * @param  array $config Array of Application data.
		 * @return array $config Filtered Application data.
		 */
		$config = apply_filters( 'rank_math/search_console/alternate_app', [
			'application_name' => 'Rank Math',
			'client_id'        => '521003500769-n68nimh2rrahq6b4cdcjm03ojgsukr1f.apps.googleusercontent.com',
			'client_secret'    => 'nPNvFDg-1MHrT1cAFQouaVtK',
		] );

		$config['redirect_uri'] = 'urn:ietf:wg:oauth:2.0:oob';
		$config['scopes']       = [ 'https://www.googleapis.com/auth/webmasters', 'https://www.googleapis.com/auth/analytics.readonly', 'https://www.googleapis.com/auth/adsense.readonly' ];

		return $config;
	}

	/**
	 * Reset request.
	 */
	private function reset() {
		$this->last_code     = 0;
		$this->last_error    = '';
		$this->is_success    = false;
		$this->last_response = [
			'body'    => null,
			'headers' => null,
		];
	}
}
