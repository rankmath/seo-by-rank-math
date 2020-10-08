<?php
/**
 *  Google AdSense.
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
 * AdSense class.
 */
class Adsense extends PageSpeed {

	/**
	 * Get adsense accounts.
	 *
	 * @return array
	 */
	public function get_adsense_accounts() {
		$accounts = [];
		$response = $this->http_get( 'https://www.googleapis.com/adsense/v1.4/accounts' );
		if ( ! $this->is_success() || isset( $response->error ) ) {
			return $accounts;
		}

		foreach ( $response['items'] as $account ) {
			if ( 'adsense#account' !== $account['kind'] ) {
				continue;
			}

			$accounts[ $account['id'] ] = [
				'name' => $account['name'],
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
	public function get_adsense( $start_date, $end_date ) {
		if ( ! $this->get_adsense_id() ) {
			return;
		}

		$request  = Security::add_query_arg_raw(
			[
				'accountId' => $this->get_adsense_id(),
				'startDate' => $start_date,
				'endDate'   => $end_date,
				'dimension' => 'DATE',
				'metric'    => 'EARNINGS',
			],
			'https://www.googleapis.com/adsense/v1.4/reports'
		);
		$response = $this->http_get( $request );

		if ( ! $this->is_success() || ! isset( $response['rows'] ) ) {
			return false;
		}

		return $response['rows'];
	}

	/**
	 * Get adsense id.
	 *
	 * @return string
	 */
	public static function get_adsense_id() {
		static $rank_math_adsense_id;

		if ( is_null( $rank_math_adsense_id ) ) {
			$options              = get_option( 'rank_math_google_analytic_options' );
			$rank_math_adsense_id = isset( $options['adsense_id'] ) ? $options['adsense_id'] : false;
		}

		return $rank_math_adsense_id;
	}
}
