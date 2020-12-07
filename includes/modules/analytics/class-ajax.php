<?php
/**
 * The Analytics AJAX
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Google\Console as Google_Analytics;
use RankMath\Google\Authentication;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * AJAX class.
 */
class AJAX {

	use \RankMath\Traits\Ajax;

	/**
	 * The Constructor
	 */
	public function __construct() {
		$this->ajax( 'query_analytics', 'query_analytics' );
		$this->ajax( 'add_site_console', 'add_site_console' );
		$this->ajax( 'analytics_delete_cache', 'delete_cache' );
		$this->ajax( 'disconnect_google', 'disconnect_google' );
		$this->ajax( 'verify_site_console', 'verify_site_console' );
		$this->ajax( 'save_analytic_profile', 'save_analytic_profile' );
		$this->ajax( 'save_analytic_options', 'save_analytic_options' );
		$this->ajax( 'google_check_all_services', 'check_all_services' );
		$this->ajax( 'analytic_start_fetching', 'analytic_start_fetching' );
	}

	/**
	 * Disconnect google tokens.
	 */
	public function disconnect_google() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );
		Api::get()->revoke_token();
		Data_Fetcher::get()->kill_process();

		$this->success();
	}

	/**
	 * Get cache progressively.
	 */
	public function analytic_start_fetching() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$this->should_fetch();

		try {
			Data_Fetcher::get()->start_process( Param::get( 'days', 90, FILTER_VALIDATE_INT ) );
			$this->success( 'Data fetching started in the background.' );
		} catch ( Exception $error ) {
			$this->error( $error->getMessage() );
		}
	}

	/**
	 * Delete cache.
	 */
	public function delete_cache() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$days = Param::get( 'days', false, FILTER_VALIDATE_INT );
		if ( ! $days ) {
			$this->error( esc_html__( 'Not a valid settings founds to delete cache.', 'rank-math' ) );
		}

		DB::delete_by_days( $days );
		Data_Fetcher::get()->kill_process();
		delete_transient( 'rank_math_analytics_data_info' );
		$db_info            = DB::info();
		$db_info['message'] = sprintf( '<div class="rank-math-console-db-info"><span class="dashicons dashicons-calendar-alt"></span> Cached Days: <strong>%s</strong></div>', $db_info['days'] ) .
		sprintf( '<div class="rank-math-console-db-info"><span class="dashicons dashicons-editor-ul"></span> Data Rows: <strong>%s</strong></div>', Str::human_number( $db_info['rows'] ) ) .
		sprintf( '<div class="rank-math-console-db-info"><span class="dashicons dashicons-editor-code"></span> Size: <strong>%s</strong></div>', size_format( $db_info['size'] ) );

		$this->success( $db_info );
	}

	/**
	 * Query analytics.
	 */
	public function query_analytics() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$query = Param::get( 'query' );

		$data = DB::objects()
			->whereLike( 'title', $query )
			->orWhereLike( 'page', $query )
			->limit( 10 )
			->get();

		$this->send( [ 'data' => $data ] );
	}

	/**
	 * Check all google services.
	 */
	public function check_all_services() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$result = [
			'isVerified'           => false,
			'inSearchConsole'      => false,
			'hasSitemap'           => false,
			'hasAnalytics'         => false,
			'hasAnalyticsProperty' => false,
		];

		$result['homeUrl']         = Google_Analytics::get_site_url();
		$result['sites']           = Api::get()->get_sites();
		$result['inSearchConsole'] = $this->is_site_in_search_console();

		if ( $result['inSearchConsole'] ) {
			$result['isVerified'] = Helper::is_localhost() ? true : Api::get()->is_site_verified( Google_Analytics::get_site_url() );
			$result['hasSitemap'] = $this->has_sitemap_submitted();
		}

		$result['accounts'] = Api::get()->get_analytics_accounts();
		if ( ! empty( $result['accounts'] ) ) {
			$result['hasAnalytics']         = true;
			$result['hasAnalyticsProperty'] = $this->is_site_in_analytics( $result['accounts'] );
		}

		$result = apply_filters( 'rank_math/analytics/check_all_services', $result );

		update_option( 'rank_math_analytics_all_services', $result );

		$this->success( $result );
	}

	/**
	 * Add site to search console
	 */
	public function add_site_console() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$home_url = Google_Analytics::get_site_url();
		Api::get()->add_site( $home_url );
		Api::get()->verify_site( $home_url );

		update_option(
			'rank_math_google_analytic_profile',
			[
				'country' => 'all',
				'profile' => $home_url,
			]
		);

		$this->success( [ 'sites' => Api::get()->get_sites() ] );
	}

	/**
	 * Verify site console.
	 */
	public function verify_site_console() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$home_url = Google_Analytics::get_site_url();
		Api::get()->verify_site( $home_url );

		$this->success( [ 'verified' => true ] );
	}

	/**
	 * Save analytic profile.
	 */
	public function save_analytic_options() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$value = [
			'account_id'       => Param::post( 'accountID' ),
			'property_id'      => Param::post( 'propertyID' ),
			'view_id'          => Param::post( 'viewID' ),
			'country'          => Param::post( 'country', 'all' ),
			'install_code'     => Param::post( 'installCode', false, FILTER_VALIDATE_BOOLEAN ),
			'anonymize_ip'     => Param::post( 'anonymizeIP', false, FILTER_VALIDATE_BOOLEAN ),
			'exclude_loggedin' => Param::post( 'excludeLoggedin', false, FILTER_VALIDATE_BOOLEAN ),
		];

		$prev = get_option( 'rank_math_google_analytic_options' );
		if ( isset( $prev['adsense_id'] ) ) {
			$value['adsense_id'] = $prev['adsense_id'];
		}
		update_option( 'rank_math_google_analytic_options', $value );

		// Remove other stored accounts from option for privacy.
		$all_accounts = get_option( 'rank_math_analytics_all_services', [] );
		if ( isset( $all_accounts['accounts'][ $value['account_id'] ] ) ) {
			foreach ( $all_accounts['accounts'] as $account_id => $account_data ) {
				if ( $account_id != $value['account_id'] ) {
					unset( $all_accounts['accounts'][ $account_id ] );
					continue;
				}
				if ( isset( $account_data['properties'][ $value['property_id'] ] ) ) {
					foreach ( $account_data['properties'] as $property_id => $property_data ) {
						if ( $property_id != $value['property_id'] ) {
							unset( $all_accounts['accounts'][ $account_id ][ $property_id ] );
							continue;
						}
					}
				}
			}
		}
		update_option( 'rank_math_analytics_all_services', $all_accounts );

		do_action( 'rank_math/analytics/options/analytics_saved' );

		$this->success();
	}

	/**
	 * Save analytic profile.
	 */
	public function save_analytic_profile() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$input_profile = Param::post( 'profile' );
		$input_country = Param::post( 'country', 'all' );

		$prev  = get_option( 'rank_math_google_analytic_profile' );
		$value = [
			'country' => $input_country,
			'profile' => $input_profile,
		];
		update_option( 'rank_math_google_analytic_profile', $value );

		// Remove other stored sites from option for privacy.
		$all_accounts = get_option( 'rank_math_analytics_all_services', [] );
		$all_accounts['sites'] = [ $input_profile => $input_profile ];
		update_option( 'rank_math_analytics_all_services', $all_accounts );

		if ( empty( $prev['profile'] ) ) {
			$this->should_pull_data();
			$this->success();
		}

		if ( $prev['profile'] !== $value['profile'] ) {
			Data_Fetcher::get()->kill_process();
			Data_Fetcher::get()->start_process( Param::post( 'days', 90, FILTER_VALIDATE_INT ) );
		}

		$this->success();
	}

	/**
	 * Pull data.
	 */
	private function should_pull_data() {
		$gsc = get_option( 'rank_math_google_analytic_profile' );
		if ( empty( $gsc['profile'] ) ) {
			return;
		}

		// Analytics.
		( new \RankMath\Analytics\Installer() )->install();

		\sleep( 2 );

		DB::purge_cache();
		Data_Fetcher::get()->start_process( Param::post( 'days', 90, FILTER_VALIDATE_INT ) );
	}

	/**
	 * Is site in search console.
	 *
	 * @return boolean
	 */
	private function is_site_in_search_console() {
		// Early Bail!!
		if ( Helper::is_localhost() ) {
			return true;
		}

		$sites    = Api::get()->get_sites();
		$home_url = Google_Analytics::get_site_url();

		foreach ( $sites as $site ) {
			if ( trailingslashit( $site ) === $home_url ) {
				$profile = get_option( 'rank_math_google_analytic_profile' );
				if ( empty( $profile ) ) {
					update_option(
						'rank_math_google_analytic_profile',
						[
							'country' => 'all',
							'profile' => $home_url,
						]
					);
				}
				return true;
			}
		}

		return false;
	}

	/**
	 * Is site in analytics.
	 *
	 * @param array $accounts Analytics accounts.
	 *
	 * @return boolean
	 */
	private function is_site_in_analytics( $accounts ) {
		$home_url = Google_Analytics::get_site_url();

		foreach ( $accounts as $account_id => $account ) {
			foreach ( $account['properties'] as $property ) {
				if ( trailingslashit( $property['url'] ) === $home_url ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Has sitemap in search console.
	 *
	 * @return boolean
	 */
	private function has_sitemap_submitted() {
		$home_url = Google_Analytics::get_site_url();
		$sitemaps = Api::get()->get_sitemaps( $home_url );

		if ( ! \is_array( $sitemaps ) || empty( $sitemaps ) ) {
			return false;
		}

		foreach ( $sitemaps as $sitemap ) {
			if ( $sitemap['path'] === $home_url . 'sitemap_index.xml' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Should fetch data.
	 */
	private function should_fetch() {
		if ( ! Authentication::is_authorized() ) {
			$this->error( esc_html__( 'Google oAuth is not authorized.', 'rank-math' ) );
		}

		$options = get_option( 'rank_math_google_analytic_options' );
		if ( empty( $options ) ) {
			$this->error( esc_html__( 'No Google Account setup.', 'rank-math' ) );
		}

		if ( empty( $options['view_id'] ) ) {
			$this->error( esc_html__( 'No Google Search Console Account selected.', 'rank-math' ) );
		}

		if ( empty( $options['account_id'] ) || empty( $options['property_id'] ) ) {
			$this->error( esc_html__( 'No Google Analytics Account selected.', 'rank-math' ) );
		}
	}
}
