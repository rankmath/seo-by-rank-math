<?php
/**
 * The Rest endpoints used in the Setup Wizard page.
 *
 * Defines the functionality loaded on admin.
 *
 * @since      1.0.245
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Rest;

use WP_REST_Request;
use WP_REST_Controller;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Setup_Wizard class.
 */
class Setup_Wizard extends WP_REST_Controller {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = \RankMath\Rest\Rest_Helper::BASE . '/setupWizard';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/getStepData',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'get_step_data' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
				'args'                => $this->get_step_args(),
			]
		);
		register_rest_route(
			$this->namespace,
			'/updateStepData',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'update_step_data' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
				'args'                => $this->update_step_args(),
			]
		);
		register_rest_route(
			$this->namespace,
			'/updateTrackingOptin',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'update_tracking_optin' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
				'args'                => $this->tracking_optin_args(),
			]
		);
	}

	/**
	 * Get Current step data.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array View Data.
	 */
	public function get_step_data( WP_REST_Request $request ) {
		$step = $request->get_param( 'step' );

		// Track step change.
		do_action( 'rank_math/setup_wizard/step_viewed', $step );

		return \RankMath\Admin\Setup_Wizard::get_localized_data( $step );
	}

	/**
	 * Update the step data.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 */
	public function update_step_data( WP_REST_Request $request ) {
		$step   = $request->get_param( 'step' );
		$values = $request->get_param( 'value' );

		return \RankMath\Admin\Setup_Wizard::save_data( $step, $values );
	}

	/**
	 * Update tracking opt-in status.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array Response data.
	 */
	public function update_tracking_optin( WP_REST_Request $request ) {
		$enable_tracking = $request->get_param( 'enable_tracking' );
		do_action( 'rank_math/setup_wizard/enable_tracking', $enable_tracking === 'on' );

		return [
			'success' => true,
			'status'  => 200,
		];
	}

	/**
	 * Get step endpoint arguments.
	 *
	 * @return array
	 */
	private function get_step_args() {
		return [
			'step' => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Current Step', 'rank-math' ),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Update step data endpoint arguments.
	 *
	 * @return array
	 */
	private function update_step_args() {
		return [
			'step'  => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Current Step', 'rank-math' ),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'value' => [
				'type'              => 'object',
				'required'          => true,
				'description'       => esc_html__( 'Current Step Data', 'rank-math' ),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Tracking opt-in endpoint arguments.
	 *
	 * @return array
	 */
	private function tracking_optin_args() {
		return [
			'enable_tracking' => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Enable Tracking', 'rank-math' ),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}
}
