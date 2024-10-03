<?php
/**
 * The JSON manager handles json output to admin and frontend.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath;

/**
 * Json_Manager class.
 */
class Json_Manager {

	/**
	 * Data.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Construct
	 */
	public function __construct() {
		$hook = is_admin() ? 'admin_footer' : 'wp_footer';
		add_action( $hook, [ $this, 'output' ], 0 );
	}

	/**
	 * Add something to JSON object.
	 *
	 * @param string $key         Unique identifier.
	 * @param mixed  $value       The data itself can be either a single or an array.
	 * @param string $object_name Name for the JavaScript object. Passed directly, so it should be qualified JS variable.
	 */
	public function add( $key, $value, $object_name ) {

		if ( empty( $key ) ) {
			return;
		}

		// If key doesn't exists.
		if ( ! isset( $this->data[ $object_name ][ $key ] ) ) {
			$this->data[ $object_name ][ $key ] = $value;
			return;
		}

		// If key already exists.
		$old_value = $this->data[ $object_name ][ $key ];

		// If both array merge them.
		if ( is_array( $old_value ) && is_array( $value ) ) {
			$this->data[ $object_name ][ $key ] = array_merge( $old_value, $value );
			return;
		}

		$this->data[ $object_name ][ $key ] = $value;
	}

	/**
	 * Remove something from JSON object.
	 *
	 * @param string $key         Unique identifier.
	 * @param string $object_name Name for the JavaScript object. Passed directly, so it should be qualified JS variable.
	 */
	public function remove( $key, $object_name ) {
		if ( isset( $this->data[ $object_name ][ $key ] ) ) {
			unset( $this->data[ $object_name ][ $key ] );
		}
	}

	/**
	 * Print data.
	 */
	public function output() {
		$script = $this->encode();
		if ( ! $script ) {
			return;
		}

		wp_print_inline_script_tag( $script, [ 'id' => 'rank-math-json' ] );
	}

	/**
	 * Get encoded string.
	 *
	 * @return string
	 */
	private function encode() {
		$script = '';
		foreach ( $this->data as $object_name => $object_data ) {
			$script .= $this->single_object( $object_name, $object_data );
		}

		return $script;
	}

	/**
	 * Encode single object.
	 *
	 * @param  string $object_name Object name to use as JS variable.
	 * @param  array  $object_data Object data to json encode.
	 * @return array
	 */
	private function single_object( $object_name, $object_data ) {
		if ( empty( $object_data ) ) {
			return '';
		}

		$object_data = apply_filters( 'rank_math/json_data', $object_data );
		foreach ( (array) $object_data as $key => $value ) {
			if ( ! is_string( $value ) ) {
				continue;
			}

			$object_data[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}

		return "var $object_name = " . wp_json_encode( $object_data ) . ';' . PHP_EOL;
	}
}
