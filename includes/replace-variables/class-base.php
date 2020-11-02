<?php
/**
 * Variable replacement base.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Replace_Variables
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Replace_Variables;

use RankMath\Post;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Base class.
 */
class Base {

	use Hooker;

	/**
	 * Get a comma separated list of the post's terms.
	 *
	 * @param int    $id            ID of the post.
	 * @param string $taxonomy      The taxonomy to get the terms from.
	 * @param bool   $return_single Return the first term only.
	 * @param array  $args          Array of arguments.
	 * @param string $field         The term field to return.
	 *
	 * @return string Either a single term field or a comma delimited list of terms.
	 */
	protected function get_terms( $id, $taxonomy, $return_single = false, $args = [], $field = 'name' ) {
		$output = $this->get_queried_term_object();
		if ( '' === $output && ! empty( $id ) && ! empty( $taxonomy ) ) {
			$output = $this->get_the_terms( $id, $taxonomy, $return_single, $args, $field );
		}

		/**
		 * Filter: Allows changing the `%category%` and `%tag%` terms lists.
		 *
		 * @param string $output   The terms list, comma separated.
		 * @param string $taxonomy The taxonomy of the terms.
		 */
		return $this->do_filter( 'vars/terms', $output, $taxonomy );
	}

	/**
	 * Get the selected term, if we're on a taxonomy archive.
	 *
	 * @return string
	 */
	private function get_queried_term_object() {
		if ( is_category() || is_tag() || is_tax() ) {
			$term = $GLOBALS['wp_query']->get_queried_object();
			if ( is_object( $term ) && isset( $term->name ) ) {
				return $term->name;
			}
		}

		return '';
	}

	/**
	 * Get the post's terms.
	 *
	 * @param int    $id            ID of the post.
	 * @param string $taxonomy      The taxonomy to get the terms from.
	 * @param bool   $return_single Return the first term only.
	 * @param array  $args          Array of arguments.
	 * @param string $field         The term field to return.
	 *
	 * @return string Either a single term field or a comma delimited list of terms.
	 */
	private function get_the_terms( $id, $taxonomy, $return_single = false, $args = [], $field = 'name' ) {
		$args = wp_parse_args(
			$args,
			[
				'limit'     => 99,
				'separator' => ', ',
				'exclude'   => [],
			]
		);

		if ( ! empty( $args['exclude'] ) ) {
			$args['exclude'] = array_map( 'intval', explode( ',', $args['exclude'] ) );
		}

		$terms = get_the_terms( $id, $taxonomy );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return '';
		}

		array_splice( $terms, $args['limit'] );
		$output = [];
		$terms  = $this->filter_exclude( $terms, $args['exclude'] );

		if ( empty( $terms ) ) {
			return '';
		}

		return $return_single ? $terms[0]->{$field} :
			join( $args['separator'], wp_list_pluck( $terms, $field ) );
	}

	/**
	 * Filter terms for exclude.
	 *
	 * @param array $terms   Terms to filter.
	 * @param array $exclude Terms to exclude.
	 *
	 * @return array
	 */
	protected function filter_exclude( $terms, $exclude ) {
		if ( empty( $exclude ) ) {
			return $terms;
		}

		return array_filter(
			$terms,
			function( $term ) use ( $exclude ) {
				return in_array( $term->term_id, $exclude, true ) ? false : true;
			}
		);
	}

	/**
	 * Get the current post type.
	 *
	 * @return string Post type name.
	 */
	protected function get_queried_post_type() {
		$post_type = get_post_type();
		if ( false !== $post_type ) {
			return $post_type;
		}

		$post_type = get_query_var( 'post_type' );

		return is_array( $post_type ) ? reset( $post_type ) : $post_type;
	}

	/**
	 * Get post `object`.
	 *
	 * @return WP_Post
	 */
	protected function get_post() {
		if ( isset( $this->post ) ) {
			return $this->post;
		}

		$this->post = get_post( Post::is_shop_page() ? Post::get_shop_page_id() : null );

		if ( is_null( $this->post ) ) {
			$posts      = get_posts(
				[
					'fields'         => 'id',
					'posts_per_page' => 1,
					'post_type'      => [ 'post', 'page' ],
				]
			);
			$this->post = isset( $posts[0] ) ? $posts[0] : null;
		}

		if ( is_null( $this->post ) ) {
			$this->post = new \WP_Post(
				(object) [
					'ID'         => 0,
					'post_title' => __( 'Example Post title', 'rank-math' ),
				]
			);
		}

		return $this->post;
	}

	/**
	 * Determine the page number of the current post/page/CPT.
	 *
	 * @return int|null
	 */
	protected function determine_page_number() {
		$page_number = is_singular() ? get_query_var( 'page' ) : get_query_var( 'paged' );
		if ( 0 === $page_number || '' === $page_number ) {
			return 1;
		}

		return $page_number;
	}

	/**
	 * Determine the max num of pages of the current post/page/CPT.
	 *
	 * @return int|null
	 */
	protected function determine_max_pages() {
		global $wp_query, $post;
		if ( is_singular() && isset( $post->post_content ) ) {
			return ( substr_count( $post->post_content, '<!--nextpage-->' ) + 1 );
		}

		return empty( $wp_query->max_num_pages ) ? 1 : $wp_query->max_num_pages;
	}

	/**
	 * Determine the post type names for the current post/page/CPT.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 *
	 * @param string $request Either 'single'|'plural' - whether to return the single or plural form.
	 *
	 * @return string|null
	 */
	protected function determine_post_type_label( $request = 'single' ) {
		$post_type = $this->get_post_type();
		if ( empty( $post_type ) ) {
			return null;
		}

		$object = get_post_type_object( $post_type );

		if ( 'single' === $request && isset( $object->labels->singular_name ) ) {
			return $object->labels->singular_name;
		}

		if ( 'plural' === $request && isset( $object->labels->name ) ) {
			return $object->labels->name;
		}

		return $object->name;
	}

	/**
	 * Get post type for current quried object.
	 *
	 * @return string
	 */
	protected function get_post_type() {
		$post_type = $this->get_post_type_from_query();
		return is_array( $post_type ) ? reset( $post_type ) : $post_type;
	}

	/**
	 * Get post type from query.
	 *
	 * @return string
	 */
	protected function get_post_type_from_query() {
		global $wp_query;

		if ( isset( $wp_query->query_vars['post_type'] ) && ( Str::is_non_empty( $wp_query->query_vars['post_type'] ) || ( is_array( $wp_query->query_vars['post_type'] ) && [] !== $wp_query->query_vars['post_type'] ) ) ) {
			return $wp_query->query_vars['post_type'];
		}

		if ( isset( $this->args->post_type ) && Str::is_non_empty( $this->args->post_type ) ) {
			return $this->args->post_type;
		}

		return $wp_query->get_queried_object()->post_type;
	}
}
