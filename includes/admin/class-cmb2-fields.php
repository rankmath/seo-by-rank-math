<?php
/**
 * The CMB2 fields for the plugin.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Runner;
use RankMath\Helpers\Str;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * CMB2_Fields class.
 *
 * @codeCoverageIgnore
 */
class CMB2_Fields implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		if ( ! has_action( 'cmb2_render_switch' ) ) {
			$this->action( 'cmb2_render_switch', 'render_switch', 10, 5 );
		}

		if ( ! has_action( 'cmb2_render_notice' ) ) {
			$this->action( 'cmb2_render_notice', 'render_notice' );
		}

		if ( ! has_action( 'cmb2_render_address' ) ) {
			$this->action( 'cmb2_render_address', 'render_address', 10, 5 );
		}

		if ( ! has_action( 'cmb2_render_advanced_robots' ) ) {
			$this->action( 'cmb2_render_advanced_robots', 'render_advanced_robots', 10, 5 );
		}

		if ( ! has_action( 'cmb2_render_toggle' ) ) {
			$this->action( 'cmb2_render_toggle', 'render_toggle', 10, 5 );
		}

		$this->filter( 'cmb2_sanitize_toggle', 'sanitize_toggle', 10, 2 );
		$this->filter( 'cmb2_field_arguments_raw', 'default_value', 10, 2 );
	}

	/**
	 * Set a default value in default_cb to prevent the callback function from executing on the site.
	 *
	 * @see https://github.com/CMB2/CMB2/issues/750
	 *
	 * @param array  $args The field arguments.
	 * @param object $cmb2 The CMB2 object.
	 */
	public function default_value( $args, $cmb2 ) {
		if (
			! Str::starts_with( 'rank-math', trim( $cmb2->cmb_id ) ) ||
			! isset( $args['default'] ) ||
			! is_callable( $args['default'] )
		) {
			return $args;
		}

		$args['default_cb'] = function () use ( $args ) {
			return $args['default'];
		};

		$args['default'] = null;

		return $args;
	}

	/**
	 * Sanitize toggle field.
	 *
	 * @param string $override_value Sanitization override value to return.
	 * @param string $value          The field value.
	 */
	public function sanitize_toggle( $override_value, $value ) {
		return is_null( $value ) ? 'off' : $value;
	}

	/**
	 * Render toggle field.
	 *
	 * @param array  $field             The passed in `CMB2_Field` object.
	 * @param mixed  $escaped_value     The value of this field escaped
	 *                                  It defaults to `sanitize_text_field`.
	 *                                  If you need the unescaped value, you can access it
	 *                                  via `$field->value()`.
	 * @param int    $object_id         The ID of the current object.
	 * @param string $object_type       The type of object you are working with.
	 *                                  Most commonly, `post` (this applies to all post-types),
	 *                                  but could also be `comment`, `user` or `options-page`.
	 * @param object $field_type_object This `CMB2_Types` object.
	 */
	public function render_toggle( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$field_name    = $field->_name();
		$active_value  = ! empty( $field->args( 'active_value' ) ) ? $field->args( 'active_value' ) : 'on';
		$escaped_value = ! empty( $field->args( 'force_enable' ) ) ? 'on' : $escaped_value;

		$args = [
			'type'     => 'checkbox',
			'id'       => $field_name,
			'name'     => $field_name,
			'desc'     => '',
			'value'    => $active_value,
			'disabled' => isset( $field->args['disabled'] ) ? $field->args['disabled'] : false,
		];

		if ( $escaped_value === $active_value ) {
			$args['checked'] = 'checked';
		}

		echo '<label class="cmb2-toggle">';
		echo $field_type_object->input( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CMB2 handles escaping.
		echo '<span class="cmb2-slider">';
		echo '<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>';
		echo '<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>';
		echo '</span>';
		echo '</label>';
		$field_type_object->_desc( true, true );
	}

	/**
	 * Render switch field.
	 *
	 * @param array  $field             The passed in `CMB2_Field` object.
	 * @param mixed  $escaped_value     The value of this field escaped
	 *                                  It defaults to `sanitize_text_field`.
	 *                                  If you need the unescaped value, you can access it
	 *                                  via `$field->value()`.
	 * @param int    $object_id         The ID of the current object.
	 * @param string $object_type       The type of object you are working with.
	 *                                  Most commonly, `post` (this applies to all post-types),
	 *                                  but could also be `comment`, `user` or `options-page`.
	 * @param object $field_type_object This `CMB2_Types` object.
	 */
	public function render_switch( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

		if ( empty( $field->args['options'] ) ) {
			$field->args['options'] = [
				'off' => esc_html( $field->get_string( 'off', __( 'Off', 'rank-math' ) ) ),
				'on'  => esc_html( $field->get_string( 'on', __( 'On', 'rank-math' ) ) ),
			];
		}
		$field->set_options();

		echo $field_type_object->radio_inline(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CMB2 handles escaping.
	}

	/**
	 * Render notices
	 *
	 * @param object $field The passed in `CMB2_Field` object.
	 */
	public function render_notice( $field ) {
		$hash = [
			'error'   => 'notice notice-alt notice-error error inline rank-math-notice',
			'info'    => 'notice notice-alt notice-info info inline rank-math-notice',
			'warning' => 'notice notice-alt notice-warning warning inline rank-math-notice',
		];

		echo '<div class="' . esc_attr( $hash[ $field->args( 'what' ) ] ) . '"><p>' . $field->args( 'content' ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CMB2 handles escaping.
	}

	/**
	 * Render address field.
	 *
	 * @param object $field             The passed in `CMB2_Field` object.
	 * @param mixed  $escaped_value     The value of this field escaped
	 *                                  It defaults to `sanitize_text_field`.
	 *                                  If you need the unescaped value, you can access it
	 *                                  via `$field->value()`.
	 * @param int    $object_id         The ID of the current object.
	 * @param string $object_type       The type of object you are working with.
	 *                                  Most commonly, `post` (this applies to all post-types),
	 *                                  but could also be `comment`, `user` or `options-page`.
	 * @param object $field_type_object This `CMB2_Types` object.
	 */
	public function render_address( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

		// Make sure we assign each part of the value we need.
		$value = wp_parse_args(
			$escaped_value,
			[
				'streetAddress'   => '',
				'addressLocality' => '',
				'addressRegion'   => '',
				'postalCode'      => '',
				'addressCountry'  => '',
			]
		);

		$strings = [
			'streetAddress'   => 'Street Address',
			'addressLocality' => 'Locality',
			'addressRegion'   => 'Region',
			'postalCode'      => 'Postal Code',
			'addressCountry'  => '2-letter Country Code (ISO 3166-1)',
		];

		foreach ( array_keys( $value ) as $id ) :
			$field_input = $field_type_object->input(
				[
					'name'        => $field_type_object->_name( '[' . $id . ']' ),
					'id'          => $field_type_object->_id( '_' . $id ),
					'value'       => $value[ $id ],
					'placeholder' => esc_html( $field->get_string( $id . '_text', $strings[ $id ] ) ),
				]
			);
			echo '<div class="cmb-address-field">' . $field_input . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CMB2 handles escaping.
		endforeach;
	}

	/**
	 * Render Advanced Robots fields.
	 *
	 * @param object $field             The passed in `CMB2_Field` object.
	 * @param mixed  $escaped_value     The value of this field escaped
	 *                                  It defaults to `sanitize_text_field`.
	 *                                  If you need the unescaped value, you can access it
	 *                                  via `$field->value()`.
	 * @param int    $object_id         The ID of the current object.
	 * @param string $object_type       The type of object you are working with.
	 *                                  Most commonly, `post` (this applies to all post-types),
	 *                                  but could also be `comment`, `user` or `options-page`.
	 * @param object $field_type_object This `CMB2_Types` object.
	 */
	public function render_advanced_robots( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		// Make sure we assign each part of the value we need.
		$values = wp_parse_args(
			$escaped_value,
			[
				'max-snippet'       => -1,
				'max-video-preview' => -1,
				'max-image-preview' => 'large',
			]
		);

		$strings = [
			'max-snippet'       => __( 'Snippet', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Specify a maximum text-length, in characters, of a snippet for your page.', 'rank-math' ) ),
			'max-video-preview' => __( 'Video Preview', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Specify a maximum duration in seconds of an animated video preview.', 'rank-math' ) ),
			'max-image-preview' => __( 'Image Preview', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Specify a maximum size of image preview to be shown for images on this page.', 'rank-math' ) ),
		];

		echo '<ul class="cmb-advanced-robots-list no-select-all cmb2-list cmb-advanced-robots-field">';
		foreach ( $values as $id => $value ) :
			$value = isset( $escaped_value[ $id ] ) ? $escaped_value[ $id ] : $value;

			echo '<li>';
				echo '<label for="' . esc_attr( $field_type_object->_id( '_' . $id . '_name' ) ) . '">';
					echo $this->get_advanced_robots_field( 'checkbox', $field_type_object, $id, $value, $escaped_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CMB2 handles escaping.
				echo wp_kses_post( $field->get_string( $id . '_text', $strings[ $id ] ) ) . '</label>';

			if ( 'max-image-preview' === $id ) {
				echo $this->get_advanced_robots_field( 'select', $field_type_object, $id, $value, $escaped_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CMB2 handles escaping.
			}

			if ( 'max-image-preview' !== $id ) {
				echo $this->get_advanced_robots_field( 'input', $field_type_object, $id, $value, $escaped_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CMB2 handles escaping.
			}

			echo '</li>';
		endforeach;
		echo '</ul>';
	}

	/**
	 * Get the field markup for the advanced robots field.
	 *
	 * @param string $field_type        The type of field.
	 * @param object $field_type_object The CMB2_Types object.
	 * @param string $id                The field id.
	 * @param string $value             The field value.
	 * @param string $escaped_value     The escaped field value.
	 *
	 * @return string The field markup.
	 */
	private function get_advanced_robots_field( $field_type, $field_type_object, $id, $value, $escaped_value ) {
		$props = [
			'name' => $field_type_object->_name( "[{$id}][length]" ),
			'id'   => $field_type_object->_id( '_' . $id . '_name' ),
		];

		switch ( $field_type ) {
			case 'checkbox':
				$props['name']    = $field_type_object->_name( "[{$id}][enable]" );
				$props['value']   = true;
				$props['checked'] = ! empty( $escaped_value[ $id ] ) || empty( $escaped_value ) ? 'checked' : false;
				break;
			case 'select':
				$props['options'] = $this->get_image_sizes( $value );
				break;
			case 'input':
				$props['value'] = $value ? $value : -1;
				$props['type']  = 'number';
				$props['min']   = -1;
				break;
		}

		return $field_type_object->$field_type( $props );
	}

	/**
	 * Get Image sizes.
	 *
	 * @param  string $size    The selected image size.
	 * @return string $options The image sizes.
	 */
	private function get_image_sizes( $size = 'large' ) {
		$values  = [
			'large'    => __( 'Large', 'rank-math' ),
			'standard' => __( 'Standard', 'rank-math' ),
			'none'     => __( 'None', 'rank-math' ),
		];
		$options = '';
		foreach ( $values as $data => $label ) {
			$options .= '<option value="' . $data . '" ' . selected( $size, $data, false ) . ' >' . $label . '</option>';
		}

		return $options;
	}
}
