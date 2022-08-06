<?php
/**
 * The Global functionality of the plugin.
 *
 * Defines the functionality loaded on admin.
 *
 * @since      1.0.15
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Rest;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Sanitizer {

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Sanitizer
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Sanitize ) ) {
			$instance = new Sanitizer();
		}

		return $instance;
	}

	/**
	 * Sanitize value.
	 *
	 * @param string $field_id Field id to sanitize.
	 * @param mixed  $value    Field value.
	 *
	 * @return mixed  Sanitized value.
	 */
	public function sanitize( $field_id, $value ) {
		$sanitize_kses = [
			'wp_filter_nohtml_kses',
			[ $value ],
		];

		$sanitize_textarea = [
			[
				$this,
				'sanitize_textarea',
			],
			[ $field_id, $value ],
		];

		$sanitize_map = [
			'rank_math_title'                              => $sanitize_kses,
			'rank_math_description'                        => $sanitize_kses,
			'rank_math_snippet_name'                       => $sanitize_kses,
			'rank_math_snippet_desc'                       => $sanitize_kses,
			'rank_math_facebook_title'                     => $sanitize_kses,
			'rank_math_facebook_description'               => $sanitize_kses,
			'rank_math_twitter_title'                      => $sanitize_kses,
			'rank_math_twitter_description'                => $sanitize_kses,
			'rank_math_snippet_recipe_ingredients'         => $sanitize_textarea,
			'rank_math_snippet_recipe_instructions'        => $sanitize_textarea,
			'rank_math_snippet_recipe_single_instructions' => $sanitize_textarea,
		];

		if ( isset( $sanitize_map[ $field_id ] ) ) {
			return call_user_func_array( $sanitize_map[ $field_id ][0], $sanitize_map[ $field_id ][1] );
		}

		return is_array( $value ) ? $this->loop_sanitize( $value ) : \RankMath\CMB2::sanitize_textfield( $value );
	}

	/**
	 * Sanitize Textarea field
	 *
	 * @param string $field_id Field id to sanitize.
	 * @param mixed  $value    Field value.
	 *
	 * @return mixed  Sanitized value.
	 */
	public function sanitize_textarea( $field_id, $value ) {
		return is_array( $value ) ? $this->loop_sanitize( $value, 'sanitize_textarea' ) : sanitize_textarea_field( $value );
	}

	/**
	 * Sanitize array
	 *
	 * @param array $array  Field value.
	 * @param array $method Sanitize Method.
	 *
	 * @return mixed  Sanitized value.
	 */
	public function loop_sanitize( $array, $method = 'sanitize' ) {
		$sanitized_value = [];

		foreach ( $array  as $key => $value ) {
			$sanitized_value[ $key ] = is_array( $value ) ? $this->loop_sanitize( $value, $method ) : $this->$method( $key, $value );
		}

		return $sanitized_value;
	}
}
