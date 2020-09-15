<?php
/**
 * The SEO Analysis Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\SEO_Analysis;

use RankMath\Helper;
use RankMath\Module\Base;
use MyThemeShop\Admin\Page;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends Base {

	/**
	 * The Constructor.
	 */
	public function __construct() {

		$directory = dirname( __FILE__ );
		$this->config(
			[
				'id'        => 'seo-analysis',
				'directory' => $directory,
			]
		);
		parent::__construct();

		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || $this->page->is_current_page() ) {
			include_once 'seo-analysis-tests.php';
			$this->analyzer = new SEO_Analyzer();
		}
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		$this->page = new Page(
			'rank-math-seo-analysis',
			esc_html__( 'SEO Analysis', 'rank-math' ),
			[
				'position'   => 60,
				'parent'     => 'rank-math',
				'capability' => 'rank_math_site_analysis',
				'classes'    => [ 'rank-math-page' ],
				'render'     => $this->directory . '/views/main.php',
				'assets'     => [
					'styles'  => [
						'rank-math-common'       => '',
						'rank-math-seo-analysis' => $uri . '/assets/css/seo-analysis.css',
					],
					'scripts' => [
						'circle-progress'        => $uri . '/assets/js/circle-progress.min.js',
						'rank-math-seo-analysis' => $uri . '/assets/js/seo-analysis.js',
					],
				],
			]
		);
	}
}
