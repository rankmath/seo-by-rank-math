<?php
/**
 * The Metadata.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Traits
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Traits;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Meta class.
 */
trait Meta {

	/**
	 * Get meta by object type.
	 *
	 * @param string $object_type Object type for destination where to save.
	 * @param int    $object_id   Object id for destination where to save.
	 * @param string $key         The meta key to retrieve. If no key is provided, fetches all metadata.
	 * @param bool   $single      Whether to return a single value.
	 *
	 * @return mixed
	 */
	public function get_meta( $object_type, $object_id, $key = '', $single = true ) {
		$func = "get_{$object_type}_meta";

		return $func( $object_id, $key, $single );
	}

	/**
	 * Update meta by object type.
	 *
	 * @param string $object_type Object type for destination where to save.
	 * @param int    $object_id   Object id for destination where to save.
	 * @param string $key         Metadata key.
	 * @param mixed  $value       Metadata value.
	 *
	 * @return mixed
	 */
	public function update_meta( $object_type, $object_id, $key, $value ) {
		$func = "update_{$object_type}_meta";

		if ( is_string( $key ) && is_protected_meta( $key ) && ( is_scalar( $value ) || is_array( $value ) ) ) {
			return $func( $object_id, $key, $value );
		}

		return false;
	}
}
