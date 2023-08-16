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
use RankMath\Google\Api;
use MyThemeShop\Helpers\Str;
use RankMath\Analytics\Workflow\Base;

/**
 * Analytics class.
 */
class Analytics extends Request {

	/**
	 * Get analytics accounts.
	 */
	public function get_analytics_accounts() {
		$accounts    = [];
		$v3_response = $this->http_get( 'https://www.googleapis.com/analytics/v3/management/accountSummaries' );
		$v3_data     = true;
		if ( ! $this->is_success() || isset( $v3_response->error ) ) {
			$v3_data = false;
		}
		if ( false !== $v3_data ) {
			foreach ( $v3_response['items'] as $account ) {
				if ( 'analytics#accountSummary' !== $account['kind'] ) {
					continue;
				}

				$properties = [];
				$account_id = $account['id'];

				foreach ( $account['webProperties'] as $property ) {
					$property_id = $property['id'];

					$properties[ $property_id ] = [
						'name'       => $property['name'],
						'id'         => $property['id'],
						'url'        => $property['websiteUrl'],
						'account_id' => $account_id,
					];

					foreach ( $property['profiles'] as $profile ) {
						unset( $profile['kind'] );
						$properties[ $property_id ]['profiles'][ $profile['id'] ] = $profile;
					}
				}

				$accounts[ $account_id ] = [
					'name'       => $account['name'],
					'properties' => $properties,
				];
			}
		}

		return $this->add_ga4_accounts( $accounts );
	}
	/**
	 * Get GA4 accounts info.
	 *
	 * @param array $accounts GA3 accounts info or empty array.
	 *
	 * @return array $accounts with added ga4 accounts
	 */
	public function add_ga4_accounts( $accounts ) {

		$v4_response = $this->http_get( 'https://analyticsadmin.googleapis.com/v1alpha/accountSummaries?pageSize=200' );
		if ( ! $this->is_success() || isset( $v4_response->error ) ) {
			return $accounts;
		}
		foreach ( $v4_response['accountSummaries'] as $account ) {
			if ( empty( $account['propertySummaries'] ) ) {
				continue;
			}

			$properties = [];
			$account_id = str_replace( 'accounts/', '', $account['account'] );

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
	 * Query analytics data from google client api.
	 *
	 * @param array   $options Analytics options.
	 * @param boolean $days    Whether to include dates.
	 *
	 * @return array
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

		// Request params.
		$row_limit = isset( $options['row_limit'] ) ? $options['row_limit'] : Api::get()->get_row_limit();
		$country   = isset( $options['country'] ) ? $options['country'] : '';
		if ( ! empty( $stored['country'] ) && 'all' !== $stored['country'] ) {
			$country = $stored['country'];
		}

		// Check the property for old Google Analytics.
		if ( Str::starts_with( 'UA-', $property_id ) ) {
			$args = [
				'viewId'                 => $view_id,
				'pageSize'               => $row_limit,
				'dateRanges'             => [
					[
						'startDate' => $start_date,
						'endDate'   => $end_date,
					],
				],
				'dimensionFilterClauses' => [
					[
						'filters' => [
							[
								'dimensionName' => 'ga:medium',
								'operator'      => 'EXACT',
								'expressions'   => 'organic',
							],
						],
					],
				],
			];

			// Include only dates.
			if ( true === $days ) {
				$args = wp_parse_args(
					[
						'dimensions' => [
							[ 'name' => 'ga:date' ],
						],
					],
					$args
				);
			} else {
				$args = wp_parse_args(
					[
						'metrics'    => [
							[ 'expression' => 'ga:pageviews' ],
							[ 'expression' => 'ga:users' ],
						],
						'dimensions' => [
							[ 'name' => 'ga:date' ],
							[ 'name' => 'ga:pagePath' ],
							[ 'name' => 'ga:hostname' ],
						],
						'orderBys'   => [
							[
								'fieldName' => 'ga:pageviews',
								'sortOrder' => 'DESCENDING',
							],
						],
					],
					$args
				);

				// Add country.
				if ( ! $country ) {
					$args['dimensionFilterClauses'][0]['filters'][] = [
						'dimensionName' => 'ga:countryIsoCode',
						'operator'      => 'EXACT',
						'expressions'   => $country,
					];
				}
			}

			$response = Api::get()->http_post(
				'https://analyticsreporting.googleapis.com/v4/reports:batchGet',
				[
					'reportRequests' => [ $args ],
				]
			);

			Api::get()->log_failed_request( $response, 'analytics', $start_date, func_get_args() );

			if ( ! Api::get()->is_success() ) {
				return new WP_Error( 'request_failed', __( 'The Google Analytics request failed.', 'rank-math' ) );
			}

			if ( ! isset( $response['reports'], $response['reports'][0]['data']['rows'] ) ) {
				return false;
			}

			return $response['reports'][0]['data']['rows'];
		}

		// Request for GA4 API.
		$args = [
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
		} else {
			$args = wp_parse_args(
				[
					'dimensions' => [
						[ 'name' => 'hostname' ],
						[ 'name' => 'pagePath' ],
						[ 'name' => 'countryId' ],
						[ 'name' => 'sessionMedium' ],
					],
					'metrics'    => [
						[ 'name' => 'screenPageViews' ],
						[ 'name' => 'totalUsers' ],
					],
				],
				$args
			);

			// Include country.
			if ( $country ) {
				$args['dimensionFilter']['andGroup']['expressions'][] = [
					'filter' => [
						'fieldName'    => 'countryId',
						'stringFilter' => [
							'matchType' => 'EXACT',
							'value'     => $country,
						],
					],
				];
			}
		}

		$workflow = 'analytics';
		Api::get()->set_workflow( $workflow );
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

		return $response['rows'];
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
