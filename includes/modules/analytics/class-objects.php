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

use Exception;
use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Objects class.
 */
class Objects extends Summary {

	/**
	 * Get objects for pages.
	 *
	 * @param  array $pages Array of urls.
	 * @return array
	 */
	public function get_objects( $pages ) {
		if ( empty( $pages ) ) {
			return [];
		}

		$pages = DB::objects()
			->whereIn( 'page', \array_unique( $pages ) )
			->get( ARRAY_A );

		return $this->set_page_as_key( $pages );
	}

	/**
	 * Get page views for pages.
	 *
	 * @param WP_REST_Request $request Filters.
	 *
	 * @return array
	 */
	public function get_objects_by_score( $request ) {
		global $wpdb;

		$min = 0;
		$max = false;

		$filters = [
			'good'   => $request->get_param( 'good' ),
			'ok'     => $request->get_param( 'ok' ),
			'bad'    => $request->get_param( 'bad' ),
			'noData' => $request->get_param( 'noData' ),
		];

		if ( $filters['good'] ) {
			$min = 81;
			$max = 100;
		}

		if ( $filters['ok'] ) {
			$min = 51;
			$max = $max ? $max : 80;
		}

		if ( $filters['bad'] ) {
			$min = 1;
			$max = $max ? $max : 50;
		}

		$per_page = 25;
		$offset   = ( $request->get_param( 'page' ) - 1 ) * $per_page;
		$pages    = DB::objects()
			->found_rows()
			->limit( $per_page, $offset )
			->orderBy( 'created', 'DESC' );

		if ( $max ) {
			$pages->whereBetween( 'seo_score', [ $min, $max ] );
		}

		if ( $filters['noData'] ) {
			$pages->orWhere( 'seo_score', 0 );
		}

		$pages = $pages->get( ARRAY_A );

		return [
			'rows'      => $this->set_page_as_key( $pages ),
			'rowsFound' => DB::objects()->get_found_rows(),
		];
	}
}
