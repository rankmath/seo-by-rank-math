<?php
/**
 * WooCommerce product sitemaps adjustments.
 *
 * @since      1.0.32
 * @package    RankMath
 * @subpackage RankMath\WooCommerce
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\WooCommerce;

use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Helpers\Attachment;

defined( 'ABSPATH' ) || exit;

/**
 * WC Sitemap class.
 */
class Sitemap {

	/**
	 * Register hooks.
	 */
	public function sitemap() {
		$this->filter( 'rank_math/sitemap/exclude_post_type', 'sitemap_exclude_post_type', 10, 2 );
		$this->filter( 'rank_math/sitemap/post_type_archive_link', 'sitemap_taxonomies', 10, 2 );
		$this->filter( 'rank_math/sitemap/post_type_archive_link', 'sitemap_post_type_archive_link', 10, 2 );
		$this->filter( 'rank_math/sitemap/urlimages', 'add_product_images_to_xml_sitemap', 10, 2 );
	}

	/**
	 * Make sure product variations and shop coupons are not included in the XML sitemap.
	 *
	 * @param bool   $bool      Whether or not to include this post type in the XML sitemap.
	 * @param string $post_type The post type of the post.
	 *
	 * @return bool
	 */
	public function sitemap_exclude_post_type( $bool, $post_type ) {
		if ( in_array( $post_type, [ 'product_variation', 'shop_coupon' ], true ) ) {
			return true;
		}

		return $bool;
	}

	/**
	 * Make sure product attribute taxonomies are not included in the XML sitemap.
	 *
	 * @param bool   $bool     Whether or not to include this post type in the XML sitemap.
	 * @param string $taxonomy The taxonomy to check against.
	 *
	 * @return bool
	 */
	public function sitemap_taxonomies( $bool, $taxonomy ) {
		if ( in_array( $taxonomy, [ 'product_type', 'product_shipping_class', 'shop_order_status' ], true ) ) {
			return true;
		}

		if ( Str::starts_with( 'pa_', $taxonomy ) ) {
			return true;
		}

		return $bool;
	}

	/**
	 * Filters the archive link on the product sitemap.
	 *
	 * @param string $link      The archive link.
	 * @param string $post_type The post type to check against.
	 *
	 * @return bool
	 */
	public function sitemap_post_type_archive_link( $link, $post_type ) {
		if ( 'product' !== $post_type || ! function_exists( 'wc_get_page_id' ) ) {
			return $link;
		}

		$shop_page_id = wc_get_page_id( 'shop' );
		$home_page_id = (int) get_option( 'page_on_front' );
		if ( 1 > $shop_page_id || 'publish' !== get_post_status( $shop_page_id ) || $home_page_id === $shop_page_id ) {
			return false;
		}

		$robots = Helper::get_post_meta( 'robots', $shop_page_id );
		if ( ! empty( $robots ) && is_array( $robots ) && in_array( 'noindex', $robots, true ) ) {
			return false;
		}

		return $link;
	}

	/**
	 * Add the product gallery images to the XML sitemap.
	 *
	 * @param array $images  The array of images for the post.
	 * @param int   $post_id The ID of the post object.
	 *
	 * @return array
	 */
	public function add_product_images_to_xml_sitemap( $images, $post_id ) {
		if ( metadata_exists( 'post', $post_id, '_product_image_gallery' ) ) {
			$product_gallery = get_post_meta( $post_id, '_product_image_gallery', true );
			$attachments     = array_filter( explode( ',', $product_gallery ) );
			foreach ( $attachments as $attachment_id ) {
				$image_src = wp_get_attachment_image_src( $attachment_id, 'full' );
				if ( empty( $image_src ) ) {
					continue;
				}

				$image = [
					'src'   => $this->do_filter( 'sitemap/xml_img_src', $image_src[0], $post_id ),
					'title' => get_the_title( $attachment_id ),
					'alt'   => Attachment::get_alt_tag( $attachment_id ),
				];
				$images[]  = $image;

				unset( $image, $image_src );
			}
		}

		return $images;
	}
}
