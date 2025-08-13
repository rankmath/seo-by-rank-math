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
use RankMath\Helpers\Param;
use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Traits\Ajax;
use RankMath\Admin\Page;

defined( 'ABSPATH' ) || exit;

/**
 * Admin_Menu class.
 *
 * @codeCoverageIgnore
 */
class Admin_Menu implements Runner {

	use Hooker;
	use Ajax;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'init', 'register_pages' );
		$this->action( 'admin_menu', 'fix_admin_menu', 999 );
		$this->action( 'admin_head', 'icon_css' );
		$this->ajax( 'remove_offer_page', 'remove_offer_page' );
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

		$modules = rank_math()->manager->modules;
		$data    = [];
		foreach ( $modules as $id => $module ) {
			$data[ $id ] = array_merge(
				[
					'id'       => $module->get_id(),
					'isActive' => $module->is_active(),
					'isHidden' => $module->is_hidden(),
					'isPro'    => $module->is_pro_module(),
				],
				$module->get_args()
			);
		}

		// Dashboard / Welcome / About.
		new Page(
			'rank-math',
			esc_html__( 'Rank Math', 'rank-math' ),
			[
				'position'   => 50,
				'menu_title' => 'Rank Math',
				'capability' => 'level_1',
				'icon'       => 'data:image/svg+xml;base64,' . \base64_encode( '<svg viewBox="0 0 462.03 462.03" xmlns="http://www.w3.org/2000/svg" width="20"><g fill="#fff"><path d="m462 234.84-76.17 3.43 13.43 21-127 81.18-126-52.93-146.26 60.97 10.14 24.34 136.1-56.71 128.57 54 138.69-88.61 13.43 21z"/><path d="m54.1 312.78 92.18-38.41 4.49 1.89v-54.58h-96.67zm210.9-223.57v235.05l7.26 3 89.43-57.05v-181zm-105.44 190.79 96.67 40.62v-165.19h-96.67z"/></g></svg>' ), // phpcs:ignore -- This should not cause any issue as we only pass a static svg code.
				'render'     => Admin_Helper::get_view( 'dashboard' ),
				'classes'    => [ 'rank-math-page' ],
				'assets'     => [
					'styles'  => [ 'rank-math-dashboard' => '' ],
					'scripts' => [
						'lodash'               => '',
						'rank-math-components' => '',
						'rank-math-dashboard'  => '',
						'rank-math-modules'    => rank_math()->plugin_url() . 'assets/admin/js/modules.js',
					],
					'json'    => [
						'isPro'                    => defined( 'RANK_MATH_PRO_FILE' ),
						'isSiteConnected'          => Helper::is_site_connected(),
						'registerProductNonce'     => wp_create_nonce( 'rank_math_register_product' ),
						'activateUrl'              => Admin_Helper::get_activate_url(),
						'isSiteUrlValid'           => Admin_Helper::is_site_url_valid(),
						'isAdvancedMode'           => Helper::is_advanced_mode(),
						'contentAiPlan'            => Helper::get_content_ai_plan(),
						'data'                     => $data,
						'isPluginActiveForNetwork' => Helper::is_plugin_active_for_network(),
						'isNetworkAdmin'           => is_network_admin(),
						'canUser'                  => [
							'manageOptions'  => current_user_can( 'manage_options' ),
							'setupNetwork'   => current_user_can( 'setup_network' ),
							'installPlugins' => current_user_can( 'install_plugins' ),
						],
					],
				],
				'is_network' => is_network_admin() && Helper::is_plugin_active_for_network(),
			]
		);
	}

	/**
	 * Fix menu names.
	 */
	public function fix_admin_menu() {

		// Replace the main menu name "Rank Math" with "Rank Math SEO".
		global $menu;
		foreach ( $menu as $key => $item ) {
			if ( 'Rank Math' === $item[0] ) {
				$menu[ $key ][0] = esc_html__( 'Rank Math SEO', 'rank-math' ); // phpcs:ignore -- This is required to change the menu name without changing its slug `rank-math`
				break;
			}
		}

		// Replace the first submenu name "Rank Math" with "Dashboard".
		global $submenu;
		if ( ! isset( $submenu['rank-math'] ) ) {
			return;
		}

		if ( 'Rank Math' === $submenu['rank-math'][0][0] ) {
			if ( current_user_can( 'manage_options' ) ) {
				$plan         = Helper::get_content_ai_plan();
				$notification = ( empty( $plan ) || 'free' === $plan ) && get_option( 'rank_math_view_modules' ) ? ' <span class="awaiting-mod count-1"><span class="pending-count" aria-hidden="true">1</span></span>' : '';

				$submenu['rank-math'][0][0] = esc_html__( 'Dashboard', 'rank-math' ) . $notification; // phpcs:ignore -- This is required to change the menu name when the plugin is not configured.
			} else {
				unset( $submenu['rank-math'][0] );
			}
		}

		if ( empty( $submenu['rank-math'] ) ) {
			return;
		}

		$submenu['rank-math'][] = [ esc_html__( 'Help &amp; Support', 'rank-math' ) . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>', 'level_1', KB::get( 'knowledgebase', 'Sidebar Help Link' ) ]; // phpcs:ignore -- A custom link to our KB article.
		$this->add_offer_link( $submenu );

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
				$( "ul#adminmenu a[href$='<?php KB::the( 'knowledgebase', 'Sidebar Help Link' ); ?>']" ).attr( 'target', '_blank' );
				$( "ul#adminmenu a[href$='<?php KB::the( 'offer', 'Offer Menu Item' ); ?>']" ).attr( 'target', '_blank' ).on( 'click', function() {
					$( this ).remove()
					$.ajax( {
						url: window.ajaxurl,
						type: 'POST',
						data: {
							action: 'rank_math_remove_offer_page',
							security: rankMath.security,
						},
					} )
				} );
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
			#toplevel_page_rank-math a[href$='<?php KB::the( 'offer', 'Offer Menu Item' ); ?>'],
			#toplevel_page_rank-math a[href$='<?php KB::the( 'offer', 'Offer Menu Item' ); ?>']:hover,
			#toplevel_page_rank-math a[href$='<?php KB::the( 'offer', 'Offer Menu Item' ); ?>']:focus {
				background-color: #10AC84;
				color: #fff;
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

	/**
	 * Function to add Offer link based on the date range.
	 *
	 * @param array $submenu Submenu items.
	 */
	private function add_offer_link( &$submenu ) {
		$offer = $this->get_active_offer();
		if ( ! $offer ) {
			return;
		}

		$submenu['rank-math'][] = [ current( $offer ) . '&nbsp;', 'level_1', KB::get( 'offer', 'Offer Menu Item' ) ];
	}

	/**
	 * Ajax handler callback to store active offer so it doesn't show up again on the site.
	 */
	public function remove_offer_page() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$offer = $this->get_active_offer();
		set_site_transient( 'rank_math_active_offer', key( $offer ) );
	}

	/**
	 * Function to get active offer
	 */
	private function get_active_offer() {
		// Early Bail if PRO plugin is active.
		if ( defined( 'RANK_MATH_PRO_FILE' ) ) {
			return false;
		}

		$timezone     = new \DateTimeZone( 'Asia/Kolkata' );
		$current_date = new \DateTime( 'now', $timezone );
		$dates        = [
			'christmas'    => [
				'start' => '2023-12-17',
				'end'   => '2023-12-26',
				'text'  => esc_html__( 'Christmas Sale', 'rank-math' ),
			],
			'new-year'     => [
				'start' => '2023-12-31',
				'end'   => '2024-01-05',
				'text'  => esc_html__( 'New Year Sale', 'rank-math' ),
			],
			'anniversary'  => [
				'start' => '2024-11-06',
				'end'   => '2024-11-13',
				'text'  => esc_html__( 'Anniversary Sale', 'rank-math' ),
			],
			'black-friday' => [
				'start' => '2024-11-27',
				'end'   => '2024-12-01',
				'text'  => esc_html__( 'Black Friday Sale', 'rank-math' ),
			],
			'cyber-monday' => [
				'start' => '2024-12-02',
				'end'   => '2024-12-04',
				'text'  => esc_html__( 'Cyber Monday Sale', 'rank-math' ),
			],
		];

		$stored_offer = get_site_transient( 'rank_math_active_offer' );
		$active_offer = '';
		foreach ( $dates as $key => $date ) {
			$start_date = new \DateTime( $date['start'] . ' 16:00:00', $timezone );
			$end_date   = new \DateTime( $date['end'] . ' 16:00:00', $timezone );

			if ( $stored_offer !== $key && $current_date >= $start_date && $current_date <= $end_date ) {
				$active_offer = [ $key => $date['text'] ];
				break;
			}
		}

		return $active_offer;
	}
}
