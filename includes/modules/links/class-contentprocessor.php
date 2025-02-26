<?php
/**
 * The Content Processor.
 *
 * Extract and save links from the content of a given post.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Links
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\Links;

use RankMath\Helpers\Str;
use RankMath\Helper;
use RankMath\Sitemap\Classifier;

defined( 'ABSPATH' ) || exit;

/**
 * ContentProcessor class.
 */
class ContentProcessor {

	/**
	 * Link storage.
	 *
	 * @var Storage
	 */
	public $storage;

	/**
	 * Link classifier.
	 *
	 * @var Classifier
	 */
	protected $classifier;

	/**
	 * The Constructor
	 */
	public function __construct() {
		$this->storage    = new Storage();
		$this->classifier = new Classifier( home_url() );
	}

	/**
	 * Process the content.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $content The content.
	 */
	public function process( $post_id, $content ) {
		$links  = $this->extract( $content );
		$counts = [
			'internal_link_count' => 0,
			'external_link_count' => 0,
		];

		$new_links      = [];
		$post_permalink = $this->normalize_link( get_permalink( $post_id ) );
		foreach ( $links as $link ) {
			$normalized_link = $this->normalize_link( $link );
			if ( $post_permalink === $normalized_link ) {
				continue;
			}

			$this->process_link( $link, $new_links, $counts );
		}

		$old_links = $this->get_stored_internal_links( $post_id );
		$this->storage->cleanup( $post_id );
		$this->storage->save_links( $post_id, $new_links );
		$this->storage->update_link_counts( $post_id, $counts, array_merge( $new_links, $old_links ) );
	}

	/**
	 * Process a link.
	 *
	 * @param string $link      Link to process.
	 * @param array  $new_links Links to add after process.
	 * @param array  $counts    Counts array.
	 */
	private function process_link( $link, &$new_links, &$counts ) {
		$link_type = $this->is_valid_link_type( $link );
		if ( empty( $link_type ) ) {
			return;
		}

		$target_post_id = 0;
		if ( Classifier::TYPE_INTERNAL === $link_type ) {
			$target_post_id = url_to_postid( $link );

			if ( 0 === $target_post_id ) {
				// Maybe a product with altered url!
				$target_post_id = $this->maybe_product_id( $link );
			}
		}
		$counts[ "{$link_type}_link_count" ] += 1;

		$new_links[] = new Link( $link, $target_post_id, $link_type );
	}

	/**
	 * Extract href property values from HTML string.
	 *
	 * @param string $content Content to extract links from.
	 *
	 * @return array The extracted links.
	 */
	public function extract( $content ) {
		$links = [];
		if ( false === Str::contains( 'href', $content ) ) {
			return $links;
		}

		$regexp = '<a\s[^>]*href=("??)([^" >]*?)\\1[^>]*>';

		// Case insensitive & ungreedy modifiers.
		if ( preg_match_all( "/$regexp/iU", $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$links[] = trim( $match[2], "'" );
			}
		}

		return $links;
	}

	/**
	 * Retrieves the stored internal links for the supplied post.
	 *
	 * @param int $post_id The post to fetch links for.
	 *
	 * @return Link[] List of internal links connected to the post.
	 */
	public function get_stored_internal_links( $post_id ) {
		$links = $this->storage->get_links( $post_id );
		return array_filter( $links, [ $this, 'filter_internal_link' ] );
	}

	/**
	 * Filter internal links.
	 *
	 * @param Link $link Link to test.
	 *
	 * @return bool True if internal, false if external.
	 */
	protected function filter_internal_link( Link $link ) {
		return $link->get_type() === Classifier::TYPE_INTERNAL;
	}

	/**
	 * Check if link is valid.
	 *
	 * @param string $link Link to check.
	 *
	 * @return boolean
	 */
	private function is_valid_link_type( $link ) {
		$type = false;
		if ( ! empty( $link ) && '#' !== $link[0] ) {
			$type = $this->classifier->classify( $link );
		}

		if ( Classifier::TYPE_INTERNAL === $type && preg_match( '/\.(jpg|jpeg|png|gif|bmp|pdf|mp3|zip)$/i', $link ) ) {
			$type = false;
		}

		/**
		 * Filter: 'rank_math/links/link_type' - Allow developers to filter the link type.
		 */
		return apply_filters( 'rank_math/links/link_type', $type, $link );
	}

	/**
	 * Normalize a link: remove trailing slash, remove fragment identifier, remove home_url.
	 *
	 * @param string $link The link to normalize.
	 * @return string
	 */
	private function normalize_link( $link ) {
		$normalized = untrailingslashit( str_replace( home_url(), '', explode( '#', $link )[0] ) );
		return $normalized;
	}


	/**
	 * Gets the post id from a modified link.
	 *
	 * @param string $link Link to process.
	 * @return int
	 */
	private function maybe_product_id( $link ) {
		// Early bail if Remove Base option is not enabled.
		if ( ! Helper::get_settings( 'general.wc_remove_product_base' ) ) {
			return 0;
		}

		$product = get_page_by_path( basename( untrailingslashit( $link ) ), OBJECT, [ 'product' ] );
		if ( ! $product ) {
			return 0;
		}
		return $product->ID;
	}
}
