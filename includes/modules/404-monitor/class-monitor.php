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
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Conditional;
use donatj\UserAgent\UserAgentParser;

defined( 'ABSPATH' ) || exit;

/**
 * Monitor class.
 */
class Monitor {

	use Hooker, Ajax;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			$this->admin = new Admin();
		}

		if ( Conditional::is_ajax() ) {
			$this->ajax( 'delete_log', 'delete_log' );
		}

		$hook = defined( 'CT_VERSION' ) ? 'oxygen_enqueue_frontend_scripts' : 'get_header';
		$this->action( $hook, 'capture_404' );
		if ( Helper::has_cap( '404_monitor' ) ) {
			$this->action( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		}
	}

	/**
	 * Add admin bar item.
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
	 * Delete a log item.
	 */
	public function delete_log() {

		check_ajax_referer( '404_delete_log', 'security' );

		$this->has_cap_ajax( '404_monitor' );

		$id = Param::request( 'log' );
		if ( ! $id ) {
			$this->error( esc_html__( 'No valid id found.', 'rank-math' ) );
		}

		DB::delete_log( $id );
		$this->success( esc_html__( 'Log item successfully deleted.', 'rank-math' ) );
	}

	/**
	 * Log the request details when is_404() is true and WP's response code is *not* 410 or 451.
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
		DB::add(
			[
				'uri'        => $uri,
				'referer'    => Param::server( 'HTTP_REFERER', '' ),
				'user_agent' => $this->get_user_agent(),
			]
		);
	}

	/**
	 * Check if given URL is excluded.
	 *
	 * @param string $uri The URL to check for exclusion.
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
	 * Get user-agent header.
	 *
	 * @return string
	 */
	private function get_user_agent() {
		$u_agent = Param::server( 'HTTP_USER_AGENT' );
		if ( empty( $u_agent ) ) {
			return '';
		}

		$parsed = $this->parse_user_agent( $u_agent );
		$nice_ua = '';
		if ( ! empty( $parsed['browser'] ) ) {
			$nice_ua .= $parsed['browser'];
		}
		if ( ! empty( $parsed['version'] ) ) {
			$nice_ua .= ' ' . $parsed['version'];
		}

		return $nice_ua . ' | ' . $u_agent;
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

		$parser = new UserAgentParser();
		$agent  = $parser->parse( $u_agent );

		return [
			'platform' => $agent->platform(),
			'browser'  => $agent->browser(),
			'version'  => $agent->browserVersion(),
		];
	}
}
