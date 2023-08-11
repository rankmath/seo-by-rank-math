<?php
/**
 * The class handles changes in image tag attributes.
 *
 * @since      1.0.15
 * @package    RankMath
 * @subpackage RankMath\Image_Seo
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
	 * Stores the image alt format if it is set.
	 *
	 * @var string|false
	 */
	public $is_alt;

	/**
	 * Stores the image title format if it is set.
	 *
	 * @var string|false
	 */
	public $is_title;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'wp', 'add_attributes', 9999 );
		$this->action( 'rest_api_init', 'add_attributes' );
	}

	/**
	 * Add nofollow, target, title and alt attributes to images.
	 */
	public function add_attributes() {
		// Add image title and alt.
		$this->is_alt   = Helper::get_settings( 'general.add_img_alt' ) && Helper::get_settings( 'general.img_alt_format' ) ? trim( Helper::get_settings( 'general.img_alt_format' ) ) : false;
		$this->is_title = Helper::get_settings( 'general.add_img_title' ) && Helper::get_settings( 'general.img_title_format' ) ? trim( Helper::get_settings( 'general.img_title_format' ) ) : false;

		if ( $this->is_alt || $this->is_title ) {
			$this->filter( 'the_content', 'add_img_attributes', 11 );
			$this->filter( 'post_thumbnail_html', 'add_img_attributes', 11, 2 );
			$this->filter( 'woocommerce_single_product_image_thumbnail_html', 'add_img_attributes', 11 );
		}
	}

	/**
	 * Add 'title' and 'alt' attribute to image.
	 *
	 * @param string   $content Post content.
	 * @param null|int $post_id The current post ID.
	 * @return string
	 */
	public function add_img_attributes( $content, $post_id = null ) {
		if ( empty( $content ) ) {
			return $content;
		}

		$stripped_content = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $content );
		preg_match_all( '/<img ([^>]+)\/?>/iU', $stripped_content, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) ) {
			return $content;
		}

		$post = $this->get_post( $post_id );
		foreach ( $matches as $image ) {
			$is_dirty = false;
			$attrs    = HTML::extract_attributes( $image[0] );

			if ( ! isset( $attrs['src'] ) && ! isset( $attrs['data-ct-lazy'] ) ) {
				continue;
			}

			$post->filename = isset( $attrs['data-ct-lazy'] ) ? $attrs['data-ct-lazy'] : $attrs['src'];

			// Lazy load support.
			if ( ! empty( $attrs['data-src'] ) ) {
				$post->filename = $attrs['data-src'];
			} elseif ( ! empty( $attrs['data-layzr'] ) ) {
				$post->filename = $attrs['data-layzr'];
			} elseif ( ! empty( $attrs['nitro-lazy-srcset'] ) ) {
				$post->filename = $attrs['nitro-lazy-srcset'];
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
	 * @param null|int $post_id The post ID.
	 * @return object
	 */
	private function get_post( $post_id = null) {
		$post = \get_post( $post_id );
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
