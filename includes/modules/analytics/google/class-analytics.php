<?php
/**
 *  Google Analytics.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Google;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Helpers\Str;
use RankMath\Analytics\Workflow\Base;

/**
 * Analytics class.
 */
class Analytics extends Request {

	/**
	 * Connection status key.
	 */
	const CONNECTION_STATUS_KEY = 'rank_math_analytics_connection_error';

	/**
	 * Get analytics accounts.
	 */
	public function get_analytics_accounts() {
		$accounts        = [];
		$next_page_token = '';
		$base_url        = 'https://analyticsadmin.googleapis.com/v1beta/accountSummaries?pageSize=200';

		do {
			$url = $base_url;
			if ( $next_page_token ) {
				$url .= '&pageToken=' . $next_page_token;
			}

			$response = $this->http_get( $url );

			if ( ! $response || ! $this->is_success() || isset( $response['error'] ) ) {
				break;
			}

			if ( ! empty( $response['accountSummaries'] ) ) {
				foreach ( $response['accountSummaries'] as $account ) {
					if ( empty( $account['propertySummaries'] ) ) {
						continue;
					}

					$account_id = str_replace( 'accounts/', '', $account['account'] );

					$accounts[ $account_id ] = [
						'name'       => $account['displayName'],
						'properties' => [],
					];

					foreach ( $account['propertySummaries'] as $property ) {
						$property_id = str_replace( 'properties/', '', $property['property'] );

						$accounts[ $account_id ]['properties'][ $property_id ] = [
							'name'       => $property['displayName'],
							'id'         => $property_id,
							'account_id' => $account_id,
							'type'       => 'GA4',
						];
					}
				}
			}

			$next_page_token = isset( $response['nextPageToken'] ) ? $response['nextPageToken'] : '';
		} while ( $next_page_token );

		return $accounts;
	}

	/**
	 * Check if google analytics is connected.
	 *
	 * @return boolean Returns True if the google analytics is connected, otherwise False.
	 */
	public static function is_analytics_connected() {
		$account = wp_parse_args(
			get_option( 'rank_math_google_analytic_options' ),
			[ 'view_id' => '' ]
		);

		return ! empty( $account['view_id'] );
	}

	/**
	 * Is valid connection
	 */
	public static function is_valid_connection() {
		return Api::get()->get_connection_status( self::CONNECTION_STATUS_KEY );
	}

	/**
	 * Test connection
	 */
	public static function test_connection() {
		return Api::get()->check_connection_status( self::CONNECTION_STATUS_KEY, [ __CLASS__, 'get_sample_response' ] );
	}

	/**
	 * Get sample response to test connection.
	 *
	 * @return array|false|WP_Error
	 */
	public static function get_sample_response() {
		return self::get_analytics(
			[
				'row_limit' => 1,
			],
			true
		);
	}

	/**
	 * Query analytics data from google client api.
	 *
	 * @param array   $options Analytics options.
	 * @param boolean $days    Whether to include dates.
	 *
	 * @return array|false|WP_Error
	 */
	public static function get_analytics( $options = [], $days = false ) {
		// Check view ID.
		$view_id = isset( $options['view_id'] ) ? $options['view_id'] : self::get_view_id();
		if ( ! $view_id ) {
			return false;
		}

		$stored = get_option(
			'rank_math_google_analytic_options',
			[
				'account_id'       => '',
				'property_id'      => '',
				'view_id'          => '',
				'measurement_id'   => '',
				'stream_name'      => '',
				'country'          => '',
				'install_code'     => '',
				'anonymize_ip'     => '',
				'local_ga_js'      => '',
				'exclude_loggedin' => '',
			]
		);

		// Check property ID.
		$property_id = isset( $options['property_id'] ) ? $options['property_id'] : $stored['property_id'];
		if ( ! $property_id ) {
			return false;
		}

		// Check dates.
		$dates      = Base::get_dates();
		$start_date = isset( $options['start_date'] ) ? $options['start_date'] : $dates['start_date'];
		$end_date   = isset( $options['end_date'] ) ? $options['end_date'] : $dates['end_date'];
		if ( ! $start_date || ! $end_date ) {
			return false;
		}

		// Request for GA4 API.
		$args = [
			'limit'           => isset( $options['row_limit'] ) ? $options['row_limit'] : Api::get()->get_row_limit(),
			'dateRanges'      => [
				[
					'startDate' => $start_date,
					'endDate'   => $end_date,
				],
			],
			'dimensionFilter' => [
				'andGroup' => [
					'expressions' => [
						[
							'filter' => [
								'fieldName'    => 'streamId',
								'stringFilter' => [
									'matchType' => 'EXACT',
									'value'     => $view_id,
								],
							],
						],
						[
							'filter' => [
								'fieldName'    => 'sessionMedium',
								'stringFilter' => [
									'matchType' => 'EXACT',
									'value'     => 'organic',
								],
							],
						],
					],
				],
			],
		];

		$dimensions = isset( $options['dimensions'] ) ? $options['dimensions'] : [];
		if ( $dimensions ) {
			$args = wp_parse_args(
				[
					'dimensions' => $dimensions,
				],
				$args
			);
		}

		$metrics = isset( $options['metrics'] ) ? $options['metrics'] : [];
		if ( $metrics ) {
			$args = wp_parse_args(
				[
					'metrics' => $metrics,
				],
				$args
			);
		}

		// Include only dates.
		if ( true === $days ) {
			$args = wp_parse_args(
				[
					'dimensions' => [
						[ 'name' => 'date' ],
					],
				],
				$args
			);
		}

		$workflow = 'analytics';
		Api::get()->set_workflow( $workflow );

		// Request.
		$response = Api::get()->http_post(
			'https://analyticsdata.googleapis.com/v1beta/properties/' . $property_id . ':runReport',
			$args
		);

		Api::get()->log_failed_request( $response, $workflow, $start_date, func_get_args() );

		if ( ! Api::get()->is_success() ) {
			return new WP_Error( 'request_failed', __( 'The Google Analytics Console request failed.', 'rank-math' ) );
		}

		if ( ! isset( $response['rows'] ) ) {
			return false;
		}

		$dimensions = isset( $response['dimensionHeaders'] ) ? array_column( $response['dimensionHeaders'], 'name' ) : [];
		$metrics    = isset( $response['metricHeaders'] ) ? array_column( $response['metricHeaders'], 'name' ) : [];

		$rows = [];
		foreach ( $response['rows'] as $row ) {
			$item = [];

			if ( isset( $row['dimensionValues'] ) ) {
				foreach ( $row['dimensionValues'] as $i => $dim ) {
					$item[ $dimensions[ $i ] ] = $dim['value'];
				}
			}

			if ( isset( $row['metricValues'] ) ) {
				foreach ( $row['metricValues'] as $i => $met ) {
					$item[ $metrics[ $i ] ] = (int) $met['value'];
				}
			}

			$rows[] = $item;
		}

		return $rows;
	}

	/**
	 * Get view id.
	 *
	 * @return string
	 */
	public static function get_view_id() {
		static $rank_math_view_id;

		if ( is_null( $rank_math_view_id ) ) {
			$options           = get_option( 'rank_math_google_analytic_options' );
			$rank_math_view_id = ! empty( $options['view_id'] ) ? $options['view_id'] : false;
		}

		return $rank_math_view_id;
	}
}
