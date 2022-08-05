<?php
/**
 * Add the OpenGraph tags to the header.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\OpenGraph
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\OpenGraph;

use RankMath\Post;
use RankMath\Term;
use RankMath\User;
use RankMath\Helper;
use RankMath\Paper\Paper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * OpenGraph class.
 */
class OpenGraph {

	use Hooker;

	/**
	 * Holds network slug.
	 *
	 * @var string
	 */
	public $network = '';

	/**
	 * Hold meta_key prefix.
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * Schema type.
	 *
	 * @var bool|string
	 */
	protected $schema = false;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/head', 'output_tags', 30 );
	}

	/**
	 * Main OpenGraph output.
	 */
	public function output_tags() {
		wp_reset_query();

		/**
		 * Hook to add all OpenGraph metadata
		 *
		 * The dynamic part of the hook name. $this->network, is the network slug.
		 *
		 * @param OpenGraph $this The current opengraph network object.
		 */
		$this->do_action( "opengraph/{$this->network}", $this );
	}

	/**
	 * Get title
	 *
	 * @return bool|string
	 */
	public function get_title() {
		$title = $this->_title();
		if ( $title && Helper::get_settings( 'titles.capitalize_titles' ) ) {
			$title = ucwords( $title );
		}

		return $title ? $title : Paper::get()->get_title();
	}

	/**
	 * Get title.
	 *
	 * @return string
	 */
	private function _title() {
		$key = $this->prefix . '_title';

		if ( Post::is_simple_page() ) {
			return Post::get_meta( $key, Post::get_page_id() );
		}

		if ( is_front_page() ) {
			return Helper::get_settings( 'titles.homepage_facebook_title' );
		}

		if ( is_category() || is_tax() || is_tag() ) {
			return Term::get_meta( $key );
		}

		return is_author() ? User::get_meta( $key ) : false;
	}

	/**
	 * Get description.
	 *
	 * @return bool|string
	 */
	public function get_description() {
		$desc = false;
		$key  = $this->prefix . '_description';

		if ( Post::is_simple_page() ) {
			$desc = Post::get_meta( $key, Post::get_page_id() );
			$desc = '' !== $desc ? $desc : $this->fallback_description( 'get_the_excerpt' );
		} elseif ( is_front_page() ) {
			$desc = Helper::get_settings( 'titles.homepage_facebook_description' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$desc = Term::get_meta( $key );
			$desc = '' !== $desc ? $desc : $this->fallback_description( 'term_description' );
		} elseif ( is_author() ) {
			$desc = User::get_meta( $key );
		}

		return $desc ? $desc : $this->fallback_description();
	}

	/**
	 * Get a fallback description.
	 *
	 * @param string $callback Function name to call.
	 *
	 * @return string
	 */
	protected function fallback_description( $callback = false ) {
		$desc = Paper::get()->get_description();
		if ( '' === $desc && $callback ) {
			$desc = $callback();
		}

		return $desc;
	}

	/**
	 * Internal function to output social meta tags.
	 *
	 * @param string $property Property attribute value.
	 * @param string $content  Content attribute value.
	 *
	 * @return bool
	 */
	public function tag( $property, $content ) {
		$og_property = str_replace( ':', '_', $property );
		/**
		 * Allow developers to change the content of specific social meta tags.
		 *
		 * The dynamic part of the hook name. $this->network, is the network slug
		 * and $og_property, is the property which we are outputting.
		 *
		 * @param string $content The content of the property.
		 */
		$content = $this->do_filter( "opengraph/{$this->network}/$og_property", $content );
		if ( empty( $content ) || ! is_scalar( $content ) ) {
			return false;
		}

		$tag = 'facebook' === $this->network ? 'property' : 'name';
		$escaped_value = esc_attr( $content );
		if ( false !== filter_var( $content, FILTER_VALIDATE_URL ) ) {
			$escaped_value = esc_url_raw( $content );
		}
		printf( '<meta %1$s="%2$s" content="%3$s" />' . "\n", $tag, esc_attr( $property ), $escaped_value );

		return true;
	}

	/**
	 * Get Overlay Image URL
	 *
	 * @param string $network The social network.
	 *
	 * @return string
	 */
	public function get_overlay_image( $network = 'facebook' ) {
		if ( is_singular() ) {
			return Helper::get_post_meta( "{$network}_enable_image_overlay" ) ? Helper::get_post_meta( "{$network}_image_overlay", '', 'play' ) : '';
		}
		if ( is_category() || is_tag() || is_tax() ) {
			return Helper::get_term_meta( "{$network}_enable_image_overlay" ) ? Helper::get_term_meta( "{$network}_image_overlay", 0, '', 'play' ) : '';
		}

		if ( is_author() ) {
			return Helper::get_user_meta( "{$network}_enable_image_overlay" ) ? Helper::get_user_meta( "{$network}_image_overlay", 0, 'play' ) : '';
		}

		return '';
	}
}
