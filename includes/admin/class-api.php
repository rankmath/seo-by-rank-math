<?php
/**
 * The RankMath API.
 *
 * @since      1.5.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Api class.
 */
class Api {

	/**
	 * Rank Math SEO Checkup API.
	 *
	 * @var string
	 */
	protected $api_url = 'https://rankmath.com/wp-json/rankmath/v1/';

	/**
	 * Was the last request successful.
	 *
	 * @var bool
	 */
	protected $is_success = false;

	/**
	 * Last error.
	 *
	 * @var string
	 */
	protected $last_error = '';

	/**
	 * Last response.
	 *
	 * @var array
	 */
	protected $last_response = [];

	/**
	 * Last response header code.
	 *
	 * @var int
	 */
	protected $last_code = 0;

	/**
	 * User agent.
	 *
	 * @var string
	 */
	protected $user_agent = '';

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
			$instance              = new Api();
			$instance->is_blocking = true;
			$instance->user_agent  = 'RankMath/' . md5( esc_url( home_url( '/' ) ) );
		}

		return $instance;
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
	 * Remove registration data and disconnect from RankMath.com.
	 *
	 * @param string $username Username.
	 * @param string $api_key  Api key.
	 */
	public function deactivate_site( $username, $api_key ) {
		$this->is_blocking = false;
		$this->http_post(
			'deactivateSite',
			[
				'username' => $username,
				'api_key'  => $api_key,
				'site_url' => esc_url( home_url() ),
			]
		);
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
	protected function make_request( $http_verb, $url, $args = [], $timeout = 10 ) {
		$params = [
			'timeout'    => $timeout,
			'method'     => $http_verb,
			'user-agent' => $this->user_agent,
			'blocking'   => $this->is_blocking,
		];

		if ( ! empty( $args ) && is_array( $args ) ) {
			$params['body'] = $args;
		}

		$this->reset();
		$response = wp_remote_request( $this->api_url . $url, $params );
		$this->determine_success( $response );

		return $this->format_response( $response );
	}

	/**
	 * Decode the response and format any error messages for debugging
	 *
	 * @param array $response The response from the curl request.
	 *
	 * @return array|false The JSON decoded into an array
	 */
	protected function format_response( $response ) {
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
	 * @param array $response The response from the curl request.
	 */
	protected function determine_success( $response ) {
		if ( is_wp_error( $response ) ) {
			$this->last_error = 'WP_Error: ' . $response->get_error_message();
			return;
		}

		$this->last_code = wp_remote_retrieve_response_code( $response );
		if ( in_array( $this->last_code, [ 200, 204 ], true ) ) {
			$this->is_success = true;
			return;
		}

		$this->last_error = 'Unknown error, call getLastResponse() to find out what happened.';
	}

	/**
	 * Reset request.
	 */
	protected function reset() {
		$this->last_code     = 0;
		$this->last_error    = '';
		$this->is_success    = false;
		$this->is_blocking   = true;
		$this->last_response = [
			'body'    => null,
			'headers' => null,
		];
	}
}
