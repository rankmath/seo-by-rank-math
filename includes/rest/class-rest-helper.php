<?php
/**
 * REST api helper.
 *
 * @since      1.0.15
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Rest;

use WP_Error;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Rest_Helper class.
 */
class Rest_Helper {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const BASE = 'rankmath/v1';

	/**
	 * Determines if the current user can manage options.
	 *
	 * @return true
	 */
	public static function can_manage_options() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Checks whether a given request has permission to update redirection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public static function get_redirection_permissions_check( $request ) {
		if ( ! Helper::is_module_active( 'redirections' ) || ! Helper::has_cap( 'redirections' ) ) {
			return new WP_Error(
				'rest_cannot_edit',
				__( 'Sorry, you are not allowed to create/update redirection.', 'rank-math' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public static function get_object_permissions_check( $request ) {
		$object_id   = $request->get_param( 'objectID' );
		$object_type = $request->get_param( 'objectType' );

		if ( 'post' === $object_type ) {
			return self::get_post_permissions_check( $request );
		}

		return false;
	}

	/**
	 * Checks whether a given request has permission to read post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public static function get_post_permissions_check( $request ) {
		$post = self::get_post( $request->get_param( 'objectID' ) );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( 'rank_math_locations' === $post->post_type ) {
			return true;
		}

		if ( ! Helper::is_post_type_accessible( $post->post_type ) && 'rank_math_schema' !== $post->post_type ) {
			return new WP_Error(
				'rest_cannot_edit',
				__( 'Sorry, you are not allowed to edit this post type.', 'rank-math' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		$post_type = get_post_type_object( $post->post_type );

		if (
			current_user_can( $post_type->cap->edit_post, $post->ID ) ||
			current_user_can( $post_type->cap->edit_others_posts )
		) {
			return true;
		}

		return new WP_Error(
			'rest_cannot_edit',
			__( 'Sorry, you are not allowed to edit this post.', 'rank-math' ),
			[ 'status' => rest_authorization_required_code() ]
		);
	}

	/**
	 * Get the post, if the ID is valid.
	 *
	 * @param int $id Supplied ID.
	 *
	 * @return WP_Post|WP_Error Post object if ID is valid, WP_Error otherwise.
	 */
	public static function get_post( $id ) {
		$error = new WP_Error(
			'rest_post_invalid_id',
			__( 'Invalid post ID.', 'rank-math' ),
			[ 'status' => 404 ]
		);

		if ( (int) $id <= 0 ) {
			return $error;
		}

		$post = get_post( (int) $id );
		if ( empty( $post ) || empty( $post->ID ) ) {
			return $error;
		}

		return $post;
	}

	/**
	 * Param emptiness validate callback.
	 *
	 * @param mixed $param Param to validate.
	 *
	 * @return boolean
	 */
	public static function is_param_empty( $param ) {
		if ( empty( $param ) ) {
			return new WP_Error(
				'param_value_empty',
				esc_html__( 'Sorry, field is empty which is not allowed.', 'rank-math' )
			);
		}
		return true;
	}
}
