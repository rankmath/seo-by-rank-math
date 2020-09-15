<?php
/**
 * The Block Base
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Block class.
 */
class Block {

	/**
	 * [clean_text description]
	 *
	 * @param [type] $text [description].
	 *
	 * @return string
	 */
	protected function clean_text( $text ) {
		return strip_tags( $text, '<h1><h2><h3><h4><h5><h6><br><ol><ul><li><a><p><b><strong><i><em>' );
	}

	/**
	 * [get_image description]
	 *
	 * @param array  $attrs [description].
	 * @param string $size  [description].
	 * @param string $class [description].
	 *
	 * @return [type]           [description]
	 */
	protected function get_image( $attrs, $size = 'thumbnail', $class = 'class=alignright' ) {
		if ( ! isset( $attrs['imageID'] ) ) {
			return '';
		}

		$image_id = absint( $attrs['imageID'] );
		if ( ! ( $image_id > 0 ) ) {
			return '';
		}

		$html = wp_get_attachment_image( $image_id, $size, false, $class );

		return $html ? $html : wp_get_attachment_image( $image_id, 'full', false, $class );
	}

	/**
	 * Get styles
	 *
	 * @param array $attributes Array of attributes.
	 *
	 * @return string
	 */
	protected function get_styles( $attributes ) {
		$out = [];

		if ( ! empty( $attributes['textAlign'] ) && 'left' !== $attributes['textAlign'] ) {
			$out[] = 'text-align:' . $attributes['textAlign'];
		}

		return empty( $out ) ? '' : ' style="' . join( ';', $out ) . '"';
	}

	/**
	 * Get list style
	 *
	 * @param string $style Style.
	 *
	 * @return string
	 */
	protected function get_list_style( $style ) {
		if ( 'numbered' === $style ) {
			return 'ol';
		}

		if ( 'unordered' === $style ) {
			return 'ul';
		}

		return 'div';
	}

	/**
	 * Get list item style
	 *
	 * @param string $style Style.
	 *
	 * @return string
	 */
	protected function get_list_item_style( $style ) {
		if ( 'numbered' === $style || 'unordered' === $style ) {
			return 'li';
		}

		return 'div';
	}
}
