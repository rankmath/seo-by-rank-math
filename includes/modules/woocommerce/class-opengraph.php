<?php
/**
 * Add Open Graph data for the WooCommerce module.
 *
 * @since      1.0.32
 * @package    RankMath
 * @subpackage RankMath\WooCommerce
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\WooCommerce;

use RankMath\Traits\Hooker;
use RankMath\OpenGraph\Image as OpenGraph_Image;

defined( 'ABSPATH' ) || exit;

/**
 * WC Opengraph class.
 */
class Opengraph extends Sitemap {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function opengraph() {
		$this->filter( 'language_attributes', 'og_product_namespace', 11 );
		$this->filter( 'rank_math/opengraph/desc', 'og_desc_product_taxonomy' );
		$this->action( 'rank_math/opengraph/facebook', 'og_enhancement', 50 );
		$this->action( 'rank_math/opengraph/facebook/add_additional_images', 'set_opengraph_image' );
	}

	/**
	 * Add the OpenGraph namespace.
	 *
	 * @param string $output The original namespace.
	 *
	 * @return string
	 */
	public function og_product_namespace( $output ) {
		if ( is_singular( 'product' ) ) {
			$output = preg_replace( '/prefix="([^"]+)"/', 'prefix="$1 product: https://ogp.me/ns/product#"', $output );
		}

		return $output;
	}

	/**
	 * Make sure the OpenGraph description is put out.
	 *
	 * @param string $desc The current description, will be overwritten if we're on a product page.
	 *
	 * @return string
	 */
	public function og_desc_product_taxonomy( $desc ) {
		if ( is_product_taxonomy() ) {
			$term_desc = term_description();
			if ( ! empty( $term_desc ) ) {
				$desc = wp_strip_all_tags( $term_desc, true );
				$desc = strip_shortcodes( $desc );
			}
		}

		return $desc;
	}

	/**
	 * Adds the other product images to the OpenGraph output.
	 *
	 * @param OpenGraph $opengraph The current opengraph network object.
	 */
	public function og_enhancement( $opengraph ) {
		$product = $this->get_product();
		if ( ! is_object( $product ) ) {
			return;
		}

		$brand = WooCommerce::get_brands( get_the_ID() );
		if ( ! empty( $brand ) ) {
			$opengraph->tag( 'product:brand', $brand );
		}

		/**
		 * Allow developers to prevent the output of the price in the OpenGraph tags.
		 *
		 * @param bool unsigned Defaults to true.
		 */
		if ( $this->do_filter( 'woocommerce/og_price', ! $product->is_type( 'variable' ) ) ) {
			$opengraph->tag( 'product:price:amount', $product->get_price() );
			$opengraph->tag( 'product:price:currency', get_woocommerce_currency() );
		}

		if ( $product->is_in_stock() ) {
			$opengraph->tag( 'product:availability', 'instock' );
		}
	}

	/**
	 * Adds the opengraph images.
	 *
	 * @param OpenGraph_Image $opengraph_image The OpenGraph image to use.
	 */
	public function set_opengraph_image( OpenGraph_Image $opengraph_image ) {
		if ( ! function_exists( 'is_product_category' ) || is_product_category() ) {
			global $wp_query;
			$cat          = $wp_query->get_queried_object();
			$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
			$opengraph_image->add_image_by_id( $thumbnail_id );
		}

		/**
		 * Passing a truthy value to the filter will effectively short-circuit the process of adding gallery images.
		 *
		 * @param bool $return Short-circuit return value. Either false or true.
		 */
		if ( ! $this->do_filter( 'woocommerce/opengraph/add_gallery_images', false ) ) {
			return;
		}

		$product = $this->get_product();
		if ( ! is_object( $product ) ) {
			return;
		}

		$this->set_image_ids( $product, $opengraph_image );
	}

	/**
	 * Set images for the given product.
	 *
	 * @param WC_Product      $product         The product to get the image ids for.
	 * @param OpenGraph_Image $opengraph_image The OpenGraph image to use.
	 */
	protected function set_image_ids( $product, $opengraph_image ) {
		$img_ids = method_exists( $product, 'get_gallery_image_ids' ) ?
			$product->get_gallery_image_ids() : $product->get_gallery_attachment_ids();

		if ( ! is_array( $img_ids ) || empty( $img_ids ) ) {
			return;
		}

		foreach ( $img_ids as $img_id ) {
			$opengraph_image->add_image_by_id( $img_id );
		}
	}
}
