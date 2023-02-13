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

use RankMath\Helpers\Security;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Request
 */
class Request {

	/**
	 * Workflow.
	 */
	private $workflow = '';

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
	 * Set workflow
	 */
	public function set_workflow( $workflow = '' ) {
		$this->workflow = $workflow;
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
	 * @return WP_Error|array|false     Assoc array of API response, decoded from JSON.
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
	 * @return WP_Error|array|false     Assoc array of API response, decoded from JSON.
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
	 * @return WP_Error|array|false     Assoc array of API response, decoded from JSON.
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
	 * @return WP_Error|array|false     Assoc array of API response, decoded from JSON.
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
		if ( ! Authentication::is_authorized() ) {
			return;
		}

		if ( $this->have_buffer_time() ) {
			return;
		}

		if ( ! $this->refresh_token() || ! is_scalar( $this->token ) ) {
			if ( ! $this->is_notice_added ) {
				$this->is_notice_added = true;
				$this->is_success      = false;
				$this->last_error      = sprintf(
					/* translators: reconnect link */
					wp_kses_post( __( 'There is a problem with the Google auth token. Please <a href="%1$s" class="button button-link rank-math-reconnect-google">reconnect your app</a>', 'rank-math' ) ),
					wp_nonce_url( admin_url( 'admin.php?reconnect=google' ), 'rank_math_reconnect_google' )
				);
				$this->log_response( $http_verb, $url, $args, '', '', '', date( 'Y-m-d H:i:s' ) . ': Google auth token has been expired or is invalid' );
			}
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
		sleep( 1 );
		$response           = wp_remote_request( $url, $params );
		$formatted_response = $this->format_response( $response );
		$this->determine_success( $response, $formatted_response );

		$this->log_response( $http_verb, $url, $args, $response, $formatted_response, $params );

		// Error handaling.
		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			// Remove workflow actions.
			if ( $this->workflow ) {
				as_unschedule_all_actions( 'rank_math/analytics/get_' . $this->workflow . '_data' );
			}
		}

		do_action(
			'rank_math/analytics/handle_' . $this->workflow . '_response',
			[
				'formatted_response' => $formatted_response,
				'response'           => $response,
				'http_verb'          => $http_verb,
				'url'                => $url,
				'args'               => $args,
				'code'               => $code,
			]
		);

		return $formatted_response;
	}

