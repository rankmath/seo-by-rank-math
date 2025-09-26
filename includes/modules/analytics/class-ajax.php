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

use WP_Error;
use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Helpers\Str;
use RankMath\Helpers\Param;
use RankMath\Google\Analytics;
use RankMath\Google\Authentication;
use RankMath\Sitemap\Sitemap;
use RankMath\Analytics\Workflow\Console;
use RankMath\Analytics\Workflow\Inspections;
use RankMath\Analytics\Workflow\Objects;
use RankMath\Google\Console as Google_Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * AJAX class.
 */
class AJAX {

	use \RankMath\Traits\Ajax;

	/**
	 * Get the instance of this class.
	 *
	 * @return AJAX
	 */
	public static function get() {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * The Constructor
	 */
	public function __construct() {
		$this->ajax( 'query_analytics', 'query_analytics' );
		$this->ajax( 'add_site_console', 'add_site_console' );
		$this->ajax( 'disconnect_google', 'disconnect_google' );
		$this->ajax( 'verify_site_console', 'verify_site_console' );
		$this->ajax( 'google_check_all_services', 'check_all_services' );

		// Google Data Management Services.
		$this->ajax( 'analytics_delete_cache', 'delete_cache' );
		$this->ajax( 'analytic_start_fetching', 'analytic_start_fetching' );
		$this->ajax( 'analytic_cancel_fetching', 'analytic_cancel_fetching' );

		// Save Linked Google Account info Services.
		$this->ajax( 'check_console_request', 'check_console_request' );
		$this->ajax( 'check_analytics_request', 'check_analytics_request' );
		$this->ajax( 'save_analytic_profile', 'save_analytic_profile' );
		$this->ajax( 'save_analytic_options', 'save_analytic_options' );

		// Create new GA4 property.
		$this->ajax( 'create_ga4_property', 'create_ga4_property' );
		$this->ajax( 'get_ga4_data_streams', 'get_ga4_data_streams' );
	}

	/**
	 * Create a new GA4 property.
	 */
	public function create_ga4_property() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );
		$account_id = Param::post( 'accountID', false, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );

		$timezone = get_option( 'timezone_string' );
		$offset   = get_option( 'gmt_offset' );

		if ( empty( $timezone ) && 0 !== $offset && floor( $offset ) === $offset ) {
			$offset_st = $offset > 0 ? "-$offset" : '+' . absint( $offset );
			$timezone  = 'Etc/GMT' . $offset_st;
		}

		$args = [
			'displayName' => get_bloginfo( 'sitename' ) . ' - GA4',
			'parent'      => "accounts/{$account_id}",
			'timeZone'    => empty( $timezone ) ? 'UTC' : $timezone,
		];

		$response = Api::get()->http_post(
			'https://analyticsadmin.googleapis.com/v1alpha/properties',
			$args
		);

		if ( ! empty( $response['error'] ) ) {
			$this->error( $response['error']['message'] );
		}

		$property_id   = str_replace( 'properties/', '', $response['name'] );
		$property_name = esc_html( $response['displayName'] );
		$all_accounts  = get_option( 'rank_math_analytics_all_services' );
		if ( isset( $all_accounts['accounts'][ $account_id ] ) ) {
			$all_accounts['accounts'][ $account_id ]['properties'][ $property_id ] = [
				'name'       => $property_name,
				'id'         => $property_id,
				'account_id' => $account_id,
				'type'       => 'GA4',
			];

			update_option( 'rank_math_analytics_all_services', $all_accounts );
		}

