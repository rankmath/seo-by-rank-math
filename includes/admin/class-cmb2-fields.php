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
		$field_name   = $field->_name();
		$active_value = ! empty( $field->args( 'active_value' ) ) ? $field->args( 'active_value' ) : 'on';

		$args = [
			'type'  => 'checkbox',
			'id'    => $field_name,
			'name'  => $field_name,
			'desc'  => '',
			'value' => $active_value,
		];

		if ( $escaped_value === $active_value ) {
			$args['checked'] = 'checked';
		}

		echo '<label class="cmb2-toggle">';
		echo $field_type_object->input( $args );
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

		echo $field_type_object->radio_inline();
	}

	/**
	 * Render notices
	 *
	 * @param array $field The passed in `CMB2_Field` object.
	 */
	public function render_notice( $field ) {
		$hash = [
			'error'   => 'notice notice-alt notice-error error inline rank-math-notice',
			'info'    => 'notice notice-alt notice-info info inline rank-math-notice',
			'warning' => 'notice notice-alt notice-warning warning inline rank-math-notice',
		];

		echo '<div class="' . $hash[ $field->args( 'what' ) ] . '"><p>' . $field->args( 'content' ) . '</p></div>';
	}

	/**
	 * Render address field.
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
			'addressCountry'  => 'Country',
		];

		foreach ( array_keys( $value ) as $id ) :
			echo '<div class="cmb-address-field">' . $field_type_object->input(
				[
					'name'        => $field_type_object->_name( '[' . $id . ']' ),
					'id'          => $field_type_object->_id( '_' . $id ),
					'value'       => $value[ $id ],
					'placeholder' => esc_html( $field->get_string( $id . '_text', $strings[ $id ] ) ),
				]
			) . '</div>';
		endforeach;
	}

	/**
	 * Render Advanced Robots fields.
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
				echo '<label for="' . $field_type_object->_id( '_' . $id . '_name' ) . '">';
					echo $field_type_object->checkbox(
						[
							'name'    => $field_type_object->_name( "[{$id}][enable]" ),
							'id'      => $field_type_object->_id( '_' . $id . '_name' ),
							'value'   => true,
							'checked' => ! empty( $escaped_value[ $id ] ) || empty( $escaped_value ) ? 'checked' : false,
						]
					);
				echo $field->get_string( $id . '_text', $strings[ $id ] ) . '</label>';

			if ( 'max-image-preview' === $id ) {
				echo $field_type_object->select(
					[
						'name'    => $field_type_object->_name( "[{$id}][length]" ),
						'id'      => $field_type_object->_id( '_' . $id . '_name' ),
						'options' => $this->get_image_sizes( $value ),
					]
				);
			}

			if ( 'max-image-preview' !== $id ) {
				echo $field_type_object->input(
					[
						'name'  => $field_type_object->_name( "[{$id}][length]" ),
						'id'    => $field_type_object->_id( '_' . $id . '_length' ),
						'value' => $value ? $value : -1,
						'type'  => 'number',
						'min'   => -1,
					]
				);
			}

			echo '</li>';
		endforeach;
		echo '</ul>';
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
