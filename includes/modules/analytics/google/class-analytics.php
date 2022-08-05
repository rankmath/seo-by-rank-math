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
}
