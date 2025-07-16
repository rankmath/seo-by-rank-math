<?php
/**
 * The sitemap provider for taxonomies.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\Sitemap\Providers;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Sitemap\Router;
use RankMath\Sitemap\Sitemap;
use RankMath\Sitemap\Image_Parser;
use RankMath\Helpers\DB as DB_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomy provider
 */
class Taxonomy implements Provider {

	use Hooker;

	/**
	 * Holds image parser instance.
	 *
	 * @var Image_Parser
	 */
	protected static $image_parser;

	/**
	 * Check if provider supports given item type.
	 *
	 * @param  string $type Type string to check for.
	 * @return boolean
	 */
	public function handles_type( $type ) {
		if ( is_a( $type, 'WP_Taxonomy' ) ) {
			$type = $type->name;
		}

		if (
			empty( $type ) ||
			false === taxonomy_exists( $type ) ||
			false === Helper::is_taxonomy_viewable( $type ) ||
			false === Helper::get_settings( 'sitemap.tax_' . $type . '_sitemap' ) ||
			in_array( $type, [ 'link_category', 'nav_menu', 'post_format' ], true )
		) {
			return false;
		}

		/**
		 * Filter decision if taxonomy is excluded from the XML sitemap.
		 *
		 * @param bool   $exclude Default false.
		 * @param string $type    Taxonomy name.
		 */
		return ! $this->do_filter( 'sitemap/exclude_taxonomy', false, $type );
	}

	/**
	 * Get set of sitemaps index link data.
	 *
	 * @param  int $max_entries Entries per sitemap.
	 * @return array
	 */
	public function get_index_links( $max_entries ) {
		$taxonomies = Helper::get_accessible_taxonomies();
		$taxonomies = array_filter( $taxonomies, [ $this, 'handles_type' ] );
		if ( empty( $taxonomies ) ) {
			return [];
		}

		// Retrieve all the taxonomies and their terms so we can do a proper count on them.
		/**
		 * Filter the setting of excluding empty terms from the XML sitemap.
		 *
		 * @param boolean $exclude        Defaults to true.
		 * @param array   $taxonomy_name Array of names for the taxonomies being processed.
		 */
		$hide_empty = $this->do_filter( 'sitemap/exclude_empty_terms', true, $taxonomies );

		$all_taxonomies = [];
		foreach ( $taxonomies as $taxonomy_name => $object ) {
			$or_meta_query = ! Helper::is_taxonomy_indexable( $taxonomy_name ) ? [] :
				[
					'key'     => 'rank_math_robots',
					'compare' => 'NOT EXISTS',
				];

			$all_taxonomies[ $taxonomy_name ] = get_terms(
				[
					'taxonomy'   => $taxonomy_name,
					'hide_empty' => $hide_empty,
					'fields'     => 'ids',
					'orderby'    => 'name',
					'meta_query' => [
						'relation' => 'OR',
						[
							'key'     => 'rank_math_robots',
							'value'   => 'noindex',
							'compare' => 'NOT LIKE',
						],
						$or_meta_query,
					],
				]
			);
		}

		$index = [];
		foreach ( $all_taxonomies as $tax_name => $terms ) {
			if ( is_wp_error( $terms ) ) {
				continue;
			}

			$max_pages   = 1;
			$total_count = empty( $terms ) ? 1 : count( $terms );
			if ( $total_count > $max_entries ) {
				$max_pages = (int) ceil( $total_count / $max_entries );
			}

			$tax = $taxonomies[ $tax_name ];
			if ( ! is_array( $tax->object_type ) || count( $tax->object_type ) === 0 ) {
				continue;
			}

			$last_modified_gmt = Sitemap::get_last_modified_gmt( $tax->object_type );
			for ( $page_counter = 0; $page_counter < $max_pages; $page_counter++ ) {
				$current_page = ( $max_pages > 1 ) ? ( $page_counter + 1 ) : '';
				$terms_page   = array_splice( $terms, 0, $max_entries );
				if ( ! $terms_page ) {
					continue;
				}

				$query = new \WP_Query(
					[
						'post_type'      => $tax->object_type,
						'tax_query'      => [
							[
								'taxonomy' => $tax_name,
								'terms'    => $terms_page,
							],
						],
						'orderby'        => 'modified',
						'order'          => 'DESC',
						'posts_per_page' => 1,
					]
				);

				$item = $this->do_filter(
					'sitemap/index/entry',
					[
						'loc'     => Router::get_base_url( $tax_name . '-sitemap' . $current_page . '.xml' ),
						'lastmod' => $query->have_posts() ? $query->posts[0]->post_modified_gmt : $last_modified_gmt,
					],
					'term',
					$tax_name,
				);

				if ( ! $item ) {
					continue;
				}

				$index[] = $item;
			}
		}

		return $index;
	}

