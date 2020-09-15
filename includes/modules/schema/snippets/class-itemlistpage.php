<?php
/**
 * The ItemListPage Class.
 *
 * @since      1.0.47
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * ItemListPage class.
 */
class ItemListPage implements Snippet {

	/**
	 * Sets the Schema structured data for the ItemList.
	 *
	 * @link https://schema.org/ItemList
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$queried_object = get_queried_object();

		/**
		 * Filter to remove snippet data: rank_math/snippet/remove_taxonomy_data.
		 *
		 * @param bool $unsigned Default: false
		 * @param string $unsigned Taxonomy Name
		 */
		if ( true === Helper::get_settings( 'titles.remove_' . $queried_object->taxonomy . '_snippet_data' ) || true === apply_filters( 'rank_math/snippet/remove_taxonomy_data', false, $queried_object->taxonomy ) ) {
			return $data;
		}

		$data['ItemList'] = [
			'@type'            => 'ItemList',
			'itemListElement'  => $this->get_post_collection( $jsonld ),
			'mainEntityOfPage' => [ '@id' => ! empty( $data['WebPage'] ) ? $data['WebPage']['@id'] : '' ],
		];

		return $data;
	}

	/**
	 * Get post parts
	 *
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function get_post_collection( $jsonld ) {
		$collection = [];
		$count      = 1;
		while ( have_posts() ) {
			the_post();
			$collection[] = [
				'@type'    => 'ListItem',
				'position' => $count,
				'url'      => $jsonld->get_post_url( get_the_ID() ),
			];

			$count++;
		}

		wp_reset_postdata();
		return $collection;
	}
}
