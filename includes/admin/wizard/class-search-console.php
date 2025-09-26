<?php
/**
 * The Search Console wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Admin\Admin_Helper;
use RankMath\Google\Authentication;
use RankMath\Google\Permissions;
use RankMath\Analytics\Email_Reports;
use RankMath\Analytics\Workflow\Objects;
use RankMath\Analytics\Workflow\Console;
use RankMath\Analytics\Workflow\Inspections;


defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Search_Console implements Wizard_Step {

	/**
	 * Get Localized data to be used in the Analytics step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		$all_services = get_option(
			'rank_math_analytics_all_services',
			[
				'isVerified'           => '',
				'inSearchConsole'      => '',
				'hasSitemap'           => '',
				'hasAnalytics'         => '',
				'hasAnalyticsProperty' => '',
				'homeUrl'              => '',
				'sites'                => '',
				'accounts'             => [],
				'adsenseAccounts'      => [],
			]
		);
		$analytics    = wp_parse_args(
			get_option( 'rank_math_google_analytic_options' ),
			[
				'adsense_id'       => '',
				'account_id'       => '',
				'property_id'      => '',
				'view_id'          => '',
				'measurement_id'   => '',
				'stream_name'      => '',
				'country'          => 'all',
				'install_code'     => false,
				'anonymize_ip'     => false,
				'local_ga_js'      => false,
				'exclude_loggedin' => false,
			]
		);

		$page         = Param::get( 'page', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
		$page         = in_array( $page, [ 'rank-math-options-general', 'rank-math-analytics' ], true ) ? 'rank-math-options-general' : 'rank-math-wizard&step=analytics';
		$activate_url = Admin_Helper::get_activate_url( admin_url( 'admin.php?analytics=1&page=' . $page ) );

		$profile = wp_parse_args(
			get_option( 'rank_math_google_analytic_profile' ),
			[
				'profile'             => '',
				'country'             => 'all',
				'enable_index_status' => true,
				'sites'               => $all_services['sites'],
			]
		);

		return [
			'isSiteConnected'        => Helper::is_site_connected(),
			'isAuthorized'           => Authentication::is_authorized(),
			'isSiteUrlValid'         => Admin_Helper::is_site_url_valid(),
			'hasConsolePermission'   => Permissions::has_console(),
			'hasAnalyticsPermission' => Permissions::has_analytics(),
			'hasAdsensePermission'   => Permissions::has_adsense(),
			'activateUrl'            => $activate_url,
			'authUrl'                => Authentication::get_auth_url(),
			'reconnectGoogleUrl'     => wp_nonce_url( admin_url( 'admin.php?reconnect=google' ), 'rank_math_reconnect_google' ),
			'showEmailReports'       => ! Email_Reports::are_fields_hidden(),
			'searchConsole'          => $profile,
			'console_email_reports'  => Helper::get_settings( 'general.console_email_reports' ),
			'analyticsData'          => $analytics,
			'allServices'            => $all_services,
		];
	}

	/**
	 * Save handler for step.
	 *
	 * @param array $values Values to save.
	 *
	 * @return bool
	 */
	public static function save( $values ) {
		if ( isset( $values['console_email_reports'] ) ) {
			$settings                                     = rank_math()->settings->all_raw();
			$settings['general']['console_email_reports'] = $values['console_email_reports'] ? 'on' : 'off';
			Helper::update_all_settings( $settings['general'], null, null );
		}

		// For Search console.
		if ( isset( $values['searchConsole'] ) && ! empty( $values['searchConsole'] ) ) {
			$search_console_data = $values['searchConsole'];
			$value               = [
				'country'             => sanitize_text_field( $search_console_data['country'] ),
				'profile'             => sanitize_text_field( $search_console_data['profile'] ?? '' ),
				'enable_index_status' => sanitize_text_field( $search_console_data['enable_index_status'] ),
			];
			update_option( 'rank_math_google_analytic_profile', $value );
		}

		// For Analytics.
		if ( isset( $values['analyticsData'] ) && ! empty( $values['analyticsData'] ) ) {
			$analytics_data = $values['analyticsData'];
			$analytic_value = [
				'adsense_id'       => sanitize_text_field( $analytics_data['adsense_id'] ),
				'account_id'       => sanitize_text_field( $analytics_data['account_id'] ),
				'property_id'      => sanitize_text_field( $analytics_data['property_id'] ),
				'view_id'          => sanitize_text_field( $analytics_data['view_id'] ),
				'measurement_id'   => sanitize_text_field( $analytics_data['measurement_id'] ),
				'stream_name'      => sanitize_text_field( $analytics_data['stream_name'] ),
				'country'          => sanitize_text_field( $analytics_data['country'] ),
				'install_code'     => sanitize_text_field( $analytics_data['install_code'] ),
				'anonymize_ip'     => sanitize_text_field( $analytics_data['anonymize_ip'] ),
				'local_ga_js'      => sanitize_text_field( $analytics_data['local_ga_js'] ),
				'exclude_loggedin' => sanitize_text_field( $analytics_data['exclude_loggedin'] ),
			];
			update_option( 'rank_math_google_analytic_options', $analytic_value );
		}

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'rank-math-wizard' === $page ) {
			new Objects();
			new Console();
			new Inspections();
		}

		return true;
	}
}
