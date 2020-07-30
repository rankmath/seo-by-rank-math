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
use RankMath\Traits\Meta;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends WP_REST_Controller {

	use Meta;

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
			'/updateRedirection',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_redirection' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'get_redirection_permissions_check' ],
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

		$this->gutenberg_routes();
	}

	/**
	 * Routes needed for gutenberg sidebar to work.
	 */
	private function gutenberg_routes() {
		register_rest_route(
			$this->namespace,
			'/updateMeta',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_metadata' ],
				'args'                => $this->get_update_metadata_args(),
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'get_object_permissions_check' ],
			]
		);
	}

	/**
	 * Update redirection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_redirection( WP_REST_Request $request ) {
		$cmb     = new \stdClass;
		$metabox = new \RankMath\Redirections\Metabox;

		$cmb->object_id    = $request->get_param( 'objectID' );
		$cmb->data_to_save = [
			'has_redirect'                 => $request->get_param( 'hasRedirect' ),
			'redirection_id'               => $request->get_param( 'redirectionID' ),
			'redirection_url_to'           => $request->get_param( 'redirectionUrl' ),
			'redirection_sources'          => \str_replace( home_url( '/' ), '', $request->get_param( 'redirectionSources' ) ),
			'redirection_header_code'      => $request->get_param( 'redirectionType' ) ? $request->get_param( 'redirectionType' ) : 301,
			'rank_math_enable_redirection' => 'on',
		];

		if ( false === $request->get_param( 'hasRedirect' ) ) {
			unset( $cmb->data_to_save['redirection_url_to'] );
		}

		if ( empty( $request->get_param( 'redirectionID' ) ) ) {
			unset( $cmb->data_to_save['redirection_id'] );
		}

		return $metabox->save_advanced_meta( $cmb );
	}

	/**
	 * Update metadata.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_metadata( WP_REST_Request $request ) {
		$object_id   = $request->get_param( 'objectID' );
		$object_type = $request->get_param( 'objectType' );
		$meta        = $request->get_param( 'meta' );

		$new_slug = true;
		if ( isset( $meta['permalink'] ) && ! empty( $meta['permalink'] ) ) {
			$post     = get_post( $object_id );
			$new_slug = wp_unique_post_slug( $meta['permalink'], $post->ID, $post->post_status, $post->post_type, $post->post_parent );
			wp_update_post(
				[
					'ID'        => $object_id,
					'post_name' => $new_slug,
				]
			);
			unset( $meta['permalink'] );
		}

		// Add protection.
		remove_all_filters( 'is_protected_meta' );
		add_filter( 'is_protected_meta', [ $this, 'only_this_plugin' ], 10, 2 );

		$sanitizer = Sanitize::get();
		foreach ( $meta as $meta_key => $meta_value ) {
			if ( empty( $meta_value ) ) {
				delete_metadata( $object_type, $object_id, $meta_key );
				continue;
			}

			$this->update_meta( $object_type, $object_id, $meta_key, $sanitizer->sanitize( $meta_key, $meta_value ) );
		}

		return $new_slug;
	}

	/**
	 * Allow only rank math meta keys
	 *
	 * @param bool   $protected Whether the key is considered protected.
	 * @param string $meta_key  Meta key.
	 *
	 * @return bool
	 */
	public function only_this_plugin( $protected, $meta_key ) {
		return Str::starts_with( 'rank_math_', $meta_key );
	}

	/**
	 * Get update metadta endpoint arguments.
	 *
	 * @return array
	 */
	private function get_update_metadata_args() {
		return [
			'objectType' => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Object Type i.e. post, term, user', 'rank-math' ),
				'validate_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'is_param_empty' ],
			],
			'objectID'   => [
				'type'              => 'integer',
				'required'          => true,
				'description'       => esc_html__( 'Object unique id', 'rank-math' ),
				'validate_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'is_param_empty' ],
			],
			'meta'       => [
				'required'          => true,
				'description'       => esc_html__( 'Meta to add or update data.', 'rank-math' ),
				'validate_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'is_param_empty' ],
			],
		];
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
		if ( ! in_array( $field, [ 'enable_auto_update', 'enable_auto_update_email' ], true ) ) {
			return false;
		}

		$value = 'true' === $request->get_param( 'value' ) ? 'on' : 'off';
		Helper::toggle_auto_update_setting( $value );

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
}
