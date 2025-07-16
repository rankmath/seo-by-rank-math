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
use RankMath\Helpers\DB as DB_Helper;
use RankMath\Traits\Hooker;
use RankMath\Admin\Database\Database;
use RankMath\Sitemap\Sitemap as SitemapBase;

defined( 'ABSPATH' ) || exit;

/**
 * Posts class.
 */
class Posts {

	use Hooker;

	/**
	 * An array of posts that have a parent.
	 *
	 * @var array
	 */
	private $children = [];

	/**
	 * Get all posts from a given post type.
	 *
	 * @param string $post_type   Post type.
	 * @param array  $post_parents An array of post parent ids.
	 *
	 * @return array
	 */
	private function get_posts( $post_type, $post_parents = [] ) {
		global $wpdb;
		$sort_map = [
			'published'    => [
				'field' => 'post_date',
				'order' => 'DESC',
			],
			'modified'     => [
				'field' => 'post_modified',
				'order' => 'DESC',
			],
			'alphabetical' => [
				'field' => 'post_title',
				'order' => 'ASC',
			],
			'post_id'      => [
				'field' => 'ID',
				'order' => 'DESC',
			],
		];

		$sort_setting = Helper::get_settings( 'sitemap.html_sitemap_sort' );
		$sort         = ( isset( $sort_map[ $sort_setting ] ) ) ? $sort_map[ $sort_setting ] : $sort_map['published'];

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
		 * Filter to add a JOIN clause for the HTML get_posts(type, post_parent[]) query.
		 *
		 * @param string $join       SQL join clause, defaults to an empty string.
		 * @param array  $post_type  The Post type.
		 */
		$join_filter = $this->do_filter( 'html_sitemap/get_posts/join', '', $post_type );

		/**
		 * Filter to add a WHERE clause for the HTML sitemap get_posts(type, post_parent[] ) query.
		 *
		 * @param string $where      SQL WHERE query, defaults to an empty string.
		 * @param array  $post_type  The Post type.
		 */
		$where_filter = $this->do_filter( 'html_sitemap/get_posts/where', '', $post_type );

		/**
		 * Filter: 'rank_math/sitemap/html_sitemap_post_statuses' - Allow changing the post statuses that should be included in the sitemap.
		 *
		 * @var array  $statuses Post statuses.
		 * @var string $post_type Post type name.
		 */
		$statuses  = $this->do_filter( 'sitemap/html_sitemap_post_statuses', $statuses, $post_type );
		$get_child = ! empty( $post_parents ) ? " WHERE post_parent !='' " : '';
		$sql       = "
			SELECT l.ID, post_title, post_name, post_parent, post_date, post_type, l.post_modified
			FROM (
				SELECT DISTINCT p.ID, p.post_modified FROM {$wpdb->posts} as p
				{$join_filter}
				LEFT JOIN {$wpdb->postmeta} AS pm ON ( p.ID = pm.post_id AND pm.meta_key = 'rank_math_robots' )
				WHERE (
					( pm.meta_key = 'rank_math_robots' AND pm.meta_value NOT LIKE '%noindex%' ) OR
					pm.post_id IS NULL
				)
				AND p.post_type IN ( '" . $post_type . "' ) AND p.post_status IN ( '" . join( "', '", esc_sql( $statuses ) ) . "' )
				{$where_filter}
				ORDER BY p.post_modified DESC
			)
			o JOIN {$wpdb->posts} l ON l.ID = o.ID " . $get_child . " ORDER BY " . $sort['field'] . " " . $sort['order']; // phpcs:ignore
		return DB_Helper::get_results( $wpdb->prepare( $sql ) );
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
			$post_ids  = [];
			$post_list = [];
			array_map(
				function ( $post ) use ( &$post_ids, &$post_list ) {
					$post_ids[]             = $post->ID;
					$post_list[ $post->ID ] = $post;
				},
				$posts
			);

			$children = $this->get_posts( $post_type, $post_ids );
			foreach ( $children as $child ) {
				// Confirm if child has a parent available, the parent might not be index-able and re-add the child to $posts!
				$parent = array_filter(
					$post_list,
					function ( $post ) use ( $child ) {
						return $child->post_parent === $post->ID;
					}
				);

				if ( empty( $parent ) ) {
					$child->child_has_no_parent = true;
					$post_list[ $child->ID ]    = $child;
					continue;
				}
				$this->children[ $post_type ][ $child->post_parent ][ $child->ID ] = $child;
			}

			$post_list = $this->remove_with_parent( $post_list );
			return $this->generate_posts_list_hierarchical( $post_list, $show_dates, $post_type );
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
			if ( ! SitemapBase::is_object_indexable( absint( $post->ID ) ) ) {
				continue;
			}

			$url = $this->do_filter( 'sitemap/entry', [ 'loc' => esc_url( $this->get_post_link( $post ) ) ], 'post', $post );
			if ( empty( $url['loc'] ) ) {
				continue;
			}

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
	 * @param bool   $child      Whether the passed posts are children.
	 *
	 * @return string
	 */
	private function generate_posts_list_hierarchical( $posts, $show_dates, $post_type, $child = false ) {
		$output  = [];
		$exclude = wp_parse_id_list( Helper::get_settings( 'sitemap.exclude_posts' ) );

		foreach ( $posts as $post ) {
			$check_parent_index = empty( $post->post_parent ) ? 0 : SitemapBase::is_object_indexable( $post->post_parent );
			$is_indexable       = SitemapBase::is_object_indexable( absint( $post->ID ) );

			if ( ( ! $check_parent_index || $child ) && $is_indexable ) {
				$output[] = '<li class="rank-math-html-sitemap__item">'
				. '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" class="rank-math-html-sitemap__link">'
				. esc_html( $this->get_post_title( $post ) )
				. '</a>'
				. ( $show_dates ? ' <span class="rank-math-html-sitemap__date">(' . esc_html( mysql2date( get_option( 'date_format' ), $post->post_date ) ) . ')</span>' : '' );
			}

			if ( ! empty( $this->children[ $post_type ][ $post->ID ] ) ) {
				if ( $is_indexable ) {
					$output[] = '<ul class="rank-math-html-sitemap__list">';
				}

				$output[] = $this->generate_posts_list_hierarchical(  $this->children[ $post_type ][ $post->ID ], $show_dates, $post_type, true ); // phpcs:ignore

				if ( $is_indexable ) {
					$output[] = '</ul>';
				}
			}

			if ( $is_indexable ) {
				$output[] = '</li>';
			}
		}

		return implode( '', $output );
	}

	/**
	 * Get the post permalink.
	 *
	 * @param object $post The post object.
	 *
	 * @return string
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

	/**
	 * Removes posts with a parent to avoid them being rendered twice.
	 *
	 * @param array $posts Array of post objects.
	 *
	 * @return array
	 */
	private function remove_with_parent( $posts ) {
		return array_filter(
			$posts,
			function ( $post ) {
				return ! $post->post_parent || isset( $post->child_has_no_parent );
			}
		);
	}
}