		$this->success(
			[
				'id'   => $property_id,
				'name' => $property_name,
			]
		);
	}

	/**
	 * Get the list of Web data streams.
	 */
	public function get_ga4_data_streams() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );
		$property_id = Param::post( 'propertyID', false, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );

		$response = Api::get()->http_get(
			"https://analyticsadmin.googleapis.com/v1alpha/properties/{$property_id}/dataStreams"
		);

		if ( ! empty( $response['error'] ) ) {
			$this->error( $response['error']['message'] );
		}

		if ( ! empty( $response['dataStreams'] ) ) {
			$streams = [];
			foreach ( $response['dataStreams'] as $data_stream ) {
				$streams[] = [
					'id'            => str_replace( "properties/{$property_id}/dataStreams/", '', $data_stream['name'] ),
					'name'          => $data_stream['displayName'],
					'measurementId' => $data_stream['webStreamData']['measurementId'],
				];
			}

			$this->success( [ 'streams' => $streams ] );
		}

		$stream = $this->create_ga4_data_stream( $property_id );
		if ( ! is_array( $stream ) ) {
			$this->error( $stream );
		}

		$this->success( [ 'streams' => [ $stream ] ] );
	}

	/**
	 * Check the Google Search Console request.
	 */
	public function check_console_request() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$success = Google_Analytics::test_connection();
		if ( false === $success ) {
			$this->error( esc_html__( 'Data import will not work for this service as sufficient permissions are not given.', 'rank-math' ) );
		}

		$this->success();
	}

	/**
	 * Check the Google Analytics request.
	 */
	public function check_analytics_request() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$success = Analytics::test_connection();
		if ( false === $success ) {
			$this->error( esc_html__( 'Data import will not work for this service as sufficient permissions are not given.', 'rank-math' ) );
		}

		$this->success();
	}

	/**
	 * Save analytic profile.
	 */
	public function save_analytic_profile() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$response = $this->do_save_analytic_profile(
			[
				'profile'             => Param::post( 'profile' ),
				'country'             => Param::post( 'country', 'all' ),
				'days'                => Param::get( 'days', 90, FILTER_VALIDATE_INT ),
				'enable_index_status' => Param::post( 'enableIndexStatus', false, FILTER_VALIDATE_BOOLEAN ),
			]
		);

		if ( is_wp_error( $response ) ) {
			$this->error( esc_html__( 'Data import will not work for this service as sufficient permissions are not given.', 'rank-math' ) );
		}

		$this->success();
	}

	/**
	 * Save analytic profile.
	 */
	public function save_analytic_options() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$request = $this->do_save_analytic_options(
			[
				'account_id'       => Param::post( 'accountID', false, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK ),
				'property_id'      => Param::post( 'propertyID', false, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK ),
				'view_id'          => Param::post( 'viewID', false, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK ),
				'measurement_id'   => Param::post( 'measurementID', false, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK ),
				'stream_name'      => Param::post( 'streamName', false, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK ),
				'country'          => Param::post( 'country', 'all', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK ),
				'install_code'     => Param::post( 'installCode', false, FILTER_VALIDATE_BOOLEAN ),
				'anonymize_ip'     => Param::post( 'anonymizeIP', false, FILTER_VALIDATE_BOOLEAN ),
				'local_ga_js'      => Param::post( 'localGAJS', false, FILTER_VALIDATE_BOOLEAN ),
				'exclude_loggedin' => Param::post( 'excludeLoggedin', false, FILTER_VALIDATE_BOOLEAN ),
				'days'             => Param::get( 'days', 90, FILTER_VALIDATE_INT ),
			]
		);

		if ( is_wp_error( $request ) ) {
			$this->error( esc_html__( 'Data import will not work for this service as sufficient permissions are not given.', 'rank-math' ) );
		}

		$this->success();
	}

	/**
	 * Disconnect google.
	 */
	public function disconnect_google() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );
		Api::get()->revoke_token();
		Workflow\Workflow::kill_workflows();

		foreach (
			[
				'rank_math_analytics_all_services',
				'rank_math_google_analytic_options',
			]
			as $option_name
		) {
			delete_option( $option_name );
		}

		$this->success();
	}

	/**
	 * Cancel fetching data.
	 */
	public function analytic_cancel_fetching() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );
		Workflow\Workflow::kill_workflows();

		$this->success( esc_html__( 'Data fetching cancelled.', 'rank-math' ) );
	}

	/**
	 * Start data fetching for console, analytics, adsense.
	 */
	public function analytic_start_fetching() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		if ( ! Authentication::is_authorized() ) {
			$this->error( esc_html__( 'Google oAuth is not authorized.', 'rank-math' ) );
		}

		$days = Param::get( 'days', 90, FILTER_VALIDATE_INT );
		$days = $days * 2;
		$rows = DB::objects()
			->selectCount( 'id' )
			->getVar();

		if ( empty( $rows ) ) {
			delete_option( 'rank_math_analytics_installed' );
		}
		delete_option( 'rank_math_analytics_last_single_action_schedule_time' );
		// Start fetching data.
		foreach ( [ 'console', 'analytics', 'adsense' ] as $action ) {
			Workflow\Workflow::do_workflow(
				$action,
				$days,
				null,
				null
			);
		}

		$this->success( esc_html__( 'Data fetching started in the background.', 'rank-math' ) );
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

		// Delete fetched console data within specified date range.
		DB::delete_by_days( $days );

		// Cancel data fetch action.
		Workflow\Workflow::kill_workflows();
		delete_transient( 'rank_math_analytics_data_info' );
		$db_info = DB::info();

		$this->success(
			[
				'days' => $db_info['days'] ?? 0,
				'rows' => Str::human_number( $db_info['rows'] ?? 0 ),
				'size' => size_format( $db_info['size'] ?? 0 ),
			]
		);
	}

	/**
	 * Search objects info by title or page and return.
	 */
	public function query_analytics() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'analytics' );

		$query = Param::get( 'query', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK );

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

		foreach ( $accounts as $account ) {
			foreach ( $account['properties'] as $property ) {
				if ( ! empty( $property['url'] ) && trailingslashit( $property['url'] ) === $home_url ) {
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
			if ( $sitemap['path'] === $home_url . Sitemap::get_sitemap_index_slug() . '.xml' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Create a new data stream.
	 *
	 * @param string $property_id GA4 property ID.
	 */
	private function create_ga4_data_stream( $property_id ) {
		$args = [
			'type'          => 'WEB_DATA_STREAM',
			'displayName'   => 'Website',
			'webStreamData' => [
				'defaultUri' => home_url(),
			],
		];

		$stream = Api::get()->http_post(
			"https://analyticsadmin.googleapis.com/v1alpha/properties/{$property_id}/dataStreams",
			$args
		);

		if ( ! empty( $stream['error'] ) ) {
			return $stream['error']['message'];
		}

		return [
			'id'            => str_replace( "properties/{$property_id}/dataStreams/", '', $stream['name'] ),
			'name'          => $stream['displayName'],
			'measurementId' => $stream['webStreamData']['measurementId'],
		];
	}

	/**
	 * Save analytic profile.
	 *
	 * @param array $data Data to save.
	 */
	public function do_save_analytic_options( $data = [] ) {
		$value = [
			'account_id'       => $data['account_id'] ?? '',
			'property_id'      => $data['property_id'] ?? '',
			'view_id'          => $data['view_id'] ?? '',
			'measurement_id'   => $data['measurement_id'] ?? '',
			'stream_name'      => $data['stream_name'] ?? '',
			'country'          => $data['country'] ?? 'all',
			'install_code'     => $data['install_code'] ?? false,
			'anonymize_ip'     => $data['anonymize_ip'] ?? false,
			'local_ga_js'      => $data['local_ga_js'] ?? false,
			'exclude_loggedin' => $data['exclude_loggedin'] ?? false,
			'days'             => $data['days'] ?? 90,
		];

		$days = $value['days'];

		$prev = get_option( 'rank_math_google_analytic_options' );
		// Preserve adsense info.
		if ( isset( $prev['adsense_id'] ) ) {
			$value['adsense_id'] = $prev['adsense_id'];
		}
		update_option( 'rank_math_google_analytic_options', $value );

		// Remove other stored accounts from option for privacy.
		$all_accounts = get_option( 'rank_math_analytics_all_services', [] );
		if ( isset( $all_accounts['accounts'][ $value['account_id'] ] ) ) {
			$account = $all_accounts['accounts'][ $value['account_id'] ];

			if ( isset( $account['properties'][ $value['property_id'] ] ) ) {
				$property              = $account['properties'][ $value['property_id'] ];
				$account['properties'] = [ $value['property_id'] => $property ];
			}

			$all_accounts['accounts'] = [ $value['account_id'] => $account ];
		}
		update_option( 'rank_math_analytics_all_services', $all_accounts );

		// Test Google Analytics (GA) connection request.
		if ( ! empty( $value['view_id'] ) || ! empty( $value['country'] ) || ! empty( $value['property_id'] ) ) {
			$request = Analytics::get_sample_response();
			if ( is_wp_error( $request ) ) {
				return new WP_Error(
					'insufficient_permissions',
					esc_html__( 'Data import will not work for this service as sufficient permissions are not given.', 'rank-math' )
				);
			}
		}

		// Start fetching analytics data.
		Workflow\Workflow::do_workflow(
			'analytics',
			$days,
			$prev,
			$value
		);

		return true;
	}

	/**
	 * Save analytic profile.
	 *
	 * @param array $data Data to save.
	 */
	public function do_save_analytic_profile( $data = [] ) {
		$profile             = $data['profile'] ?? '';
		$country             = $data['country'] ?? 'all';
		$days                = $data['days'] ?? 90;
		$enable_index_status = $data['enable_index_status'] ?? false;

		$success = Api::get()->get_search_analytics(
			[
				'country' => $country,
				'profile' => $profile,
			]
		);

		if ( is_wp_error( $success ) ) {
			return new WP_Error( 'insufficient_permissions', esc_html__( 'Data import will not work for this service as sufficient permissions are not given.', 'rank-math' ) );
		}

		$prev  = get_option( 'rank_math_google_analytic_profile', [] );
		$value = [
			'country'             => $country,
			'profile'             => $profile,
			'enable_index_status' => $enable_index_status,
		];
		update_option( 'rank_math_google_analytic_profile', $value );

		// Remove other stored sites from option for privacy.
		$all_accounts          = get_option( 'rank_math_analytics_all_services', [] );
		$all_accounts['sites'] = [ $profile => $profile ];

		update_option( 'rank_math_analytics_all_services', $all_accounts );

		// Purge Cache.
		if ( ! empty( array_diff( $prev, $value ) ) ) {
			DB::purge_cache();
		}

		new Objects();
		new Console();
		new Inspections();

		// Start fetching console data.
		Workflow\Workflow::do_workflow(
			'console',
			$days,
			$prev,
			$value
		);

		return true;
	}
}
