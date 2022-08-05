<?php
/**
 * The Breadcrumbs.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Frontend
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2015, WooCommerce
 * The following code is a derivative work of the code from the WooCommerce(https://github.com/woocommerce/woocommerce/), which is licensed under GPL v3.
 */

namespace RankMath\Frontend;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Breadcrumbs class.
 */
class Breadcrumbs {

	use Hooker;

	/**
	 * Breadcrumb trail.
	 *
	 * @var array
	 */
	private $crumbs = [];

	/**
	 * Breadcrumb settings.
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 * String.
	 *
	 * @var array
	 */
	private $strings = [];

	/**
	 * Get an instance of the class.
	 *
	 * @return Breadcrumb The instancec.
	 */
	public static function get() {
		static $instance;

		$instance = false;
		if ( Helper::is_breadcrumbs_enabled() && false === $instance ) {
			$instance = new Breadcrumbs();
		}

		return $instance;
	}

	/**
	 * Convenience method to output as string.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->get_breadcrumb();
	}

	/**
	 * The Constructor
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		$this->settings = wp_parse_args(
			$this->do_filter( 'frontend/breadcrumb/settings', [] ),
			[
				'home'            => Helper::get_settings( 'general.breadcrumbs_home' ),
				'separator'       => Helper::get_settings( 'general.breadcrumbs_separator' ),
				'remove_title'    => Helper::get_settings( 'general.breadcrumbs_remove_post_title' ),
				'hide_tax_name'   => Helper::get_settings( 'general.breadcrumbs_hide_taxonomy_name' ),
				'show_ancestors'  => Helper::get_settings( 'general.breadcrumbs_ancestor_categories' ),
				'show_blog'       => Helper::get_settings( 'general.breadcrumbs_blog_page' ),
				'show_pagination' => true,
			]
		);

		$this->strings = wp_parse_args(
			$this->do_filter( 'frontend/breadcrumb/strings', [] ),
			[
				'prefix'         => Helper::get_settings( 'general.breadcrumbs_prefix' ),
				'home'           => Helper::get_settings( 'general.breadcrumbs_home_label' ),
				'home_link'      => Helper::get_settings( 'general.breadcrumbs_home_link', home_url() ),
				'error404'       => Helper::get_settings( 'general.breadcrumbs_404_label' ),
				/* translators: search query */
				'search_format'  => Helper::get_settings( 'general.breadcrumbs_search_format' ),
				/* translators: archive title */
				'archive_format' => Helper::get_settings( 'general.breadcrumbs_archive_format' ),
			]
		);
	}

	/**
	 * Get the breadcrumbs.
	 *
	 * @param array $args Arguments.
	 * @return string
	 */
	public function get_breadcrumb( $args = [] ) {
		$args = $this->do_filter(
			'frontend/breadcrumb/args',
			wp_parse_args(
				$args,
				[
					'delimiter'   => '&nbsp;&#47;&nbsp;',
					'wrap_before' => '<nav aria-label="breadcrumbs" class="rank-math-breadcrumb"><p>',
					'wrap_after'  => '</p></nav>',
					'before'      => '',
					'after'       => '',
				]
			)
		);

		$html   = '';
		$crumbs = $this->get_crumbs();

		$remove_title = ( is_single() || is_page() ) && $this->settings['remove_title'];
		if ( $remove_title ) {
			array_pop( $crumbs );
		}
		$size = count( $crumbs );

		if ( ! empty( $this->strings['prefix'] ) ) {
			$html .= \sprintf( '<span class="label">%s</span> ', $this->strings['prefix'] );
		}

		foreach ( $crumbs as $key => $crumb ) {
			$link = ! empty( $crumb[1] ) && ( $remove_title || $size !== $key + 1 );
			$link = $link ? '<a href="' . esc_url( $crumb[1] ) . '">' . esc_html( $crumb[0] ) . '</a>' :
				'<span class="last">' . esc_html( $crumb[0] ) . '</span>';

			$html .= $args['before'] . $link . $args['after'];

			if ( $size !== $key + 1 ) {
				$html .= '<span class="separator"> ' . wp_kses_post( $this->settings['separator'] ) . ' </span>';
			}
		}

		$html = $args['wrap_before'] . $html . $args['wrap_after'];

		/**
		 * Change the breadcrumbs HTML output.
		 *
		 * @param string      $html   HTML output.
		 * @param array       $crumbs The breadcrumbs array.
		 * @param Breadcrumbs $this   Current breadcrumb.
		 */
		return $this->do_filter( 'frontend/breadcrumb/html', $html, $crumbs, $this );
	}

	/**
	 * Get the breadrumb trail.
	 *
	 * @return array
	 */
	public function get_crumbs() {
		if ( empty( $this->crumbs ) ) {
			$this->generate();
		}

		/**
		 * Change the breadcrumb items.
		 *
		 * @param array       $crumbs The breadcrumbs array.
		 * @param Breadcrumbs $this   Current breadcrumb.
		 */
		return $this->do_filter( 'frontend/breadcrumb/items', $this->crumbs, $this );
	}

	/**
	 * Add an item to the breadcrumbs.
	 *
	 * @param string $name           Name.
	 * @param string $link           Link.
	 * @param bool   $hide_in_schema Don't include in JSON-LD.
	 */
	private function add_crumb( $name, $link = '', $hide_in_schema = false ) {
		$this->crumbs[] = [
			wp_strip_all_tags( $name ),
			$link,
			'hide_in_schema' => $hide_in_schema,
		];
	}

	/**
	 * Generate the breadcrumb trail.
	 */
	private function generate() {
		$conditionals = [
			'is_home',
			'is_404',
			'is_search',
			'is_attachment',
			'is_shop',
			'is_product',
			'is_singular',
			'is_product_category',
			'is_product_tag',
			'is_post_type_archive',
			'is_category',
			'is_tag',
			'is_tax',
			'is_date',
			'is_author',
		];

		$this->maybe_add_home_crumb();

		if ( ! $this->can_generate() ) {
			return;
		}

		foreach ( $conditionals as $conditional ) {
			if ( function_exists( $conditional ) && call_user_func( $conditional ) ) {
				call_user_func( [ $this, 'add_crumbs_' . substr( $conditional, 3 ) ] );
				break;
			}
		}

		$this->maybe_add_page_crumb();
	}

	/**
	 * Can generate breadcrumb.
	 *
	 * @return bool
	 */
	private function can_generate() {
		return (
			! is_front_page() &&
			! (
				is_post_type_archive() &&
				function_exists( 'wc_get_page_id' ) &&
				intval( get_option( 'page_on_front' ) ) === wc_get_page_id( 'shop' ) )
			) ||
			is_paged();
	}

	/**
	 * Is home trail.
	 */
	private function add_crumbs_home() {
		$this->add_crumb( single_post_title( '', false ) );
	}

	/**
	 * 404 trail.
	 */
	private function add_crumbs_404() {
		$this->add_crumb( $this->strings['error404'] );
	}

	/**
	 * Search results trail.
	 */
	private function add_crumbs_search() {
		$this->add_crumb( sprintf( $this->strings['search_format'], get_search_query() ), Security::remove_query_arg_raw( 'paged' ) );
	}

	/**
	 * Attachment trail.
	 */
	private function add_crumbs_attachment() {
		global $post;

		$this->add_crumbs_singular( $post->post_parent, get_permalink( $post->post_parent ) );
		$this->add_crumb( $this->get_breadcrumb_title( 'post', get_the_ID(), get_the_title() ), get_permalink() );
	}

	/**
	 * Single product trail.
	 */
	private function add_crumbs_product() {
		global $post;

		$this->prepend_shop_page();
		$main_tax = Helper::get_settings( 'titles.pt_product_primary_taxonomy' );
		if ( $main_tax ) {
			$this->maybe_add_primary_term( get_the_terms( $post->ID, $main_tax ) );
		}

		if ( isset( $post->ID ) ) {
			$this->add_crumb( $this->get_breadcrumb_title( 'post', $post->ID, get_the_title( $post ) ), get_permalink( $post ) );
		}
	}

	/**
	 * Single post trail.
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $permalink Post permalink.
	 */
	private function add_crumbs_singular( $post_id = 0, $permalink = '' ) {
		$post      = ! $post_id ? $GLOBALS['post'] : get_post( $post_id );
		$post_type = get_post_type( $post );
		$permalink = $permalink ? $permalink : get_permalink( $post );

		$this->add_crumbs_post_type_archive( $post_type );

		if ( ! isset( $post->ID ) || empty( $post->ID ) ) {
			return;
		}

		$this->maybe_add_blog();
		$main_tax = Helper::get_settings( 'titles.pt_' . $post_type . '_primary_taxonomy' );
		if ( isset( $post->post_parent ) && 0 === $post->post_parent && $main_tax ) {
			$this->maybe_add_primary_term( get_the_terms( $post, $main_tax ) );
		}

		if ( isset( $post->post_parent ) && 0 !== $post->post_parent ) {
			$this->add_post_ancestors( $post );
		}

		$this->add_crumb( $this->get_breadcrumb_title( 'post', $post->ID, get_the_title( $post ) ), $permalink );
	}

	/**
	 * Product category trail.
	 */
	private function add_crumbs_product_category() {
		$term = $GLOBALS['wp_query']->get_queried_object();
		$this->prepend_shop_page();
		$this->maybe_add_term_ancestors( $term );
		$this->add_crumb( $this->get_breadcrumb_title( 'term', $term, $term->name ), get_term_link( $term ) );
	}

	/**
	 * Product tag trail.
	 */
	private function add_crumbs_product_tag() {
		$term = $GLOBALS['wp_query']->get_queried_object();
		$this->prepend_shop_page();
		/* translators: %s: product tag */
		$this->add_crumb( sprintf( __( 'Products tagged &ldquo;%s&rdquo;', 'rank-math' ), $this->get_breadcrumb_title( 'term', $term, $term->name ) ), get_term_link( $term ) );
	}

	/**
	 * Shop trail.
	 */
	private function add_crumbs_shop() {
		$shop_page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : false;
		if ( intval( get_option( 'page_on_front' ) ) === $shop_page_id ) {
			return;
		}

		$name = $shop_page_id ? $this->get_breadcrumb_title( 'post', $shop_page_id, get_the_title( $shop_page_id ) ) : '';
		if ( ! $name ) {
			$post_type = get_post_type_object( 'product' );
			$name      = $post_type->labels->singular_name;
		}
		$this->add_crumb( $name, get_post_type_archive_link( 'product' ) );
	}

	/**
	 * Post type archive trail.
	 *
	 * @param string $post_type Post type.
	 */
	private function add_crumbs_post_type_archive( $post_type = null ) {
		if ( ! $post_type ) {
			$post_type = $GLOBALS['wp_query']->get( 'post_type' );
		}

		if ( 'post' === $post_type ) {
			return;
		}

		$type_object = get_post_type_object( $post_type );
		if ( ! empty( $type_object->has_archive ) ) {
			$this->add_crumb( $type_object->labels->singular_name, get_post_type_archive_link( $post_type ) );
		}
	}

	/**
	 * Category trail.
	 */
	private function add_crumbs_category() {
		$this->maybe_add_blog();
		$term = $GLOBALS['wp_query']->get_queried_object();
		$this->maybe_add_term_ancestors( $term );
		$this->add_crumb( $this->get_breadcrumb_title( 'term', $term, $term->name ), get_term_link( $term ) );
	}

	/**
	 * Tag trail.
	 */
	private function add_crumbs_tag() {
		$this->maybe_add_blog();
		$term = $GLOBALS['wp_query']->get_queried_object();
		$this->add_crumb( $this->get_breadcrumb_title( 'term', $term, $term->name ), get_term_link( $term ) );
	}

	/**
	 * Taxonomies trail.
	 */
	private function add_crumbs_tax() {
		$term = $GLOBALS['wp_query']->get_queried_object();
		if ( ! $this->settings['hide_tax_name'] ) {
			$taxonomy = get_taxonomy( $term->taxonomy );
			$this->add_crumb( $taxonomy->labels->name );
		}

		$this->maybe_add_term_ancestors( $term );
		$this->add_crumb( $this->get_breadcrumb_title( 'term', $term, $term->name ), get_term_link( $term ) );
	}

	/**
	 * Trail for date based archives.
	 */
	private function add_crumbs_date() {
		if ( is_year() || is_month() || is_day() ) {
			$this->add_crumb( sprintf( $this->strings['archive_format'], get_the_time( 'Y' ) ), get_year_link( get_the_time( 'Y' ) ) );
		}
		if ( is_month() || is_day() ) {
			$this->add_crumb( sprintf( $this->strings['archive_format'], get_the_time( 'F' ) ), get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) );
		}
		if ( is_day() ) {
			$this->add_crumb( sprintf( $this->strings['archive_format'], get_the_time( 'd' ) ) );
		}
	}

	/**
	 * Trail for author archives.
	 */
	private function add_crumbs_author() {
		global $author;

		$userdata = get_userdata( $author );
		$this->add_crumb( sprintf( $this->strings['archive_format'], $this->get_breadcrumb_title( 'user', $userdata->ID, $userdata->display_name ) ) );
	}

	/**
	 * Single post trail.
	 * 
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast (https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 *
	 * @param WP_Post $post Post object.
	 */
	private function add_post_ancestors( $post ) {
		$ancestors = [];
		if ( isset( $post->ancestors ) ) {
			$ancestors = is_array( $post->ancestors ) ? array_values( $post->ancestors ) : [ $post->ancestors ];
		} elseif ( isset( $post->post_parent ) ) {
			$ancestors = [ $post->post_parent ];
		}

		if ( ! is_array( $ancestors ) ) {
			return;
		}

		$ancestors = array_reverse( $ancestors );
		foreach ( $ancestors as $ancestor ) {
			$this->add_crumb( $this->get_breadcrumb_title( 'post', $ancestor, get_the_title( $ancestor ) ), get_permalink( $ancestor ) );
		}
	}

	/**
	 * Prepend the shop page to the shop trail.
	 */
	private function prepend_shop_page() {
		$shop_page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : false;
		$shop_page    = get_post( $shop_page_id );

		// If permalinks contain the shop page in the URI prepend the breadcrumb with shop.
		if ( $shop_page_id && $shop_page && $this->is_using_shop_base( $shop_page ) && intval( get_option( 'page_on_front' ) ) !== $shop_page_id ) {
			$this->add_crumb( $this->get_breadcrumb_title( 'post', $shop_page_id, get_the_title( $shop_page ) ), get_permalink( $shop_page ) );
		}
	}

	/**
	 * Checks if the permalinks product base is using the shop base.
	 *
	 * @param \WP_Post $shop_page The shop page.
	 *
	 * @return bool
	 */
	private function is_using_shop_base( $shop_page ) {
		$permalinks         = wc_get_permalink_structure();
		$is_using_shop_base = isset( $permalinks['product_base'] ) && strstr( $permalinks['product_base'], '/' . $shop_page->post_name );

		/**
		 * Allows to filter the "is using shop base" condition.
		 *
		 * @param bool True if using shop base or false otherwise.
		 */
		return $this->do_filter( 'frontend/breadcrumb/is_using_shop_base', $is_using_shop_base );
	}
	/**
	 * Get the primary term.
	 *
	 * @param array $terms Terms attached to the current post.
	 */
	private function maybe_add_primary_term( $terms ) {
		// Early Bail!
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		/**
		 * Allow changing the primary term output of the breadcrumbs class.
		 *
		 * @param WP_Term $term  Primary term.
		 * @param array   $terms Terms attached to the current post.
		 */
		$term = $this->do_filter( 'frontend/breadcrumb/main_term', $terms[0], $terms );
		$this->maybe_add_term_ancestors( $term );
		$this->add_crumb( $this->get_breadcrumb_title( 'term', $term, $term->name ), get_term_link( $term ) );
	}

	/**
	 * Add ancestor taxonomy crumbs to the hierachical taxonomy trails.
	 *
	 * @param object $term Term data object.
	 */
	private function maybe_add_term_ancestors( $term ) {
		// Early Bail!
		if ( ! $this->can_add_term_ancestors( $term ) ) {
			return;
		}

		$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
		$ancestors = array_reverse( $ancestors );

		foreach ( $ancestors as $ancestor ) {
			$ancestor = get_term( $ancestor, $term->taxonomy );
			if ( ! is_wp_error( $ancestor ) && $ancestor ) {
				$this->add_crumb( $this->get_breadcrumb_title( 'term', $ancestor, $ancestor->name ), get_term_link( $ancestor ) );
			}
		}
	}

	/**
	 * Can add ancestor taxonomy crumbs to the hierachical taxonomy trails.
	 *
	 * @param object $term Term data object.
	 *
	 * @return bool
	 */
	private function can_add_term_ancestors( $term ) {
		if ( 0 === $term->parent || false === $this->settings['show_ancestors'] || false === is_taxonomy_hierarchical( $term->taxonomy ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Add a page crumb to paginated trails.
	 *
	 * @since 1.0.8
	 */
	private function maybe_add_page_crumb() {
		if ( empty( $this->settings['show_pagination'] ) || ! is_paged() ) {
			return;
		}

		$current_page = get_query_var( 'paged', 1 );
		if ( $current_page <= 1 ) {
			return;
		}

		/* translators: %s expands to the current page number */
		$this->add_crumb( sprintf( esc_html__( 'Page %s', 'rank-math' ), $current_page ), '', true );
	}

	/**
	 * Add home label.
	 */
	private function maybe_add_home_crumb() {
		if ( ! empty( $this->settings['home'] ) ) {
			$this->add_crumb( $this->strings['home'], $this->strings['home_link'] );
		}
	}

	/**
	 * Get the Blog Page.
	 *
	 * @since 1.0.33
	 */
	private function maybe_add_blog() {
		// Early Bail!
		$blog_id = get_option( 'page_for_posts' );
		if ( ! $blog_id || ! $this->can_add_blog() ) {
			return;
		}

		$this->add_crumb( $this->get_breadcrumb_title( 'post', $blog_id, get_the_title( $blog_id ) ), get_permalink( $blog_id ) );
	}

	/**
	 * Can add Blog page crumb.
	 *
	 * @since 1.0.33
	 *
	 * @return bool
	 */
	private function can_add_blog() {
		if ( empty( $this->settings['show_blog'] ) || 'page' !== get_option( 'show_on_front' ) ) {
			return false;
		}

		if ( ! is_singular( 'post' ) && ! is_category() && ! is_tag() ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the breadcrumb title.
	 *
	 * @param  string $object_type Object type.
	 * @param  int    $object_id   Object ID to get the title for.
	 * @param  string $default     Default value to use for title.
	 * @return string
	 */
	private function get_breadcrumb_title( $object_type, $object_id, $default ) {
		$title = '';
		if ( 'post' === $object_type ) {
			$title = Helper::get_post_meta( 'breadcrumb_title', $object_id );
		} elseif ( 'term' === $object_type ) {
			$title = Helper::get_term_meta( 'breadcrumb_title', $object_id );
		} elseif ( 'user' === $object_type ) {
			$title = Helper::get_user_meta( 'breadcrumb_title', $object_id );
		}

		return $title ? $title : $default;
	}
}
