<?php
/**
 * The Shop paper.
 *
 * @since      1.0.22
 * @package    RankMath
 * @subpackage RankMath\Paper
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Paper;

use RankMath\Post;
use RankMath\Helper;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Shop.
 */
class Shop extends Singular {

	/**
	 * Retrieves the WooCommerce Shop SEO title.
	 *
	 * @return string
	 */
	public function title() {
		$post  = Post::get( Post::get_shop_page_id() );
		$title = $this->get_post_title( $post->get_object() );

		// Early Bail!!!
		if ( Str::is_non_empty( $title ) ) {
			return $title;
		}

		return Paper::get_from_options( 'pt_product_archive_title', [], '%pt_plural% Archive %page% %sep% %sitename%' );
	}

	/**
	 * Retrieves the WooCommerce Shop SEO description.
	 *
	 * @return string
	 */
	public function description() {
		$post = Post::get( Post::get_shop_page_id() );
		$desc = $this->get_post_description( $post->get_object() );

		return '' !== $desc ? $desc : Paper::get_from_options( 'pt_product_archive_description', [], '%pt_plural% Archive %page% %sep% %sitename%' );
	}

	/**
	 * Retrieves the WooCommerce Shop robots.
	 *
	 * @return string
	 */
	public function robots() {
		$post = Post::get( Post::get_shop_page_id() );
		return $this->get_post_robots( $post->get_object() );
	}

	/**
	 * Retrieves the WooCommerce Shop advanced robots.
	 *
	 * @return array
	 */
	public function advanced_robots() {
		$post   = Post::get( Post::get_shop_page_id() );
		$object = $post->get_object();
		return $this->get_post_advanced_robots( $object );
	}

	/**
	 * Retrieves meta keywords.
	 *
	 * @return string The focus keywords.
	 */
	public function keywords() {
		return Post::get_meta( 'focus_keyword', Post::get_shop_page_id() );
	}
}
