<?php
/**
 * The routes for post related functionality
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

defined( 'ABSPATH' ) || exit;

/**
 * Post class.
 */
class Post extends WP_REST_Controller {

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
			'/updateMetaBulk',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => function () {
					return \RankMath\Helper::has_cap( 'onpage_general' );
				},
				'callback'            => [ $this, 'update_bulk_meta' ],
				'args'                => $this->get_update_bulk_meta_args(),
			]
		);

		if ( Helper::is_site_editor() ) {
			register_rest_field(
				'page',
				'rankMath',
				[
					'get_callback'        => [ $this, 'get_post_screen_meta' ],
					'schema'              => null,
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
				]
			);
		}
	}

	/**
	 * Update bulk metadata.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_bulk_meta( WP_REST_Request $request ) {
		$rows        = $request->get_param( 'rows' );
		$object_type = $request->get_param( 'objectType' );

		foreach ( $rows as $object_id => $data ) {
			$object_id = absint( $object_id );
			if ( ! $this->can_process( $object_id, $object_type ) ) {
				continue;
			}

			$this->save_row( $object_id, $object_type, $data );
		}

		return [ 'success' => true ];
	}

	/**
	 * Retrieves the Post screen metadata to be utilized when a Page is changed from the Full Site Editor.
	 */
	public function get_post_screen_meta() {
		$screen = new \RankMath\Admin\Metabox\Screen();
		$screen->load_screen( 'post' );
		return $screen->get_values();
	}

	/**
	 * Whether to process current object.
	 *
	 * @param int    $object_id   Object ID.
	 * @param string $object_type Object type.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	private function can_process( $object_id, $object_type ) {
		if ( ! $object_id ) {
			return false;
		}

		if ( $object_type === 'term' ) {
			$taxonomy = get_term( $object_id )->taxonomy;
			return in_array( $taxonomy, Helper::get_allowed_taxonomies(), true );
		}

		$post_type = get_post_type( $object_id );
		if ( ! Helper::is_post_type_accessible( $post_type ) && 'attachment' !== $post_type ) {
			return false;
		}

		// Checks whether the current has permission to edit post.
		$post_type_obj = get_post_type_object( $post_type );
		if (
			is_null( $post_type_obj ) ||
			(
				! current_user_can( $post_type_obj->cap->edit_post, $object_id ) &&
				! current_user_can( $post_type_obj->cap->edit_others_posts )
			)
		) {
			return false;
		}

		return true;
	}

	/**
	 * Save single row.
	 *
	 * @param int    $object_id    Object ID.
	 * @param string $object_type Object type.
	 * @param array  $data         Object data.
	 */
	private function save_row( $object_id, $object_type, $data ) {
		foreach ( $data as $key => $value ) {
			$this->save_column( $object_id, $object_type, $key, $value );
		}
	}

	/**
	 * Save row columns.
	 *
	 * @param int    $object_id   Object ID.
	 * @param string $object_type Object type.
	 * @param string $column      Column name.
	 * @param string $value       Column value.
	 */
	private function save_column( $object_id, $object_type, $column, $value ) {
		if ( ! in_array( $column, [ 'focus_keyword', 'title', 'description', 'image_alt', 'image_title' ], true ) ) {
			return;
		}

		$sanitizer = Sanitize::get();
		if ( 'image_title' === $column ) {
			wp_update_post(
				[
					'ID'         => $object_id,
					'post_title' => $sanitizer->sanitize( 'image_title', $value ),
				]
			);
			return;
		}

		if ( 'focus_keyword' === $column ) {
			$focus_keyword    = get_post_meta( $object_id, 'rank_math_' . $column, true );
			$focus_keyword    = explode( ',', $focus_keyword );
			$focus_keyword[0] = $value;
			$value            = implode( ',', $focus_keyword );
		}

		$column = 'image_alt' === $column ? '_wp_attachment_image_alt' : 'rank_math_' . $column;

		$method = $object_type === 'term' ? 'update_term_meta' : 'update_post_meta';
		$method( $object_id, $column, $sanitizer->sanitize( $column, $value ) );
	}

	/**
	 * Get update metadata endpoint arguments.
	 *
	 * @return array
	 */
	private function get_update_bulk_meta_args() {
		return [
			'rows' => [
				'required'          => true,
				'description'       => esc_html__( 'Selected posts to update the data for.', 'rank-math' ),
				'validate_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'is_param_empty' ],
			],
		];
	}
}
