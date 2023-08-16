<?php
/**
 * The HTML sitemap generator for terms.
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
 * Terms class.
 */
class Terms {

	use Hooker;

	/**
	 * Get all terms from a given taxonomy.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @param int    $parent   Parent term ID.
	 *
	 * @return array
	 */
	private function get_terms( $taxonomy, $parent = 0 ) {
		$sort_map = [
			'published'    => [
				'field' => 'term_id',
				'order' => 'DESC',
			],
			'modified'     => [
				'field' => 'term_id',
				'order' => 'DESC',
			],
			'alphabetical' => [
				'field' => 'name',
				'order' => 'ASC',
			],
			'post_id'      => [
				'field' => 'term_id',
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
		 * @var string $taxonomy The taxonomy name.
		 * @var string $order    The item type.
		 */
		$sort = $this->do_filter( 'sitemap/html_sitemap/sort_items', $sort, 'terms', $taxonomy );

		$exclude     = wp_parse_id_list( Helper::get_settings( 'sitemap.exclude_terms' ) );
		$terms_table = Database::table( 'terms' );
		$tt_table    = Database::table( 'term_taxonomy' );

		$query = $terms_table->where( 'taxonomy', $taxonomy )
			->select( [ $terms_table->table . '.term_id', 'name', 'slug', 'taxonomy' ] )
			->leftJoin( $tt_table->table, $terms_table->table . '.term_id', $tt_table->table . '.term_id' )
			->where( 'parent', $parent );

		if ( ! empty( $exclude ) ) {
			$query->whereNotIn( $terms_table->table . '.term_id', $exclude );
		}

		$terms = $query->orderBy( $sort['field'], $sort['order'] )->get();

		return array_filter(
			$terms,
			function( $term ) use ( $taxonomy ) {
				return SitemapBase::is_object_indexable( get_term( $term->term_id, $taxonomy ), 'term' );
			}
		);
	}

	/**
	 * Generate the HTML sitemap for a given taxonomy.
	 *
	 * @param string $taxonomy   Taxonomy name.
	 * @param bool   $show_dates Whether to show dates.
	 * @param array  $args       Array with term query arguments.
	 *
	 * @return string
	 */
	public function generate_sitemap( $taxonomy, $show_dates, $args = [] ) {
		$terms = get_terms( $taxonomy, $args );
		if ( empty( $terms ) ) {
			return '';
		}

		$output[] = '<div class="rank-math-html-sitemap__section rank-math-html-sitemap__section--taxonomy rank-math-html-sitemap__section--' . $taxonomy . '">';
		$output[] = '<h2 class="rank-math-html-sitemap__title">' . esc_html( get_taxonomy( $taxonomy )->labels->name ) . '</h2>';
		$output[] = '<ul class="rank-math-html-sitemap__list">';
		$output[] = $this->generate_terms_list( $terms, $taxonomy );
		$output[] = '</ul>';
		$output[] = '</div>';

		$output = implode( '', $output );

		return $output;
	}

	/**
	 * Get the term list HTML.
	 *
	 * @param array  $terms    The terms to output.
	 * @param object $taxonomy The taxonomy object.
	 *
	 * @return string
	 */
	private function generate_terms_list( $terms, $taxonomy ) {
		if ( empty( $terms ) ) {
			return '';
		}

		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			return $this->generate_terms_list_hierarchical( $terms, $taxonomy );
		}

		return $this->generate_terms_list_flat( $terms, $taxonomy );
	}

	/**
	 * Get the term list HTML for non-hierarchical taxonomies.
	 *
	 * @param array  $terms    The terms to output.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return string
	 */
	private function generate_terms_list_flat( $terms, $taxonomy ) {
		$output = [];
		foreach ( $terms as $term ) {
			$output[] = '<li class="rank-math-html-sitemap__item">'
				. '<a href="' . esc_url( $this->get_term_link( (int) $term->term_id, $taxonomy ) ) . '" class="rank-math-html-sitemap__link">'
				. esc_html( $this->get_term_title( $term, $taxonomy ) )
				. '</a>'
				. '</li>';
		}

		return implode( '', $output );
	}

	/**
	 * Get the term list HTML for hierarchical taxonomies. This will output the
	 * terms in a nested list.
	 *
	 * @param array  $terms    The terms to output.
	 * @param string $taxonomy The taxonomy name.
	 * @param bool   $remove_children Whether to remove terms that have a parent.
	 *
	 * @return string
	 */
	private function generate_terms_list_hierarchical( $terms, $taxonomy, $remove_children = true ) {
		$output = [];
		if ( $remove_children ) {
			// Remove initial with parents because they are queried below in $this->get_terms!
			$terms = $this->remove_with_parent( $terms );
		}
		foreach ( $terms as $term ) {
			$output[] = '<li class="rank-math-html-sitemap__item">'
							. '<a href="' . esc_url( $this->get_term_link( (int) $term->term_id, $taxonomy ) ) . '" class="rank-math-html-sitemap__link">'
							. esc_html( $this->get_term_title( $term, $taxonomy ) )
							. '</a>'
						. '</li>';

			$children = $this->get_terms( $taxonomy, $term->term_id );

			if ( ! empty( $children ) ) {
				$output[] = '<ul class="rank-math-html-sitemap__list">';
				$output[] = $this->generate_terms_list_hierarchical( $children, $taxonomy, false );
				$output[] = '</ul>';
			}
		}

		return implode( '', $output );
	}

	/**
	 * Get the term link.
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return string
	 */
	private function get_term_link( $term_id, $taxonomy ) {
		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) ) {
			return '';
		}

		return get_term_link( $term );
	}

	/**
	 * Get the term title.
	 *
	 * @param object $term     The term data.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return string
	 */
	private function get_term_title( $term, $taxonomy ) {
		if ( Helper::get_settings( 'sitemap.html_sitemap_seo_titles' ) !== 'seo_titles' ) {
			return $term->name;
		}

		// Custom SEO title.
		$meta = get_term_meta( $term->term_id, 'rank_math_title', true );
		if ( ! empty( $meta ) ) {
			return Helper::replace_vars( $meta, get_term( $term->term_id, $taxonomy ) );
		}

		// Default SEO title from the global settings.
		$template = Helper::get_settings( "titles.tax_{$taxonomy}_title" );
		if ( ! empty( $template ) ) {
			return Helper::replace_vars( $template, get_term( $term->term_id, $taxonomy ) );
		}

		// Fallback to term name.
		return $term->name;
	}

	/**
	 * Removes terms that have a parent from the list.
	 *
	 * @param array $terms The terms list.
	 *
	 * @return array
	 */
	private function remove_with_parent( $terms ) {
		return array_filter(
			$terms,
			function ( $term ) {
				return ! $term->parent;
			}
		);
	}

}
