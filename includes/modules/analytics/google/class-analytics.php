<?php
/**
 *  Google Analytics.
 *
 * @since      1.0.34
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Google;

use RankMath\Helpers\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics class.
 */
class Analytics extends Adsense {

	/**
	 * Get analytics accounts.
	 *
	 * @return array
	 */
	public function get_analytics_accounts() {
		$accounts = [];
		$response = $this->http_get( 'https://www.googleapis.com/analytics/v3/management/accountSummaries' );
		if ( ! $this->is_success() || isset( $response->error ) ) {
			return $accounts;
		}

		foreach ( $response['items'] as $account ) {
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
					$properties[ $property_id ]['profiles'][] = $profile;
				}
			}

			$accounts[ $account_id ] = [
				'name'       => $account['name'],
				'properties' => $properties,
			];
		}

		return $accounts;
	}

	/**
	 * Query analytics data from google client api.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 *
	 * @return array
	 */
	public function get_analytics( $start_date, $end_date ) {
		$args = [
			'viewId'                 => $this->get_view_id(),
			'pageSize'               => $this->get_row_limit(),
			'dateRanges'             => [
				[
					'startDate' => $start_date,
					'endDate'   => $end_date,
				],
			],
			'metrics'                => [
				[ 'expression' => 'ga:pageviews' ],
				[ 'expression' => 'ga:visitors' ],
			],
			'dimensions'             => [
				[ 'name' => 'ga:date' ],
				[ 'name' => 'ga:pagePath' ],
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

		$options = get_option( 'rank_math_google_analytic_options', [] );
		if ( ! empty( $options ) && 'all' !== $options['country'] ) {
			$args['dimensionFilterClauses'][0]['filters'][] = [
				'dimensionName' => 'ga:countryIsoCode',
				'operator'      => 'EXACT',
				'expressions'   => $options['country'],
			];
		}

		$response = $this->http_post(
			'https://analyticsreporting.googleapis.com/v4/reports:batchGet',
			[
				'reportRequests' => [ $args ],
			]
		);

		if ( ! $this->is_success() || ! isset( $response['reports'], $response['reports'][0]['data']['rows'] ) ) {
			return false;
		}

		return $response['reports'][0]['data']['rows'];
	}

	/**
	 * Add analytics web view
	 *
	 * @param string $account_id  Account ID to create the view (profile) for.
	 * @param string $property_id Web property ID to create the view (profile) for.
	 * @param string $view_name   View name.
	 */
	public function add_analytics_web_view( $account_id, $property_id, $view_name ) {
		$url = 'https://www.googleapis.com/analytics/v3/management/accounts/' . $account_id . '/webproperties/' . $property_id . '/profiles';

		return $this->http_post( $url, [ 'name' => $view_name ] );
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
			$rank_math_view_id = $options['view_id'];
		}

		return $rank_math_view_id;
	}

	/**
	 * Get site url.
	 *
	 * @return string
	 */
	public static function get_site_url() {
		static $rank_math_site_url;

		if ( is_null( $rank_math_site_url ) ) {
			$default            = trailingslashit( strtolower( home_url() ) );
			$rank_math_site_url = get_option( 'rank_math_google_analytic_profile', [ 'profile' => $default ] );
			$rank_math_site_url = $rank_math_site_url['profile'];
		}

		return $rank_math_site_url;
	}
}
