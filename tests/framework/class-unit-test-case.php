<?php
/**
 * Unit tests for Breadcrumbs Helper
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Frontend
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests;

use ReflectionClass;
use WP_UnitTestCase;

defined( 'ABSPATH' ) || exit;

abstract class UnitTestCase extends WP_UnitTestCase {

	/**
	 * Invoke private and protected methods.
	 */
	public function invokeMethod( $object, $method, $parameters = array() ) {
		$reflection = new ReflectionClass( is_string( $object ) ? $object : get_class( $object ) );
		$method     = $reflection->getMethod( $method );
		$method->setAccessible( true );

		return is_string( $object ) ? $method->invokeArgs( null, $parameters ) : $method->invokeArgs( $object, $parameters );
	}

	public function getPrivate( $obj, $attribute ) {
		$getter = function() use ( $attribute ) {
			return $this->$attribute;
		};
		$get = \Closure::bind( $getter, $obj, get_class( $obj ) );
		return $get();
	}

	public function dump( $var ) {
		fwrite( STDERR, print_r( $var, true ) );
	}
}
