<?php
/**
 * The Redirections Module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use RankMath\Helpers\Security;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Conditional;

/**
 * Redirections class.
 *
 * @codeCoverageIgnore
 */
class Redirections {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->load_admin();

		if ( ! is_admin() ) {
			// Delay the redirection when BuddyPress plugin  is active since it uses template_redirect hook to show the group page content.
			$hook = class_exists( 'BuddyPress' ) ? 'template_redirect' : 'wp';
			$this->action( $hook, 'do_redirection', 11 );
		}

		if ( Helper::has_cap( 'redirections' ) ) {
			$this->action( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		}

		if ( $this->disable_auto_redirect() ) {
			remove_action( 'template_redirect', 'wp_old_slug_redirect' );
		}
	}

	/**
	 * Load redirection admin and the REST API.
	 */
	private function load_admin() {
		if ( is_admin() ) {
			$this->admin = new Admin();
		}

		if ( is_admin() || Conditional::is_rest() ) {
			new Watcher();
		}
	}

	/**
	 * Do redirection on frontend.
	 */
	public function do_redirection() {
		if (
			$this->is_wp_login() ||
			is_customize_preview() ||
			Conditional::is_ajax() ||
			! isset( $_SERVER['REQUEST_URI'] ) ||
			empty( $_SERVER['REQUEST_URI'] ) ||
			$this->is_script_uri_or_http_x() ||
			isset( $_GET['elementor-preview'] )
		) {
			return;
		}

		$redirector = new Redirector();
	}

	/**
	 * Add admin bar item.
	 *
	 * @param Admin_Bar_Menu $menu Menu class instance.
	 */
	public function admin_bar_items( $menu ) {
		$menu->add_sub_menu(
			'redirections',
			[
				'title'    => esc_html__( 'Redirections', 'rank-math' ),
				'href'     => Helper::get_admin_url( 'redirections' ),
				'meta'     => [ 'title' => esc_html__( 'Create and edit redirections', 'rank-math' ) ],
				'priority' => 50,
			]
		);

		$menu->add_sub_menu(
			'redirections-edit',
			[
				'title' => esc_html__( 'Manage Redirections', 'rank-math' ),
				'href'  => Helper::get_admin_url( 'redirections' ),
				'meta'  => [ 'title' => esc_html__( 'Create and edit redirections', 'rank-math' ) ],
			],
			'redirections'
		);

		$menu->add_sub_menu(
			'redirections-settings',
			[
				'title' => esc_html__( 'Redirection Settings', 'rank-math' ),
				'href'  => Helper::get_admin_url( 'options-general' ) . '#setting-panel-redirections',
				'meta'  => [ 'title' => esc_html__( 'Redirection Settings', 'rank-math' ) ],
			],
			'redirections'
		);

		if ( ! is_admin() ) {
			$menu->add_sub_menu(
				'redirections-redirect-me',
				[
					'title' => esc_html__( '&raquo; Redirect this page', 'rank-math' ),
					'href'  => Security::add_query_arg_raw( 'url', urlencode( ltrim( Param::server( 'REQUEST_URI' ), '/' ) ), Helper::get_admin_url( 'redirections' ) ),
					'meta'  => [ 'title' => esc_html__( 'Redirect the current URL', 'rank-math' ) ],
				],
				'redirections'
			);
		}
	}

	/**
	 * Check if request is WordPress login.
	 *
	 * @return boolean
	 */
	private function is_wp_login() {
		$uri = Param::server( 'REQUEST_URI' );
		if ( Str::contains( 'wp-admin', $uri ) || Str::contains( 'wp-login', $uri ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if request is script URI or a http-x request.
	 *
	 * @return boolean
	 */
	private function is_script_uri_or_http_x() {
		if ( isset( $_SERVER['SCRIPT_URI'] ) && ! empty( $_SERVER['SCRIPT_URI'] ) && admin_url( 'admin-ajax.php' ) === Param::server( 'SCRIPT_URI' ) ) {
			return true;
		}

		if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( Param::server( 'HTTP_X_REQUESTED_WITH' ) ) === 'xmlhttprequest' ) {
			return true;
		}

		return false;
	}

	/**
	 * Disable Auto-Redirect.
	 *
	 * @return bool
	 */
	private function disable_auto_redirect() {
		return get_option( 'permalink_structure' ) && Helper::get_settings( 'general.redirections_post_redirect' );
	}
}
