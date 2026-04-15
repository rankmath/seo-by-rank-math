<?php
/**
 * The Links module admin page registration.
 *
 * @since      1.0.266
 * @package    RankMath
 * @subpackage RankMath\Links\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Links\Admin;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Admin\Page;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin {

	use Hooker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->filter( 'rank_math/admin_pages', 'add_links_page' );
		$this->action( 'init', 'register_admin_page' );
	}

	/**
	 * Add Links page to Rank Math admin menu.
	 *
	 * @param array $pages Existing pages.
	 * @return array
	 */
	public function add_links_page( $pages ) {
		$pages[] = 'rank-math_page_rank-math-links-page';
		return $pages;
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$new_label = '<span class="rank-math-new-label" style="color:#ed5e5e;font-size:10px;font-weight:normal;">' . esc_html__( 'New!', 'rank-math' ) . '</span>';
		new Page(
			'rank-math-links-page',
			esc_html__( 'Link Genius', 'rank-math' ),
			[
				'position'   => 4,
				'parent'     => 'rank-math',
				// Translators: placeholder is the new label.
				'menu_title' => sprintf( esc_html__( 'Link Genius %s', 'rank-math' ), $new_label ),
				'render'     => function () {
					echo '<div id="rank-math-links-page-container"></div>';
				},
				'classes'    => [ 'rank-math-page' ],
				'assets'     => [
					'styles'  => [
						'rank-math-common' => '',
					],
					'scripts' => [
						'lodash'               => '',
						'wp-components'        => '',
						'wp-element'           => '',
						'rank-math-components' => '',
						'rank-math-links-page' => RANK_MATH_URL . 'includes/modules/links/assets/js/links-page.js',
					],
					'json'    => [
						'links' => [
							'postTypes' => Helper::choices_post_types(),
							'imagesUrl' => RANK_MATH_URL . 'includes/modules/links/assets/images/',
						],
					],
				],
			]
		);

		/**
		 * Fires after the Links admin page is registered.
		 *
		 * PRO plugin (Link Genius) hooks here to dequeue the Free bundle
		 * and enqueue its own full-featured React app instead.
		 *
		 * @since 1.0.266
		 */
		do_action( 'rank_math/links/admin_page_registered' );
	}
}
