<?php
/**
 * Advanced variable replacer.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Replace_Variables
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Replace_Variables;

use RankMath\Paper\Paper;
use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Advanced_Variables class.
 */
class Advanced_Variables extends Author_Variables {

	/**
	 * Setup advanced variables.
	 */
	public function setup_advanced_variables() {
		$post = $this->get_post();

		$this->register_replacement(
			'id',
			[
				'name'        => esc_html__( 'Post ID', 'rank-math' ),
				'description' => esc_html__( 'ID of the current post/page', 'rank-math' ),
				'variable'    => 'id',
				'example'     => ! empty( $post ) ? $post->ID : __( 'Post ID', 'rank-math' ),
			],
			[ $this, 'get_id' ]
		);

		$keyword = $this->get_focus_keyword();
		$this->register_replacement(
			'focuskw',
			[
				'name'        => esc_html__( 'Focus Keyword', 'rank-math' ),
				'description' => esc_html__( 'Focus Keyword of the current post', 'rank-math' ),
				'variable'    => 'focuskw',
				'example'     => \is_null( $keyword ) ? '' : $keyword,
			],
			[ $this, 'get_focus_keyword' ]
		);

		$this->register_replacement(
			'keywords',
			[
				'name'        => esc_html__( 'Focus Keywords', 'rank-math' ),
				'description' => esc_html__( 'Focus Keywords of the current post', 'rank-math' ),
				'variable'    => 'keywords',
				'example'     => $this->get_focus_keywords(),
			],
			[ $this, 'get_focus_keywords' ]
		);

		$this->register_replacement(
			'customfield',
			[
				'name'        => esc_html__( 'Custom Field (advanced)', 'rank-math' ),
				'description' => esc_html__( 'Custom field value.', 'rank-math' ),
				'variable'    => 'customfield(field-name)',
				'example'     => esc_html__( 'Custom field value', 'rank-math' ),
				'nocache'     => true,
			],
			[ $this, 'get_customfield' ]
		);

		$this->setup_paging_variables();
		$this->setup_post_types_variables();
	}

	/**
	 * Setup paging variables.
	 */
	private function setup_paging_variables() {
		$this->register_replacement(
			'page',
			[
				'name'        => esc_html__( 'Page', 'rank-math' ),
				'description' => esc_html__( 'Page number with context (i.e. page 2 of 4). Only displayed on page 2 and above.', 'rank-math' ),
				'variable'    => 'page',
				'example'     => ' page 2 of 4',
			],
			[ $this, 'get_page' ]
		);

		$this->register_replacement(
			'pagenumber',
			[
				'name'        => esc_html__( 'Page Number', 'rank-math' ),
				'description' => esc_html__( 'Current page number', 'rank-math' ),
				'variable'    => 'pagenumber',
				'example'     => '4',
			],
			[ $this, 'get_pagenumber' ]
		);

		$this->register_replacement(
			'pagetotal',
			[
				'name'        => esc_html__( 'Max Pages', 'rank-math' ),
				'description' => esc_html__( 'Max pages number', 'rank-math' ),
				'variable'    => 'pagetotal',
				'example'     => '4',
			],
			[ $this, 'get_pagetotal' ]
		);
	}

	/**
	 * Setup post types variables.
	 */
	private function setup_post_types_variables() {
		$this->register_replacement(
			'pt_single',
			[
				'name'        => esc_html__( 'Post Type Name Singular', 'rank-math' ),
				'description' => esc_html__( 'Name of current post type (singular)', 'rank-math' ),
				'variable'    => 'pt_single',
				'example'     => esc_html__( 'Product', 'rank-math' ),
			],
			[ $this, 'get_post_type_single' ]
		);

		$this->register_replacement(
			'pt_plural',
			[
				'name'        => esc_html__( 'Post Type Name Plural', 'rank-math' ),
				'description' => esc_html__( 'Name of current post type (plural)', 'rank-math' ),
				'variable'    => 'pt_plural',
				'example'     => esc_html__( 'Products', 'rank-math' ),
			],
			[ $this, 'get_post_type_plural' ]
		);
	}

	/**
	 * Get the numeric post ID.
	 *
	 * @return string|null
	 */
	public function get_id() {
		return ! empty( $this->args->ID ) ? $this->args->ID : null;
	}

	/**
	 * Get the focus keyword.
	 *
	 * @return string|null
	 */
	public function get_focus_keyword() {
		$keywords = '';
		if ( ! empty( $this->args->ID ) ) {
			$keywords = get_post_meta( $this->args->ID, 'rank_math_focus_keyword', true );
		}

		if ( ! empty( $this->args->term_id ) ) {
			$keywords = get_term_meta( $this->args->term_id, 'rank_math_focus_keyword', true );
		}

		$keywords = explode( ',', $keywords );
		if ( '' !== $keywords[0] ) {
			return $keywords[0];
		}

		return null;
	}

	/**
	 * Get Focus keywords.
	 *
	 * @return string
	 */
	public function get_focus_keywords() {
		if ( is_singular() || is_category() || is_tag() || is_tax() ) {
			return Paper::get()->get_keywords();
		}

		$keywords = '';
		if ( ! empty( $this->args->ID ) ) {
			$keywords = get_post_meta( $this->args->ID, 'rank_math_focus_keyword', true );
		}

		if ( ! empty( $this->args->term_id ) ) {
			$keywords = get_term_meta( $this->args->term_id, 'rank_math_focus_keyword', true );
		}

		return $keywords;
	}

	/**
	 * Get the current page number as a string (i.e. "page 1 of 5").
	 *
	 * @return string
	 */
	public function get_page() {
		$sep  = $this->get_sep();
		$max  = $this->determine_max_pages();
		$page = $this->determine_page_number();

		if ( $max > 1 && $page > 1 ) {
			/* translators: %1$d: current page number, %2$d: max pages. */
			return sprintf( $sep . ' ' . __( 'Page %1$d of %2$d', 'rank-math' ), $page, $max );
		}

		return null;
	}

	/**
	 * Get only the page number (without context).
	 *
	 * @return string|null
	 */
	public function get_pagenumber() {
		$page = $this->determine_page_number();

		return $page > 0 ? (string) $page : null;
	}

	/**
	 * Get the max page number.
	 *
	 * @return string|null
	 */
	public function get_pagetotal() {
		$max = $this->determine_max_pages();

		return $max > 0 ? (string) $max : null;
	}

	/**
	 * Get a specific custom field value.
	 *
	 * @param  string $name The name of the custom field to retrieve.
	 * @return string|null
	 */
	public function get_customfield( $name ) {
		if ( Str::is_empty( $name ) ) {
			return null;
		}

		if ( ! empty( get_query_var( 'sitemap' ) ) && 'locations' !== get_query_var( 'sitemap' ) ) {
			return null;
		}

		if ( is_author() ) {
			return get_user_meta( $this->args->ID, $name, true );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			return get_term_meta( $this->args->term_id, $name, true );
		}

		return is_singular() || ! empty( $this->args->post_type ) ? get_post_meta( $this->args->ID, $name, true ) : null;
	}

	/**
	 * Get the post type "single" label.
	 *
	 * @return string|null
	 */
	public function get_post_type_single() {
		$name = $this->determine_post_type_label( 'single' );

		return '' !== $name ? $name : null;
	}

	/**
	 * Get the post type "plural" label.
	 *
	 * @return string|null
	 */
	public function get_post_type_plural() {
		$name = $this->determine_post_type_label( 'plural' );

		return '' !== $name ? $name : null;
	}
}
