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
use Exception;
use WP_Error;
use WP_REST_Request;
use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Analytics\DB;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Posts class.
 */
class Posts extends Objects {

	/**
	 * Get post data.
	 *
	 * @param int $id Post id.
	 *
	 * @return object
	 */
	public function get_post( $id ) {
		$post = DB::objects()
			->where( 'object_id', $id )
			->one();

		if ( is_null( $post ) ) {
			return [ 'errorMessage' => esc_html__( 'Sorry, no post found for given id.', 'rank-math' ) ];
		}

		$metrices = $this->get_analytics_data(
			[
				'pages'     => [ $post->page ],
				'pageview'  => true,
				'sub_where' => " AND page = '{$post->page}'",
			]
		);
		if ( ! empty( $metrices ) ) {
			$metrices = current( $metrices );
		}

		// Keywords.
		$keywords = DB::analytics()
			->distinct()
			->selectCount( 'query', 'keywords' )
			->whereLike( 'page', $post->page, '%', '' )
			->whereBetween( 'created', [ $this->start_date, $this->end_date ] )
			->getVar();

		$old_keywords = DB::analytics()
			->distinct()
			->selectCount( 'query', 'keywords' )
			->whereLike( 'page', $post->page, '%', '' )
			->whereBetween( 'created', [ $this->compare_start_date, $this->compare_end_date ] )
			->getVar();

		$post->keywords = [
			'total'      => (int) $keywords,
			'previous'   => (int) $old_keywords,
			'difference' => $keywords - $old_keywords,
		];

		$post = apply_filters( 'rank_math/analytics/single/report', $post, $this );

		return array_merge(
			(array) $post,
			(array) $metrices
		);
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

		// Pagination.
		$per_page = 25;
		$offset   = ( $request->get_param( 'page' ) - 1 ) * $per_page;

		$objects = $this->get_objects_by_score( $request );
		$pages   = \array_keys( $objects['rows'] );
		$console = $this->get_analytics_data(
			[
				'limit'     => "LIMIT {$offset}, {$per_page}",
				'sub_where' => " AND page IN ('" . join( "', '", $pages ) . "')",
			]
		);

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

		return [
			'rows'      => $new_rows,
			'rowsFound' => $objects['rowsFound'],
		];
	}
}
