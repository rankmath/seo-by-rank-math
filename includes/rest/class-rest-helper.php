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
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public static function get_redirection_permissions_check() {
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
	 * Checks whether a given request has permission to update schema.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public static function get_schema_permissions_check( $request ) {
		if ( ! Helper::is_module_active( 'rich-snippet' ) || ! Helper::has_cap( 'onpage_snippet' ) ) {
			return new WP_Error(
				'rest_cannot_edit',
				__( 'Sorry, you are not allowed to create/update schema.', 'rank-math' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return self::get_object_permissions_check( $request );
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

		if ( in_array( $object_type, [ 'post', 'term', 'user' ], true ) ) {
			$method = "get_{$object_type}_permissions_check";
			return self::$method( $request );
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
		$object_id = $request->get_param( 'objectID' );
		if ( $object_id === 0 ) {
			return true;
		}

		$post = self::get_post( $object_id );
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
	 * Checks whether a given request has permission to read term.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public static function get_term_permissions_check( $request ) {
		$term_id = $request->get_param( 'objectID' );
		$term    = self::get_term( $term_id );
		if ( is_wp_error( $term ) ) {
			return $term;
		}

		if (
			! in_array( $term->taxonomy, array_keys( Helper::get_accessible_taxonomies() ), true ) ||
			! current_user_can( get_taxonomy( $term->taxonomy )->cap->edit_terms, $term_id )
		) {
			return new WP_Error(
				'rest_cannot_edit',
				__( 'Sorry, you are not allowed to edit this term.', 'rank-math' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Get the term, if the ID is valid.
	 *
	 * @param int $id Supplied ID.
	 *
	 * @return WP_Term|WP_Error Term object if ID is valid, WP_Error otherwise.
	 */
	public static function get_term( $id ) {
		$error = new WP_Error(
			'rest_term_invalid_id',
			__( 'Invalid term ID.', 'rank-math' ),
			[ 'status' => 404 ]
		);

		if ( (int) $id <= 0 ) {
			return $error;
		}

		global $wpdb;
		$term = $wpdb->get_row( $wpdb->prepare( "SELECT t.* FROM $wpdb->term_taxonomy AS t WHERE t.term_id = %d LIMIT 1", $id ) );
		if ( empty( $term ) || empty( $term->term_id ) ) {
			return $error;
		}

		return $term;
	}

	/**
	 * Checks whether a given request has permission to read user.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public static function get_user_permissions_check( $request ) {
		$user_id = $request->get_param( 'objectID' );
		return current_user_can( 'edit_user', $user_id ) && Helper::get_settings( 'titles.author_add_meta_box' );
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

	/**
	 * Checks whether a given request has permission to update settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public static function can_manage_settings( $request ) {
		$type = $request->get_param( 'type' );
		return $type === 'roleCapabilities' ? current_user_can( 'rank_math_role_manager' ) : current_user_can( "rank_math_$type" );
	}

	/**
	 * Param emptiness validate callback.
	 *
	 * @param mixed $param Param to validate.
	 *
	 * @return boolean
	 */
	public static function is_valid_string( $param ) {
		if ( empty( $param ) ) {
			return new WP_Error(
				'param_value_empty',
				esc_html__( 'Sorry, field is empty which is not allowed.', 'rank-math' )
			);
		}

		return self::is_alphanumerical( $param );
	}

	/**
	 * Check the alphanumerical string.
	 *
	 * @param mixed $param Param to validate.
	 *
	 * @return boolean
	 */
	public static function is_alphanumerical( $param ) {
		if ( ! preg_match( '/^[a-zA-Z0-9]+$/', $param ) ) {
			return new WP_Error(
				'param_value_empty',
				esc_html__( 'Sorry, the field contains invalid characters.', 'rank-math' )
			);
		}
		return true;
	}
}
