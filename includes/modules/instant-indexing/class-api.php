<?php
/**
 * IndexNow API
 *
 * @since      1.0.56
 * @package    RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Instant_Indexing;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * API class.
 *
 * @codeCoverageIgnore
 */
class Api {

	use Hooker;

	/**
	 * IndexNow API URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://api.indexnow.org/indexnow/';

	/**
	 * IndexNow API key.
	 *
	 * @var string
	 */
	protected $api_key = '';

	/**
	 * Was the last request successful.
	 *
	 * @var bool
	 */
	protected $is_success = false;

	/**
	 * Last error.
	 *
	 * @var string
	 */
	protected $last_error = '';

	/**
	 * Last response.
	 *
	 * @var array
	 */
	protected $last_response = '';

	/**
	 * Last response header code.
	 *
	 * @var int
	 */
	protected $last_code = 0;

	/**
	 * Next submission is a manual submission.
	 *
	 * @var bool
	 */
	public $is_manual = true;

	/**
	 * User agent used for the API requests.
	 *
	 * @var string
	 */
	protected $user_agent = '';

	/**
	 * User agent used for the API requests.
	 *
	 * @var string
	 */
	protected $version = '';

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Api
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Api ) ) {
			$instance             = new Api();
			$instance->user_agent = 'RankMath/' . md5( esc_url( home_url( '/' ) ) );
			$instance->version    = rank_math()->version;
		}

		return $instance;
	}

	/**
	 * Make request to the IndexNow API.
	 *
	 * @param array $urls URLs to submit.
	 * @param bool  $manual Whether the request is manual or not.
	 *
	 * @return bool
	 */
	public function submit( $urls, $manual = null ) {
		$this->reset();

		if ( ! is_null( $manual ) ) {
			$this->is_manual = (bool) $manual;
		}

		$data = $this->get_payload( $urls );

		$response = wp_remote_post(
			'https://api.indexnow.org/indexnow/',
			[
				'body'    => $data,
				'headers' => [
					'Content-Type'  => 'application/json',
					'User-Agent'    => $this->user_agent,
					'X-Source-Info' => 'https://rankmath.com/' . $this->version . '/' . ( $this->is_manual ? '1' : '' ),
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			$this->last_error = 'WP_Error: ' . $response->get_error_message();
			$this->log( (array) $urls, 0, $this->last_error );
			return false;
		}

		$this->last_code     = wp_remote_retrieve_response_code( $response );
		$this->last_response = wp_remote_retrieve_body( $response );
		if ( in_array( $this->last_code, [ 200, 202, 204 ], true ) ) {
			$this->is_success = true;
			$this->log( (array) $urls, $this->last_code, 'OK' );
			return true;
		}

		$message = wp_remote_retrieve_response_message( $response );
		$this->set_error_message( $message );

		$this->log( (array) $urls, $this->last_code, $this->last_error );
		return false;
	}

	/**
	 * Get the last error message.
	 *
	 * @return string
	 */
	public function get_error() {
		return $this->last_error;
	}

	/**
	 * Get the last response code.
	 *
	 * @return int
	 */
	public function get_response_code() {
		return $this->last_code;
	}

	/**
	 * Get the last response.
	 *
	 * @return string
	 */
	public function get_response() {
		return $this->last_response;
	}

	/**
	 * Get the host parameter value to send to the API.
	 *
	 * @return string
	 */
	public function get_host() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( empty( $host ) ) {
			$host = 'localhost';
		}

		/**
		 * Filter the host parameter value to send to the API.
		 *
		 * @param string $host Host.
		 */
		return $this->do_filter( 'instant_indexing/indexnow_host', $host );
	}

	/**
	 * Get the API key.
	 *
	 * @return string
	 */
	public function get_key() {
		if ( ! empty( $this->api_key ) ) {
			return $this->api_key;
		}

		$api_key = Helper::get_settings( 'instant_indexing.indexnow_api_key' );

		/**
		 * Filter the API key.
		 *
		 * @param string $api_key API key.
		 */
		$this->api_key = $this->do_filter( 'instant_indexing/indexnow_key', $api_key );

		return $this->api_key;
	}

	/**
	 * Alias for get_key().
	 */
	public function get_api_key() {
		return $this->get_key();
	}

	/**
	 * Get the API key location.
	 *
	 * @param string $context Key context.
	 * @return string
	 */
	public function get_key_location( $context = '' ) {
		/**
		 * Filter the API key location.
		 *
		 * @param string $location Location.
		 */
		return $this->do_filter( 'instant_indexing/indexnow_key_location', trailingslashit( home_url() ) . $this->get_key() . '.txt', $context );
	}

	/**
	 * Log the request.
	 *
	 * @param array  $urls    URLs to submit.
	 * @param int    $status  Response code.
	 * @param string $message Response message.
	 */
	public function log( $urls, $status, $message = '' ) {
		$log = get_option( 'rank_math_indexnow_log', [] );
		$url = $this->get_loggable_url( $urls );

		if ( ! $url ) {
			return;
		}

		$log[] = [
			'url'               => $url,
			'status'            => (int) $status,
			'manual_submission' => (bool) $this->is_manual,
			'message'           => $message,
			'time'              => time(),
		];

		// Only keep the last 100 entries.
		$log = array_slice( $log, -100 );

		update_option( 'rank_math_indexnow_log', $log, false );
	}

	/**
	 * Get the loggable URL from an array of URLs.
	 * If multiple URLs are submitted, return the first one and [+12]
	 *
	 * @param array $urls URLs to submit.
	 *
	 * @return string
	 */
	public function get_loggable_url( $urls ) {
		$urls       = array_values( (array) $urls );
		$count_urls = count( $urls );
		if ( ! $count_urls ) {
			return '';
		}

		$url = $urls[0];
		if ( $count_urls > 1 ) {
			$url .= ' [+' . ( $count_urls - 1 ) . ']';
		}

		return $url;
	}

	/**
	 * Get the log.
	 *
	 * @return array
	 */
	public function get_log() {
		return get_option( 'rank_math_indexnow_log', [] );
	}

	/**
	 * Clear the log.
	 */
	public function clear_log() {
		delete_option( 'rank_math_indexnow_log' );
	}

	/**
	 * Reset object properties.
	 */
	private function reset() {
		$this->last_error    = '';
		$this->last_code     = 0;
		$this->last_response = '';
		$this->is_success    = false;
	}

	/**
	 * Get the additional data to send to the API.
	 *
	 * @param array $urls URLs to submit.
	 *
	 * @return array
	 */
	private function get_payload( $urls ) {
		return wp_json_encode(
			[
				'host'        => $this->get_host(),
				'key'         => $this->get_key(),
				'keyLocation' => $this->get_key_location( 'request_payload' ),
				'urlList'     => (array) $urls,
			]
		);
	}

	/**
	 * Get the error message from the response message.
	 *
	 * @param string $message Response message.
	 */
	private function set_error_message( $message ) {
		if ( ! empty( $message ) ) {
			$this->last_error = $message;
			return;
		}

		$message     = __( 'Unknown error.', 'rank-math' );
		$message_map = [
			400 => __( 'Invalid request.', 'rank-math' ),
			403 => __( 'Invalid API key.', 'rank-math' ),
			422 => __( 'Invalid URL.', 'rank-math' ),
			429 => __( 'Too many requests.', 'rank-math' ),
			500 => __( 'Internal server error.', 'rank-math' ),
		];

		if ( isset( $message_map[ $this->last_code ] ) ) {
			$message = $message_map[ $this->last_code ];
		}

		$this->last_error = $message;
	}


	/**
	 * Generate and save a new API key.
	 */
	public function reset_key() {
		$settings                     = Helper::get_settings( 'instant_indexing', [] );
		$settings['indexnow_api_key'] = $this->generate_api_key();
		$this->api_key                = $settings['indexnow_api_key'];
		update_option( 'rank-math-options-instant-indexing', $settings );
	}

	/**
	 * Generate new random API key.
	 */
	private function generate_api_key() {
		$api_key = wp_generate_uuid4();
		$api_key = preg_replace( '[-]', '', $api_key );

		return $api_key;
	}
}
