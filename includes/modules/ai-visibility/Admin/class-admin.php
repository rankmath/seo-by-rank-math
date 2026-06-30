<?php
/**
 * The AI Visibility module admin page registration.
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\AI_Visibility
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\AI_Visibility;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Admin\Page;
use RankMath\Admin\Admin_Helper;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin {

	use Hooker;

	/**
	 * Admin page slug.
	 */
	const PAGE_SLUG = 'rank-math-ai-visibility';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->filter( 'rank_math/admin_pages', 'add_admin_page' );
		$this->action( 'init', 'register_admin_page' );
		$this->action( 'admin_enqueue_scripts', 'enqueue_mixpanel' );
		$this->action( 'rank_math/admin_bar/items', 'admin_bar_items' );
	}

	/**
	 * Allow the AI Visibility page slug through Rank Math's page allowlist.
	 *
	 * @param array $pages Existing allowed page slugs.
	 *
	 * @return array
	 */
	public function add_admin_page( $pages ) {
		$pages[] = 'rank-math_page_' . self::PAGE_SLUG;
		return $pages;
	}

	/**
	 * Register the admin page with Rank Math's Page wrapper.
	 */
	public function register_admin_page() {
		$new_label = '<span class="rank-math-new-label" style="color:#ed5e5e;font-size:10px;font-weight:normal;">' . esc_html__( 'New!', 'seo-by-rank-math' ) . '</span>';
		new Page(
			self::PAGE_SLUG,
			esc_html__( 'AI Visibility', 'seo-by-rank-math' ),
			[
				'position'   => 4,
				'parent'     => 'rank-math',
				// Translators: placeholder is the new label.
				'menu_title' => sprintf( esc_html__( 'AI Visibility %s', 'seo-by-rank-math' ), $new_label ),
				'render'     => function () {
					echo '<div id="rank-math-ai-visibility-container"></div>';
				},
				'classes'    => [ 'rank-math-page' ],
				'assets'     => [
					'styles'  => [
						'rank-math-common' => '',
					],
					'scripts' => [
						'lodash'                  => '',
						'wp-api-fetch'            => '',
						'wp-components'           => '',
						'wp-element'              => '',
						'wp-url'                  => '',
						'rank-math-components'    => '',
						'rank-math-ai-visibility' => RANK_MATH_URL . 'includes/modules/ai-visibility/assets/js/ai-visibility.js',
					],
					'json'    => [
						'aiVisibility' => [
							'isSiteConnected' => Helper::is_site_connected(),
							'activateUrl'     => Admin_Helper::get_activate_url( admin_url( 'admin.php?page=rank-math-ai-visibility' ) ),
							'isSiteUrlValid'  => Admin_Helper::is_site_url_valid(),
							'isPro'           => defined( 'RANK_MATH_PRO_FILE' ),
							'plan'            => Helper::get_content_ai_plan(),
							'locales'         => array_values(
								array_filter(
									array_map(
										function ( $key, $label ) {
											// 'all' (Worldwide) has no country code — skip it.
											if ( 'all' === $key ) {
												return null;
											}
											// Extract country code: 'en_US' → 'US', 'zh-TW_TW' → 'TW'.
											$parts        = explode( '_', $key );
											$country_code = strtoupper( end( $parts ) );
											return [
												'label' => $label,
												'value' => $country_code,
											];
										},
										array_keys( Helper::choices_contentai_countries() ),
										Helper::choices_contentai_countries()
									)
								)
							),
						],
					],
				],
			]
		);
	}

	/**
	 * Enqueue Mixpanel script and inject tracking data for JS events on this page.
	 */
	public function enqueue_mixpanel() {
		if ( Param::get( 'page' ) !== self::PAGE_SLUG ) {
			return;
		}

		if ( ! rank_math()->tracking->is_opted_in() ) {
			return;
		}

		rank_math()->tracking->enqueue_mixpanel_for_page();
	}

	/**
	 * Add an "AI Visibility" entry to the Rank Math admin-bar menu.
	 *
	 * @param \RankMath\Admin\Admin_Bar_Menu $menu Admin bar menu object.
	 */
	public function admin_bar_items( $menu ) {
		if ( ! Helper::has_cap( 'general' ) ) {
			return;
		}

		$menu->add_sub_menu(
			'ai-visibility',
			[
				'title'    => esc_html__( 'AI Visibility', 'seo-by-rank-math' ),
				'href'     => Helper::get_admin_url( 'ai-visibility' ),
				'priority' => 65,
			]
		);
	}
}
