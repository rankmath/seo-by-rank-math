<?php
/**
 * Trial activation REST API controller.
 *
 * @since      1.0.281
 * @package    RankMath
 * @subpackage RankMath\AI_Visibility\Api
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\AI_Visibility\Api;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Trial_Controller class.
 */
class Trial_Controller extends Base_Controller {

	use Hooker;

	/**
	 * Plan slug requested when activating the AI Visibility free trial.
	 */
	const TRIAL_PLAN = 'content-ai-creator-trial';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/trial/activate',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'activate_trial' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
			]
		);
	}

	/**
	 * Activate 15-day AI Visibility free trial.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function activate_trial( $request ) {
		unset( $request );

		$result = $this->request_trial_activation();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Refresh the cached Content AI plan & credits so the UI unlocks on reload.
		Helper::get_content_ai_credits( true );

		rank_math()->tracking->track_event(
			'AI Visibility Trial Activated',
			[ 'plan' => Helper::get_content_ai_plan() ]
		);

		return $this->success(
			$result,
			__( 'Your AI Visibility trial has been activated.', 'seo-by-rank-math' )
		);
	}

	/**
	 * Request trial activation from the Rank Math account API.
	 *
	 * Hits the `planUpgrade` endpoint with the connected account's
	 * credentials. The base URL defaults to the test host and can be pointed
	 * at production via the `rank_math/ai_visibility/plan_upgrade_url` filter.
	 *
	 * @return array|WP_Error Decoded response array, or WP_Error on failure.
	 */
	private function request_trial_activation() {
		$registered = Admin_Helper::get_registration_data();

		if ( empty( $registered['api_key'] ) || empty( $registered['username'] ) ) {
			return new WP_Error(
				'aiv_unauthorized',
				__( 'Rank Math account not connected. Please connect your account and try again.', 'seo-by-rank-math' ),
				[ 'status' => 401 ]
			);
		}

		$response = wp_remote_post(
			RANK_MATH_SITE_URL . '/wp-json/rankmath/v1/planUpgrade',
			[
				'timeout' => 60,
				'body'    => [
					'api_key'  => $registered['api_key'],
					'username' => $registered['username'],
					'plan'     => self::TRIAL_PLAN,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'aiv_request_failed',
				$response->get_error_message(),
				[ 'status' => 502 ]
			);
		}

		$http_code = (int) wp_remote_retrieve_response_code( $response );
		$decoded   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $http_code < 200 || $http_code >= 300 ) {
			$message = ! empty( $decoded['message'] )
				? $decoded['message']
				: __( 'We could not activate your trial. Please try again.', 'seo-by-rank-math' );

			return new WP_Error( 'aiv_trial_activation_failed', $message, [ 'status' => $http_code ] );
		}

		return is_array( $decoded ) ? $decoded : [ 'activated' => true ];
	}
}
