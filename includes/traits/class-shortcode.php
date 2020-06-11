<?php
/**
 * The Shortcode trait.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Traits
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Shortcode
 */
trait Shortcode {

	/**
	 * Adds a new shortcode
	 *
	 * @param string   $tag  Shortcode tag to be searched in post content.
	 * @param callable $func The callback function to run when the shortcode is found.
	 */
	protected function add_shortcode( $tag, $func ) {
		\add_shortcode( $tag, [ $this, $func ] );
	}

	/**
	 * Removes hook for shortcode.
	 *
	 * @param string $tag Shortcode tag to remove hook for.
	 */
	protected function remove_shortcode( $tag ) {
		\remove_shortcode( $tag );
	}
}
