<?php
/**
 * The HTML sitemap generator for posts.
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap\Html;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Database\Database;
use RankMath\Sitemap\Sitemap as SitemapBase;

defined( 'ABSPATH' ) || exit;

/**
 * Posts class.
 */
class Posts {

	use Hooker;

	/**
	 * Get all posts from a given post type.
	 *
	 * @param string $post_type   Post type.
	 * @param int    $post_parent Post parent.
	 *
	 * @return array
	 */
	private function get_posts( $post_type, $post_parent = 0 ) {
		$sort_map = [
			'published' => [
				'field' => 'post_date',
				'order' => 'DESC',
			],
			'modified'  => [
				'field' => 'post_modified',
				'order' => 'DESC',
			],
			'alphabetical' => [
				'field' => 'post_title',
				'order' => 'ASC',
			],
			'post_id' => [
				'field' => 'ID',
				'order' => 'DESC',
			],
		];

		$sort_setting = Helper::get_settings( 'sitemap.html_sitemap_sort' );
		$sort = ( isset( $sort_map[ $sort_setting ] ) ) ? $sort_map[ $sort_setting ] : $sort_map['published'];

		/**
		 * Filter: 'rank_math/sitemap/html_sitemap/sort_items' - Allow changing the sort order of the HTML sitemap.
		 *
		 * @var array $sort {
		 *    @type string $field The field to sort by.
		 *    @type string $order The sort order.
		 * }
		 * @var string $post_type The post type name.
		 * @var string $order     The item type.
		 */
		$sort = $this->do_filter( 'sitemap/html_sitemap/sort_items', $sort, 'posts', $post_type );

		$statuses = [ 'publish' ];

		/**
		 * Filter: 'rank_math/sitemap/html_sitemap_post_statuses' - Allow changing the post statuses that should be included in the sitemap.
		 *
		 * @var array  $statuses Post statuses.
		 * @var string $post_type Post type name.
		 */
		$statuses = $this->do_filter( 'sitemap/html_sitemap_post_statuses', $statuses, $post_type );

		$exclude = wp_parse_id_list( Helper::get_settings( 'sitemap.exclude_posts' ) );
		$table   = Database::table( 'posts' );

		$query = $table
			->select( [ 'ID', 'post_title', 'post_name', 'post_date', 'post_parent', 'post_type' ] )
			->where( 'post_type', $post_type )
			->where( 'post_parent', $post_parent )
			->whereIn( 'post_status', $statuses );

		if ( ! empty( $exclude ) ) {
			$query->whereNotIn( 'ID', $exclude );
		}

		$posts = $query->orderBy( $sort['field'], $sort['order'] )->get();

		return array_filter( $posts, function( $post ) {
			return SitemapBase::is_object_indexable( $post->ID );
		} );
	}

	/**
	 * Generate the HTML sitemap for a given post type.
	 *
	 * @param string $post_type Post type name.
	 * @param bool   $show_dates Whether to show dates.
	 *
	 * @return string
	 */
	public function generate_sitemap( $post_type, $show_dates ) {
		$posts = $this->get_posts( $post_type );

		if ( empty( $posts ) ) {
			return '';
		}

		$output[] = '<div class="rank-math-html-sitemap__section rank-math-html-sitemap__section--post-type rank-math-html-sitemap__section--' . $post_type . '">';
		$output[] = '<h2 class="rank-math-html-sitemap__title">' . esc_html( get_post_type_object( $post_type )->labels->name ) . '</h2>';
		$output[] = '<ul class="rank-math-html-sitemap__list">';
		$output[] = $this->generate_posts_list( $posts, $show_dates, $post_type );
		$output[] = '</ul>';
		$output[] = '</div>';

		$output = implode( '', $output );

		return $output;
	}

	/**
	 * Generate the post list HTML.
	 *
	 * @param array  $posts Array of posts.
	 * @param bool   $show_dates Whether to show dates.
	 * @param string $post_type Post type name.
	 *
	 * @return string
	 */
	private function generate_posts_list( $posts, $show_dates, $post_type ) {
		if ( empty( $posts ) ) {
			return '';
		}

		if ( is_post_type_hierarchical( $post_type ) ) {
			return $this->generate_posts_list_hierarchical( $posts, $show_dates, $post_type );
		}

		return $this->generate_posts_list_flat( $posts, $show_dates );
	}

	/**
	 * Get the post list HTML for non-hierarchical post types.
	 *
	 * @param array $posts The posts to output.
	 * @param bool  $show_dates Whether to show the post dates.
	 *
	 * @return string
	 */
	private function generate_posts_list_flat( $posts, $show_dates ) {
		$output = [];
		foreach ( $posts as $post ) {
			$output[] = '<li class="rank-math-html-sitemap__item">'
				. '<a href="' . esc_url( $this->get_post_link( $post ) ) . '" class="rank-math-html-sitemap__link">'
				. esc_html( $this->get_post_title( $post ) )
				. '</a>'
				. ( $show_dates ? ' <span class="rank-math-html-sitemap__date">(' . esc_html( mysql2date( get_option( 'date_format' ), $post->post_date ) ) . ')</span>' : '' )
				. '</li>';
		}

		return implode( '', $output );
	}

	/**
	 * Get the post list HTML for hierarchical post types. This will output the
	 * posts in a nested list.
	 *
	 * @param array  $posts      The posts to output.
	 * @param bool   $show_dates Whether to show the post dates.
	 * @param string $post_type  Post type name.
	 *
	 * @return string
	 */
	private function generate_posts_list_hierarchical( $posts, $show_dates, $post_type ) {
		$output = [];
		foreach ( $posts as $post ) {
			$output[] = '<li class="rank-math-html-sitemap__item">'
				. '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" class="rank-math-html-sitemap__link">'
				. esc_html( $this->get_post_title( $post ) )
				. '</a>'
				. ( $show_dates ? ' <span class="rank-math-html-sitemap__date">(' . esc_html( mysql2date( get_option( 'date_format' ), $post->post_date ) ) . ')</span>' : '' )
				. '</li>';

			$children = $this->get_posts( $post_type, $post->ID );
			if ( ! empty( $children ) ) {
				$output[] = '<ul class="rank-math-html-sitemap__list">';
				$output[] = $this->generate_posts_list_hierarchical( $children, $show_dates, $post_type );
				$output[] = '</ul>';
			}
		}

		return implode( '', $output );
	}

	/**
	 * Get the post permalink.
	 */
	private function get_post_link( $post ) {
		return get_permalink( $post->ID );
	}

	/**
	 * Get the post title.
	 *
	 * @param object $post The post data.
	 *
	 * @return string
	 */
	private function get_post_title( $post ) {
		if ( Helper::get_settings( 'sitemap.html_sitemap_seo_titles' ) !== 'seo_titles' ) {
			return $post->post_title;
		}

		// Custom SEO title.
		$meta = get_post_meta( $post->ID, 'rank_math_title', true );
		if ( ! empty( $meta ) ) {
			return Helper::replace_vars( $meta, get_post( $post->ID ) );
		}

		// Default SEO title from the global settings.
		$template = Helper::get_settings( "titles.pt_{$post->post_type}_title" );
		if ( ! empty( $template ) ) {
			return Helper::replace_vars( $template, get_post( $post->ID ) );
		}

		// Fallback to post title.
		return $post->post_title;
	}
}
