<?php
/**
 * This class handles the Slack sharing functionality.
 *
 * @since      1.0.76
 * @package    RankMath
 * @subpackage RankMath\OpenGraph
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\OpenGraph;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Slack class.
 */
class Slack extends OpenGraph {

	/**
	 * Network slug.
	 *
	 * @var string
	 */
	public $network = 'slack';

	/**
	 * Enhanced data tag counter.
	 *
	 * @var int
	 */
	private static $data_tag_count = 0;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/opengraph/slack', 'enhanced_data', 20 );

		parent::__construct();
	}

	/**
	 * Outputs the Slack enhanced data.
	 */
	public function enhanced_data() {
		$data = $this->get_enhanced_data();

		foreach ( $data as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			$this->enhanced_data_tag( $key, $value );
		}
	}

	/**
	 * Get Slack enhanced data tags for a key/value pair.
	 *
	 * @param string $key   The key.
	 * @param string $value The value.
	 *
	 * @return void
	 */
	public function enhanced_data_tag( $key, $value ) {
		self::$data_tag_count++;

		$this->tag( sprintf( 'twitter:label%d', self::$data_tag_count ), $key );
		$this->tag( sprintf( 'twitter:data%d', self::$data_tag_count ), $value );
	}

	/**
	 * Get Slack data for the current page.
	 *
	 * @return array
	 */
	private function get_enhanced_data() {
		$data = [];

		if ( $this->is_product() ) {
			$data = $this->get_product_data();
		} elseif ( $this->is_page() ) {
			$data = $this->get_page_data();
		} elseif ( $this->is_singular() ) {
			$data = $this->get_post_data();
		} elseif ( $this->is_term() ) {
			$data = $this->get_term_data();
		} elseif ( $this->is_author() ) {
			$data = $this->get_author_data();
		}

		$data = $this->do_filter( 'opengraph/slack_enhanced_data', $data );
		return $data;
	}

	/**
	 * Check if current page is a product.
	 *
	 * @return bool
	 */
	private function is_product() {
		return $this->is_woo_product() || $this->is_edd_product();
	}

	/**
	 * Get product data.
	 *
	 * @return array
	 */
	private function get_product_data() {
		if ( $this->is_woo_product() ) {
			return $this->get_woo_product_data();
		}

		return $this->get_edd_product_data();
	}

	/**
	 * Check if current page is a WooCommerce product.
	 *
	 * @return bool
	 */
	private function is_woo_product() {
		return Helper::get_settings( 'titles.pt_product_slack_enhanced_sharing' ) && is_singular( 'product' ) && function_exists( 'wc_get_product' );
	}

	/**
	 * Check if current page is a EDD download.
	 *
	 * @return bool
	 */
	private function is_edd_product() {
		return Helper::get_settings( 'titles.pt_download_slack_enhanced_sharing' ) && is_singular( 'download' ) && class_exists( 'Easy_Digital_Downloads' );
	}

	/**
	 * Check if current page is a post or a CPT.
	 *
	 * @return bool
	 */
	private function is_singular() {
		return is_singular() && Helper::get_settings( sprintf( 'titles.pt_%s_slack_enhanced_sharing', get_post_type() ) );
	}

	/**
	 * Check if current page is a page.
	 *
	 * @return bool
	 */
	private function is_page() {
		return Helper::get_settings( 'titles.pt_page_slack_enhanced_sharing' ) && is_singular( 'page' ) && ! is_front_page();
	}

	/**
	 * Check if current page is a term archive.
	 *
	 * @return bool
	 */
	private function is_term() {
		if ( is_category() || is_tag() || is_tax() ) {
			global $wp_query;
			return Helper::get_settings( sprintf( 'titles.tax_%s_slack_enhanced_sharing', $wp_query->get_queried_object()->taxonomy ) );
		}

		return false;
	}

	/**
	 * Check if current page is an author archive.
	 *
	 * @return bool
	 */
	private function is_author() {
		return Helper::get_settings( 'titles.author_slack_enhanced_sharing' ) && is_author();
	}

	/**
	 * Get Slack data for WooCommerce product.
	 *
	 * @return array
	 */
	private function get_woo_product_data() {
		global $post;

		$data    = [];
		$product = \wc_get_product( $post );

		$data[ __( 'Price', 'rank-math' ) ]        = $this->get_product_price( $product );
		$data[ __( 'Availability', 'rank-math' ) ] = $this->get_product_availability( $product );

		return $data;
	}

	/**
	 * Get Slack data for EDD download.
	 *
	 * @return array
	 */
	private function get_edd_product_data() {
		global $post;

		$data                               = [];
		$data[ __( 'Price', 'rank-math' ) ] = wp_strip_all_tags( \edd_price( $post->ID, false ) );

		return $data;
	}

	/**
	 * Get availability of product.
	 *
	 * @param object $product Product object.
	 *
	 * @return string
	 */
	private function get_product_availability( $product ) {
		$availability_text = $product->get_availability()['availability'];
		if ( ! $availability_text ) {
			$availability_text = __( 'In stock', 'rank-math' );
		}

		return $availability_text;
	}

	/**
	 * Get price of WooCommerce product.
	 * Gets price range for variable products.
	 *
	 * @param object $product Product object.
	 *
	 * @return string
	 */
	private function get_product_price( $product ) {
		$price = wp_strip_all_tags( \wc_price( $product->get_price() ) );
		if ( $product->is_type( 'variable' ) ) {
			$lowest  = \wc_format_decimal( $product->get_variation_price( 'min', false ), \wc_get_price_decimals() );
			$highest = \wc_format_decimal( $product->get_variation_price( 'max', false ), \wc_get_price_decimals() );

			$price = wp_strip_all_tags( \wc_price( $lowest ) . ' - ' . \wc_price( $highest ) );
			if ( $lowest === $highest ) {
				$price = wp_strip_all_tags( \wc_price( $lowest ) );
			}
		}

		return $price;
	}

	/**
	 * Get Slack data for post.
	 *
	 * @return array
	 */
	private function get_post_data() {
		global $post;

		$data = [];

		$data[ __( 'Written by', 'rank-math' ) ]   = get_the_author();
		$data[ __( 'Time to read', 'rank-math' ) ] = $this->calculate_time_to_read( $post );

		return $data;
	}

	/**
	 * Get Slack data for page.
	 *
	 * @return array
	 */
	private function get_page_data() {
		global $post;

		$data                                      = [];
		$data[ __( 'Time to read', 'rank-math' ) ] = $this->calculate_time_to_read( $post );

		return $data;
	}

	/**
	 * Calculate the time to read for the post.
	 *
	 * @param object $post Post object.
	 *
	 * @return string
	 */
	private function calculate_time_to_read( $post ) {

		/**
		 * Filter: 'rank_math/frontend/time_to_read_content' - Change the text to calculate the time to read.
		 */
		$content = $this->do_filter( 'frontend/time_to_read_content', wp_strip_all_tags( $post->post_content ) );

		/**
		 * Filter: 'rank_math/frontend/time_to_read_wpm' - Change the words per minute to calculate the time to read.
		 */
		$words_per_minute = absint( $this->do_filter( 'frontend/time_to_read_wpm', 200 ) );

		$words   = str_word_count( $content );
		$minutes = floor( $words / $words_per_minute );

		if ( $minutes > 0 ) {
			return sprintf(
				/* translators: %d: minutes */
				_n( '%d minute', '%d minutes', $minutes, 'rank-math' ),
				$minutes
			);
		}

		return __( 'Less than a minute', 'rank-math' );
	}

	/**
	 * Get Slack data for term.
	 *
	 * @return array
	 */
	private function get_term_data() {
		global $wp_query;

		$data = [];

		$term = $wp_query->get_queried_object();
		if ( ! $term ) {
			return $data;
		}

		$label            = __( 'Items', 'rank-math' );
		$post_type_object = get_post_type_object( get_post_type() );
		if ( is_object( $post_type_object ) && isset( $post_type_object->labels->name ) ) {
			$label = $post_type_object->labels->name;
		}

		$data[ $label ] = ( ! empty( $term->category_count ) ? $term->category_count : $term->count );

		return $data;
	}

	/**
	 * Get Slack data for author.
	 *
	 * @return array
	 */
	private function get_author_data() {
		global $wp_query;

		$data = [];

		$author = $wp_query->get_queried_object();
		if ( ! $author ) {
			return $data;
		}

		$data[ __( 'Name', 'rank-math' ) ]  = $author->display_name;
		$data[ __( 'Posts', 'rank-math' ) ] = count_user_posts( $author->ID );

		return $data;
	}

}
