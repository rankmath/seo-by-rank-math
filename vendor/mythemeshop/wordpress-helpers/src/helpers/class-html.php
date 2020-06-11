<?php
/**
 * The HTML helpers.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Helpers
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Helpers;

/**
 * HTML class.
 */
class HTML {

	/**
	 * Extract attributes from a html tag.
	 *
	 * @param string $elem Extract attributes from tag.
	 *
	 * @return array
	 */
	public static function extract_attributes( $elem ) {
		$regex = '#([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*")#';
		preg_match_all( $regex, $elem, $attributes, PREG_SET_ORDER );

		$new = [];
		foreach ( $attributes as $attribute ) {
			$new[ $attribute[1] ] = substr( $attribute[2], 1, -1 );
		}

		return $new;
	}

	/**
	 * Generate html attribute string for array.
	 *
	 * @param array  $attributes Contains key/value pair to generate a string.
	 * @param string $prefix     If you want to append a prefic before every key.
	 *
	 * @return string
	 */
	public static function attributes_to_string( $attributes = [], $prefix = '' ) {

		// Early Bail!
		if ( empty( $attributes ) ) {
			return false;
		}

		$out = '';
		foreach ( $attributes as $key => $value ) {
			if ( true === $value || false === $value ) {
				$value = $value ? 'true' : 'false';
			}

			$out .= ' ' . esc_html( $prefix . $key );
			$out .= empty( $value ) ? '' : sprintf( '="%s"', esc_attr( $value ) );
		}

		return $out;
	}
}
