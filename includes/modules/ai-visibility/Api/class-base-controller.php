<?php
/**
 * Base REST API controller.
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\AI_Visibility\Api
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\AI_Visibility\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Controller;
use RankMath\Rest\Rest_Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract base.
 */
abstract class Base_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = Rest_Helper::BASE . '/ai-visibility';
	}

	/**
	 * Permission check — user must have `manage_options`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return true|WP_Error
	 */
	public function check_admin_permission( $request ) {
		return Rest_Helper::can_manage_options();
	}

	/**
	 * Build a success response envelope.
	 *
	 * @param mixed  $data    Payload to return under `data`.
	 * @param string $message Optional human-readable status message.
	 *
	 * @return WP_REST_Response
	 */
	protected function success( $data, $message = '' ) {
		$response = [
			'success' => true,
			'data'    => $data,
		];

		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Build an error response.
	 *
	 * @param string $code    Error code slug.
	 * @param string $message Human-readable message.
	 * @param int    $status  HTTP status code.
	 *
	 * @return WP_Error
	 */
	protected function error( $code, $message, $status = 400 ) {
		return new WP_Error( $code, $message, [ 'status' => $status ] );
	}

	/**
	 * Send a request to the AI Visibility backend.
	 *
	 * Centralises all `wp_remote_*` calls so every controller method only has
	 * to supply the HTTP verb, the path, and an optional payload.
	 *
	 * Return value
	 * ------------
	 * Returns the decoded JSON array on success. Returns a `WP_Error` on:
	 *   - Missing / invalid registration data (not connected to Rank Math).
	 *   - `wp_remote_request()` transport error (timeout, DNS, TLS, …).
	 *   - Non-2xx HTTP status from the backend (code mapped to a slug).
	 *   - Response body that cannot be decoded as JSON.
	 *
	 * @param string $method           HTTP verb — 'GET', 'POST', 'PATCH', 'PUT', 'DELETE'.
	 * @param string $path             Backend path, e.g. '/api/v1/brands'.
	 * @param array  $body             Request payload (JSON-encoded automatically).
	 *
	 * @return array|WP_Error Decoded response array, or WP_Error on failure.
	 */
	protected function remote_request( $method, $path, $body = [] ) {
		$data = Admin_Helper::get_registration_data();
		if ( ! $data || empty( $data['api_key'] ) ) {
			return new WP_Error(
				'aiv_unauthorized',
				__( 'Rank Math account not connected. Please connect your account and try again.', 'seo-by-rank-math' ),
				[ 'status' => 401 ]
			);
		}

		$headers = [
			'Accept'        => 'application/json',
			'Content-Type'  => 'application/json',
			'x-username'    => $data['username'] ?? '',
			'x-site-url'    => $data['site_url'] ?? '',
			'x-cai-api-key' => $data['api_key'],
		];

		$args = [
			'method'  => strtoupper( $method ),
			'timeout' => 30,
			'headers' => $headers,
		];

		if ( ! empty( $body ) ) {
			$args['body'] = wp_json_encode( $body );
		}

		$url      = 'https://ai-visibility.rankmath.com/' . ltrim( $path, '/' );
		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'aiv_request_failed',
				$response->get_error_message(),
				[ 'status' => 502 ]
			);
		}

		$http_code   = (int) wp_remote_retrieve_response_code( $response );
		$raw_body    = wp_remote_retrieve_body( $response );
		$decoded     = json_decode( $raw_body, true );
		$backend_msg = isset( $decoded['message'] ) ? $decoded['message'] : '';

		if ( $http_code < 200 || $http_code >= 300 ) {
			$error_map = [
				400 => 'aiv_bad_request',
				401 => 'aiv_unauthorized',
				402 => 'aiv_insufficient_credits',
				403 => 'aiv_plan_limit',
				404 => 'aiv_not_found',
				409 => 'aiv_conflict',
				422 => 'aiv_invalid_credentials',
			];

			if ( $http_code >= 500 ) {
				$code = 'aiv_server_error';
			} else {
				$code = isset( $error_map[ $http_code ] ) ? $error_map[ $http_code ] : 'aiv_request_failed';
			}

			$message = ! empty( $backend_msg )
				? $backend_msg
				/* translators: %d: HTTP status code returned by the backend. */
				: sprintf( __( 'AI Visibility API returned an unexpected status: %d', 'seo-by-rank-math' ), $http_code );

			return new WP_Error( $code, $message, [ 'status' => $http_code ] );
		}

		if ( null === $decoded ) {
			return new WP_Error(
				'aiv_bad_response',
				__( 'AI Visibility API returned an invalid response.', 'seo-by-rank-math' ),
				[ 'status' => 502 ]
			);
		}

		return $decoded;
	}
}
