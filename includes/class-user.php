<?php
/**
 * The User Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use WP_User;

defined( 'ABSPATH' ) || exit;

/**
 * User class.
 */
class User extends Metadata {

	/**
	 * The type of object the metadata is for.
	 *
	 * @var string
	 */
	protected $meta_type = 'user';

	/**
	 * Get User instance.
	 *
	 * @param WP_User|object|int $user User ID or user object.
	 * @return User|false User object or false if not found.
	 */
	public static function get( $user = 0 ) {
		if ( is_int( $user ) && 0 === absint( $user ) ) {
			$user = $GLOBALS['wp_query']->get_queried_object();
		}

		if ( is_object( $user ) ) {
			$user = $user->ID ?? '';
		}

		if ( empty( $user ) ) {
			return null;
		}

		if ( isset( self::$objects[ $user ] ) && 'user' === self::$objects[ $user ]->meta_type ) {
			return self::$objects[ $user ];
		}

		$_user                  = new self( get_user_by( 'id', $user ) );
		$_user->object_id       = $user;
		self::$objects[ $user ] = $_user;

		return $_user;
	}

	/**
	 * Get user meta data.
	 *
	 * @param string $key           Meta key.
	 * @param mixed  $user          User ID or user object.
	 * @param string $default_value Default value to use when metadata does not exists.
	 * @return mixed
	 */
	public static function get_meta( $key, $user = 0, $default_value = '' ) {
		$user = self::get( $user );

		if ( is_null( $user ) || ! $user->is_found() ) {
			return $default_value;
		}

		return $user->get_metadata( $key, $default_value );
	}
}
