<?php
/**
 * The WooCommerce Product Class.
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
 * Product_WooCommerce class.
 */
class Product_WooCommerce {

	/**
	 * Attribute assigner.
	 *
	 * @var WC_Attributes
	 */
	private $attributes;

	/**
	 * Set product data for rich snippet.
	 *
	 * @param array  $entity Array of JSON-LD entity.
	 * @param JsonLD $jsonld JsonLD Instance.
	 */
	public function set_product( &$entity, $jsonld ) {
		$product          = wc_get_product( get_the_ID() );
		$this->attributes = new WC_Attributes( $product );

		if ( Helper::is_module_active( 'woocommerce' ) ) {
			$brand = \RankMath\WooCommerce\Woocommerce::get_brands( $product->get_id() );

			// Brand.
			if ( ! empty( $brand ) ) {
				$entity['brand'] = [
					'@type' => 'Brand',
					'name'  => $brand,
				];
			}
		}

		$entity['name']             = $jsonld->get_product_title( $product );
		$entity['description']      = $jsonld->get_product_desc( $product );
		$entity['sku']              = $product->get_sku() ? $product->get_sku() : '';
		$entity['category']         = Product::get_category( $product->get_id(), 'product_cat' );
		$entity['mainEntityOfPage'] = [ '@id' => $jsonld->parts['canonical'] . '#webpage' ];

		$this->set_weight( $product, $entity );
		$this->set_dimensions( $product, $entity );
		$this->set_images( $product, $entity );
		$this->set_ratings( $product, $entity );
		$this->set_offers( $product, $entity, Product::get_seller( $jsonld ) );

		// GTIN numbers need product attributes.
		$this->attributes->assign_property( $entity, 'gtin8' );
		$this->attributes->assign_property( $entity, 'gtin12' );
		$this->attributes->assign_property( $entity, 'gtin13' );
		$this->attributes->assign_property( $entity, 'gtin14' );

		// Color.
		$this->attributes->assign_property( $entity, 'color' );

		// Remaining Attributes.
		$this->attributes->assign_remaining( $entity );
	}

	/**
	 * Set product weight.
	 *
	 * @param object $product Product instance.
	 * @param array  $entity  Array of JSON-LD entity.
	 */
	private function set_weight( $product, &$entity ) {
		if ( ! $product->has_weight() ) {
			return;
		}

		$hash = [
			'lbs' => 'LBR',
			'kg'  => 'KGM',
			'g'   => 'GRM',
			'oz'  => 'ONZ',
		];
		$unit = get_option( 'woocommerce_weight_unit' );

		$entity['weight'] = [
			'@type'    => 'QuantitativeValue',
			'unitCode' => isset( $hash[ $unit ] ) ? $hash[ $unit ] : 'LBR',
			'value'    => $product->get_weight(),
		];
	}

	/**
	 * Set product dimension.
	 *
	 * @param object $product Product instance.
	 * @param array  $entity  Array of JSON-LD entity.
	 */
	private function set_dimensions( $product, &$entity ) {
		if ( ! $product->has_dimensions() ) {
			return;
		}

		$hash = [
			'in' => 'INH',
			'm'  => 'MTR',
			'cm' => 'CMT',
			'mm' => 'MMT',
			'yd' => 'YRD',
		];
		$unit = get_option( 'woocommerce_dimension_unit' );
		$code = isset( $hash[ $unit ] ) ? $hash[ $unit ] : '';

		$entity['height'] = [
			'@type'    => 'QuantitativeValue',
			'unitCode' => $code,
			'value'    => $product->get_height(),
		];

		$entity['width'] = [
			'@type'    => 'QuantitativeValue',
			'unitCode' => $code,
			'value'    => $product->get_width(),
		];

		$entity['depth'] = [
			'@type'    => 'QuantitativeValue',
			'unitCode' => $code,
			'value'    => $product->get_length(),
		];
	}

	/**
	 * Set product images.
	 *
	 * @param object $product Product instance.
	 * @param array  $entity  Array of JSON-LD entity.
	 */
	private function set_images( $product, &$entity ) {
		if ( ! $product->get_image_id() ) {
			return;
		}

		$image = wp_get_attachment_image_src( $product->get_image_id(), 'single-post-thumbnail' );
		if ( ! empty( $image ) ) {
			$entity['image'][] = [
				'@type'  => 'ImageObject',
				'url'    => $image[0],
				'height' => $image[2],
				'width'  => $image[1],
			];
		}

		$gallery = $product->get_gallery_image_ids();
		foreach ( $gallery as $image_id ) {
			$image = wp_get_attachment_image_src( $image_id, 'single-post-thumbnail' );
			if ( empty( $image ) ) {
				continue;
			}

			$entity['image'][] = [
				'@type'  => 'ImageObject',
				'url'    => $image[0],
				'height' => $image[2],
				'width'  => $image[1],
			];
		}
	}

	/**
	 * Set product ratings.
	 *
	 * @param object $product Product instance.
	 * @param array  $entity  Array of JSON-LD entity.
	 */
	private function set_ratings( $product, &$entity ) {
		if ( $product->get_rating_count() < 1 ) {
			return;
		}

		// Aggregate Rating.
		$entity['aggregateRating'] = [
			'@type'       => 'AggregateRating',
			'ratingValue' => $product->get_average_rating(),
			'bestRating'  => '5',
			'ratingCount' => $product->get_rating_count(),
			'reviewCount' => $product->get_review_count(),
		];

		// Reviews.
		$comments  = get_comments(
			[
				'post_type' => 'product',
				'post_id'   => get_the_ID(),
				'status'    => 'approve',
				'parent'    => 0,
			]
		);
		$permalink = $product->get_permalink();

		foreach ( $comments as $comment ) {
			$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
			if ( ! $rating ) {
				continue;
			}

			$entity['review'][] = [
				'@type'         => 'Review',
				'@id'           => $permalink . '#li-comment-' . $comment->comment_ID,
				'description'   => $comment->comment_content,
				'datePublished' => $comment->comment_date,
				'reviewRating'  => [
					'@type'       => 'Rating',
					'ratingValue' => $rating,
					'bestRating'  => '5',
					'worstRating' => '1',
				],
				'author'        => [
					'@type' => 'Person',
					'name'  => $comment->comment_author,
					'url'   => $comment->comment_author_url,
				],
			];
		}
	}

