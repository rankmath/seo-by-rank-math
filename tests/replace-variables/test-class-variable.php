<?php
/**
 * The Redirector
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Replace_Variables
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests\Replace_Variables;

use RankMath\Tests\UnitTestCase;
use RankMath\Replace_Variables\Variable;

defined( 'ABSPATH' ) || exit;

/**
 * TestVariable class.
 */
class TestVariable extends UnitTestCase {

	/**
	 * If no id is provided throws exception.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function test_new_variable_exception() {
		Variable::from( '', [] );
	}

	/**
	 * If no name is provided throws exception.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function test_new_variable_name_exception() {
		Variable::from( 'awesome', [
			'description' => 'Test description',
			'variable'    => 'test_variable',
			'example'     => 'Test example',
		] );
	}

	/**
	 * Test variable is successfull.
	 */
	public function test_new_variable() {
		$variable = Variable::from( 'awesome', [
			'name'        => 'Test variable',
			'description' => 'Test description',
			'variable'    => 'test_variable',
			'example'     => 'Test example',
		] );

		$this->assertEquals( $variable->get_id(), 'awesome' );
		$this->assertEquals( $variable->get_name(), 'Test variable' );
		$this->assertEquals( $variable->get_description(), 'Test description' );
		$this->assertEquals( $variable->get_variable(), 'test_variable' );
		$this->assertEquals( $variable->get_example(), 'Test example' );

		$variable->set_example( 'Change of example' );
		$this->assertEquals( $variable->get_example(), 'Change of example' );
	}
}
