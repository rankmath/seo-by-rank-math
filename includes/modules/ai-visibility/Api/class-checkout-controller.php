<?php
/**
 * Content AI checkout REST API controller.
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\AI_Visibility\Api
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\AI_Visibility\Api;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Checkout_Controller class.
 */
class Checkout_Controller extends Base_Controller {

	use Hooker;

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/checkout-url',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_checkout_url' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
			]
		);
	}

	/**
	 * POST /checkout-url — build an authenticated Content AI checkout iframe URL.
	 *
	 * Fetches a fresh single-use token (15-min, consumed on first iframe load)
	 * and returns the full iframe `src`. A new URL must be requested per
	 * iframe session — never reload the iframe with the same URL.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|WP_Error
	 */
	public function get_checkout_url( $request ) {
		$token = $this->get_checkout_token();

		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$url = add_query_arg(
			[
				'site_checkout' => 1,
				'rm_token'      => $token,
			],
			RANK_MATH_SITE_URL . '/site-checkout-cai',
		);

		return $this->success( [ 'url' => $url ] );
	}

	/**
	 * Fetch a single-use checkout token from the Rank Math account API.
	 *
	 * @return string|WP_Error Token string, or WP_Error on failure.
	 */
	private function get_checkout_token() {
		$registered = Admin_Helper::get_registration_data();

		if ( empty( $registered['api_key'] ) || empty( $registered['username'] ) ) {
			return new WP_Error(
				'aiv_unauthorized',
				__( 'Rank Math account not connected. Please connect your account and try again.', 'seo-by-rank-math' ),
				[ 'status' => 401 ]
			);
		}

		$response = wp_remote_post(
			RANK_MATH_SITE_URL . '/wp-json/rankmath/v1/checkoutToken',
			[
				'timeout' => 60,
				'headers' => [ 'Content-Type' => 'application/json' ],
				'body'    => wp_json_encode(
					[
						'username' => $registered['username'],
						'api_key'  => $registered['api_key'],
					]
				),
			]
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'aiv_request_failed', $response->get_error_message(), [ 'status' => 502 ] );
		}

		$http_code = (int) wp_remote_retrieve_response_code( $response );
		$decoded   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $http_code < 200 || $http_code >= 300 || empty( $decoded['token'] ) ) {
			$message = ! empty( $decoded['message'] )
				? $decoded['message']
				: __( 'Could not start the checkout session. Please try again.', 'seo-by-rank-math' );

			return new WP_Error( 'aiv_checkout_token_failed', $message, [ 'status' => $http_code ? $http_code : 502 ] );
		}

		return $decoded['token'];
	}
}
