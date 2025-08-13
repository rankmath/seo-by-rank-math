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

use RankMath\Traits\Hooker;
use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Helpers\Security;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Redirections class.
 */
class Redirections {

	use Hooker;

	/**
	 * Admin object.
	 *
	 * @var Admin
	 */
	public $admin;

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

			if ( Helper::is_rest() ) {
				$this->action( 'rank_math/dashboard/widget', 'dashboard_widget', 12 );
			}
		}

		if ( $this->disable_auto_redirect() ) {
			remove_action( 'template_redirect', 'wp_old_slug_redirect' );
		}
	}

	/**
	 * Add stats in the Rank Math admin dashboard widget.
	 */
	public function dashboard_widget() {
		$data = DB::get_stats();
		?>
		<h3>
			<?php esc_html_e( 'Redirections', 'rank-math' ); ?>
			<a href="<?php echo esc_url( Helper::get_admin_url( 'redirections' ) ); ?>" class="rank-math-view-report" title="<?php esc_html_e( 'View Report', 'rank-math' ); ?>"><i class="dashicons dashicons-chart-bar"></i></a>
		</h3>
		<div class="rank-math-dashboard-block">
			<div>
				<h4>
					<?php esc_html_e( 'Redirection Count', 'rank-math' ); ?>
					<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php esc_html_e( 'Total number of Redirections created in the Rank Math.', 'rank-math' ); ?></span></span>
				</h4>
				<strong class="text-large"><?php echo esc_html( Str::human_number( $data->total ) ); ?></strong>
			</div>
			<div>
				<h4>
					<?php esc_html_e( 'Redirection Hits', 'rank-math' ); ?>
					<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php esc_html_e( 'Total number of hits received by all the Redirections.', 'rank-math' ); ?></span></span>
				</h4>
				<strong class="text-large"><?php echo esc_html( Str::human_number( $data->hits ) ); ?></strong>
			</div>
		</div>
		<?php
	}

	/**
	 * Load redirection admin and the REST API.
	 */
	private function load_admin() {
		if ( is_admin() ) {
			$this->admin = new Admin();
		}

		if ( is_admin() || Helper::is_rest() ) {
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
			Helper::is_ajax() ||
			! isset( $_SERVER['REQUEST_URI'] ) ||
			empty( $_SERVER['REQUEST_URI'] ) ||
			$this->is_script_uri_or_http_x() ||
			(bool) Param::get( 'elementor-preview' )
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
				'href'  => Helper::get_settings_url( 'general', 'redirections' ),
				'meta'  => [ 'title' => esc_html__( 'Redirection Settings', 'rank-math' ) ],
			],
			'redirections'
		);

		if ( ! is_admin() ) {
			$menu->add_sub_menu(
				'redirections-redirect-me',
				[
					'title' => esc_html__( '&raquo; Redirect this page', 'rank-math' ),
					'href'  => Security::add_query_arg_raw( 'url', rawurlencode( ltrim( Param::server( 'REQUEST_URI' ), '/' ) ), Helper::get_admin_url( 'redirections' ) ),
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
