<?php
/**
 * The Updates routine for version 1.0.54
 *
 * @since      1.0.54
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Delete extra accounts in Analytics.
 */
function rank_math_1_0_54_delete_unneeded_analytics_accounts() {
	if ( ! Helper::is_module_active( 'analytics' ) ) {
		return;
	}

	$sc_options = get_option( 'rank_math_google_analytic_profile', [] );
	if ( ! empty( $sc_options['profile'] ) ) {
		$all_accounts = get_option( 'rank_math_analytics_all_services', [] );

		$all_accounts['sites'] = [ $sc_options['profile'] => $sc_options['profile'] ];
		update_option( 'rank_math_analytics_all_services', $all_accounts );
	}

	$ga_options = get_option( 'rank_math_google_analytic_options' );
	if ( ! empty( $ga_options['account_id'] ) ) {
		$all_accounts = get_option( 'rank_math_analytics_all_services', [] );
		if ( isset( $all_accounts['accounts'][ $ga_options['account_id'] ] ) ) {
			foreach ( $all_accounts['accounts'] as $account_id => $account_data ) {
				if ( $account_id != $ga_options['account_id'] ) {
					unset( $all_accounts['accounts'][ $account_id ] );
					continue;
				}
				if ( isset( $account_data['properties'][ $ga_options['property_id'] ] ) ) {
					foreach ( $account_data['properties'] as $property_id => $property_data ) {
						if ( $property_id != $ga_options['property_id'] ) {
							unset( $all_accounts['accounts'][ $account_id ]['properties'][ $property_id ] );
							continue;
						}
					}
				}
			}
			update_option( 'rank_math_analytics_all_services', $all_accounts );
		}
	}

}

rank_math_1_0_54_delete_unneeded_analytics_accounts();
