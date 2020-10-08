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

namespace RankMath\Analytics;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Controller;
use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\SEO_Analysis\SEO_Analyzer;

defined( 'ABSPATH' ) || exit;

/**
 * Rest class.
 */
class Rest extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = \RankMath\Rest\Rest_Helper::BASE . '/analytics';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/dashboard',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_dashboard' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/keywordsOverview',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_keywords_overview' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/postsOverview',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_posts_overview' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/postsSummary',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ Stats::get(), 'get_posts_summary' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/postsRows',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ Stats::get(), 'get_posts_rows_by_pageviews' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/postsRowsByObjects',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ Stats::get(), 'get_posts_rows_by_objects' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/post/(?P<id>\d+)',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_post' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/keywordsSummary',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ Stats::get(), 'get_keywords_summary' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/analyticsSummary',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_analytics_summary' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/keywordsRows',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ Stats::get(), 'get_keywords_rows' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/getTrackedKeywords',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_tracked_keywords' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/addTrackKeyword',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'add_track_keyword' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/removeTrackKeyword',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'remove_track_keyword' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/userPreferences',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_user_preferences' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/getPagespeed',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_pagespeed' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/trackedKeywordsOverview',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_tracked_keywords_overview' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);
	}

	/**
	 * Determines if the current user can manage analytics.
	 *
	 * @return true
	 */
	public function has_permission() {
		return current_user_can( 'rank_math_analytics' );
	}

	/**
	 * Add track keyword to DB.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_user_preferences( WP_REST_Request $request ) {
		$pref = $request->get_param( 'preferences' );
		if ( empty( $pref ) ) {
			return new WP_Error(
				'param_value_empty',
				esc_html__( 'Sorry, no preference found.', 'rank-math' )
			);
		}

		update_user_meta(
			get_current_user_id(),
			'rank_math_analytics_table_columns',
			$pref
		);

		return true;
	}

	/**
	 * Add track keyword to DB.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function add_track_keyword( WP_REST_Request $request ) {
		$keyword = $request->get_param( 'keyword' );
		if ( empty( $keyword ) ) {
			return new WP_Error(
				'param_value_empty',
				esc_html__( 'Sorry, no keyword found.', 'rank-math' )
			);
		}

		Stats::get()->add_track_keyword( $keyword );
		return true;
	}

	/**
	 * Remove track keyword to DB.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function remove_track_keyword( WP_REST_Request $request ) {
		$keyword = $request->get_param( 'keyword' );
		if ( empty( $keyword ) ) {
			return new WP_Error(
				'param_value_empty',
				esc_html__( 'Sorry, no keyword found.', 'rank-math' )
			);
		}

		Stats::get()->remove_track_keyword( $keyword );
		return true;
	}

	/**
	 * Get dashboard.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_post( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		if ( empty( $id ) ) {
			return new WP_Error(
				'param_value_empty',
				esc_html__( 'Sorry, no post id found.', 'rank-math' )
			);
		}

		return rest_ensure_response( Stats::get()->get_post( $id ) );
	}

	/**
	 * Get dashboard.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_dashboard( WP_REST_Request $request ) {
		return rest_ensure_response(
			[
				'stats'        => Stats::get()->get_analytics_summary(),
				'optimization' => Stats::get()->get_optimization_summary(),
			]
		);
	}

	/**
	 * Get dashboard.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_analytics_summary( WP_REST_Request $request ) {
		return rest_ensure_response(
			[
				'summary'      => Stats::get()->get_posts_summary(),
				'optimization' => Stats::get()->get_optimization_summary(),
			]
		);
	}

	/**
	 * Get keywords overview.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_keywords_overview( WP_REST_Request $request ) {
		return rest_ensure_response(
			[
				'winningKeywords' => Stats::get()->get_winning_keywords(),
				'losingKeywords'  => Stats::get()->get_losing_keywords(),
				'topKeywords'     => Stats::get()->get_top_keywords(),
				'positionGraph'   => Stats::get()->get_top_position_graph(),
			]
		);
	}

	/**
	 * Get keywords overview.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_tracked_keywords_overview( WP_REST_Request $request ) {
		return rest_ensure_response(
			[
				'winningKeywords' => Stats::get()->get_tracked_winning_keywords(),
				'losingKeywords'  => Stats::get()->get_tracked_losing_keywords(),
			]
		);
	}

	/**
	 * Get keywords overview.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_tracked_keywords( WP_REST_Request $request ) {
		return rest_ensure_response(
			[
				'summary' => Stats::get()->get_tracked_keywords_summary(),
				'rows'    => Stats::get()->get_tracked_keywords(),
			]
		);
	}

	/**
	 * Get posts overview.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_posts_overview( WP_REST_Request $request ) {
		return rest_ensure_response(
			[
				'winningPosts' => Stats::get()->get_winning_posts(),
				'losingPosts'  => Stats::get()->get_losing_posts(),
			]
		);
	}

	/**
	 * Get posts overview.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_pagespeed( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		if ( empty( $id ) ) {
			return new WP_Error(
				'param_value_empty',
				esc_html__( 'Sorry, no record id found.', 'rank-math' )
			);
		}

		$post_id = $request->get_param( 'objectID' );
		if ( empty( $id ) ) {
			return new WP_Error(
				'param_value_empty',
				esc_html__( 'Sorry, no post id found.', 'rank-math' )
			);
		}

		$force = \boolval( $request->get_param( 'force' ) );

		if ( Helper::is_localhost() ) {
			return [
				'page_score'          => 0,
				'desktop_interactive' => 0,
				'desktop_pagescore'   => 0,
				'mobile_interactive'  => 0,
				'mobile_pagescore'    => 0,
				'pagespeed_refreshed' => current_time( 'mysql' ),
			];
		}

		$url = get_permalink( $post_id );
		$pre = apply_filters( 'rank_math/analytics/pre_pagespeed', false, $post_id, $force );
		if ( false !== $pre ) {
			return $pre;
		}

		if ( $force || $this->should_update_pagespeed( $id ) ) {
			// Page Score.
			$analyzer = new SEO_Analyzer();
			$score    = $analyzer->get_page_score( $url );
			$update   = [];
			if ( $score > 0 ) {
				$update['page_score'] = $score;
			}

			// PageSpeed desktop.
			$desktop = Api::get()->get_pagespeed( $url, 'desktop' );
			if ( ! empty( $desktop ) ) {
				$update                        = \array_merge( $update, $desktop );
				$update['pagespeed_refreshed'] = current_time( 'mysql' );
			}

			// PageSpeed mobile.
			$mobile = Api::get()->get_pagespeed( $url, 'mobile' );
			if ( ! empty( $mobile ) ) {
				$update                        = \array_merge( $update, $mobile );
				$update['pagespeed_refreshed'] = current_time( 'mysql' );
			}
		}

		if ( ! empty( $update ) ) {
			$update['id'] = $id;
			DB::update_object( $update );
			$update['object_id'] = $post_id;
		}

		return empty( $update ) ? false : $update;
	}

	/**
	 * Should update pagespeed record.
	 *
	 * @param  int $id      Database row id.
	 * @return bool
	 */
	private function should_update_pagespeed( $id ) {
		$record = DB::objects()->where( 'id', $id )->one();

		return \time() > ( \strtotime( $record->pagespeed_refreshed ) + ( DAY_IN_SECONDS * 7 ) );
	}
}
