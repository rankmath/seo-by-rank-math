<?php
/**
 * The option center of the plugin.
 *
 * @since      1.0.250
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Helpers\Str;

/**
 * Settings Sanitizer for React-based settings.
 */
class Sanitize_Settings {

	/**
	 * Sanitize all settings data using field types.
	 *
	 * @param array $settings_data Array of setting data [field_id => value].
	 * @param array $field_types   Array of field types [field_id => type].
	 *
	 * @return array Sanitized settings data.
	 */
	public static function sanitize( $settings_data, $field_types ) {
		$sanitized = [];

		foreach ( $settings_data as $field_id => $value ) {
			$type                   = $field_types[ $field_id ] ?? 'text';
			$sanitized[ $field_id ] = self::sanitize_field( $value, $type, $field_id );
		}

		return $sanitized;
	}

	/**
	 * Sanitize an individual field based on its type.
	 *
	 * @param mixed  $value    Field value.
	 * @param string $type     Field type.
	 * @param string $field_id Field ID.
	 *
	 * @return mixed Sanitized value.
	 */
	public static function sanitize_field( $value, $type, $field_id ) {
		// First: Check field ID-specific logic.
		$field_specific = apply_filters( 'rank_math/settings/sanitize_fields', self::sanitize_by_field_id( $value, $field_id ), $value, $field_id );
		if ( $field_specific !== null ) {
			return $field_specific;
		}

		switch ( $type ) {
			case 'text':
				return is_array( $value ) ? array_map( [ __CLASS__, 'sanitize_textfield' ], $value ) : self::sanitize_textfield( $value );

			case 'textarea':
				return is_array( $value ) ? array_map( 'wp_kses_post', $value ) : wp_kses_post( $value );

			case 'toggle':
				return $value ? 'on' : 'off';

			case 'checkbox':
			case 'checkboxlist':
				return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : [];

			case 'select':
			case 'selectSearch':
			case 'selectVariable':
			case 'searchPage':
			case 'toggleGroup':
				return is_array( $value ) ? array_map( [ __CLASS__, 'sanitize_textfield' ], $value ) : self::sanitize_textfield( $value );

			case 'number':
				return is_array( $value ) ? array_map( 'intval', $value ) : intval( $value );

			case 'file':
				return esc_url_raw( $value );

			case 'group':
			case 'repeatableGroup':
				return self::sanitize_group_value( $value );
			default:
				// Fallback.
				return map_deep( $value, [ __CLASS__, 'sanitize_default_value' ] );
		}
	}

	/**
	 * Handles sanitization for default fields. Make sure to not change the boolean to blank text.
	 *
	 * @param string $value The unsanitized value from the form.
	 *
	 * @return string Sanitized value to be stored.
	 */
	public static function sanitize_default_value( $value ) {
		return is_string( $value ) ? sanitize_text_field( $value ) : $value;
	}

	/**
	 * Sanitize an individual field based on its id.
	 *
	 * @param mixed  $value    Field value.
	 * @param string $field_id Field ID.
	 *
	 * @return mixed Sanitized value.
	 */
	private static function sanitize_by_field_id( $value, $field_id ) {
		switch ( $field_id ) {
			case 'robots_txt_content':
				return self::sanitize_robots_text( $value );

			case 'google_verify':
			case 'bing_verify':
			case 'baidu_verify':
			case 'yandex_verify':
			case 'pinterest_verify':
			case 'norton_verify':
				return self::sanitize_webmaster_tags( $value );

			case 'custom_webmaster_tags':
				return self::sanitize_custom_webmaster_tags( $value );

			case 'console_caching_control':
				return self::sanitize_cache_control( $value );
		}

		// Returning null means no special handling; fall back to type-based logic.
		return null;
	}

	/**
	 * Handles sanitization for text fields.
	 *
	 * @param string $value The unsanitized value from the form.
	 *
	 * @return string Sanitized value to be stored.
	 */
	private static function sanitize_textfield( $value ) {
		if ( is_object( $value ) || is_array( $value ) ) {
			return '';
		}

		$value    = (string) $value;
		$filtered = wp_check_invalid_utf8( $value );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );

			// Strip extra whitespace.
			$filtered = wp_strip_all_tags( $filtered, false );

