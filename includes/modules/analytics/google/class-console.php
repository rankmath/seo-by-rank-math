<?php
/**
 *  Google Search Console.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Google;

use RankMath\Google\Api;
use RankMath\Helpers\Str;
use RankMath\Helpers\Schedule;
use RankMath\Analytics\Workflow\Base;
use RankMath\Sitemap\Sitemap;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Console class.
 */
class Console extends Analytics {

	/**
	 * Connection status key.
	 */
	const CONNECTION_STATUS_KEY = 'rank_math_console_connection_error';

	/**
	 * Add site.
	 *
	 * @param string $url Site url to add.
	 *
	 * @return bool
	 */
	public function add_site( $url ) {
		$this->http_put( 'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode( $url ) );
		return $this->is_success();
	}

	/**
	 * Get site verification token.
	 *
	 * @param string $url Site url to add.
	 *
	 * @return bool|string
	 */
	public function get_site_verification_token( $url ) {
		$args = [
			'site'               => [
				'type'       => 'SITE',
				'identifier' => $url,
			],
			'verificationMethod' => 'META',
		];

		$response = $this->http_post( 'https://www.googleapis.com/siteVerification/v1/token', $args );
		if ( ! $this->is_success() ) {
			return false;
		}

		return \RankMath\CMB2::sanitize_webmaster_tags( $response['token'] );
	}

	/**
	 * Verify site token.
	 *
	 * @param string $url Site url to add.
	 *
	 * @return bool|string
	 */
	public function verify_site( $url ) {
		$token = $this->get_site_verification_token( $url );
		if ( ! $token ) {
			return;
		}

		// Save in transient.
		set_transient( 'rank_math_google_site_verification', $token, DAY_IN_SECONDS * 2 );

		// Call Google site verification.
		$args = [
			'site' => [
				'type'       => 'SITE',
				'identifier' => $url,
			],
		];

		$this->http_post( 'https://www.googleapis.com/siteVerification/v1/webResource?verificationMethod=META', $args );

		// Sync sitemap.
		Schedule::async_action( 'rank_math/analytics/sync_sitemaps', [], 'rank-math' );

		return $this->is_success();
	}

	/**
	 * Get sites.
	 *
	 * @return array
	 */
	public function get_sites() {
		static $rank_math_google_sites;
		if ( ! \is_null( $rank_math_google_sites ) ) {
			return $rank_math_google_sites;
		}

		$rank_math_google_sites = [];
		$response               = $this->http_get( 'https://www.googleapis.com/webmasters/v3/sites' );
		if ( ! $this->is_success() || empty( $response['siteEntry'] ) ) {
			return $rank_math_google_sites;
		}

		foreach ( $response['siteEntry'] as $site ) {
			$rank_math_google_sites[ $site['siteUrl'] ] = $site['siteUrl'];
		}

		return $rank_math_google_sites;
	}

	/**
	 * Fetch sitemaps.
	 *
	 * @param string  $url        Site to get sitemaps for.
	 * @param boolean $with_index With index data.
	 *
	 * @return array
	 */
	public function get_sitemaps( $url, $with_index = false ) {
		$with_index = $with_index ? '?sitemapIndex=' . rawurlencode( $url . Sitemap::get_sitemap_index_slug() . '.xml' ) : '';
		$response   = $this->http_get( 'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode( $url ) . '/sitemaps' . $with_index );

		if ( ! $this->is_success() || empty( $response['sitemap'] ) ) {
			return [];
		}

		return $response['sitemap'];
	}

	/**
	 * Submit sitemap to search console.
	 *
	 * @param string $url     Site to add sitemap for.
	 * @param string $sitemap Sitemap url.
	 *
	 * @return array
	 */
	public function add_sitemap( $url, $sitemap ) {
		return $this->http_put( 'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode( $url ) . '/sitemaps/' . rawurlencode( $sitemap ) );
	}

	/**
	 * Delete sitemap from search console.
	 *
	 * @param string $url     Site to delete sitemap for.
	 * @param string $sitemap Sitemap url.
	 *
	 * @return array
	 */
	public function delete_sitemap( $url, $sitemap ) {
		return $this->http_delete( 'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode( $url ) . '/sitemaps/' . rawurlencode( $sitemap ) );
	}

	/**
	 * Query analytics data from google client api.
	 *
	 * @param array $args  Query arguments.
	 *
	 * @return array|false|WP_Error
	 */
	public function get_search_analytics( $args = [] ) {
		$dates = Base::get_dates();

		$start_date = isset( $args['start_date'] ) ? $args['start_date'] : $dates['start_date'];
		$end_date   = isset( $args['end_date'] ) ? $args['end_date'] : $dates['end_date'];
		$dimensions = isset( $args['dimensions'] ) ? $args['dimensions'] : 'date';
		$row_limit  = isset( $args['row_limit'] ) ? $args['row_limit'] : Api::get()->get_row_limit();

		$params = [
			'startDate'  => $start_date,
			'endDate'    => $end_date,
			'rowLimit'   => $row_limit,
			'dimensions' => \is_array( $dimensions ) ? $dimensions : [ $dimensions ],
		];

		$stored  = get_option(
			'rank_math_google_analytic_profile',
			[
				'country'             => '',
				'profile'             => '',
				'enable_index_status' => true,
			]
		);
		$country = isset( $args['country'] ) ? $args['country'] : $stored['country'];
		$profile = isset( $args['profile'] ) ? $args['profile'] : $stored['profile'];

		if ( 'all' !== $country ) {
			$params['dimensionFilterGroups'] = [
				[
					'filters' => [
						[
							'dimension'  => 'country',
							'operator'   => 'equals',
							'expression' => $country,
						],
					],
				],
			];
		}

		if ( empty( $profile ) ) {
			$profile = trailingslashit( strtolower( home_url() ) );
		}

		$workflow = 'console';
		$this->set_workflow( $workflow );
		$response = $this->http_post(
			'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode( $profile ) . '/searchAnalytics/query',
			$params
		);

		$this->log_failed_request( $response, $workflow, $start_date, func_get_args() );

		if ( ! $this->is_success() ) {
			return new WP_Error( 'request_failed', __( 'The Google Search Console request failed.', 'rank-math' ) );
		}

		if ( ! isset( $response['rows'] ) ) {
			return false;
		}

		return $response['rows'];
	}

	/**
	 * Is site verified.
	 *
	 * @param string $url Site to verify.
	 *
	 * @return boolean
	 */
	public function is_site_verified( $url ) {
		$response = $this->http_get( 'https://www.googleapis.com/siteVerification/v1/webResource/' . rawurlencode( $url ) );
		if ( ! $this->is_success() ) {
			return false;
		}

		return isset( $response['owners'] );
	}

	/**
	 * Sync sitemaps with google search console.
	 */
	public function sync_sitemaps() {
		$site_url = self::get_site_url();
		$data     = $this->get_sitemap_to_sync();

		// Submit it.
		if ( ! $data['sitemaps_in_list'] ) {
			$this->add_sitemap( $site_url, $data['local_sitemap'] );
		}

		if ( empty( $data['delete_sitemaps'] ) ) {
			return;
		}

		// Delete it.
		foreach ( $data['delete_sitemaps'] as $sitemap ) {
			$this->delete_sitemap( $site_url, $sitemap );
		}
	}

	/**
	 * Get sitemaps to sync.
	 *
	 * @return array
	 */
	private function get_sitemap_to_sync() {
		$delete_sitemaps  = [];
		$sitemaps_in_list = false;
		$site_url         = self::get_site_url();
		$sitemaps         = $this->get_sitemaps( $site_url );
		$local_sitemap    = trailingslashit( $site_url ) . Sitemap::get_sitemap_index_slug() . '.xml';

		// Early Bail if there are no sitemaps.
		if ( empty( $sitemaps ) ) {
			return compact( 'delete_sitemaps', 'sitemaps_in_list', 'local_sitemap' );
		}

		foreach ( $sitemaps as $sitemap ) {
			if ( $sitemap['path'] === $local_sitemap ) {
				$sitemaps_in_list = true;
				continue;
			}

			$delete_sitemaps[] = $sitemap['path'];
		}

		return compact( 'delete_sitemaps', 'sitemaps_in_list', 'local_sitemap' );
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
			$rank_math_site_url = empty( $rank_math_site_url['profile'] ) ? $default : $rank_math_site_url['profile'];

			if ( Str::contains( 'sc-domain:', $rank_math_site_url ) ) {
				$rank_math_site_url = str_replace( 'sc-domain:', '', $rank_math_site_url );
				$rank_math_site_url = ( is_ssl() ? 'https://' : 'http://' ) . $rank_math_site_url;
			}
		}

		return $rank_math_site_url;
	}

	/**
	 * Check if console is connected.
	 *
	 * @return boolean Returns True if the console is connected, otherwise False.
	 */
	public static function is_console_connected() {
		$profile = wp_parse_args(
			get_option( 'rank_math_google_analytic_profile' ),
			[
				'profile' => '',
				'country' => 'all',
			]
		);

		return ! empty( $profile['profile'] );
	}

	/**
	 * Is valid connection
	 */
	public static function is_valid_connection() {
		return Api::get()->get_connection_status( self::CONNECTION_STATUS_KEY );
	}

	/**
	 * Test connection
	 */
	public static function test_connection() {
		return Api::get()->check_connection_status( self::CONNECTION_STATUS_KEY, [ Api::get(), 'get_search_analytics' ] );
	}
}
