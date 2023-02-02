<?php
/**
 * Google URL Inspection API.
 *
 * @since      1.0.84
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Google;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics class.
 */
class Url_Inspection extends Request {
	/**
	 * URL Inspection API base URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://searchconsole.googleapis.com/v1/urlInspection/index:inspect';

	/**
	 * Access token.
	 *
	 * @var array
	 */
	public $token = [];

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Url_Inspection
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Url_Inspection ) ) {
			$instance = new Url_Inspection();
			$instance->setup();
		}

		return $instance;
	}

	/**
	 * Setup token.
	 */
	public function setup() {
		if ( ! Authentication::is_authorized() ) {
			return;
		}

		$tokens      = Authentication::tokens();
		$this->token = $tokens['access_token'];
	}

	/**
	 * Send URL to the API and return the response, or false on failure.
	 *
	 * @param string $page URL to inspect (relative).
	 */
	public function get_api_results( $page ) {
		$lang_arr  = \explode( '_', get_locale() );
		$lang_code = empty( $lang_arr[1] ) ? $lang_arr[0] : $lang_arr[0] . '-' . $lang_arr[1];

		$args = [
			'inspectionUrl' => untrailingslashit( home_url() ) . $page,
			'siteUrl'       => Console::get_site_url(),
			'languageCode'  => $lang_code,
		];

		set_time_limit( 90 );

		$workflow = 'inspections';
		$this->set_workflow( $workflow );

		$response = $this->http_post( $this->api_url, $args, 60 );

		$this->log_failed_request( $response, $workflow, $page, func_get_args() );

		if ( ! $this->is_success() ) {
			return false;
		}

		return $response;
	}

	/**
	 * Get inspection data.
	 *
	 * @param string $page URL to inspect.
	 */
	public function get_inspection_data( $page ) {
		$inspection = $this->get_api_results( $page );
		if ( empty( $inspection ) || empty( $inspection['inspectionResult'] ) ) {
			return;
		}
		$inspection = $this->normalize_inspection_data( $inspection );

		$inspection['page'] = $page;

		return $inspection;
	}

	/**
	 * Normalize inspection data.
	 *
	 * @param  array $inspection Inspection data.
	 */
	private function normalize_inspection_data( $inspection ) {
		$incoming   = $inspection['inspectionResult'];
		$normalized = [];

		$map_properties = [
			'indexStatusResult.verdict'         => 'index_verdict',
			'indexStatusResult.coverageState'   => 'coverage_state',
			'indexStatusResult.indexingState'   => 'indexing_state',
			'indexStatusResult.pageFetchState'  => 'page_fetch_state',
			'indexStatusResult.robotsTxtState'  => 'robots_txt_state',
			'mobileUsabilityResult.verdict'     => 'mobile_usability_verdict',
			'mobileUsabilityResult.issues'      => 'mobile_usability_issues',
			'richResultsResult.verdict'         => 'rich_results_verdict',
			'indexStatusResult.crawledAs'       => 'crawled_as',
			'indexStatusResult.googleCanonical' => 'google_canonical',
			'indexStatusResult.userCanonical'   => 'user_canonical',
			'indexStatusResult.sitemap'         => 'sitemap',
			'indexStatusResult.referringUrls'   => 'referring_urls',
		];

		$this->assign_inspection_values( $incoming, $map_properties, $normalized );

		$normalized = apply_filters( 'rank_math/analytics/url_inspection_map_properties', $normalized, $incoming );

		return $normalized;
	}

	/**
	 * Assign inspection field value to the data array.
	 *
	 * @param  array  $raw_data  Raw data.
	 * @param  string $field     Field name.
	 * @param  string $assign_to Field name to assign to.
	 * @param  array  $data      Data array.
	 *
	 * @return void
	 */
	public function assign_inspection_value( $raw_data, $field, $assign_to, &$data ) {
		$data[ $assign_to ] = $this->get_result_field( $raw_data, $field );

		if ( is_array( $data[ $assign_to ] ) ) {
			$data[ $assign_to ] = wp_json_encode( $data[ $assign_to ] );
		} elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data[ $assign_to ], $matches ) ) {
			// If it's a date, convert to MySQL format.
			$data[ $assign_to ] = date( 'Y-m-d H:i:s', strtotime( $matches[0] ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Date is stored as TIMESTAMP, so the timezone is converted automatically.
		}
	}

	/**
	 * Get a field from the inspection result.
	 *
	 * @param array  $raw_data Incoming data.
	 * @param string $field    Field name.
	 *
	 * @return mixed
	 */
	protected function get_result_field( $raw_data, $field ) {
		if ( false !== strpos( $field, '.' ) ) {
			$fields = explode( '.', $field );

			if ( ! isset( $raw_data[ $fields[0] ] ) || ! isset( $raw_data[ $fields[0] ][ $fields[1] ] ) ) {
				return '';
			}

			return $raw_data[ $fields[0] ][ $fields[1] ];
		}

		if ( ! isset( $raw_data[ $field ] ) ) {
			return '';
		}

		return $raw_data[ $field ];
	}

	/**
	 * Assign inspection field values to the data array.
	 *
	 * @param  array $raw_data Raw data.
	 * @param  array $fields   Map properties.
	 * @param  array $data     Data array.
	 *
	 * @return void
	 */
	public function assign_inspection_values( $raw_data, $fields, &$data ) {
		foreach ( $fields as $field => $assign_to ) {
			$this->assign_inspection_value( $raw_data, $field, $assign_to, $data );
		}
	}
}
