<?php
/**
 * Variable replacement functionality.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Replace_Variables
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\Replace_Variables;

use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Replacer class.
 */
class Replacer {

	/**
	 * Default post data.
	 *
	 * @var array
	 */
	public static $defaults = [
		'ID'            => '',
		'name'          => '',
		'post_author'   => '',
		'post_content'  => '',
		'post_date'     => '',
		'post_excerpt'  => '',
		'post_modified' => '',
		'post_title'    => '',
		'taxonomy'      => '',
		'term_id'       => '',
		'term404'       => '',
		'filename'      => '',
	];

	/**
	 *  Replace `%variables%` with context-dependent value.
	 *
	 * @param string $string  The string containing the %variables%.
	 * @param array  $args    Context object, can be post, taxonomy or term.
	 * @param array  $exclude Excluded variables won't be replaced.
	 *
	 * @return string
	 */
	public function replace( $string, $args = [], $exclude = [] ) {
		$string = wp_strip_all_tags( $string );

		// Bail early.
		if ( ! Str::contains( '%', $string ) ) {
			return $string;
		}

		if ( Str::ends_with( ' %sep%', $string ) ) {
			$string = str_replace( ' %sep%', '', $string );
		}

		$this->pre_replace( $args, $exclude );
		$replacements = $this->set_up_replacements( $string );

		/**
		 * Filter: Allow customizing the replacements.
		 *
		 * @param array $replacements The replacements.
		 * @param array $args The object some of the replacement values might come from,
		 *                    could be a post, taxonomy or term.
		 */
		$replacements = apply_filters( 'rank_math/replacements', $replacements, $this->args );

		// Do the replacements.
		if ( is_array( $replacements ) && [] !== $replacements ) {
			$string = str_replace( array_keys( $replacements ), array_values( $replacements ), $string );
		}

		if ( isset( $replacements['%sep%'] ) && Str::is_non_empty( $replacements['%sep%'] ) ) {
			$q_sep  = preg_quote( $replacements['%sep%'], '`' );
			$string = preg_replace( '`' . $q_sep . '(?:\s*' . $q_sep . ')*`u', $replacements['%sep%'], $string );
		}

		return $string;
	}

	/**
	 * Run prior to replacement.
	 *
	 * @param array $args    Context object, can be post, taxonomy or term.
	 * @param array $exclude Excluded variables won't be replaced.
	 */
	private function pre_replace( $args, $exclude ) {
		// Setup arguments.
		$this->args = (object) wp_parse_args( $args, self::$defaults );
		if ( ! empty( $this->args->post_content ) ) {
			$this->args->post_content = WordPress::strip_shortcodes( $this->args->post_content );
		}
		if ( ! empty( $this->args->post_excerpt ) ) {
			$this->args->post_excerpt = WordPress::strip_shortcodes( $this->args->post_excerpt );
		}

		// Setup exlucusion.
		if ( is_array( $exclude ) ) {
			$this->exclude = $exclude;
		}
	}

	/**
	 * Get the replacements for the variables.
	 *
	 * @param string $string String to parse for variables.
	 *
	 * @return array Retrieved replacements.
	 */
	private function set_up_replacements( $string ) {
		$replacements = [];
		if ( ! preg_match_all( '/%(([a-z0-9_-]+)\(([^)]*)\)|[^\s]+)%/iu', $string, $matches ) ) {
			return $replacements;
		}

		foreach ( $matches[1] as $index => $variable_id ) {
			$value = $this->get_variable_value( $matches, $index, $variable_id );
			if ( false !== $value ) {
				$replacements[ $matches[0][ $index ] ] = $value;
			}

			unset( $variable );
		}

		return $replacements;
	}

	/**
	 * Get variable value.
	 *
	 * @param array  $matches Regex matches found in the string.
	 * @param int    $index   Index of the matched.
	 * @param string $id      Variable id.
	 *
	 * @return mixed
	 */
	private function get_variable_value( $matches, $index, $id ) {
		// Don't set up excluded replacements.
		if ( in_array( $matches[0][ $index ], $this->exclude, true ) ) {
			return false;
		}

		$has_args = ! empty( $matches[2][ $index ] ) && ! empty( $matches[3][ $index ] );
		$id       = $has_args ? $matches[2][ $index ] : $id;
		$var_args = $has_args ? $this->normalize_args( $matches[3][ $index ] ) : [];
		$variable = $this->get_variable_by_id( $id, $var_args );

		if ( is_null( $variable ) ) {
			return rank_math()->variables->remove_non_replaced ? '' : false;
		}

		return $variable->run_callback( $var_args, $this->args );
	}

	/**
	 * Find variable.
	 *
	 * @param string $id   Variable id.
	 * @param array  $args Array of arguments.
	 *
	 * @return Variable|null
	 */
	private function get_variable_by_id( $id, $args ) {
		if ( ! isset( rank_math()->variables ) ) {
			return null;
		}

		$replacements = rank_math()->variables->get_replacements();
		if ( isset( $replacements[ $id ] ) ) {
			return $replacements[ $id ];
		}

		if ( ! empty( $args ) && isset( $replacements[ $id . '_args' ] ) ) {
			return $replacements[ $id . '_args' ];
		}

		return null;
	}

	/**
	 * Convert arguments string to arguments array.
	 *
	 * @param  string $string The string that needs to be converted.
	 *
	 * @return array
	 */
	private function normalize_args( $string ) {
		if ( ! Str::contains( '=', $string ) ) {
			return $string;
		}

		return wp_parse_args( $string, [] );
	}
}
