<?php
/**
 * The Global functionality of the plugin.
 *
 * Defines the functionality loaded on admin.
 *
 * @since      1.0.71
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ContentAI;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Controller;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Rest class.
 */
class Rest extends WP_REST_Controller {

	/**
	 * Registered data.
	 * 
	 * @var array|false
	 */
	private $registered;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace  = \RankMath\Rest\Rest_Helper::BASE . '/ca';
		$this->registered = Admin_Helper::get_registration_data();
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/researchKeyword',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'research_keyword' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/getCredits',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_credits' ],
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
		if ( ! Helper::has_cap( 'content_ai' ) || empty( $this->registered ) ) {
			return new WP_Error(
				'rest_cannot_access',
				__( 'Sorry, only authenticated users can research the keyword.', 'rank-math' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Get Content AI Credits.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return int Credits.
	 */
	public function get_credits( WP_REST_Request $request ) {
		return Helper::get_content_ai_credits( true );
	}

	/**
	 * Research a keyword.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function research_keyword( WP_REST_Request $request ) {
		$object_id    = $request->get_param( 'objectID' );
		$country      = $request->get_param( 'country' );
		$keyword      = mb_strtolower( $request->get_param( 'keyword' ) );
		$force_update = $request->get_param( 'force_update' );
		$keyword_data = get_option( 'rank_math_ca_data' );

		if ( ! in_array( get_post_type( $object_id ), (array) Helper::get_settings( 'general.content_ai_post_types' ), true ) ) {
			return [
				'data' => esc_html__( 'Content AI is not enabled on this Post type.', 'rank-math' ),
			];
		}

		if ( ! apply_filters( 'rank_math/content_ai/call_api', true ) ) {
			return [
				'data' => 'show_dummy_data',
			];
		}

		if (
			! $force_update &&
			! empty( $keyword_data ) &&
			! empty( $keyword_data[ $country ] ) &&
			! empty( $keyword_data[ $country ][ $keyword ] )
		) {
			update_post_meta(
				$object_id,
				'rank_math_ca_keyword',
				[
					'keyword' => $keyword,
					'country' => $country,
				]
			);

			return [
				'data'    => $keyword_data[ $country ][ $keyword ],
				'keyword' => $keyword,
			];
		}

		$data = $this->get_researched_data( $keyword, $country, $force_update );
		if ( ! empty( $data['error'] ) ) {
			return $this->get_errored_data( $data['error'] );
		}

		$credits = $data['remaining_credits'];
		$data    = $data['data']['details'];
		$this->get_recommendations( $data );

		update_post_meta(
			$object_id,
			'rank_math_ca_keyword',
			[
				'keyword' => $keyword,
				'country' => $country,
			]
		);
		$keyword_data[ $country ][ $keyword ] = $data;
		update_option( 'rank_math_ca_data', $keyword_data, false );
		update_option( 'rank_math_ca_credits', $credits, false );

		return [
			'data'    => $keyword_data[ $country ][ $keyword ],
			'credits' => $credits,
			'keyword' => $keyword,
		];
	}

	/**
	 * Get data from the API.
	 *
	 * @param string $keyword      Researched keyword.
	 * @param string $country      Researched country.
	 * @param bool   $force_update Whether to force update the researched data.
	 *
	 * @return array
	 */
	private function get_researched_data( $keyword, $country, $force_update = false ) {
		$args = [
			'username' => rawurlencode( $this->registered['username'] ),
			'api_key'  => rawurlencode( $this->registered['api_key'] ),
			'keyword'  => rawurlencode( $keyword ),
			'site_url' => rawurlencode( Helper::get_home_url() ),
			'new_api'  => 1,
		];

		if ( 'all' !== $country ) {
			$args['locale'] = rawurlencode( $country );
		}

		if ( $force_update ) {
			$args['force_refresh'] = 1;
		}

		$url = add_query_arg(
			$args,
			'https://rankmath.com/wp-json/rankmath/v1/contentAi'
		);

		$data = wp_remote_get(
			$url,
			[
				'timeout' => 60,
			]
		);

		$response_code = wp_remote_retrieve_response_code( $data );
		if ( 200 !== $response_code ) {
			return [
				'error' => $data['response']['message'],
			];
		}

		$data = wp_remote_retrieve_body( $data );
		$data = json_decode( $data, true );

		if ( empty( $data['error'] ) && empty( $data['data']['details'] ) ) {
			return [
				'error' => esc_html__( 'No data found for the researched keyword.', 'rank-math' ),
			];
		}

		return $data;
	}

	/**
	 * Get errored data.
	 *
	 * @param array $error Error data received from the API.
	 *
	 * @return array
	 */
	private function get_errored_data( $error ) {
		if ( empty( $error['code'] ) ) {
			return [
				'data' => $error,
			];
		}

		if ( 'invalid_domain' === $error['code'] ) {
			return [
				'data' => esc_html__( 'This feature is not available on the localhost.', 'rank-math' ),
			];
		}

		if ( 'domain_limit_reached' === $error['code'] ) {
			return [
				'data' => esc_html__( 'You have used all the free credits which are allowed to this domain.', 'rank-math' ),
			];
		}

		return [
			'data'    => '',
			'credits' => $error['code'],
		];
	}

	/**
	 * Get the Recommendations data.
	 *
	 * @param array $data Researched data.
	 */
	private function get_recommendations( &$data ) {
		foreach ( $data['recommendations'] as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			$data['recommendations'][ $key ]['total'] = array_sum( $value );
		}
	}
}
