<?php
/**
 * The Products Page Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Products_Page class.
 */
class Products_Page implements Snippet {

	/**
	 * Sets the Schema structured data for the CollectionPage.
	 *
	 * @link https://schema.org/CollectionPage
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		if ( ! $this->can_add_snippet_taxonomy() || ! $this->can_add_snippet_shop() ) {
			return $data;
		}

		$data['ProductsPage'] = [
			'@context' => 'https://schema.org/',
			'@graph'   => [],
		];

		while ( have_posts() ) {
			the_post();

			$post_id = get_the_ID();
			$product = wc_get_product( $post_id );
			$url     = $jsonld->get_post_url( $post_id );

			$part = [
				'@type'       => 'Product',
				'name'        => $jsonld->get_product_title( $product ),
				'url'         => $url,
				'@id'         => $url,
				'description' => $jsonld->get_product_desc( $product ),
			];

			$data['ProductsPage']['@graph'][] = $part;
		}

		wp_reset_query();

		return $data;
	}

	/**
	 * Check if structured data can be added for the current taxonomy.
	 *
	 * @return boolean|string
	 */
	private function can_add_snippet_taxonomy() {
		$queried_object = get_queried_object();

		/**
		 * Allow developer to remove snippet data.
		 *
		 * @param bool $unsigned Default: false
		 * @param string $unsigned Taxonomy Name
		 */
		if (
			! is_shop() &&
			(
				true === Helper::get_settings( 'titles.remove_' . $queried_object->taxonomy . '_snippet_data' ) ||
				true === apply_filters( 'rank_math/snippet/remove_taxonomy_data', false, $queried_object->taxonomy )
			)
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check if structured data can be added for the Shop page.
	 *
	 * @return boolean|string
	 */
	private function can_add_snippet_shop() {
		/**
		 * Allow developer to remove snippet data from Shop page.
		 *
		 * @param bool $unsigned Default: false
		 */
		if (
			is_shop() &&
			(
				true === Helper::get_settings( 'general.remove_shop_snippet_data' ) ||
				true === apply_filters( 'rank_math/snippet/remove_shop_data', false )
			)
		) {
			return false;
		}

		return true;
	}
}
