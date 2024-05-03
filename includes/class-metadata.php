<?php
/**
 * The Metadata Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

defined( 'ABSPATH' ) || exit;

/**
 * Metadata class.
 */
abstract class Metadata {

	/**
	 * Type of object the metadata is for.
	 *
	 * @var string
	 */
	protected $meta_type = 'post';

	/**
	 * Holds the object.
	 *
	 * @var WP_Post|WP_Term|WP_User
	 */
	protected $object = null;

	/**
	 * Holds the object ID.
	 *
	 * @var int
	 */
	protected $object_id = null;

	/**
	 * Holds multiple objects.
	 *
	 * @var array
	 */
	protected static $objects = [];

	/**
	 * Holds the properties.
	 *
	 * @var array
	 */
	protected $properties = [];

	/**
	 * Getter.
	 *
	 * @param string $property Key to get.
	 * @return mixed
	 */
	public function __get( $property ) {

		if ( \property_exists( $this, $property ) ) {
			return $this->$property;
		}

		if ( isset( $this->properties[ $property ] ) ) {
			return $this->properties[ $property ];
		}

		if ( isset( $this->object->$property ) ) {
			return $this->object->$property;
		}

		return get_metadata( $this->meta_type, $this->object_id, $property, true );
	}

	/**
	 * Setter.
	 * This prevents the Dynamic Properties deprecation notice in PHP 8.2.
	 *
	 * @param string $property Key to set.
	 * @param mixed  $value    Value to set.
	 * @return void
	 */
	public function __set( $property, $value ) {
		$this->properties[ $property ] = $value;
	}

	/**
	 * Constructor.
	 *
	 * @param WP_Post|WP_Term|WP_User $object Current object.
	 */
	public function __construct( $object ) {
		$this->object = $object;
	}

	/**
	 * If object found.
	 *
	 * @return bool
	 */
	public function is_found() {
		return ! is_null( $this->object );
	}

	/**
	 * Get attached object.
	 *
	 * @return object
	 */
	public function get_object() {
		return $this->object;
	}

	/**
	 * Get metadata for the object.
	 *
	 * @param  string $key     Value to get, without prefix.
	 * @param  string $default Default value to use when metadata does not exists.
	 * @return mixed
	 */
	public function get_metadata( $key, $default = '' ) {
		$meta_key = 'rank_math_' . $key;
		if ( isset( $this->$meta_key ) ) {
			return $this->$meta_key;
		}

		$value    = $this->$meta_key;
		$replaced = $this->maybe_replace_vars( $key, $value, $this->object );
		if ( false !== $replaced ) {
			$this->$meta_key = $replaced;
			return $this->$meta_key;
		}

		if ( ! $value ) {
			return $default;
		}

		$this->$meta_key = Helper::normalize_data( $value );
		return $this->$meta_key;
	}

	/**
	 * Maybe replace variables in meta data.
	 *
	 * @param  string $key    Key to check whether it contains variables.
	 * @param  mixed  $value  Value used to replace variables in.
	 * @param  object $object Object used for replacements.
	 * @return string|bool False if replacement not needed. Replaced variable string.
	 */
	public function maybe_replace_vars( $key, $value, $object ) {
		$need_replacements = [ 'title', 'description', 'facebook_title', 'twitter_title', 'facebook_description', 'twitter_description', 'snippet_name', 'snippet_desc' ];

		// Early bail.
		if ( ! in_array( $key, $need_replacements, true ) || ! is_string( $value ) || '' === $value ) {
			return false;
		}

		$value = \str_replace(
			[ '%seo_title%', '%seo_description%' ],
			[ '%title%', '%excerpt%' ],
			$value
		);

		return Helper::replace_vars( $value, $object );
	}
}
