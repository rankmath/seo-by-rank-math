<?php
/**
 * The Schema Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Traits\Hooker;
use RankMath\Helper;
defined( 'ABSPATH' ) || exit;

/**
 * Schema class.
 */
class Schema {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			new Admin();
		}
		$this->action( 'wp', 'integrations' );
		$this->filter( 'rank_math/elementor/dark_styles', 'add_dark_style' );
		$this->filter( 'oembed_response_data', 'set_oembed_data', 10, 2 );
		new Blocks();
		new Snippet_Shortcode();
	}

	/**
	 * Callback function to pass to the oEmbed's response data that will enable
	 * support for using the image and title set by the WordPress SEO plugin's fields. This
	 * address the concern where some social channels/subscribed use oEmebed data over Open Graph data
	 * if both are present.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/oembed_response_data/ for hook info.
	 *
	 * @param array   $data The oEmbed data.
	 * @param WP_Post $post The current Post object.
	 *
	 * @return array An array of oEmbed data with modified values where appropriate.
	 */
	public function set_oembed_data( $data, $post ) {
		$title = get_post_meta( $post->ID, 'rank_math_title', true );
		if ( ! empty( $title ) ) {
			$data['title'] = Helper::replace_vars( $title, $post );
		}
		return $data;
	}


	/**
	 * Add dark style
	 *
	 * @param array $styles The dark mode styles.
	 */
	public function add_dark_style( $styles = [] ) {
		$styles['rank-math-schema-dark'] = rank_math()->plugin_url() . 'includes/modules/schema/assets/css/schema-dark.css';

		return $styles;
	}

	/**
	 * Initialize integrations.
	 */
	public function integrations() {
		$type = get_query_var( 'sitemap' );
		if ( ! empty( $type ) ) {
			return;
		}

		( new JsonLD() )->setup();
	}
}
