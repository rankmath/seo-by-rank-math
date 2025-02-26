<?php
/**
 * The Analytics Module
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use stdClass;
use WP_Error;
use WP_REST_Request;
use RankMath\Helper;
use RankMath\Analytics\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Posts class.
 *
 * @method get_analytics_data()
 */
class Posts extends Objects {

	/**
	 * Get post data.
	 *
	 * @param  WP_REST_Request $request post object.
	 *
	 * @return object
	 */
	public function get_post( $request ) {
		$id   = $request->get_param( 'id' );
		$post = DB::objects()
			->where( 'object_id', $id )
			->one();

		if ( is_null( $post ) ) {
			return [ 'errorMessage' => esc_html__( 'Sorry, no post found for given id.', 'rank-math' ) ];
		}

		$post->admin_url = admin_url();
		$post->home_url  = home_url();

		return apply_filters( 'rank_math/analytics/post_data', (array) $post, $request );
	}

	/**
	 * Get posts by objects.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_posts_rows_by_objects( WP_REST_Request $request ) {
		$pre = apply_filters( 'rank_math/analytics/get_posts_rows_by_objects', false, $request );
		if ( false !== $pre ) {
			return $pre;
		}

		$cache_group = 'rank_math_posts_rows_by_objects';
		$cache_key   = $this->generate_hash( $request );
		$data        = $this->get_cache( $cache_key, $cache_group );
		if ( false !== $data ) {
			return rest_ensure_response( $data );
		}

		// Pagination.
		$per_page = 25;
		$offset   = ( $request->get_param( 'page' ) - 1 ) * $per_page;

		// Get objects filtered by seo score range and it's analytics data.
		$objects = $this->get_objects_by_score( $request );
		$pages   = \array_keys( $objects['rows'] );
		$console = $this->get_analytics_data(
			[
				'offset'    => 0, // Here offset should always zero.
				'perpage'   => $objects['rowsFound'],
				'sub_where' => " AND page IN ('" . join( "', '", $pages ) . "')",
			]
		);

		// Construct return data.
		$new_rows = [];
		foreach ( $objects['rows'] as $object ) {
			$page = $object['page'];

			if ( isset( $console[ $page ] ) ) {
				$object = \array_merge( $console[ $page ], $object );
			}

			if ( ! isset( $object['links'] ) ) {
				$object['links'] = new stdClass();
			}

			$new_rows[ $page ] = $object;
		}

		$count = count( $new_rows );

		if ( $offset + 25 <= $count ) {
			$new_rows = array_slice( $new_rows, $offset, 25 );

		} else {
			$rest     = $count - $offset;
			$new_rows = array_slice( $new_rows, $offset, $rest );
		}
		if ( empty( $new_rows ) ) {
			$new_rows['response'] = 'No Data';
		}

		$output = [
			'rows'      => $new_rows,
			'rowsFound' => $objects['rowsFound'],
		];

		$this->set_cache( $cache_key, $output, $cache_group, DAY_IN_SECONDS );

		return rest_ensure_response( $output );
	}
}
