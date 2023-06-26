<?php
/**
 * The admin pages of the plugin.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Runner;
use RankMath\Traits\Hooker;
use MyThemeShop\Admin\Page;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Admin_Menu class.
 *
 * @codeCoverageIgnore
 */
class Admin_Menu implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'init', 'register_pages' );
		$this->action( 'admin_menu', 'fix_first_submenu', 999 );
		$this->action( 'admin_head', 'icon_css' );
	}

	/**
	 * Register admin pages for plugin.
	 */
	public function register_pages() {
		$this->maybe_deregister();

		if ( Helper::is_invalid_registration() && ! is_network_admin() ) {
			return;
		}

		$current_user = wp_get_current_user();
		$capabilities = array_keys( Helper::get_roles_capabilities() );

		if ( empty( array_intersect( $current_user->roles, $capabilities ) ) && ! current_user_can( 'setup_network' ) ) {
			return;
		}

		// Dashboard / Welcome / About.
		new Page(
			'rank-math',
			esc_html__( 'Rank Math', 'rank-math' ),
			[
				'position'   => 50,
				'capability' => 'level_1',
				'icon'       => 'data:image/svg+xml;base64,' . \base64_encode( '<svg viewBox="0 0 462.03 462.03" xmlns="http://www.w3.org/2000/svg" width="20"><g fill="#fff"><path d="m462 234.84-76.17 3.43 13.43 21-127 81.18-126-52.93-146.26 60.97 10.14 24.34 136.1-56.71 128.57 54 138.69-88.61 13.43 21z"/><path d="m54.1 312.78 92.18-38.41 4.49 1.89v-54.58h-96.67zm210.9-223.57v235.05l7.26 3 89.43-57.05v-181zm-105.44 190.79 96.67 40.62v-165.19h-96.67z"/></g></svg>' ),
				'render'     => Admin_Helper::get_view( 'dashboard' ),
				'classes'    => [ 'rank-math-page' ],
				'assets'     => [
					'styles'  => [ 'rank-math-dashboard' => '' ],
					'scripts' => [
						'rank-math-dashboard' => '',
					],
				],
				'is_network' => is_network_admin() && Helper::is_plugin_active_for_network(),
			]
		);
	}

	/**
	 * Fix first submenu name.
	 */
	public function fix_first_submenu() {
		global $submenu;
		if ( ! isset( $submenu['rank-math'] ) ) {
			return;
		}

		if ( 'Rank Math' === $submenu['rank-math'][0][0] ) {
			if ( current_user_can( 'manage_options' ) ) {
				$submenu['rank-math'][0][0] = esc_html__( 'Dashboard', 'rank-math' );
			} else {
				unset( $submenu['rank-math'][0] );
			}
		}

		if ( empty( $submenu['rank-math'] ) ) {
			return;
		}

		$submenu['rank-math'][] = [ esc_html__( 'Help &amp; Support', 'rank-math' ) . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>', 'level_1', KB::get( 'knowledgebase', 'Sidebar Help Link' ) ];

		// Store ID of first_menu item so we can use it in the Admin menu item.
		set_transient( 'rank_math_first_submenu_id', array_values( $submenu['rank-math'] )[0][2] );
	}

	/**
	 * Print icon CSS for admin menu bar.
	 */
	public function icon_css() {
		?>
		<script type="text/javascript">
			// Open RM KB menu link in the new tab.
			jQuery( document ).ready( function( $ ) {
				$( "ul#adminmenu a[href$='<?php KB::the( 'kb-seo-suite', 'Sidebar Help Link' ); ?>']" ).attr( 'target', '_blank' );
			} );
		</script>
		<style>
			#wp-admin-bar-rank-math .rank-math-icon {
				display: inline-block;
				top: 6px;
				position: relative;
				padding-right: 10px;
				max-width: 20px;
			}
			#wp-admin-bar-rank-math .rank-math-icon svg {
				fill-rule: evenodd;
				fill: #dedede;
			}
			#wp-admin-bar-rank-math:hover .rank-math-icon svg {
				fill-rule: evenodd;
				fill: #00b9eb;
			}
			#toplevel_page_rank-math:not(.wp-has-submenu) {
				display: none;
			}
			.multisite.network-admin #toplevel_page_rank-math {
				display: block;
			}
		</style>
		<?php
	}

	/**
	 * Check for deactivation.
	 */
	private function maybe_deregister() {
		if ( ! Helper::has_cap( 'general' ) || 'deregister' !== Param::post( 'registration-action' ) ) {
			return;
		}

		$nonce = Param::post( '_wpnonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'rank_math_register_product' ) ) {
			return;
		}

		Admin_Helper::deregister_user();
	}
}
