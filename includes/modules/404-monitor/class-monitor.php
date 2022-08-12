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

		if ( Helper::has_cap( '404_monitor' ) && Conditional::is_rest() ) {
			$this->action( 'rank_math/dashboard/widget', 'dashboard_widget', 11 );
		}

		$this->action( $this->get_hook(), 'capture_404' );

		if ( Helper::has_cap( '404_monitor' ) ) {
			$this->action( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		}
	}

	/**
	 * Add stats in the admin dashboard widget.
	 */
	public function dashboard_widget() {
		$data = DB::get_stats();
		?>
		<h3>
			<?php esc_html_e( '404 Monitor', 'rank-math' ); ?>
			<a href="<?php echo esc_url( Helper::get_admin_url( '404-monitor' ) ); ?>" class="rank-math-view-report" title="<?php esc_html_e( 'View Report', 'rank-math' ); ?>"><i class="dashicons dashicons-chart-bar"></i></a>
		</h3>
		<div class="rank-math-dashabord-block">
			<div>
				<h4>
					<?php esc_html_e( 'Log Count', 'rank-math' ); ?>
					<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php esc_html_e( 'Total number of 404 pages opened by the users.', 'rank-math' ); ?></span></span>
				</h4>
				<strong class="text-large"><?php echo esc_html( Str::human_number( $data->total ) ); ?></strong>
			</div>
			<div>
				<h4>
					<?php esc_html_e( 'URL Hits', 'rank-math' ); ?>
					<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php esc_html_e( 'Total number visits received on all the 404 pages.', 'rank-math' ); ?></span></span>
				</h4>
				<strong class="text-large"><?php echo esc_html( Str::human_number( $data->hits ) ); ?></strong>
			</div>
		</div>
		<?php
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
			$rule['exclude'] = empty( $rule['exclude'] ) ? '' : $this->sanitize_exclude_pattern( $rule['exclude'], $rule['comparison'] );

			if ( ! empty( $rule['exclude'] ) && Str::comparison( $rule['exclude'], $uri, $rule['comparison'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if regex pattern has delimiters or not, and add them if not.
	 *
	 * @param string $pattern The pattern to check.
	 * @param string $comparison The comparison type.
	 *
	 * @return string
	 */
	private function sanitize_exclude_pattern( $pattern, $comparison ) {
		if ( 'regex' !== $comparison ) {
			return $pattern;
		}

		if ( preg_match( '[^(?:([^a-zA-Z0-9\\\\]).*\\1|\\(.*\\)|\\{.*\\}|\\[.*\\]|<.*>)[imsxADSUXJu]*$]', $pattern ) ) {
			return $pattern;
		}

		return '[' . addslashes( $pattern ) . ']';
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

		$parsed  = $this->parse_user_agent( $u_agent );
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

	/**
	 * Function to get the hook name depending on the theme.
	 *
	 * @return string WP hook.
	 */
	private function get_hook() {
		if ( defined( 'CT_VERSION' ) ) {
			return 'oxygen_enqueue_frontend_scripts';
		}

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			return 'wp_head';
		}

		return 'get_header';
	}
}
