<?php
/**
 *  Google Permissions.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Google;

defined( 'ABSPATH' ) || exit;

/**
 * Permissions class.
 */
class Permissions {

	const OPTION_NAME = 'rank_math_analytics_permissions';

	/**
	 * Permission info.
	 */
	public static function fetch() {
		$tokens = Authentication::tokens();
		if ( empty( $tokens['access_token'] ) ) {
			return;
		}

		$url      = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' . $tokens['access_token'];
		$response = wp_remote_get( $url );
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$response = wp_remote_retrieve_body( $response );
		if ( empty( $response ) ) {
			return;
		}

		$response = \json_decode( $response, true );
		$scopes   = $response['scope'];
		$scopes   = explode( ' ', $scopes );
		$scopes   = str_replace( 'https://www.googleapis.com/auth/', '', $scopes );

		update_option( self::OPTION_NAME, $scopes );
	}

	/**
	 * Get permissions.
	 *
	 * @return array
	 */
	public static function get() {
		return get_option( self::OPTION_NAME, [] );
	}

	/**
	 * If user give permission or not.
	 *
	 * @param  string $permission Permission name.
	 * @return boolean
	 */
	public static function has( $permission ) {
		$permissions = self::get();

		return in_array( $permission, $permissions, true );
	}

	/**
	 * If user give permission or not.
	 *
	 * @return boolean
	 */
	public static function has_console() {
		return self::has( 'webmasters' );
	}

	/**
	 * If user give permission or not.
	 *
	 * @return boolean
	 */
	public static function has_analytics() {
		return self::has( 'analytics.readonly' ) ||
			self::has( 'analytics.provision' ) ||
			self::has( 'analytics.edit' );
	}

	/**
	 * If user give permission or not.
	 *
	 * @return boolean
	 */
	public static function has_adsense() {
		return self::has( 'adsense.readonly' );
	}

	/**
	 * If user give permission or not.
	 *
	 * @return string
	 */
	public static function get_status() {
		return [
			esc_html__( 'Search Console', 'rank-math' ) => self::get_status_text( self::has_console() ),
		];
	}

	/**
	 * Status text
	 *
	 * @param  boolean $check Truthness.
	 * @return string
	 */
	public static function get_status_text( $check ) {
		return $check ? esc_html__( 'Given', 'rank-math' ) : esc_html__( 'Not Given', 'rank-math' );
	}

	/**
	 * Print warning
	 */
	public static function print_warning() {
		?>
		<p class="warning"><strong class="warning"><?php esc_html_e( 'Warning:', 'rank-math' ); ?></strong> <?php printf( wp_kses_post( __( 'You have not given the permission to fetch this data. Please <a href="%s">reconnect</a> with all required permissions.', 'rank-math' ) ), wp_nonce_url( admin_url( 'admin.php?reconnect=google' ), 'rank_math_reconnect_google' ) ); ?></p>
		<?php
	}
}
