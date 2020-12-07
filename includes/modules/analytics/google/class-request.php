<?php
/**
 * Google API Request.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Google;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Request
 */
class Request {

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
	protected $last_code = 0;

	/**
	 * Is refresh token notice added.
	 *
	 * @var bool
	 */
	private $is_notice_added = false;

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
	public function http_get( $url, $args = [], $timeout = 10 ) {
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
	public function http_post( $url, $args = [], $timeout = 10 ) {
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
	public function http_put( $url, $args = [], $timeout = 10 ) {
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
	public function http_delete( $url, $args = [], $timeout = 10 ) {
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
		// Early Bail!!
		if ( ! $this->refresh_token() && ! $this->is_notice_added ) {
			$this->is_notice_added = true;
			$this->is_success      = false;
			$this->last_error      = sprintf(
				/* translators: reconnect link */
				wp_kses_post( __( 'There is no refresh token. Please <a href="%1$s" class="button button-link rank-math-reconnect-google">reconnect your app</a>', 'rank-math' ) ),
				wp_nonce_url( admin_url( 'admin.php?reconnect=google' ), 'rank_math_reconnect_google' )
			);
			return;
		}

		$params = [
			'timeout' => $timeout,
			'method'  => $http_verb,
		];

		$params['headers'] = [ 'Authorization' => 'Bearer ' . $this->token ];

		if ( 'DELETE' === $http_verb || 'PUT' === $http_verb ) {
			$params['headers']['Content-Length'] = '0';
		} elseif ( 'POST' === $http_verb && ! empty( $args ) && is_array( $args ) ) {
			$json                                = wp_json_encode( $args );
			$params['body']                      = $json;
			$params['headers']['Content-Type']   = 'application/json';
			$params['headers']['Content-Length'] = strlen( $json );
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
