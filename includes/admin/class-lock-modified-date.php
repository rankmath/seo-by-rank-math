<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.223
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Helpers\Editor;
use RankMath\Helpers\Param;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Lock_Modified_Date class.
 *
 * @codeCoverageIgnore
 */
class Lock_Modified_Date {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function __construct() {
		$this->action( 'rest_api_init', 'init_rest_api' );
		$this->action( 'wp_insert_post_data', 'update_modified_date', 999, 3 );
	}

	/**
	 * Add REST filter to modify the post object.
	 */
	public function init_rest_api() {
		$post_types = Helper::get_allowed_post_types();
		foreach ( $post_types as $post_type ) {
			$this->filter( "rest_pre_insert_{$post_type}", 'update_last_modified_parameter', 99, 2 );
		}
	}

	/**
	 * Add last_modified parameter to a post when a post is updated from Block Editor.
	 *
	 * @param WP_POST         $prepared_post Post object.
	 * @param WP_REST_Request $request       Request object.
	 */
	public function update_last_modified_parameter( $prepared_post, $request ) {
		$params = $request->get_params();
		if ( isset( $params['meta']['rank_math_lock_modified_date'] ) ) {
			$prepared_post->lock_modified_date = ! empty( $params['meta']['rank_math_lock_modified_date'] );
		}

		return $prepared_post;
	}

	/**
	 * Lock Modified date by overwriting the old value.
	 *
	 * @param array $data    An array of slashed, sanitized, and processed post data.
	 * @param array $postarr An array of sanitized (and slashed) but otherwise unmodified post data.
	 */
	public function update_modified_date( $data, $postarr ) {
		$post_id = ! empty( $postarr['ID'] ) ? $postarr['ID'] : 0;
		if (
			! $post_id ||
			! isset( $postarr['post_modified'], $postarr['post_modified_gmt'] ) ||
			! $this->lock_modified_date( $postarr, $post_id )
		) {
			return $data;
		}

		$data['post_modified']     = $postarr['post_modified'];
		$data['post_modified_gmt'] = $postarr['post_modified_gmt'];

		return $data;
	}

	/**
	 * Whether to lock modified date.
	 *
	 * @param array $data    An array of sanitized (and slashed) but otherwise unmodified post data.
	 * @param int   $post_id Post ID.
	 */
	private function lock_modified_date( $data, $post_id ) {
		if ( ! Editor::can_add_lock_modified_date() ) {
			return false;
		}

		if ( Param::request( 'action' ) === 'et_fb_ajax_save' ) {
			if (
				empty( $_REQUEST['et_fb_save_nonce'] ) ||
				! wp_verify_nonce( Param::request( 'et_fb_save_nonce' ), 'et_fb_save_nonce' )
			) {
				return false;
			}

			$options = ! empty( $_REQUEST['options'] ) ? $_REQUEST['options'] : [];
			return ! empty( $options['conditional_tags'] ) && ! empty( $options['conditional_tags']['lock_modified_date'] );
		}

		if ( Param::request( 'action' ) === 'elementor_ajax' ) {
			return wp_verify_nonce( Param::request( '_nonce' ), 'elementor_ajax' ) && ! empty( $_REQUEST['lock_modified_date'] );
		}

		return isset( $data['lock_modified_date'] ) ? $data['lock_modified_date'] : Helper::get_post_meta( 'lock_modified_date', $post_id );
	}
}
