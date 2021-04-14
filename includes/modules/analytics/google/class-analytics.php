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
					$properties[ $property_id ]['profiles'][ $profile['id'] ] = $profile;
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
