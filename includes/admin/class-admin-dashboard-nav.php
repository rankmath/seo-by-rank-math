<?php
/**
 * The nav tabs on the Dashboard page.
 *
 * @since      1.0.40
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Helpers\Security;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Dashboard Nav class.
 *
 * @codeCoverageIgnore
 */
class Admin_Dashboard_Nav {

	/**
	 * Display dashboard tabs.
	 */
	public function display() {
		$nav_links = $this->get_nav_links();
		if ( empty( $nav_links ) ) {
			return;
		}
		?>
		<div class="rank-math-tab-nav" role="tablist" aria-orientation="horizontal">
			<?php
			foreach ( $nav_links as $id => $link ) {
				$this->nav_link( $link );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Get URL for dashboard nav links.
	 *
	 * @param  array $link Link data.
	 * @return string      Link URL.
	 */
	public function get_link_url( $link ) {
		return is_network_admin() ?
			Security::add_query_arg(
				[
					'page' => 'rank-math',
					'view' => $link['id'],
				],
				network_admin_url( 'admin.php' )
			) :
			Helper::get_admin_url( $link['url'], $link['args'] );
	}

	/**
	 * Output dashboard nav link.
	 *
	 * @param  array $link Link data.
	 * @return void
	 */
	public function nav_link( $link ) {
		if ( isset( $link['cap'] ) && ! current_user_can( $link['cap'] ) ) {
			return;
		}

		$default_tab = is_network_admin() ? 'help' : 'modules';
		?>
		<a
			class="rank-math-tab<?php echo Param::get( 'view', $default_tab ) === sanitize_html_class( $link['id'] ) ? ' is-active' : ''; ?>"
			href="<?php echo esc_url( $this->get_link_url( $link ) ); ?>"
			title="<?php echo esc_attr( $link['title'] ); ?>">
			<?php echo esc_html( $link['title'] ); ?>
		</a>
		<?php
	}

	/**
	 * Get dashbaord navigation links
	 *
	 * @return array
	 */
	private function get_nav_links() {
		$links = [
			'modules' => [
				'id'    => 'modules',
				'url'   => '',
				'args'  => 'view=modules',
				'cap'   => 'manage_options',
				'title' => esc_html__( 'Modules', 'rank-math' ),
			],
			'help'    => [
				'id'    => 'help',
				'url'   => '',
				'args'  => 'view=help',
				'cap'   => 'manage_options',
				'title' => esc_html__( 'Help', 'rank-math' ),
			],
			'wizard'  => [
				'id'    => 'wizard',
				'url'   => 'wizard',
				'args'  => '',
				'cap'   => 'manage_options',
				'title' => esc_html__( 'Setup Wizard', 'rank-math' ),
			],
		];

		if ( Helper::is_advanced_mode() ) {
			$links['import-export'] = [
				'id'    => 'import-export',
				'url'   => 'status',
				'args'  => 'view=import_export',
				'cap'   => 'install_plugins',
				'title' => esc_html__( 'Import &amp; Export', 'rank-math' ),
			];
		}

		if ( Helper::is_plugin_active_for_network() ) {
			unset( $links['help'] );
		}

		if ( is_network_admin() ) {
			$links = [];
		}

		return apply_filters( 'rank_math/admin/dashboard_nav_links', $links );
	}
}
