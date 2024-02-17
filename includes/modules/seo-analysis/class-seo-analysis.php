<?php
/**
 * The SEO Analyzer module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\SEO_Analysis;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Analysis class.
 */
class SEO_Analysis {

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
		if ( Helper::is_heartbeat() ) {
			return;
		}

		if ( is_admin() ) {
			$this->admin = new Admin();
		}

		if ( Helper::has_cap( 'rank_math_site_analysis' ) ) {
			$this->action( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		}

		$this->filter( 'rank_math/seo_analysis/admin_tab_view', 'add_tab_previews', 10, 2 );
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
				'title'    => esc_html__( 'SEO Analyzer', 'rank-math' ),
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
					'title' => $link ? esc_html__( 'Analyze this Page', 'rank-math' ) : esc_html__( 'SEO Analyzer', 'rank-math' ),
					'href'  => Helper::get_admin_url( 'seo-analysis' ) . ( $link ? '&u=' . rawurlencode( $link ) : '' ),
					'meta'  => [ 'title' => esc_html__( 'SEO Analysis for this page', 'rank-math' ) ],
				],
				'seo-analysis'
			);
		}
	}

	/**
	 * Add PRO tab previews.
	 *
	 * @param string $file        Include file.
	 * @param string $current_tab Current tab.
	 *
	 * @return string
	 */
	public function add_tab_previews( $file, $current_tab ) {
		if ( 'competitor_analyzer' === $current_tab ) {
			$file = dirname( __FILE__ ) . '/views/competitor-analysis.php';
		}

		return $file;
	}
}
