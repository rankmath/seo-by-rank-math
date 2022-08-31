<?php
/**
 * The shared REST routes for front and backend.
 *
 * Defines the functionality loaded both on front and backend.
 *
 * @since      1.0.60
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Rest;

use MyThemeShop\Helpers\Str;
use RankMath\Helper;
use RankMath\Redirections\Metabox;
use RankMath\Rest\Rest_Helper;
use RankMath\Rest\Sanitize;
use RankMath\Traits\Meta;
use RankMath\Schema\DB;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Shared class.
 */
class Shared extends WP_REST_Controller {

	use Meta;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = Rest_Helper::BASE;
	}

	/**
	 * Register shared routes.
	 */
	public function register_routes() {

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
			'/updateMeta',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_metadata' ],
				'args'                => $this->get_update_metadata_args(),
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'get_object_permissions_check' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/updateSchemas',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_schemas' ],
				'args'                => $this->get_update_schemas_args(),
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
		$cmb     = new \stdClass();
		$metabox = new Metabox();

		$cmb->object_id    = $request->get_param( 'objectID' );
		$cmb->object_type  = null !== $request->get_param( 'objectType' ) ? $request->get_param( 'objectType' ) : 'post';
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
		$meta        = apply_filters( 'rank_math/filter_metadata', $request->get_param( 'meta' ), $request );
		$content     = $request->get_param( 'content' );
		do_action( 'rank_math/pre_update_metadata', $object_id, $object_type, $content );

		$new_slug = true;
		if ( isset( $meta['permalink'] ) && ! empty( $meta['permalink'] ) && 'post' === $object_type ) {
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
			// Delete schema by meta id.
			if ( Str::starts_with( 'rank_math_delete_', $meta_key ) ) {
				\delete_metadata_by_mid( $object_type, absint( \str_replace( 'rank_math_delete_schema-', '', $meta_key ) ) );
				update_metadata( $object_type, $object_id, 'rank_math_rich_snippet', 'off' );
				continue;
			}

			if ( empty( $meta_value ) ) {
				delete_metadata( $object_type, $object_id, $meta_key );
				continue;
			}

			$this->update_meta( $object_type, $object_id, $meta_key, $sanitizer->sanitize( $meta_key, $meta_value ) );
		}

		return [
			'slug'    => $new_slug,
			'schemas' => DB::get_schemas( $object_id ),
		];
	}

	/**
	 * Get update metadata endpoint arguments.
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
	 * Update metadata.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_schemas( WP_REST_Request $request ) {
		$object_id   = $request->get_param( 'objectID' );
		$object_type = $request->get_param( 'objectType' );
		$schemas     = apply_filters( 'rank_math/schema/filter_data', $request->get_param( 'schemas' ), $request );
		$new_ids     = [];

		do_action( 'rank_math/pre_update_schema', $object_id, $object_type );
		foreach ( $schemas as $meta_id => $schema ) {
			$type     = is_array( $schema['@type'] ) ? $schema['@type'][0] : $schema['@type'];
			$meta_key = 'rank_math_schema_' . $type;
			$schema   = wp_kses_post_deep( $schema );

			// Add new.
			if ( Str::starts_with( 'new-', $meta_id ) ) {
				$new_ids[ $meta_id ] = add_metadata( $object_type, $object_id, $meta_key, $schema );
				continue;
			}

			// Update old.
			$db_id      = absint( str_replace( 'schema-', '', $meta_id ) );
			$prev_value = update_metadata_by_mid( $object_type, $db_id, $schema, $meta_key );
		}

		do_action( 'rank_math/schema/update', $object_id, $schemas, $object_type );

		return $new_ids;
	}

	/**
	 * Get update schemas endpoint arguments.
	 *
	 * @return array
	 */
	private function get_update_schemas_args() {
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
			'schemas'    => [
				'required'          => true,
				'description'       => esc_html__( 'schemas to add or update data.', 'rank-math' ),
				'validate_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'is_param_empty' ],
			],
		];
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
}
