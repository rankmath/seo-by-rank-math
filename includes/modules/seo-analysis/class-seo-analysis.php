<?php
/**
 * The SEO Analysis module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\SEO_Analysis;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Analysis class.
 */
class SEO_Analysis {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() ) {
			return;
		}

		if ( is_admin() ) {
			$this->admin = new Admin();
		}

		if ( Helper::has_cap( 'rank_math_site_analysis' ) ) {
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
			'seo-analysis',
			[
				'title'    => esc_html__( 'SEO Analysis', 'rank-math' ),
				'href'     => Helper::get_admin_url( 'seo-analysis' ),
				'meta'     => [ 'title' => esc_html__( 'Site-wide analysis', 'rank-math' ) ],
				'priority' => 50,
			]
		);

		if ( ! is_admin() && ! is_404() ) {
			$link = is_front_page() ? '' : ( is_ssl() ? 'https' : 'http' ) . '://' . Param::server( 'HTTP_HOST' ) . Param::server( 'REQUEST_URI' );

			$menu->add_sub_menu(
				'analyze',
				[
					'title' => $link ? esc_html__( 'Analyze this Page', 'rank-math' ) : esc_html__( 'SEO Analysis', 'rank-math' ),
					'href'  => Helper::get_admin_url( 'seo-analysis' ) . ( $link ? '&u=' . rawurlencode( $link ) : '' ),
					'meta'  => [ 'title' => esc_html__( 'SEO Analysis for this page', 'rank-math' ) ],
				],
				'seo-analysis'
			);
		}
	}
}
