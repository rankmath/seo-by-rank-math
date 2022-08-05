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
}
