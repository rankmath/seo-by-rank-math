<?php
/**
 * The Easy Digital Downloads Product Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use EDD_Download;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Product_Edd class.
 */
class Product_Edd {

	/**
	 * Set product data for rich snippet.
	 *
	 * @param array  $entity Array of JSON-LD entity.
	 * @param JsonLD $jsonld JsonLD Instance.
	 */
	public function set_product( &$entity, $jsonld ) {
		$product_id = get_the_ID();
		$permalink  = get_permalink();
		$product    = new EDD_Download( $product_id );

		$entity['url']      = $permalink;
		$entity['name']     = $jsonld->post->post_title;
		$entity['category'] = Product::get_category( $product_id, 'download_category' );

		// SKU.
		if ( $product->get_sku() ) {
			$entity['sku'] = $product->get_sku();
		}

		// Offers.
		$seller     = Product::get_seller( $jsonld );
		$variations = $this->has_variations( $product );
		if ( false !== $variations ) {
			$entity['offers'] = [];
			foreach ( $variations as $variation ) {
				$offer = [
					'@type'         => 'Offer',
					'description'   => $variation['name'],
					'price'         => $variation['amount'],
					'priceCurrency' => edd_get_currency(),
					'url'           => $permalink,
					'seller'        => $seller,
				];

				// Set Price Specification.
				$this->set_price_specification( $variation['amount'], $offer );
				$entity['offers'][] = $offer;
			}

			return;
		}

		// Single offer.
		$entity['offers'] = [
			'@type'         => 'Offer',
			'price'         => $product->get_price() ? $product->get_price() : '0',
			'priceCurrency' => edd_get_currency(),
			'seller'        => $seller,
			'url'           => $permalink,
		];

		// Set Price Specification.
		$this->set_price_specification( $product->get_price(), $entity['offers'] );
	}

	/**
	 * Set price specification.
	 *
	 * @param object $price  Product price.
	 * @param array  $entity Array of offer entity.
	 */
	private function set_price_specification( $price, &$entity ) {
		if ( ! edd_use_taxes() ) {
			return;
		}

		$entity['priceSpecification'] = [
			'price'                 => $price ? $price : '0',
			'priceCurrency'         => edd_get_currency(),
			'valueAddedTaxIncluded' => edd_prices_include_tax() ? 'true' : 'false',
		];
	}

	/**
	 * If product is variable, set variations.
	 *
	 * @param  EDD_Download $product Current product.
	 *
	 * @return array|boolean
	 */
	private function has_variations( $product ) {
		if ( ! $product->has_variable_prices() ) {
			return false;
		}

		$variations = $product->get_prices();
		return ! empty( $variations ) ? $variations : false;
	}
}
