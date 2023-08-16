<?php
/**
 * The WooCommerce module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\WooCommerce
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\WooCommerce;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce class.
 */
class WooCommerce extends WC_Vars {

	use Hooker;

	/**
	 * Holds the product object.
	 *
	 * @var WC_Product
	 */
	private $product = null;

	/**
	 * Remove product base.
	 *
	 * @var bool
	 */
	private $remove_product_base;

	/**
	 * Remove category base.
	 *
	 * @var bool
	 */
	private $remove_category_base;

	/**
	 * Remove parent slugs.
	 *
	 * @var bool
	 */
	private $remove_parent_slugs;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->remove_product_base  = Helper::get_settings( 'general.wc_remove_product_base' );
		$this->remove_category_base = Helper::get_settings( 'general.wc_remove_category_base' );
		$this->remove_parent_slugs  = Helper::get_settings( 'general.wc_remove_category_parent_slugs' );

		if ( is_admin() ) {
			new Admin();
		}

		$this->integrations();

		if ( $this->remove_product_base || $this->remove_category_base ) {
			new Product_Redirection();
		}

		new Permalink_Watcher();
		parent::__construct();
	}

	/**
	 * Initialize integrations.
	 */
	public function integrations() {
		if ( is_admin() ) {
			return;
		}

		// Permalink Manager.
		if ( $this->remove_product_base ) {
			$this->action( 'request', 'request', 11 );
		}

		if ( Helper::get_settings( 'general.wc_remove_generator' ) ) {
			remove_action( 'get_the_generator_html', 'wc_generator_tag', 10 );
			remove_action( 'get_the_generator_xhtml', 'wc_generator_tag', 10 );
		}

		$this->sitemap();
		$this->opengraph();
		$this->filter( 'rank_math/frontend/description', 'metadesc' );
		$this->filter( 'rank_math/frontend/robots', 'robots' );
	}

	/**
	 * Replace request if product was found.
	 *
	 * @param array $request Current request.
	 *
	 * @return array
	 */
	public function request( $request ) {
		global $wp, $wpdb;
		$url = $wp->request;

		if ( empty( $url ) ) {
			return $request;
		}

		$replace = [];
		$url     = explode( '/', $url );
		$slug    = array_pop( $url );

		if ( 'feed' === $slug ) {
			$replace['feed'] = $slug;
			$slug            = array_pop( $url );
		}

		if ( 'amp' === $slug ) {
			$replace['amp'] = $slug;
			$slug           = array_pop( $url );
		}

		if ( 0 === strpos( $slug, 'comment-page-' ) ) {
			$replace['cpage'] = substr( $slug, strlen( 'comment-page-' ) );
			$slug             = array_pop( $url );
		}

		if ( 0 === strpos( $slug, 'schema-preview' ) ) {
			$replace['schema-preview'] = '';
			$slug                      = array_pop( $url );
		}

		$query = "SELECT COUNT(ID) as count_id FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s";
		$num   = intval( $wpdb->get_var( $wpdb->prepare( $query, [ $slug, 'product' ] ) ) ); // phpcs:ignore
		if ( $num > 0 ) {
			$replace['page']      = '';
			$replace['name']      = $slug;
			$replace['product']   = $slug;
			$replace['post_type'] = 'product';

			return $replace;
		}

		return $request;
	}

	/**
	 * Change robots for WooCommerce pages according to the settings.
	 *
	 * @param array $robots Array of robots to sanitize.
	 *
	 * @return array Modified robots.
	 */
	public function robots( $robots ) {

		// Early Bail if current page is Woocommerce OnePage Checkout.
		if ( function_exists( 'is_wcopc_checkout' ) && is_wcopc_checkout() ) {
			return $robots;
		}

		if ( is_cart() || is_checkout() || is_account_page() ) {
			remove_action( 'wp_head', 'wc_page_noindex' );
			return [
				'index'  => 'noindex',
				'follow' => 'follow',
			];
		}

		return $robots;
	}

	/**
	 * Returns the meta description. Checks which value should be used when the given meta description is empty.
	 *
	 * It will use the short_description if that one is set. Otherwise it will use the full
	 * product description limited to 156 characters. If everything is empty, it will return an empty string.
	 *
	 * @param string $metadesc The meta description to check.
	 *
	 * @return string The meta description.
	 */
	public function metadesc( $metadesc ) {
		if ( '' !== $metadesc || ! is_singular( 'product' ) ) {
			return $metadesc;
		}

		$product = $this->get_product_by_id( get_the_id() );
		if ( ! is_object( $product ) ) {
			return '';
		}

		$short_desc = $this->get_short_description( $product );
		if ( '' !== $short_desc ) {
			return $short_desc;
		}

		$long_desc = $this->get_long_description( $product );
		return '' !== $long_desc ? Str::truncate( $long_desc, 156 ) : '';
	}

	/**
	 * Returns the product for given product_id.
	 *
	 * @param int $product_id The id to get the product for.
	 *
	 * @return null|WC_Product
	 */
	protected function get_product_by_id( $product_id ) {
		if ( function_exists( 'wc_get_product' ) ) {
			return wc_get_product( $product_id );
		}

		if ( function_exists( 'get_product' ) ) {
			return get_product( $product_id );
		}

		return null;
	}

	/**
	 * Checks if product class has a description method.
	 * Otherwise it returns the value of the post_content.
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return string
	 */
	protected function get_long_description( $product ) {
		if ( method_exists( $product, 'get_description' ) ) {
			return $product->get_description();
		}

		return $product->post->post_content;
	}

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