			// Use html entities in a special case to make sure no later
			// newline stripping stage could lead to a functional tag!
			$filtered = str_replace( "<\n", "&lt;\n", $filtered );
		}
		$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
		$filtered = trim( $filtered );

		$found = false;
		while ( preg_match( '/%[0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace( $match[0], '', $filtered );
			$found    = true;
		}

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
		}

		return apply_filters( 'sanitize_text_field', $filtered, $value );
	}

	/**
	 * Handles sanitization of Robots text.
	 *
	 * @since 1.0.45
	 *
	 * @param mixed $value The unsanitized Robots text.
	 *
	 * @return string Sanitized Robots text to be stored.
	 */
	private static function sanitize_robots_text( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		return wp_strip_all_tags( $value );
	}

	/**
	 * Handles sanitization for webmaster tag and remove <meta> tag.
	 *
	 * @param mixed $value The unsanitized value from the form.
	 *
	 * @return mixed Sanitized value to be stored.
	 */
	private static function sanitize_webmaster_tags( $value ) {
		$value = trim( $value );

		if ( ! empty( $value ) && Str::starts_with( '<meta', trim( $value ) ) ) {
			preg_match( '/content="([^"]+)"/i', stripslashes( $value ), $matches );
			$value = $matches[1];
		}

		return htmlentities( wp_strip_all_tags( $value ) );
	}

	/**
	 * Handles sanitization for custom webmaster tags.
	 * Only <meta> tags are allowed.
	 *
	 * @param mixed $value The unsanitized value from the form.
	 */
	private static function sanitize_custom_webmaster_tags( $value ) {
		$sanitized = wp_kses(
			$value,
			[
				'meta' => [
					'name'    => [],
					'content' => [],
				],
			]
		);

		return $sanitized;
	}

	/**
	 * Handles sanitization for Analytics cache control option.
	 *
	 * @param mixed $value The unsanitized value from the form.
	 */
	private static function sanitize_cache_control( $value ) {
		$max   = apply_filters( 'rank_math/analytics/max_days_allowed', 90 );
		$value = absint( $value );
		if ( $value > $max ) {
			$value = $max;
		}

		return $value;
	}

	/**
	 * Do not save if name or image is empty.
	 *
	 * @param array $value Field value to save.
	 * @return array
	 */
	private function sanitize_overlays( $value ) {
		if ( ! is_array( $value ) ) {
			return [];
		}

		foreach ( $value as $key => $overlay ) {
			if ( empty( $overlay['image'] ) ) {
				unset( $value[ $key ] );
			} elseif ( empty( $overlay['name'] ) ) {
				Helper::add_notification( esc_html__( 'A Custom Watermark item could not be saved because the name field is empty.', 'rank-math' ), [ 'type' => 'error' ] );
				unset( $value[ $key ] );
			}
		}

		return $value;
	}

	/**
	 * Handles sanitization of advanced robots data.
	 *
	 * @param array $robots The unsanitized value from the form.
	 *
	 * @return array Sanitized value to be stored.
	 */
	private static function sanitize_advanced_robots( $robots ) {
		if ( empty( $robots ) ) {
			return [];
		}

		$advanced_robots = [];
		foreach ( $robots as $key => $robot ) {
			$advanced_robots[ $key ] = ! empty( $robot['enable'] ) ? $robot['length'] : false;
		}

		return $advanced_robots;
	}

	/**
	 * Sanitize a group or repeatable group field.
	 *
	 * - For a single group, sanitizes keys and values.
	 * - For repeatable groups (array of group items), recursively sanitizes each item.
	 * - Preserves key casing.
	 *
	 * @param array $group_value The group or repeatable group value.
	 * @return array Sanitized group value.
	 */
	private static function sanitize_group_value( $group_value ) {
		if ( ! is_array( $group_value ) ) {
			return [];
		}

		// Check if this is a repeatable group (array of associative arrays).
		$is_repeatable = array_keys( $group_value ) === range( 0, count( $group_value ) - 1 );

		if ( $is_repeatable ) {
			$sanitized = [];

			foreach ( $group_value as $item ) {
				if ( is_array( $item ) ) {
					$sanitized[] = self::sanitize_array_recursive( $item );
				}
			}

			return $sanitized;
		}

		// Single group.
		return self::sanitize_array_recursive( $group_value );
	}

	/**
	 * Recursively sanitize an array's keys (preserving casing) and values.
	 *
	 * Uses sanitize_text_field() for scalar values. Nested arrays are handled recursively.
	 *
	 * @param array $data The array to recursively sanitize.
	 * @return array The sanitized array.
	 */
	private static function sanitize_array_recursive( $data ) {
		$sanitized = [];

		foreach ( $data as $key => $val ) {
			$clean_key = self::sanitize_key_preserve_case( $key );

			if ( is_array( $val ) ) {
				$sanitized[ $clean_key ] = self::sanitize_array_recursive( $val );
			} else {
				$sanitized[ $clean_key ] = self::sanitize_textfield( $val );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize a key while preserving original casing.
	 *
	 * Removes unsafe characters like spaces, HTML, and control characters,
	 * but keeps casing and underscores intact.
	 *
	 * @param string $key The key to sanitize.
	 * @return string The sanitized key.
	 */
	private static function sanitize_key_preserve_case( $key ) {
		$key = wp_strip_all_tags( $key );
		$key = preg_replace( '/[^A-Za-z0-9_\-]/', '', $key );
		return $key;
	}
}
