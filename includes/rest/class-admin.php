<?php
/**
 * The Global functionality of the plugin.
 *
 * Defines the functionality loaded on admin.
 *
 * @since      1.0.15
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Rest;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Controller;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Traits\Meta;
use RankMath\Role_Manager\Capability_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends WP_REST_Controller {

	use Meta, Hooker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = \RankMath\Rest\Rest_Helper::BASE;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/saveModule',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'save_module' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
				'args'                => $this->get_save_module_args(),
			]
		);

		register_rest_route(
			$this->namespace,
			'/autoUpdate',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'auto_update' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/toolsAction',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'tools_actions' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/updateMode',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_mode' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/dashboardWidget',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'dashboard_widget_items' ],
				'permission_callback' => function() {
					return current_user_can( 'read' );
				},
			]
		);

		register_rest_route(
			$this->namespace,
			'/updateSeoScore',
			[
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_seo_score' ],
				'permission_callback' => [ $this, 'can_edit_posts' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/updateSettings',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_settings' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_settings' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/resetSettings',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'reset_settings' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_settings' ],
			]
		);
	}

	/**
	 * Save module state.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function save_module( WP_REST_Request $request ) {
		$module = $request->get_param( 'module' );
		$state  = $request->get_param( 'state' );

		Helper::update_modules( [ $module => $state ] );

		do_action( 'rank_math/module_changed', $module, $state );
		return true;
	}

	/**
	 * Enable Auto update.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function auto_update( WP_REST_Request $request ) {
		$field = $request->get_param( 'key' );
		if ( 'enable_auto_update' !== $field ) {
			return false;
		}

		$value = 'true' === $request->get_param( 'value' ) ? 'on' : 'off';
		Helper::toggle_auto_update_setting( $value );

		return true;
	}

	/**
	 * Function to get the dashboard widget content.
	 */
	public function dashboard_widget_items() {
		ob_start();
		$this->do_action( 'dashboard/widget' );
		return ob_get_clean();
	}

	/**
	 * Tools actions.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function tools_actions( WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );
		return apply_filters( 'rank_math/tools/' . $action, 'Something went wrong.' );
	}

	/**
	 * Rest route to update the seo score.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_seo_score( WP_REST_Request $request ) {
		$post_scores = $request->get_param( 'postScores' );
		if ( empty( $post_scores ) ) {
			return 0;
		}

		foreach ( $post_scores as $post_id => $score ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}

			$score = (int) $score;
			if ( $score < 0 || $score > 100 ) {
				continue;
			}

			update_post_meta( $post_id, 'rank_math_seo_score', $score );
		}

		return 1;
	}

	/**
	 * Update Setup Mode.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_mode( WP_REST_Request $request ) {
		$settings = wp_parse_args(
			rank_math()->settings->all_raw(),
			[ 'general' => '' ]
		);

		$settings['general']['setup_mode'] = $request->get_param( 'mode' );
		Helper::update_all_settings( $settings['general'], null, null );

		return true;
	}

	/**
	 * Check if user can edit post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool
	 */
	public function can_edit_posts( WP_REST_Request $request ) {
		$post_scores = $request->get_param( 'postScores' );
		if ( empty( $post_scores ) ) {
			return false;
		}

		foreach ( $post_scores as $post_id => $score ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Update Settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_settings( WP_REST_Request $request ) {
		$settings = $request->get_param( 'settings' );
		$type     = $request->get_param( 'type' );

		if ( $type === 'roleCapabilities' ) {
			Helper::set_capabilities( $settings );
			return true;
		}

		Helper::update_all_settings( ...$settings );
		rank_math()->settings->reset();

		return true;
	}

	/**
	 * Reset settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function reset_settings( WP_REST_Request $request ) {
		$type = $request->get_param( 'type' );
		if ( $type === 'roleCapabilities' ) {
			Capability_Manager::get()->reset_capabilities();
			return true;
		}

		delete_option( "rank-math-options-$type" );
		return true;
	}

	/**
	 * Get save module endpoint arguments.
	 *
	 * @return array
	 */
	private function get_save_module_args() {
		return [
			'module' => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Module slug', 'rank-math' ),
				'validate_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'is_param_empty' ],
			],
			'state'  => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Module state either on or off', 'rank-math' ),
				'validate_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'is_param_empty' ],
			],
		];
	}
}
