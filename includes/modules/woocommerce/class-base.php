<?php
/**
 * The Module Base Class
 *
 * ALl the classes inherit from this class
 *
 * @since      1.0.238
 * @package    RankMath
 * @subpackage RankMath\WooCommerce
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\WooCommerce;

use RankMath\Helper;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Base class.
 *
 * This is the parent class for all WooCommerce-related functionality
 * in the RankMath plugin. It contains common methods and utilities
 * that are shared across the WooCommerce module.
 */
class Base {

	/**
	 * Holds the product object.
	 *
	 * @var WC_Product
	 */
	private $product = null;

	/**
	 * Returns the product object when the current page is the product page.
	 *
	 * @return null|WC_Product
	 */
	public function get_product() {
		if ( ! is_null( $this->product ) ) {
			return $this->product;
		}

		$product_id    = Param::get( 'post', get_queried_object_id(), FILTER_VALIDATE_INT );
		$this->product = (
			! function_exists( 'wc_get_product' ) ||
			! $product_id ||
			(
				! is_admin() &&
				! is_singular( 'product' )
			)
		) ? null : wc_get_product( $product_id );

		return $this->product;
	}

	/**
	 * Returns the array of brand taxonomy.
	 *
	 * @param int $product_id The id to get the product brands for.
	 *
	 * @return bool|array
	 */
	public static function get_brands( $product_id ) {
		$brand    = '';
		$taxonomy = Helper::get_settings( 'general.product_brand' );
		if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
			$brands = get_the_terms( $product_id, $taxonomy );
			$brand  = is_wp_error( $brands ) || empty( $brands[0] ) ? '' : $brands[0]->name;
		}

		return apply_filters( 'rank_math/woocommerce/product_brand', $brand );
	}
}
