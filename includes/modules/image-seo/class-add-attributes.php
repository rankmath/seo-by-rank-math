<?php
/**
 * The class handles adding of attributes to links and images to content.
 *
 * @since      1.0.15
 * @package    RankMath
 * @subpackage RankMath\Frontend
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Image_Seo;

use stdClass;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Add Attributes class.
 */
class Add_Attributes {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'wp', 'add_attributes', 9999 );
	}

	/**
	 * Add nofollow, target, title and alt attributes to link and images.
	 */
	public function add_attributes() {
		// Add image title and alt.
		$this->is_alt   = Helper::get_settings( 'general.add_img_alt' ) && Helper::get_settings( 'general.img_alt_format' ) ? trim( Helper::get_settings( 'general.img_alt_format' ) ) : false;
		$this->is_title = Helper::get_settings( 'general.add_img_title' ) && Helper::get_settings( 'general.img_title_format' ) ? trim( Helper::get_settings( 'general.img_title_format' ) ) : false;

		if ( $this->is_alt || $this->is_title ) {
			$this->filter( 'the_content', 'add_img_attributes', 11 );
			$this->filter( 'post_thumbnail_html', 'add_img_attributes', 11 );
			$this->filter( 'woocommerce_single_product_image_thumbnail_html', 'add_img_attributes', 11 );
		}
	}

	/**
	 * Add 'title' and 'alt' attribute to image.
	 *
	 * @param  string $content Post content.
	 * @return string
	 */
	public function add_img_attributes( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		$stripped_content = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $content );
		preg_match_all( '/<img ([^>]+)\/?>/iU', $stripped_content, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) ) {
			return $content;
		}

		$post = $this->get_post();
		foreach ( $matches as $image ) {
			$is_dirty = false;
			$attrs    = HTML::extract_attributes( $image[0] );

			if ( ! isset( $attrs['src'] ) ) {
				continue;
			}

			$post->filename = $attrs['src'];

			// Lazy load support.
			if ( ! empty( $attrs['data-src'] ) ) {
				$post->filename = $attrs['data-src'];
			} elseif ( ! empty( $attrs['data-layzr'] ) ) {
				$post->filename = $attrs['data-layzr'];
			}

			// Pass attributes so they can be used later.
			$post->alttext   = isset( $attrs['alt'] ) ? $attrs['alt'] : '';
			$post->titletext = isset( $attrs['title'] ) ? $attrs['title'] : '';

			$this->set_image_attribute( $attrs, 'alt', $this->is_alt, $is_dirty, $post );
			$this->set_image_attribute( $attrs, 'title', $this->is_title, $is_dirty, $post );

			if ( $is_dirty ) {
				$new     = '<img' . HTML::attributes_to_string( $attrs ) . '>';
				$content = str_replace( $image[0], $new, $content );
			}
		}

		return $content;
	}

	/**
	 * Get post object.
	 *
	 * @return object
	 */
	private function get_post() {
		$post = \get_post();
		if ( empty( $post ) ) {
			$post = new stdClass();
		}

		return $post;
	}

	/**
	 * Set image attribute after checking condition.
	 *
	 * @param array   $attrs     Array which hold rel attribute.
	 * @param string  $attribute Attribute to set.
	 * @param boolean $condition Condition to check.
	 * @param boolean $is_dirty  Is dirty variable.
	 * @param object  $post      Post Object.
	 */
	private function set_image_attribute( &$attrs, $attribute, $condition, &$is_dirty, $post ) {
		if ( $condition && empty( $attrs[ $attribute ] ) ) {
			$is_dirty            = true;
			$attrs[ $attribute ] = trim( Helper::replace_vars( $condition, $post ) );
		}
	}
}
