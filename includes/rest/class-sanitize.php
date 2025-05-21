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

use RankMath\CMB2;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Sanitize {

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Sanitize
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Sanitize ) ) {
			$instance = new Sanitize();
		}

		return $instance;
	}

	/**
	 * Sanitize value
	 *
	 * @param string $field_id Field id to sanitize.
	 * @param mixed  $value    Field value.
	 *
	 * @return mixed  Sanitized value.
	 */
	public function sanitize( $field_id, $value ) {
		$sanitized_value = '';
		switch ( $field_id ) {
			case 'rank_math_title':
			case 'rank_math_description':
			case 'rank_math_snippet_name':
			case 'rank_math_snippet_desc':
			case 'rank_math_facebook_title':
			case 'rank_math_facebook_description':
			case 'rank_math_twitter_title':
			case 'rank_math_twitter_description':
				$sanitized_value = wp_filter_nohtml_kses( $value );
				break;
			case 'rank_math_snippet_recipe_ingredients':
			case 'rank_math_snippet_recipe_instructions':
			case 'rank_math_snippet_recipe_single_instructions':
				$sanitized_value = $this->sanitize_textarea( $field_id, $value );
				break;
			case 'rank_math_canonical_url':
				$sanitized_value = esc_url_raw( $value );
				break;
			case 'rank_math_snippet_job_description':
				$sanitized_value = wp_kses(
					$value,
					[
						'br' => [],
						'p'  => [],
						'ul' => [],
						'li' => [],
					]
				);
				break;
			case 'rank_math_snippet_answer':
				$sanitized_value = wp_kses(
					$value,
					[
						'h1'     => [],
						'h2'     => [],
						'h3'     => [],
						'h4'     => [],
						'h5'     => [],
						'h6'     => [],
						'br'     => [],
						'ol'     => [],
						'ul'     => [],
						'li'     => [],
						'a'      => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
						'p'      => [],
						'b'      => [],
						'i'      => [],
						'div'    => [],
						'strong' => [],
						'em'     => [],
					]
				);
				break;
			default:
				$sanitized_value = is_array( $value ) ? $this->loop_sanitize( $value ) : CMB2::sanitize_textfield( $value );
		}

		return $sanitized_value;
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
	 * @param array $values  Field value.
	 * @param array $method Sanitize Method.
	 *
	 * @return mixed  Sanitized value.
	 */
	public function loop_sanitize( $values, $method = 'sanitize' ) {
		$sanitized_value = [];
		$type            = $values['@type'] ?? '';

		foreach ( $values  as $key => $value ) {
			$field_id = $key;

			if ( 'Answer' === $type && 'text' === $key ) {
				$field_id = 'rank_math_snippet_answer';
			}

			if ( 'JobPosting' === $type && 'description' === $key ) {
				$field_id = 'rank_math_snippet_job_description';
			}

			$sanitized_value[ CMB2::sanitize_textfield( $key ) ] = is_array( $value ) ? $this->loop_sanitize( $value, $method ) : $this->$method( $field_id, $value );
		}

		return $sanitized_value;
	}
}
