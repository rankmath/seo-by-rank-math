<?php
/**
 * The Product Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use MyThemeShop\Helpers\Conditional;
use RankMath\Schema\Product_Edd;
use RankMath\Schema\Product_WooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Product class.
 */
class Product implements Snippet {

	/**
	 * Hold JsonLD Instance.
	 *
	 * @var JsonLD
	 */
	private $json = '';

	/**
	 * Product rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$entity = [
			'@type' => 'Product',
		];
		if ( Conditional::is_woocommerce_active() && is_product() ) {
			remove_action( 'wp_footer', [ WC()->structured_data, 'output_structured_data' ], 10 );
			remove_action( 'woocommerce_email_order_details', [ WC()->structured_data, 'output_email_structured_data' ], 30 );
			$product = new Product_WooCommerce();
			$product->set_product( $entity, $jsonld );
		}

		if ( Conditional::is_edd_active() && is_singular( 'download' ) ) {
			remove_action( 'edd_purchase_link_top', 'edd_purchase_link_single_pricing_schema', 10 );
			remove_action( 'loop_start', 'edd_microdata_wrapper_open', 10 );
			$product = new Product_Edd();
			$product->set_product( $entity, $jsonld );
		}

		return $entity;
	}

	/**
	 * Get seller.
	 *
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public static function get_seller( $jsonld ) {
		$site_url = home_url();
		$type     = Helper::get_settings( 'titles.knowledgegraph_type' );
		$seller   = [
			'@type' => 'person' === $type ? 'Person' : 'Organization',
			'@id'   => trailingslashit( $site_url ),
			'name'  => $jsonld->get_website_name(),
			'url'   => $site_url,
		];

		if ( 'company' === $type ) {
			$seller['logo'] = Helper::get_settings( 'titles.knowledgegraph_logo' );
		}

		return $seller;
	}

	/**
	 * Set product categories.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $taxonomy   Taxonomy.
	 */
	public static function get_category( $product_id, $taxonomy ) {
		$categories = get_the_terms( $product_id, $taxonomy );
		if ( is_wp_error( $categories ) || empty( $categories ) ) {
			return;
		}

		if ( 0 === $categories[0]->parent ) {
			return $categories[0]->name;
		}

		$ancestors = array_reverse( get_ancestors( $categories[0]->term_id, $taxonomy ) );
		foreach ( $ancestors as $parent ) {
			$term       = get_term( $parent, $taxonomy );
			$category[] = $term->name;
		}
		$category[] = $categories[0]->name;

		return join( ' > ', $category );
	}
}
