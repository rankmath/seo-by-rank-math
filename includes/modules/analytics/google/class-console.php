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

use RankMath\Analytics\Data_Fetcher;

defined( 'ABSPATH' ) || exit;

/**
 * Console class.
 */
class Console extends Analytics {

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
		as_enqueue_async_action( 'rank_math/analytics/sync_sitemaps' );

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
		if ( ! $this->is_success() ) {
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
		$with_index = $with_index ? '?sitemapIndex=' . rawurlencode( $url . 'sitemap_index.xml' ) : '';
		$response   = $this->http_get( 'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode( $url ) . '/sitemaps' . $with_index );

		if ( ! $this->is_success() ) {
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
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param string $dimension  Dimension of data.
	 *
	 * @return array
	 */
	public function get_search_analytics( $start_date, $end_date, $dimension ) {
		$args = [
			'startDate'  => $start_date,
			'endDate'    => $end_date,
			'rowLimit'   => $this->get_row_limit(),
			'dimensions' => \is_array( $dimension ) ? $dimension : [ $dimension ],
		];

		$options = get_option( 'rank_math_google_analytic_profile', [] );
		if ( ! empty( $options ) && 'all' !== $options['country'] ) {
			$args['dimensionFilterGroups'] = [
				[
					'filters' => [
						[
							'dimension'  => 'country',
							'operator'   => 'equals',
							'expression' => $options['country'],
						],
					],
				],
			];
		}

		$response = $this->http_post(
			'https://www.googleapis.com/webmasters/v3/sites/' . rawurlencode( self::get_site_url() ) . '/searchAnalytics/query',
			$args
		);

		$this->log_failed_request( $response, $start_date, func_get_args() );

		if ( ! $this->is_success() || ! isset( $response['rows'] ) ) {
			return false;
		}

		return $response['rows'];
	}

	/**
	 * Log every failed API call.
	 * And kill all next scheduled event if failed count is more then three.
	 */
	private function log_failed_request( $response, $start_date, $args ) {
		if ( $this->is_success() ) {
			return;
		}

		// Number of allowed attempt.
		$allow_fail_attempt          = 3;
		$option_key                  = 'rankmath_google_api_failed_attempts_data';
		$reconnect_google_option_key = 'rankmath_google_api_reconnect';

		if ( ! empty( $response['error'] ) && is_array( $response['error'] ) ) {

			$failed_attempts   = get_option( $option_key, [] );
			$failed_attempts   = ( ! empty( $failed_attempts ) && is_array( $failed_attempts ) ) ? $failed_attempts : [];
			$failed_attempts[] = [
				'args'  => $args,
				'error' => $response['error'],
			];

			update_option( $option_key, $failed_attempts );

			if ( $allow_fail_attempt < count( $failed_attempts ) ) {
				update_option( $reconnect_google_option_key, 'search_analytics_query' );
				Data_Fetcher::get()->kill_process();
			} else {
				as_schedule_single_action(
					time() + ( 60 ),
					'rank_math/analytics/get_analytics',
					[ $start_date ]
				);
			}
		} else {
			delete_option( $option_key );
			delete_option( $reconnect_google_option_key );
		}
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
		$local_sitemap    = trailingslashit( $site_url ) . 'sitemap_index.xml';

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
			$rank_math_site_url = $rank_math_site_url['profile'];
		}

		return $rank_math_site_url;
	}

	/**
	 * Get site url.
	 *
	 * @return string
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
}
