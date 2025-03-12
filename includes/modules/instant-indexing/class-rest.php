<?php
/**
 * The Global functionality of the plugin.
 *
 * Defines the functionality loaded on admin.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Instant_Indexing;

use RankMath\Helper;
use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Controller;
use WP_REST_Response;
use RankMath\Helpers\Arr;

defined( 'ABSPATH' ) || exit;

/**
 * Rest class.
 */
class Rest extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = \RankMath\Rest\Rest_Helper::BASE . '/in';
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		$namespace = $this->namespace;

		register_rest_route(
			$namespace,
			'/submitUrls',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'submit_urls' ],
					'permission_callback' => [ $this, 'has_permission' ],
					'args'                => [
						'urls' => [
							'description' => __( 'The list of urls to submit to the Instant Indexing API.', 'rank-math' ),
							'type'        => 'string',
							'required'    => true,
						],
					],
				],
			]
		);

		register_rest_route(
			$namespace,
			'/getLog',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_log' ],
					'permission_callback' => [ $this, 'has_permission' ],
					'args'                => [
						'filter' => [
							'description' => __( 'Filter log by type.', 'rank-math' ),
							'type'        => 'string',
							'enum'        => [ 'all', 'manual', 'auto' ],
							'default'     => 'all',
						],
					],
				],
			]
		);

		register_rest_route(
			$namespace,
			'/clearLog',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'clear_log' ],
					'permission_callback' => [ $this, 'has_permission' ],
					'args'                => [
						'filter' => [
							'description' => __( 'Clear log by type.', 'rank-math' ),
							'type'        => 'string',
							'enum'        => [ 'all', 'manual', 'auto' ],
							'default'     => 'all',
						],
					],
				],
			]
		);

		register_rest_route(
			$namespace,
			'/resetKey',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'reset_key' ],
					'permission_callback' => [ $this, 'has_permission' ],
				],
			]
		);
	}

	/**
	 * Submit URLs.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function submit_urls( WP_REST_Request $request ) {
		$urls = $request->get_param( 'urls' );
		if ( empty( $urls ) ) {
			return new WP_Error( 'empty_urls', __( 'No URLs provided.', 'rank-math' ) );
		}

		$urls = Arr::from_string( $urls, "\n" );
		$urls = array_values( array_unique( array_filter( $urls, 'wp_http_validate_url' ) ) );

		if ( ! $urls ) {
			return new WP_Error( 'invalid_urls', __( 'Invalid URLs provided.', 'rank-math' ) );
		}

		$result = Api::get()->submit( $urls );
		if ( ! $result ) {
			return new WP_Error( 'submit_failed', __( 'Failed to submit URLs. See details in the History tab.', 'rank-math' ) );
		}

		$urls_number = count( $urls );
		return new WP_REST_Response(
			[
				'success' => true,
				'message' => sprintf(
					// Translators: %s is the number of URLs submitted.
					_n(
						'Successfully submitted %s URL.',
						'Successfully submitted %s URLs.',
						$urls_number,
						'rank-math'
					),
					$urls_number
				),
			]
		);
	}

	/**
	 * Get log.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_log( WP_REST_Request $request ) {
		$filter = $request->get_param( 'filter' );
		$result = Api::get()->get_log();
		$total  = count( $result );
		foreach ( $result as $key => $value ) {
			$result[ $key ]['timeFormatted'] = wp_date( 'Y-m-d H:i:s', $value['time'] );
			// Translators: placeholder is human-readable time, e.g. "1 hour".
			$result[ $key ]['timeHumanReadable'] = sprintf( __( '%s ago', 'rank-math' ), human_time_diff( $value['time'] ) );

			if ( 'manual' === $filter && empty( $result[ $key ]['manual_submission'] ) ) {
				unset( $result[ $key ] );
			} elseif ( 'auto' === $filter && ! empty( $result[ $key ]['manual_submission'] ) ) {
				unset( $result[ $key ] );
			}
		}

		$result = array_values( array_reverse( $result ) );

		return new WP_REST_Response(
			[
				'data'  => $result,
				'total' => $total,
			]
		);
	}

	/**
	 * Clear log.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function clear_log( WP_REST_Request $request ) {
		Api::get()->clear_log();
		return new WP_REST_Response( [ 'status' => 'ok' ] );
	}

	/**
	 * Reset key.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function reset_key( WP_REST_Request $request ) {
		$api = Api::get();
		$api->reset_key();
		$key      = $api->get_key();
		$location = $api->get_key_location( 'reset_key' );
		return new WP_REST_Response(
			[
				'status'   => 'ok',
				'key'      => $key,
				'location' => $location,
			]
		);
	}

	/**
	 * Determine if the current user can manage instant indexing.
	 *
	 * @return bool
	 */
	public function has_permission() {
		return Helper::has_cap( 'general' );
	}
}
