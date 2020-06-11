<?php
/**
 * The 404 Monitor Module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Monitor
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Monitor;

use RankMath\Helper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Monitor class.
 */
class Monitor {

	use Hooker, Ajax;

	/**
	 * The Constructor.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		if ( is_admin() ) {
			$this->admin = new Admin;
		}

		if ( Conditional::is_ajax() ) {
			$this->ajax( 'delete_log', 'delete_log' );
		}

		$this->action( 'get_header', 'capture_404' );
		if ( Helper::has_cap( '404_monitor' ) ) {
			$this->action( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		}
	}

	/**
	 * Add admin bar item.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param Admin_Bar_Menu $menu Menu class instance.
	 */
	public function admin_bar_items( $menu ) {
		$menu->add_sub_menu(
			'404-monitor',
			[
				'title'    => esc_html__( '404 Monitor', 'rank-math' ),
				'href'     => Helper::get_admin_url( '404-monitor' ),
				'meta'     => [ 'title' => esc_html__( 'Review 404 errors on your site', 'rank-math' ) ],
				'priority' => 50,
			]
		);
	}

	/**
	 * Delete log.
	 *
	 * @codeCoverageIgnore
	 */
	public function delete_log() {

		check_ajax_referer( '404_delete_log', 'security' );

		$this->has_cap_ajax( '404_monitor' );

		$id = Param::request( 'log' );
		if ( ! $id ) {
			$this->error( esc_html__( 'No valid id found.', 'rank-math' ) );
		}

		DB::delete_log( $id );
		$this->success( esc_html__( 'Log successfully deleted.', 'rank-math' ) );
	}

	/**
	 * This function logs the request details when is_404().
	 */
	public function capture_404() {
		if ( ! is_404() || in_array( http_response_code(), [ 410, 451 ], true ) ) {
			return;
		}

		$uri = untrailingslashit( Helper::get_current_page_url( Helper::get_settings( 'general.404_monitor_ignore_query_parameters' ) ) );
		$uri = str_replace( home_url( '/' ), '', $uri );

		// Check if excluded.
		if ( $this->is_url_excluded( $uri ) ) {
			return;
		}

		// Mode = simple.
		if ( 'simple' === Helper::get_settings( 'general.404_monitor_mode' ) ) {
			DB::update( [ 'uri' => $uri ] );
			return;
		}

		// Mode = advanced.
		DB::add([
			'uri'        => $uri,
			'ip'         => Param::server( 'REMOTE_ADDR', '' ),
			'referer'    => Param::server( 'HTTP_REFERER', '' ),
			'user_agent' => $this->get_user_agent(),
		]);
	}

	/**
	 * Check if current URL is excluded.
	 *
	 * @param string $uri Check this URI for exclusion.
	 *
	 * @return boolean
	 */
	private function is_url_excluded( $uri ) {
		$excludes = Helper::get_settings( 'general.404_monitor_exclude' );
		if ( ! is_array( $excludes ) ) {
			return false;
		}

		foreach ( $excludes as $rule ) {
			if ( ! empty( $rule['exclude'] ) && Str::comparison( $rule['exclude'], $uri, $rule['comparison'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get user-agent.
	 *
	 * @return string
	 */
	private function get_user_agent() {
		$u_agent = Param::server( 'HTTP_USER_AGENT' );
		if ( empty( $u_agent ) ) {
			return '';
		}

		$parsed = $this->parse_user_agent( $u_agent );
		if ( ! empty( $parsed['browser'] ) ) {
			$u_agent .= $parsed['browser'];
		}
		if ( ! empty( $parsed['version'] ) ) {
			$u_agent .= ' ' . $parsed['version'];
		}

		return $u_agent;
	}

	/**
	 * Parses a user-agent string into its parts.
	 *
	 * @link https://github.com/donatj/PhpUserAgent
	 *
	 * @param string $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL.
	 *
	 * @return string[] an array with browser, version and platform keys
	 */
	private function parse_user_agent( $u_agent ) {
		if ( ! $u_agent ) {
			return [
				'platform' => null,
				'browser'  => null,
				'version'  => null,
			];
		}

		$response = wp_remote_get( 'https://api.redirect.li/v1/useragent/' . urlencode( $u_agent ) );
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response, true );

		return [
			'platform' => $response['os']['name'] . ' ' . $response['os']['version'],
			'browser'  => $response['browser']['name'],
			'version'  => $response['browser']['version'],
		];
	}
}
