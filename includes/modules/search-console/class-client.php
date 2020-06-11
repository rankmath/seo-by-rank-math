<?php
/**
 * The Search Console Client
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Search_Console;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Client class.
 */
class Client {

	use Hooker;

	/**
	 * Google Client object.
	 *
	 * @var Google_API
	 */
	private $google_api;

	/**
	 * Hold data.
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Hold selected profile.
	 *
	 * @var string
	 */
	public $profile;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Client
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Client ) ) {
			$instance = new Client;
			$instance->set_data();
			$instance->refresh_auth_token_on_login();
		}

		return $instance;
	}

	/**
	 * Gets the google client instance.
	 *
	 * @return Google_Api Google client instance.
	 */
	public function get_google_client() {
		if ( ! $this->google_api instanceof Google_Api ) {
			$this->google_api = new Google_Api;
		}

		return $this->google_api;
	}

	/**
	 * Fetch access token
	 *
	 * @param string $code oAuth token.
	 *
	 * @return array
	 */
	public function get_access_token( $code ) {
		$api      = $this->get_google_client();
		$response = $api->get_access_token( $code );

		if ( ! $api->is_success() ) {
			return [
				'success' => false,
				'error'   => $api->get_error(),
			];
		}

		Helper::search_console_data(
			[
				'authorized'    => true,
				'expire'        => time() + $response['expires_in'],
				'access_token'  => $response['access_token'],
				'refresh_token' => $response['refresh_token'],
			]
		);

		$this->set_data();

		return 'Done';
	}

	/**
	 * Refresh authentication token when user login.
	 */
	public function refresh_auth_token_on_login() {
		// Bail if the user is not authenticated at all yet.
		if ( ! $this->is_authenticated() || ! $this->is_token_expired() ) {
			return;
		}

		$api      = $this->get_google_client();
		$response = $api->refresh_token( $this->data );

		if ( ! $api->is_success() ) {
			$this->disconnect();
			return;
		}

		Helper::search_console_data(
			[
				'expire'       => time() + $response['expires_in'],
				'access_token' => $response['access_token'],
			]
		);

		$this->set_data();
	}

	/**
	 * Fetch profiles api wrapper.
	 *
	 * @return array
	 */
	public function get_profiles() {
		$profiles = [];

		if ( ! $this->is_authenticated() ) {
			return $profiles;
		}

		$api      = $this->get_google_client();
		$profiles = $api->get_profiles();
		Helper::search_console_data( [ 'profiles' => $profiles ] );

		return $profiles;
	}

	/**
	 * Fetch sitemaps.
	 *
	 * @param boolean $with_index With index data.
	 * @param boolean $force      Purge cache and fetch new data.
	 *
	 * @return array
	 */
	public function get_sitemaps( $with_index = false, $force = false ) {
		if ( empty( $this->profile ) ) {
			return [];
		}

		$key      = $this->generate_key( 'sitemaps', ( $with_index ? 'index' : '' ) );
		$sitemaps = get_transient( $key );
		if ( ! $force && false !== $sitemaps ) {
			return $sitemaps;
		}

		$sitemap_index_uri = apply_filters( 'rank_math/sitemap/sitemap_index_uri', 'sitemap_index.xml' );

		$with_index = $with_index ? '?sitemapIndex=' . urlencode( trailingslashit( $this->profile ) . $sitemap_index_uri ) : '';

		$api      = $this->get_google_client();
		$response = $api->get( 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( $this->profile ) . '/sitemaps' . $with_index );

		if ( ! $api->is_success() ) {
			Helper::add_notification( $response['error']['message'] );
			return [];
		}

		set_transient( $key, $response['sitemap'], DAY_IN_SECONDS );

		return $sitemaps;
	}

	/**
	 * Submit sitemap to search console.
	 *
	 * @param string $sitemap Sitemap url.
	 *
	 * @return array
	 */
	public function submit_sitemap( $sitemap ) {
		return $this->get_google_client()->put( 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( $this->profile ) . '/sitemaps/' . urlencode( $sitemap ) );
	}

	/**
	 * Delete sitemap from search console.
	 *
	 * @param string $sitemap Sitemap url.
	 *
	 * @return array
	 */
	public function delete_sitemap( $sitemap ) {
		return $this->get_google_client()->delete( 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode( $this->profile ) . '/sitemaps/' . urlencode( $sitemap ) );
	}

	/**
	 * Revokes authentication along with user options settings.
	 */
	public function disconnect() {
		$this->get_google_client()->revoke_token( $this->data );
		Helper::search_console_data( false );
		Helper::search_console_data(
			[
				'authorized' => false,
				'profiles'   => [],
			]
		);

		$this->set_data();
	}

	/**
	 * Check if the current user is authenticated.
	 *
	 * @return boolean True if the user is authenticated, false otherwise.
	 */
	public function is_authenticated() {
		return $this->data['authorized'] && $this->data['access_token'] && $this->data['refresh_token'];
	}

	/**
	 * Check if token is expired.
	 *
	 * @return boolean
	 */
	public function is_token_expired() {
		return $this->data['expire'] && time() > ( $this->data['expire'] - 120 );
	}

	/**
	 * Get Search Console auth url.
	 *
	 * @return string
	 */
	public function get_auth_url() {
		return $this->get_google_client()->get_auth_url();
	}

	/**
	 * Set data.
	 */
	private function set_data() {
		$this->data    = Helper::search_console_data();
		$this->profile = Helper::get_settings( 'general.console_profile' );

		if ( ! $this->profile && ! empty( $this->data['profiles'] ) ) {
			$this->profile = key( $this->data['profiles'] );
		}
		$this->profile_salt = $this->profile ? md5( $this->profile ) : '';
		if ( isset( $this->data['access_token'] ) ) {
			$this->get_google_client()->set_token( $this->data['access_token'] );
		}
	}

	/**
	 * Generate Cache Keys.
	 *
	 * @param string $what What for you need the key.
	 * @param mixed  $args more salt to add into key.
	 *
	 * @return string
	 */
	private function generate_key( $what, $args = [] ) {
		$key = '_rank_math_' . $this->profile_salt . '_sc_' . $what;

		if ( ! empty( $args ) ) {
			$key .= '_' . join( '_', (array) $args );
		}

		return $key;
	}
}
