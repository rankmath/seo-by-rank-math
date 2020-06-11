<?php
/**
 * Defaults by plugins.
 *
 * @since      1.0.40
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Defaults class.
 */
class Defaults {

	use Hooker;

	/**
	 * Constructor method.
	 */
	public function __construct() {
		$this->filter( 'rank_math/excluded_taxonomies', 'exclude_taxonomies' );
		$this->filter( 'rank_math/excluded_post_types', 'excluded_post_types' );
	}

	/**
	 * Exclude taxonomies.
	 *
	 * @param array $taxonomies Excluded taxonomies.
	 *
	 * @return array
	 */
	public function exclude_taxonomies( $taxonomies ) {
		if ( ! current_theme_supports( 'post-formats' ) ) {
			unset( $taxonomies['post_format'] );
		}
		unset( $taxonomies['product_shipping_class'] );

		return $taxonomies;
	}

	/**
	 * Exclude post_types.
	 *
	 * @param array $post_types Excluded post_types.
	 *
	 * @return array
	 */
	public function excluded_post_types( $post_types ) {
		if ( isset( $post_types['elementor_library'] ) ) {
			unset( $post_types['elementor_library'] );
		}

		return $post_types;
	}
}