	/**
	 * Get set of sitemap link data.
	 *
	 * @param  string $type         Sitemap type.
	 * @param  int    $max_entries  Entries per sitemap.
	 * @param  int    $current_page Current page of the sitemap.
	 * @return array
	 */
	public function get_sitemap_links( $type, $max_entries, $current_page ) {
		$links    = [];
		$taxonomy = get_taxonomy( $type );
		$terms    = $this->get_terms( $taxonomy, $max_entries, $current_page );
		Sitemap::maybe_redirect( count( $this->get_terms( $taxonomy, 0, $current_page ) ), $max_entries );

		foreach ( $terms as $term ) {
			$url = [];
			if ( ! Sitemap::is_object_indexable( $term, 'term' ) ) {
				continue;
			}

			$link = $this->get_term_link( $term );
			if ( ! $link ) {
				continue;
			}

			$url['loc']    = $link;
			$url['mod']    = $this->get_lastmod( $term );
			$url['images'] = ! is_null( $this->get_image_parser() ) ? $this->get_image_parser()->get_term_images( $term ) : [];

			/** This filter is documented at inc/sitemaps/class-post-type-sitemap-provider.php */
			$url = $this->do_filter( 'sitemap/entry', $url, 'term', $term );

			if ( ! empty( $url ) ) {
				$links[] = $url;
			}
		}

		return $links;
	}

	/**
	 * Get the Image Parser.
	 *
	 * @return Image_Parser
	 */
	protected function get_image_parser() {
		if ( class_exists( 'RankMath\Sitemap\Image_Parser' ) && ! isset( self::$image_parser ) ) {
			self::$image_parser = new Image_Parser();
		}

		return self::$image_parser;
	}

	/**
	 * Get terms for taxonomy.
	 *
	 * @param  object $taxonomy     Taxonomy name.
	 * @param  int    $max_entries  Entries per sitemap.
	 * @param  int    $current_page Current page of the sitemap.
	 * @return false|array
	 */
	private function get_terms( $taxonomy, $max_entries, $current_page ) {
		$offset     = $current_page > 1 ? ( ( $current_page - 1 ) * $max_entries ) : 0;
		$hide_empty = ! Helper::get_settings( 'sitemap.tax_' . $taxonomy->name . '_include_empty' );

		// Getting terms.
		$terms = get_terms(
			[
				'taxonomy'               => $taxonomy->name,
				'orderby'                => 'term_order',
				'hide_empty'             => $hide_empty,
				'offset'                 => $offset,
				'number'                 => $max_entries,
				'exclude'                => wp_parse_id_list( Helper::get_settings( 'sitemap.exclude_terms' ) ),

				/*
				 * Limits aren't included in queries when hierarchical is set to true (by default).
				 *
				 * @link: https://github.com/WordPress/WordPress/blob/5.3/wp-includes/class-wp-term-query.php#L558-L567
				 */
				'hierarchical'           => false,
				'update_term_meta_cache' => false,
				'meta_query'             => [
					'relation' => 'OR',
					[
						'key'     => 'rank_math_robots',
						'value'   => 'noindex',
						'compare' => 'NOT LIKE',
					],
					[
						'key'     => 'rank_math_robots',
						'compare' => 'NOT EXISTS',
					],
				],
			]
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		return $terms;
	}

	/**
	 * Get term link.
	 *
	 * @param  WP_Term $term Term object.
	 * @return string
	 */
	private function get_term_link( $term ) {
		$url       = get_term_link( $term, $term->taxonomy );
		$canonical = Helper::get_term_meta( 'canonical_url', $term, $term->taxonomy );
		if ( $canonical && $canonical !== $url ) {
			/*
			 * Let's assume that if a canonical is set for this term and it's different from
			 * the URL of this term, that page is either already in the XML sitemap OR is on
			 * an external site, either way, we shouldn't include it here.
			 */
			return false;
		}

		return $url;
	}

	/**
	 * Get last modified date of post by term.
	 *
	 * @param  WP_Term $term Term object.
	 * @return string
	 */
	public function get_lastmod( $term ) {
		global $wpdb;

		return DB_Helper::get_var(
			$wpdb->prepare(
				"
			SELECT MAX(p.post_modified_gmt) AS lastmod
			FROM	$wpdb->posts AS p
			INNER JOIN $wpdb->term_relationships AS term_rel
				ON		term_rel.object_id = p.ID
			INNER JOIN $wpdb->term_taxonomy AS term_tax
				ON		term_tax.term_taxonomy_id = term_rel.term_taxonomy_id
				AND		term_tax.taxonomy = %s
				AND		term_tax.term_id = %d
			WHERE	p.post_status IN ('publish', 'inherit')
				AND		p.post_password = ''
		",
				$term->taxonomy,
				$term->term_id
			)
		);
	}
}
