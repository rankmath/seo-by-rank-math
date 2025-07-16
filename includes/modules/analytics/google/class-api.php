<?php
/**
 * Minimal Google API wrapper.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Google;

defined( 'ABSPATH' ) || exit;

/**
 * Api
 */
class Api extends Console {

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
	 * @return Api
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Api ) ) {
			$instance = new Api();
			$instance->setup();
		}

		return $instance;
	}

	/**
	 * Setup token.
	 */
	private function setup() {
		if ( ! Authentication::is_authorized() ) {
			return;
		}

		$tokens      = Authentication::tokens();
		$this->token = $tokens['access_token'];
	}

	/**
	 * Get row limit.
	 *
	 * @return int
	 */
	public function get_row_limit() {
		return apply_filters( 'rank_math/analytics/row_limit', 10000 );
	}

	/**
	 * Get connection status.
	 *
	 * @param string $key Connection status key.
	 *
	 * @return bool
	 */
	public function get_connection_status( $key ) {
		return ! get_option( $key, false );
	}

	/**
	 * Set connection status.
	 *
	 * @param string $key   Connection status key.
	 * @param bool   $status Connection status.
	 */
	public function set_connection_status( $key, $status ) {
		if ( $status ) {
			update_option( $key, true );
		} else {
			delete_option( $key );
		}
	}

	/**
	 * Check connection status.
	 *
	 * @param string   $key     Connection status key.
	 * @param callable $callback Callback to check connection.
	 *
	 * @return bool
	 */
	public function check_connection_status( $key, $callback ) {
		$this->set_connection_status( $key, false );

		$response = call_user_func( $callback );

		if ( is_wp_error( $response ) ) {
			$this->set_connection_status( $key, true );
			return false;
		}

		return true;
	}
}