	/**
	 * Set product offers.
	 *
	 * @param object $product Product instance.
	 * @param array  $entity  Array of JSON-LD entity.
	 * @param array  $seller  Seller info.
	 */
	private function set_offers( $product, &$entity, $seller ) {
		if ( '' === $product->get_price() ) {
			return;
		}

		if ( true === $this->set_offers_variable( $product, $entity, $seller ) ) {
			return;
		}

		$offer = [
			'@type'           => 'Offer',
			'price'           => $product->get_price() ? wc_format_decimal( $product->get_price(), wc_get_price_decimals() ) : '0',
			'priceCurrency'   => get_woocommerce_currency(),
			'priceValidUntil' => $product->is_on_sale() && ! empty( $product->get_date_on_sale_to() ) ? date_i18n( 'Y-m-d', strtotime( $product->get_date_on_sale_to() ) ) : date( 'Y-12-31', time() + YEAR_IN_SECONDS ),
			'availability'    => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'itemCondition'   => 'NewCondition',
			'url'             => $product->get_permalink(),
			'seller'          => $seller,
		];

		// Set Price Specification.
		$this->set_price_specification( $product->get_price(), $offer );

		$this->attributes->assign_property( $offer, 'itemCondition' );
		$entity['offers'] = $offer;
	}

	/**
	 * Set product variable offers.
	 *
	 * @param object $product Product instance.
	 * @param array  $entity  Array of JSON-LD entity.
	 * @param array  $seller  Seller info.
	 */
	private function set_offers_variable( $product, &$entity, $seller ) {
		if ( ! $product->is_type( 'variable' ) ) {
			return false;
		}

		if ( $this->set_single_variable_offer( $product, $entity, $seller ) ) {
			return true;
		}

		$permalink = $product->get_permalink();
		$lowest    = wc_format_decimal( $product->get_variation_price( 'min', false ), wc_get_price_decimals() );
		$highest   = wc_format_decimal( $product->get_variation_price( 'max', false ), wc_get_price_decimals() );

		if ( $lowest === $highest ) {
			$offer = [
				'@type'           => 'Offer',
				'price'           => $lowest,
				'priceValidUntil' => date( 'Y-12-31', time() + YEAR_IN_SECONDS ),
			];

			// Set Price Specification.
			$this->set_price_specification( $lowest, $offer );
		} else {
			$offer = [
				'@type'      => 'AggregateOffer',
				'lowPrice'   => $lowest,
				'highPrice'  => $highest,
				'offerCount' => count( $product->get_children() ),
			];
		}

		$offer += [
			'priceCurrency' => get_woocommerce_currency(),
			'availability'  => 'http://schema.org/' . ( $product->is_in_stock() ? 'InStock' : 'OutOfStock' ),
			'seller'        => $seller,
			'url'           => $permalink,
		];

		$entity['offers'] = $offer;

		return true;
	}

	/**
	 * Set Single Variable Product offer.
	 *
	 * Credit @leewillis77: https://github.com/leewillis77/wc-structured-data-option-4
	 *
	 * @param object $product Product instance.
	 * @param array  $entity  Array of JSON-LD entity.
	 * @param array  $seller  Seller info.
	 */
	private function set_single_variable_offer( $product, &$entity, $seller ) {
		$data_store   = \WC_Data_Store::load( 'product' );
		$variation_id = $data_store->find_matching_product_variation( $product, wp_unslash( $_GET ) );
		$variation    = $variation_id ? wc_get_product( $variation_id ) : false;
		if ( empty( $variation ) ) {
			return false;
		}

		$price_valid_until = date( 'Y-12-31', current_time( 'timestamp', true ) + YEAR_IN_SECONDS );
		if ( $variation->is_on_sale() && $variation->get_date_on_sale_to() ) {
			$price_valid_until = date( 'Y-m-d', $variation->get_date_on_sale_to()->getTimestamp() );
		}

		$entity['offers'] = [
			'@type'           => 'Offer',
			'url'             => $variation->get_permalink(),
			'sku'             => $variation->get_sku(),
			'price'           => wc_format_decimal( $variation->get_price(), wc_get_price_decimals() ),
			'priceCurrency'   => get_woocommerce_currency(),
			'priceValidUntil' => $price_valid_until,
			'seller'          => $seller,
			'availability'    => 'http://schema.org/' . ( $variation->is_in_stock() ? 'InStock' : 'OutOfStock' ),
		];

		return true;
	}

	/**
	 * Set price specification.
	 *
	 * @param object $price  Product price.
	 * @param array  $entity Array of offer entity.
	 */
	private function set_price_specification( $price, &$entity ) {
		if ( ! wc_tax_enabled() ) {
			return;
		}

		$entity['priceSpecification'] = [
			'price'                 => $price ? $price : '0',
			'priceCurrency'         => get_woocommerce_currency(),
			'valueAddedTaxIncluded' => wc_prices_include_tax() ? 'true' : 'false',
		];
	}
}