	/**
	 * Log the response in analytics_debug.log file.
	 *
	 * @param string $http_verb The HTTP verb to use: get, post, put, patch, delete.
	 * @param string $url       URL to do request.
	 * @param array  $args       Assoc array of parameters to be passed.
	 * @param string $response make_request response.
	 */
	private function log_response( $http_verb = '', $url = '', $args = [], $response = [], $formatted_response = '', $params = [], $text = '' ) {
		if ( ! apply_filters( 'rank_math/analytics/log_response', false ) ) {
			return;
		}

		do_action( 'rank_math/analytics/log', $http_verb, $url, $args, $response, $formatted_response, $params );

		$uploads = wp_upload_dir();
		$file    = $uploads['basedir'] . '/rank-math/analytics-debug.log';

		$wp_filesystem = WordPress::get_filesystem();

		// Create log file if it doesn't exist.
		$wp_filesystem->touch( $file );

		// Not writable? Bail.
		if ( ! $wp_filesystem->is_writable( $file ) ) {
			return;
		}

		$message  = '********************************' . PHP_EOL;
		$message .= date( 'Y-m-d h:i:s' ) . PHP_EOL;

		$tokens = Authentication::tokens();
		if ( ! empty( $tokens ) && is_array( $tokens ) && isset( $tokens['expire'] ) ) {
			$message .= 'Expiry: ' . date( 'Y-m-d h:i:s', $tokens['expire'] ) . PHP_EOL;
			$message .= 'Expiry Readable: ' . human_time_diff( $tokens['expire'] ) . PHP_EOL;
		}

		$message .= $text . PHP_EOL;

		if ( is_wp_error( $response ) ) {
			$message .= '<span class="fail">FAIL</span>' . PHP_EOL;
			$message .= 'WP_Error: ' . $response->get_error_message() . PHP_EOL;
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$message .= '<span class="fail">FAIL</span>' . PHP_EOL;
		} elseif ( isset( $formatted_response['error_description'] ) ) {
			$message .= '<span class="fail">FAIL</span>' . PHP_EOL;
			$message .= 'Bad Request' === $formatted_response['error_description'] ?
			esc_html__( 'Bad request. Please check the code.', 'rank-math' ) : $formatted_response['error_description'];
		} else {
			$message .= '<span class="pass">PASS</span>' . PHP_EOL;
		}
		$message .= 'REQUEST: ' . $http_verb . ' > ' . $url . PHP_EOL;
		$message .= 'REQUEST_PARAMETERS: ' . wp_json_encode( $params ) . PHP_EOL;
		$message .= 'REQUEST_API_ARGUMENTS: ' . wp_json_encode( $args ) . PHP_EOL;
		$message .= 'RESPONSE_CODE: ' . wp_remote_retrieve_response_code( $response ) . PHP_EOL;
		$message .= 'RESPONSE_CODE_MESSAGE: ' . wp_remote_retrieve_body( $response ) . PHP_EOL;
		$message .= 'RESPONSE_FORMATTED: ' . wp_json_encode( $formatted_response ) . PHP_EOL;
		$message .= 'ORIGINAL_RESPONSE: ' . wp_json_encode( $response ) . PHP_EOL;
		$message .= '================================' . PHP_EOL;
		$message .= $wp_filesystem->get_contents( $file );

		$wp_filesystem->put_contents( $file, $message );
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
	 * @param object      $response           The response from the curl request.
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

		$this->last_error = esc_html__( 'Unknown error, call get_response() to find out what happened.', 'rank-math' );
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

	/**
	 * Refresh access token when user login.
	 */
	public function refresh_token() {
		// Bail if the user is not authenticated at all yet.
		if ( ! Authentication::is_authorized() || ! Authentication::is_token_expired() ) {
			return true;
		}

		$token = $this->get_refresh_token();
		if ( ! $token ) {
			return false;
		}

		$tokens = Authentication::tokens();

		// Save new token.
		$this->token            = $token;
		$tokens['expire']       = time() + 3600;
		$tokens['access_token'] = $token;
		Authentication::tokens( $tokens );

		$this->set_buffer_time();

		return true;
	}

	/**
	 * Set buffer time.
	 *
	 * @param int $buffer_time The buffer time to hold the request until the new token generate.
	 */
	protected function set_buffer_time( $buffer_time = 120 ) {
		update_option( 'rank_math_google_api_buffer_time', time() + $buffer_time );
	}

	/**
	 * Check buffer time.
	 *
	 * @return boolean
	 */
	protected function have_buffer_time() {
		$tokens      = Authentication::tokens();
		$buffer_time = get_option( 'rank_math_google_api_buffer_time', '' );
		// Set buffer time only once before 2 min token expiry.
		if ( empty( $buffer_time ) && $tokens['expire'] && time() > ( $tokens['expire'] - 120 ) ) {
			$this->set_buffer_time();
			return true;
		}

		if ( empty( $buffer_time ) ) {
			return false;
		}

		// Check the current time exceed the buffer time.
		if ( time() <= $buffer_time ) {
			return true;
		}

		delete_option( 'rank_math_google_api_buffer_time' );
		return false;
	}

	/**
	 * Get the new refresh token.
	 *
	 * @return mixed
	 */
	protected function get_refresh_token() {
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

		return $response;
	}

	/**
	 * Revoke an OAuth2 token.
	 *
	 * @return boolean Whether the token was revoked successfully.
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
	 * Log every failed API call.
	 * And kill all next scheduled event if failed count is more then three.
	 *
	 * @param array  $response   Response from api.
	 * @param string $action     Action performing.
	 * @param string $start_date Start date fetching for (or page URI for inspections).
	 * @param array  $args       Array of arguments.
	 */
	public function log_failed_request( $response, $action, $start_date, $args ) {
		if ( $this->is_success() ) {
			return;
		}

		$option_key                  = 'rankmath_google_api_failed_attempts_data';
		$reconnect_google_option_key = 'rankmath_google_api_reconnect';
		if ( empty( $response['error'] ) || ! is_array( $response['error'] ) ) {
			delete_option( $option_key );
			delete_option( $reconnect_google_option_key );
			return;
		}

		// Limit maximum 10 failed attempt data to log.
		$failed_attempts   = get_option( $option_key, [] );
		$failed_attempts   = ( ! empty( $failed_attempts ) && is_array( $failed_attempts ) ) ? array_slice( $failed_attempts, -9, 9 ) : [];
		$failed_attempts[] = [
			'action' => $action,
			'args'   => $args,
			'error'  => $response['error'],
		];

		update_option( $option_key, $failed_attempts, false );

		// Number of allowed attempt.
		if ( 3 < count( $failed_attempts ) ) {
			update_option( $reconnect_google_option_key, 'search_analytics_query' );
			return;
		}

		as_schedule_single_action(
			time() + 60,
			"rank_math/analytics/get_{$action}_data",
			[ $start_date ],
			'rank-math'
		);
	}
}
