<?php
/**
 * The SEO Analyzer module - admin side functionality.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\SEO_Analysis;

use RankMath\Module\Base;
use RankMath\Admin\Page;
use RankMath\Helper;
use RankMath\KB;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends Base {

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Module directory.
	 *
	 * @var string
	 */
	public $directory = '';

	/**
	 * Module page.
	 *
	 * @var object
	 */
	public $page;

	/**
	 * SEO Analyzer object.
	 *
	 * @var object
	 */
	public $analyzer;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		$directory = __DIR__;
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
			Helper::add_json( 'results', $this->analyzer->get_results_from_storage() );
			Helper::add_json( 'analyzeSubpage', $this->analyzer->analyse_subpage );
			Helper::add_json( 'analyzeUrl', $this->analyzer->analyse_url );
		}
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		$new_label = '';
		if ( ! get_option( 'rank_math_viewed_seo_analyer', false ) && strtotime( '28 December 2022' ) > get_option( 'rank_math_install_date' ) ) {
			$new_label = '<span class="rank-math-new-label" style="color:#ed5e5e;font-size:10px;font-weight:normal;">' . esc_html__( 'New!', 'rank-math' ) . '</span>';
		}

		$this->page = new Page(
			'rank-math-seo-analysis',
			// Translators: placeholder is the new Rank Math label.
			sprintf( esc_html__( 'SEO Analyzer %s', 'rank-math' ), $new_label ),
			[
				'position'   => 60,
				'parent'     => 'rank-math',
				'capability' => 'rank_math_site_analysis',
				'classes'    => [ 'rank-math-page' ],
				'render'     => $this->directory . '/views/main.php',
				'assets'     => [
					'styles'  => [
						'wp-components'          => '',
						'rank-math-common'       => '',
						'rank-math-seo-analysis' => $uri . '/assets/css/seo-analysis.css',
					],
					'scripts' => [
						'wp-element'             => '',
						'rank-math-components'   => '',
						'rank-math-seo-analysis' => $uri . '/assets/js/seo-analysis.js',
					],
					'json'    => [
						'connectUrl'      => Helper::get_connect_url(),
						'isSiteConnected' => Helper::is_site_connected(),
					],
				],
			]
		);
	}
}
