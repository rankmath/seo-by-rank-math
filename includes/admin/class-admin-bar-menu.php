<?php
/**
 * Admin bar menu.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Paper\Paper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Meta;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\Url;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Admin_Bar_Menu class.
 */
class Admin_Bar_Menu {

	use Hooker, Ajax, Meta;

	/**
	 * The unique identifier used for the menu.
	 *
	 * @var string
	 */
	const MENU_IDENTIFIER = 'rank-math';

	/**
	 * Hold menu items.
	 *
	 * @var array
	 */
	private $items = [];

	/**
	 * Constructor method.
	 */
	public function __construct() {
		$this->ajax( 'mark_page_as', 'mark_page_as' );
		$this->action( 'admin_bar_menu', 'add_menu', 100 );
	}

	/**
	 * AJAX function to mark page as Pillar Content/Noindex/Nofollow.
	 */
	public function mark_page_as() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'onpage_general' );

		$what        = Param::post( 'what' );
		$object_id   = Param::post( 'objectID' );
		$object_type = Param::post( 'objectType' );

		if ( ! $what || ! $object_id || ! $object_type ) {
			return 0;
		}

		if ( 'pillar_content' === $what ) {
			$current = $this->get_meta( $object_type, $object_id, 'rank_math_pillar_content' );
			$updated = 'on' === $current ? 'off' : 'on';
			$this->update_meta( $object_type, $object_id, 'rank_math_pillar_content', $updated );
			die( '1' );
		}

		if ( 'noindex' === $what || 'nofollow' === $what ) {
			$robots = (array) $this->get_meta( $object_type, $object_id, 'rank_math_robots' );
			$robots = array_filter( $robots );

			Arr::add_delete_value( $robots, $what );
			$robots = array_unique( $robots );

			$this->update_meta( $object_type, $object_id, 'rank_math_robots', $robots );

			if ( 'noindex' === $what ) {
				$this->do_action( 'sitemap/invalidate_object_type', $object_type, $object_id );
			}

			die( '1' );
		}

		die();
	}

	/**
	 * Add SEO item to admin bar with context-specific submenu items.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance to add the menu to.
	 */
	public function add_menu( $wp_admin_bar ) {
		if ( ! $this->can_add_menu() ) {
			return;
		}

		$this->add_root_menu();

		if ( Helper::has_cap( 'titles' ) ) {
			$this->add_page_menu();
		}

		if ( $this->is_front() ) {
			$this->add_seo_tools();
		}

		if ( $this->can_add_mark_menu() ) {
			$this->add_mark_page_menu();
		}

		/**
		 * Add item to rank math admin bar node.
		 *
		 * @param Admin_Bar_Menu $this Class instance.
		 */
		$this->do_action( 'admin_bar/items', $this );

		$this->add_order();
		uasort( $this->items, [ $this, 'sort_by_priority' ] );
		array_walk( $this->items, [ $wp_admin_bar, 'add_node' ] );
	}

	/**
	 * Keep original order when uasort() deals with equal "priority" values.
	 */
	private function add_order() {
		$order = 0;
		foreach ( $this->items as &$item ) {
			$item['order'] = $order++;
		}
	}

	/**
	 * Add root menu.
	 */
	private function add_root_menu() {
		$first_menu = get_transient( 'rank_math_first_submenu_id' );
		$first_menu = $first_menu && 'rank-math' !== $first_menu ? str_replace( 'rank-math-', '', $first_menu ) : '';

		$this->items['main'] = [
			'id'       => self::MENU_IDENTIFIER,
			'title'    => '<span class="rank-math-icon">' . $this->get_icon() . '</span><span class="rank-math-text">' . esc_html__( 'Rank Math SEO', 'rank-math' ) . '</span>',
			'href'     => Helper::get_admin_url( $first_menu ),
			'meta'     => [ 'title' => esc_html__( 'Rank Math Dashboard', 'rank-math' ) ],
			'priority' => 10,
		];

		if ( current_user_can( 'manage_options' ) ) {
			$this->add_sub_menu(
				'dashboard',
				[
					'title'    => esc_html__( 'Dashboard', 'rank-math' ),
					'href'     => $this->items['main']['href'],
					'meta'     => [ 'title' => esc_html__( 'Dashboard', 'rank-math' ) ],
					'priority' => 20,
				]
			);
		}
	}

	/**
	 * Add page menu.
	 */
	private function add_page_menu() {
		$hash = [
			'add_home_menu'      => is_home(),
			'add_post_type_menu' => is_singular( Helper::get_accessible_post_types() ),
			'add_date_menu'      => is_date(),
			'add_taxonomy_menu'  => is_archive() && ! is_post_type_archive() && ! is_author(),
			'add_search_menu'    => is_search(),
		];

		foreach ( $hash as $func => $can_run ) {
			if ( true === $can_run ) {
				$this->$func();
				break;
			}
		}
	}


	/**
	 * Add homepage menu
	 */
	private function add_home_menu() {
		$this->add_sub_menu(
			'home',
			[
				'title'    => esc_html__( 'Homepage SEO', 'rank-math' ),
				'href'     => Helper::get_admin_url( 'options-titles#setting-panel-homepage' ),
				'meta'     => [ 'title' => esc_html__( 'Edit Homepage SEO Settings', 'rank-math' ) ],
				'priority' => 35,
			]
		);
	}

	/**
	 * Add post_type menu
	 */
	private function add_post_type_menu() {
		$post_type = get_post_type();
		$object    = get_post_type_object( $post_type );
		$this->add_sub_menu(
			'posttype',
			[
				/* translators: Post Type Singular Name */
				'title'    => sprintf( esc_html__( 'SEO Settings for %s', 'rank-math' ), $object->labels->name ),
				'href'     => Helper::get_admin_url( 'options-titles#setting-panel-post-type-' . $post_type ),
				'meta'     => [ 'title' => esc_html__( 'Edit default SEO settings for this post type', 'rank-math' ) ],
				'priority' => 35,
			]
		);
	}

	/**
	 * Add taxonomy menu
	 */
	private function add_taxonomy_menu() {
		$term = get_queried_object();
		if ( empty( $term ) ) {
			return;
		}

		$labels = get_taxonomy_labels( get_taxonomy( $term->taxonomy ) );
		$this->add_sub_menu(
			'tax',
			[
				/* translators: Taxonomy Singular Name */
				'title'    => sprintf( esc_html__( 'SEO Settings for %s', 'rank-math' ), $labels->name ),
				'href'     => Helper::get_admin_url( 'options-titles#setting-panel-taxonomy-' . $term->taxonomy ),
				'meta'     => [ 'title' => esc_html__( 'Edit SEO settings for this archive page', 'rank-math' ) ],
				'priority' => 35,
			]
		);
	}

	/**
	 * Add date archive menu
	 */
	private function add_date_menu() {
		$this->add_sub_menu(
			'date',
			[
				'title'    => esc_html__( 'SEO Settings for Date Archives', 'rank-math' ),
				'href'     => Helper::get_admin_url( 'options-titles#setting-panel-global' ),
				'meta'     => [ 'title' => esc_html__( 'Edit SEO settings for this archive page', 'rank-math' ) ],
				'priority' => 35,
			]
		);
	}

	/**
	 * Add search result menu
	 */
	private function add_search_menu() {
		$this->add_sub_menu(
			'search',
			[
				'title'    => esc_html__( 'SEO Settings for Search Page', 'rank-math' ),
				'href'     => Helper::get_admin_url( 'options-titles#setting-panel-global' ),
				'meta'     => [ 'title' => esc_html__( 'Edit SEO settings for the search results page', 'rank-math' ) ],
				'priority' => 35,
			]
		);
	}

	/**
	 * Add mark page menu.
	 */
	private function add_mark_page_menu() {
		$this->add_sub_menu(
			'mark-me',
			[
				'title'    => esc_html__( 'Mark this page', 'rank-math' ),
				'href'     => '#',
				'priority' => 100,
			]
		);

		$is_pillar_content = '';
		$dashicon_format   = '<span class="dashicons dashicons-%s" style="font-family: dashicons; font-size: 19px;"></span>';

		if ( is_singular( Helper::get_accessible_post_types() ) ) {
			if ( get_post_meta( get_the_ID(), 'rank_math_pillar_content', true ) === 'on' ) {
				$is_pillar_content = sprintf( $dashicon_format, 'yes' );
			}

			$this->add_sub_menu(
				'pillar-content',
				[
					'title' => $is_pillar_content . esc_html__( 'As Pillar Content', 'rank-math' ),
					'href'  => '#pillar_content',
					'meta'  => [ 'class' => 'mark-page-as' ],
				],
				'mark-me'
			);
		}

		if ( Paper::get() ) {
			$robots        = Paper::get()->get_robots();
			$noindex_check = in_array( 'noindex', $robots, true ) ? sprintf( $dashicon_format, 'yes' ) : '';
			$this->add_sub_menu(
				'no-index',
				[
					'title' => $noindex_check . esc_html__( 'As NoIndex', 'rank-math' ),
					'href'  => '#noindex',
					'meta'  => [ 'class' => 'mark-page-as' ],
				],
				'mark-me'
			);

			$nofollow_check = in_array( 'nofollow', $robots, true ) ? sprintf( $dashicon_format, 'yes' ) : '';
			$this->add_sub_menu(
				'no-follow',
				[
					'title' => $nofollow_check . esc_html__( 'As NoFollow', 'rank-math' ),
					'href'  => '#nofollow',
					'meta'  => [ 'class' => 'mark-page-as' ],
				],
				'mark-me'
			);
		}
	}

	/**
	 * Third party SEO Tools, like the Google Structured Data Testing Tool.
	 */
	private function add_seo_tools() {
		$this->add_sub_menu(
			'third-party',
			[
				'title'    => esc_html__( 'External Tools', 'rank-math' ),
				'href'     => '#',
				'priority' => 200,
			]
		);

		$url   = rawurlencode( Url::get_current_url() );
		$items = [
			'google-pagespeed'           => [
				'title' => esc_html__( 'Google PageSpeed', 'rank-math' ),
				'href'  => 'https://developers.google.com/speed/pagespeed/insights/?url=' . $url,
				'meta'  => [ 'title' => esc_html__( 'Google PageSpeed Insights', 'rank-math' ) ],
			],

			'google-mobilefriendly'      => [
				'title' => esc_html__( 'Google Mobile-Friendly', 'rank-math' ),
				'href'  => 'https://search.google.com/test/mobile-friendly?url=' . $url,
				'meta'  => [ 'title' => esc_html__( 'Google Mobile-Friendly Test', 'rank-math' ) ],
			],

			'google-richresults-mobile'  => [
				'title' => esc_html__( 'Google Rich Results (Mobile)', 'rank-math' ),
				'href'  => 'https://search.google.com/test/rich-results?url=' . $url . '&user_agent=1',
				'meta'  => [ 'title' => esc_html__( 'Google Rich Results Test - Googlebot Smartphone', 'rank-math' ) ],
			],

			'google-richresults-desktop' => [
				'title' => esc_html__( 'Google Rich Results (Desktop)', 'rank-math' ),
				'href'  => 'https://search.google.com/test/rich-results?url=' . $url . '&user_agent=2',
				'meta'  => [ 'title' => esc_html__( 'Google Rich Results Test - Googlebot Desktop', 'rank-math' ) ],
			],

			'google-cache'               => [
				'title' => esc_html__( 'Google Cache', 'rank-math' ),
				'href'  => 'https://webcache.googleusercontent.com/search?q=cache:' . $url,
				'meta'  => [ 'title' => esc_html__( 'See Google\'s cached version of your site', 'rank-math' ) ],
			],

			'fb-debugger'                => [
				'title' => esc_html__( 'Facebook Debugger', 'rank-math' ),
				'href'  => 'https://developers.facebook.com/tools/debug/sharing/?q=' . $url,
				'meta'  => [ 'title' => esc_html__( 'Facebook Sharing Debugger', 'rank-math' ) ],
			],
		];

		foreach ( $items as $id => $args ) {
			$args['meta']['target'] = '_blank';
			$this->add_sub_menu( $id, $args, 'third-party' );
		}
	}

	/**
	 * Add sub menu item
	 *
	 * @param string $id     Unique id for the node.
	 * @param array  $args   Arguments for adding a node.
	 * @param string $parent Node parent.
	 */
	public function add_sub_menu( $id, $args, $parent = '' ) {
		$args['priority']   = isset( $args['priority'] ) ? $args['priority'] : 999;
		$args['id']         = 'rank-math-' . $id;
		$args['parent']     = '' !== $parent ? 'rank-math-' . $parent : self::MENU_IDENTIFIER;
		$this->items[ $id ] = $args;
	}

	/**
	 * Can current user has capability for admin menu.
	 *
	 * @return bool
	 */
	private function can_add_menu() {
		return Helper::has_cap( 'admin_bar' );
	}

	/**
	 * Can add mark me menu.
	 *
	 * @return bool
	 */
	private function can_add_mark_menu() {
		return $this->is_front() && Helper::has_cap( 'onpage_general' );
	}

	/**
	 * Is frontend.
	 *
	 * @return bool
	 */
	private function is_front() {
		return ! is_admin() && ! is_preview();
	}

	/**
	 * Sort admin bar items callback.
	 *
	 * @param array $item1 Item A to compare.
	 * @param array $item2 Item B to compare.
	 *
	 * @return integer
	 */
	private function sort_by_priority( $item1, $item2 ) {
		if ( $item1['priority'] === $item2['priority'] ) {
			return $item1['order'] < $item2['order'] ? -1 : 1;
		}

		return $item1['priority'] < $item2['priority'] ? -1 : 1;
	}

	/**
	 * Get Rank Math icon.
	 *
	 * @param integer $width Width of the icon.
	 *
	 * @return string
	 */
	private function get_icon( $width = 20 ) {
		return '<svg viewBox="0 0 462.03 462.03" xmlns="http://www.w3.org/2000/svg" width="' . $width . '"><g><path d="m462 234.84-76.17 3.43 13.43 21-127 81.18-126-52.93-146.26 60.97 10.14 24.34 136.1-56.71 128.57 54 138.69-88.61 13.43 21z"/><path d="m54.1 312.78 92.18-38.41 4.49 1.89v-54.58h-96.67zm210.9-223.57v235.05l7.26 3 89.43-57.05v-181zm-105.44 190.79 96.67 40.62v-165.19h-96.67z"/></g></svg>';
	}
}
