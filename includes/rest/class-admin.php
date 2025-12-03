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
use RankMath\Redirections\Redirection;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends WP_REST_Controller {

	use Meta;
	use Hooker;

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
			'/toolsAction',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'tools_actions' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
				'args'                => $this->get_tools_action_args(),
			]
		);

		register_rest_route(
			$this->namespace,
			'/updateMode',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_mode' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
				'args'                => $this->get_update_mode_args(),
			]
		);

		register_rest_route(
			$this->namespace,
			'/dashboardWidget',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'dashboard_widget_items' ],
				'permission_callback' => function () {
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
				'args'                => $this->get_update_seo_score_args(),
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
			'/searchPage',
			[
				'methods'             => WP_REST_Server::ALLMETHODS,
				'callback'            => [ $this, 'search_page' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
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
		$this->maybe_delete_rewrite_rules( $module );
		do_action( 'rank_math/module_changed', $module, $state );
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
		return apply_filters( 'rank_math/tools/' . $action, 'Something went wrong.', $request );
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
		$is_reset = $request->get_param( 'isReset' );

		if ( $type === 'roleCapabilities' ) {
			$is_reset ? Capability_Manager::get()->reset_capabilities() : Helper::set_capabilities( $settings );
			return [
				'settings' => Helper::get_roles_capabilities(),
			];
		}

		if ( $type === 'redirections' ) {
			$redirection = Redirection::from(
				[
					'id'          => isset( $settings['id'] ) ? $settings['id'] : '',
					'sources'     => $settings['sources'],
					'url_to'      => isset( $settings['url_to'] ) ? $settings['url_to'] : '',
					'header_code' => $settings['header_code'],
					'status'      => $settings['status'],
				]
			);
			if ( $redirection->is_infinite_loop() ) {
				if ( ! $redirection->get_id() ) {
					$redirection->set_status( 'inactive' );
					return rest_ensure_response(
						[
							'error' => __( 'The redirection you are trying to create may cause an infinite loop. Please check the source and destination URLs. The redirection has been deactivated.', 'rank-math' ),
						]
					);
				}

				return rest_ensure_response(
					[
						'error' => __( 'The redirection you are trying to update may cause an infinite loop. Please check the source and destination URLs.', 'rank-math' ),
					]
				);

			}

			if ( false === $redirection->save() ) {
				return __( 'Please add at least one valid source URL.', 'rank-math' );
			}

			$this->do_action( 'redirection/saved', $redirection, $settings );
			return true;
		}

		if ( $type === 'instant-indexing' ) {
			$key          = 'rank-math-options-instant-indexing';
			$org_settings = get_option( $key );
			if ( $is_reset ) {
				if ( isset( $org_settings['bing_post_types'] ) ) {
					unset( $org_settings['bing_post_types'] );
				}
			}

			$org_settings['bing_post_types'] = isset( $settings['bing_post_types'] ) ? array_map( 'sanitize_text_field', $settings['bing_post_types'] ) : [];

			update_option( $key, $org_settings, false );
			return [
				'settings' => $org_settings,
			];
		}

		if ( ! in_array( $type, [ 'general', 'titles', 'sitemap' ], true ) ) {
			return __( 'Invalid type.', 'rank-math' );
		}

		return \RankMath\Admin\Option_Center::save_settings(
			$type,
			$settings,
			$request->get_param( 'fieldTypes' ),
			$request->get_param( 'updated' ),
			$is_reset
		);
	}

	/**
	 * Update Settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function search_page( WP_REST_Request $request ) {
		$term = sanitize_text_field( $request->get_param( 'searchedTerm' ) );

		if ( empty( $term ) ) {
			return rest_ensure_response( [ 'results' => [] ] );
		}

		global $wpdb;
		$pages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_type = 'page' AND post_status = 'publish' AND post_title LIKE %s",
				'%' . $wpdb->esc_like( $term ) . '%'
			),
			ARRAY_A
		);

		$data = array_map(
			function ( $page ) {
				return [
					'id'   => (int) $page['ID'],
					'text' => $page['post_title'],
					'url'  => get_permalink( $page['ID'] ),
				];
			},
			$pages
		);

		return rest_ensure_response( [ 'results' => $data ] );
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
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => function ( $param, $request, $key ) {
					$modules = array_keys( rank_math()->manager->modules );
					if ( ! in_array( $param, $modules, true ) ) {
						return new WP_Error( 'invalid_module', esc_html__( 'Invalid module', 'rank-math' ), [ 'status' => 400 ] );
					}

					return rest_validate_request_arg( $param, $request, $key );
				},
			],
			'state'  => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Module state either on or off', 'rank-math' ),
				'enum'              => [ 'on', 'off' ],
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Get update seo score endpoint arguments.
	 *
	 * @return array
	 */
	private function get_update_seo_score_args() {
		return [
			'postScores' => [
				'type'              => 'object',
				'required'          => true,
				'description'       => esc_html__( 'Post scores', 'rank-math' ),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Get tools action endpoint arguments.
	 *
	 * @return array
	 */
	private function get_tools_action_args() {
		return [
			'action' => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Action to perform', 'rank-math' ),
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Get update mode endpoint arguments.
	 *
	 * @return array
	 */
	private function get_update_mode_args() {
		return [
			'mode' => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Mode to set', 'rank-math' ),
				'enum'              => [ 'easy', 'advanced', 'custom' ],
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Maybe update(delete) rewrite rules.
	 *
	 * @param string $module The module name.
	 *
	 * @return void
	 */
	private function maybe_delete_rewrite_rules( $module ) {
		if ( in_array( $module, [ 'sitemap', 'llms-txt' ], true ) ) {
			delete_option( 'rewrite_rules' );
		}
	}
}
