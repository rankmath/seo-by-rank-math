<?php
/**
 * Instant Indexing API
 *
 * @since      1.0.56
 * @package    RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Instant_Indexing;

use RankMath\Helper;
use RankMath\Module\Base;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * API class.
 *
 * @codeCoverageIgnore
 */
class Api extends Base {

	use Hooker;

	/**
	 * Bing URL Submission API URL.
	 *
	 * @var string
	 */
	private $bing_api = 'https://ssl.bing.com/webmaster/api.svc/json/';

	/**
	 * Get Daily Quota left value.
	 *
	 * @param bool $force_update Force Update.
	 * @return string
	 */
	public function get_daily_quota( $force_update = false ) {
		$stored = get_transient( 'rank_math_instant_indexing_bing_daily_quota' );
		if ( $stored && ! $force_update ) {
			return $this->response( 'ok', '', [ 'daily_quota' => $stored ] );
		}

		$data   = $this->request( 'GetUrlSubmissionQuota', [ 'query' => [ 'siteUrl' => untrailingslashit( home_url() ) ] ] );
		$result = isset( $data['body'] ) ? json_decode( $data['body'], true ) : [];

		if ( 'ok' === $data['status'] && is_array( $result ) && isset( $result['d']['DailyQuota'] ) ) {
			set_transient( 'rank_math_instant_indexing_bing_daily_quota', $result['d']['DailyQuota'], 3600 );

			// Translators: The placeholder is the number of URLs.
			return $this->response( 'ok', '', [ 'daily_quota' => $result['d']['DailyQuota'] ] );
		}

		return $this->response( 'error', $data['message'] );
	}

	/**
	 * Submit one or more URLs to Bing's API.
	 *
	 * @param  array $url_input URLs.
	 * @return bool  $blocking  Result of the API call.
	 */
	public function batch_submit_urls( $url_input ) {
		if ( empty( $url_input ) ) {
			return $this->response( 'error', __( 'Bing URL Submission API: Insert one or more URLs.', 'rank-math' ) );
		}

		$body = [
			'siteUrl' => untrailingslashit( home_url() ),
			'urlList' => (array) $url_input,
		];

		$data = $this->request(
			'SubmitUrlBatch',
			[
				'method' => 'POST',
				'body'   => wp_json_encode( $body ),
			]
		);
		if ( 'ok' === $data['status'] ) {
			delete_transient( 'rank_math_instant_indexing_bing_daily_quota' );
			$count = count( (array) $url_input );

			// Translators: The placeholder is the number of URLs.
			return $this->response( 'ok', sprintf( _n( 'Successfully submitted %s URL to the Bing URL Submission API.', 'Successfully submitted %s URLs to the Bing URL Submission API.', $count, 'rank-math' ), $count ) );
		}

		return $this->response( 'error', $data['message'] );
	}

	/**
	 * Batch submit single URL.
	 *
	 * @param string $url URL to submit.
	 * @return array
	 */
	public function submit_url( $url ) {
		$data = $this->batch_submit_urls( $url );

		$message = __( 'Failed to submit post to the Bing URL Submission API.', 'rank-math' );
		if ( 'ok' === $data['status'] ) {
			$message = __( 'Post successfully submitted to the Bing URL Submission API.', 'rank-math' );
		}

		return $this->response( $data['status'], $message );
	}

	/**
	 * Get API URL.
	 *
	 * @param string $method API Method.
	 * @param array  $args   Additional query parameters.
	 * @return string
	 */
	private function get_api_url( $method, $args = [] ) {
		$args = array_merge( [ 'apiKey' => Helper::get_settings( 'instant_indexing.bing_api_key' ) ], $args );
		$url  = add_query_arg( $args, $this->bing_api . $method );

		return $url;
	}

	/**
	 * Make request to the Bing API.
	 *
	 * @param string $method HTTP method.
	 * @param array  $args   Additional request arguments.
	 */
	private function request( $method, $args ) {
		if ( ! Helper::get_settings( 'instant_indexing.bing_api_key' ) ) {
			return $this->response( 'error', __( 'Please configure the Instant Indexing module in the Settings tab first.', 'rank-math' ) );
		}

		$default_args = [
			'method'   => 'GET',
			'headers'  => [
				'Content-Type' => 'application/json',
				'charset'      => 'utf-8',
			],
			'timeout'  => 15,
			'blocking' => true,
			'query'    => [],
		];

		$args = wp_parse_args( $args, $default_args );
		$url  = $this->get_api_url( $method, $args['query'] );
		unset( $args['query'] );

		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			// Translators: placeholder is the error message.
			return $this->response( 'error', sprintf( __( 'Bing URL Submission API error: %s', 'rank-math' ), $response->get_error_message() ) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( 200 !== $code ) {
			// Translators: 1. the error code, 2. the error message.
			return $this->response( 'error', sprintf( __( 'Bing URL Submission API HTTP error %1$s: %2$s', 'rank-math' ), $code, $body ) );
		}

		return $this->response( 'ok', '', [ 'body' => $body ] );
	}

	/**
	 * A consistent response format.
	 *
	 * @param string $status  Response status keyword.
	 * @param string $message Response message.
	 * @param array  $details Additional details.
	 * @return array
	 */
	private function response( $status, $message = '', $details = [] ) {
		return array_merge(
			[
				'status'  => $status,
				'message' => esc_html( $message ),
			],
			$details
		);
	}
}
