<?php
/**
 * Shared GRND (mint → seal → exchange) acquisition, used by any WAP agent persona.
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Agent
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Traits;

use RankMath\Admin\Admin_Helper;
use GroupOne\WapClient\WrapKeyClient;
use GroupOne\WapClient\AppPasswordManager;
use GroupOne\WapClient\TokenSealer;

defined( 'ABSPATH' ) || exit;

/**
 * Grnd_Provider trait.
 *
 * Classes using this trait must define a SERVER_URL constant.
 */
trait Grnd_Provider {

	/**
	 * Mints, seals, and exchanges the credential for a GRND, scoped to a given persona.
	 *
	 * @param string $product        WAP product/role slug for this persona.
	 * @param string $password_label WP Application Password label for this persona.
	 * @return array{grnd:string,expires_at:int}|\WP_Error
	 */
	protected function acquire_grnd_for( $product, $password_label ) {
		$user = wp_get_current_user();
		if ( ! $user || ! $user->exists() ) {
			return new \WP_Error( 'rank_math_wap_no_user', esc_html__( 'No authenticated WordPress user.', 'seo-by-rank-math' ) );
		}

		$registration = Admin_Helper::get_registration_data();
		$license_key  = ! empty( $registration['api_key'] ) ? $registration['api_key'] : '';
		if ( ! $license_key ) {
			return new \WP_Error( 'rank_math_wap_not_connected', esc_html__( 'Connect your Rank Math account before using the AI assistant.', 'seo-by-rank-math' ) );
		}

		// 1. WAP's public wrap key (cached inside the library for its TTL + grace).
		$wrap = ( new WrapKeyClient( static::SERVER_URL ) )->get_key();
		if ( is_wp_error( $wrap ) ) {
			return $wrap;
		}

		// 2. Mint a fresh WP Application Password (revoked on a 401 refresh).
		$app_password = ( new AppPasswordManager() )->provision(
			$user->ID,
			$product,
			$password_label
		);
		if ( is_wp_error( $app_password ) ) {
			return $app_password;
		}

		// 3. Seal "user:password" to the wrap key — plaintext never leaves the site.
		$sealed = TokenSealer::seal(
			$user->user_login . ':' . $app_password,
			$wrap['public_key']
		);
		if ( is_wp_error( $sealed ) ) {
			return $sealed;
		}

		// 4. Exchange at Rank Math's GRND issuer with our Connect license key.
		return $this->exchange_grnd( $license_key, $sealed, (string) $wrap['key_id'], $product );
	}

	/**
	 * Exchange the sealed credential for a GRND at Rank Math's GRND issuer.
	 *
	 * @param string $license_key Rank Math Connect API key for this site.
	 * @param string $sealed      Base64 sealed-box ciphertext of "user:password".
	 * @param string $key_id      Wrap key id the credential was sealed to.
	 * @param string $product     WAP product/role slug for this persona.
	 * @return array{grnd:string,expires_at:int}|\WP_Error
	 */
	private function exchange_grnd( $license_key, $sealed, $key_id, $product ) {
		$headers = [
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		];

		$response = wp_remote_post(
			RANK_MATH_SITE_URL . '/wp-json/grnd/v1/token',
			[
				'timeout' => 15,
				'headers' => $headers,
				'body'    => wp_json_encode(
					[
						'license_key'       => $license_key,
						'site_url'          => home_url(),
						'product'           => $product,
						'wrapped_app_token' => $sealed,
						'wrap_key_id'       => $key_id,
					]
				),
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status < 200 || $status >= 300 || ! is_array( $body ) || empty( $body['grnd'] ) ) {
			return new \WP_Error(
				'rank_math_wap_grnd_exchange_failed',
				sprintf(
					/* translators: %d: HTTP status code */
					esc_html__( 'GRND exchange with the Rank Math issuer failed (HTTP %d).', 'seo-by-rank-math' ),
					(int) $status
				),
				[ 'status' => $status ]
			);
		}

		return [
			'grnd'       => (string) $body['grnd'],
			'expires_at' => (int) ( $body['expires_at'] ?? 0 ),
		];
	}
}
