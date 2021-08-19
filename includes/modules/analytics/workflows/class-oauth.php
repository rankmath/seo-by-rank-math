<?php
/**
 * Authentication workflow.
 *
 * @since      1.0.55
 * @package    RankMath
 * @subpackage RankMath\Analytics
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics\Workflow;

use RankMath\Helper;
use RankMath\Google\Api;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;
use RankMath\Analytics\DB;
use RankMath\Helpers\Security;
use RankMath\Google\Permissions;
use RankMath\Google\Authentication;

defined( 'ABSPATH' ) || exit;

/**
 * OAuth class.
 */
class OAuth {

	use Hooker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->action( 'admin_init', 'process_oauth' );
		$this->action( 'admin_init', 'reconnect_google' );
	}

	/**
	 * OAuth reply back
	 */
	public function process_oauth() {
		$process_oauth = Param::get( 'process_oauth', false, FILTER_SANITIZE_STRING );
		$access_token  = Param::get( 'access_token', false, FILTER_SANITIZE_STRING );
		$security      = Param::get( 'rankmath_security', false, FILTER_SANITIZE_STRING );

		// Early Bail!!
		if ( empty( $security ) || ( $process_oauth < 1 && empty( $access_token ) ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $security, 'rank_math_oauth_token' ) ) {
			wp_nonce_ays( 'rank_math_oauth_token' );
			die();
		}

		$redirect = false;
		// Backward compatibility.
		if ( ! empty( $process_oauth ) ) {
			$redirect = $this->get_tokens_from_server();
		}

		// New version.
		if ( ! empty( $access_token ) ) {
			$redirect = $this->get_tokens_from_url();
		}

		// Remove possible admin notice if we have new access token.
		delete_option( 'rankmath_google_api_failed_attempts_data' );
		delete_option( 'rankmath_google_api_reconnect' );

		Permissions::fetch();

		if ( ! empty( $redirect ) ) {
			Helper::redirect( $redirect );
			exit;
		}
	}

	/**
	 * Reconnect Google.
	 */
	public function reconnect_google() {
		if ( ! isset( $_GET['reconnect'] ) || 'google' !== $_GET['reconnect'] ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'rank_math_reconnect_google' ) ) {
			wp_nonce_ays( 'rank_math_reconnect_google' );
			die();
		}

		if ( ! Helper::has_cap( 'analytics' ) ) {
			return;
		}

		$rows = DB::objects()
			->selectCount( 'id' )
			->getVar();

		if ( empty( $rows ) ) {
			delete_option( 'rank_math_analytics_installed' );
		}

		Api::get()->revoke_token();
		Workflow::kill_workflows();

		wp_redirect( Authentication::get_auth_url() ); // phpcs:ignore
		die();
	}

	/**
	 * Get access token from url.
	 *
	 * @return string
	 */
	private function get_tokens_from_url() {
		$data = [
			'access_token'  => urldecode( Param::get( 'access_token', '', FILTER_SANITIZE_STRING ) ),
			'refresh_token' => urldecode( Param::get( 'refresh_token', '', FILTER_SANITIZE_STRING ) ),
			'expire'        => urldecode( Param::get( 'expire', '', FILTER_SANITIZE_STRING ) ),
		];

		Authentication::tokens( $data );

		$current_request = remove_query_arg(
			[
				'access_token',
				'refresh_token',
				'expire',
				'security',
			]
		);

		return $current_request;
	}

	/**
	 * Get access token from rankmath server.
	 *
	 * @return string
	 */
	private function get_tokens_from_server() {
		// Bail if the user is not authenticated at all yet.
		$id = Param::get( 'process_oauth', 0, FILTER_VALIDATE_INT );
		if ( $id < 1 ) {
			return;
		}

		$response = wp_remote_get( Authentication::get_auth_app_url() . '/get.php?id=' . $id );
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$response = wp_remote_retrieve_body( $response );
		if ( empty( $response ) ) {
			return;
		}

		$response = \json_decode( $response, true );
		unset( $response['id'] );

		// Save new token.
		Authentication::tokens( $response );

		$redirect = Security::remove_query_arg_raw( [ 'process_oauth', 'security' ] );
		if ( Str::contains( 'rank-math-options-general', $redirect ) ) {
			$redirect .= '#setting-panel-analytics';
		}

		Helper::remove_notification( 'rank_math_analytics_reauthenticate' );

		return $redirect;
	}
}
