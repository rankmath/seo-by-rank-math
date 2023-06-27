<?php
/**
 * Class HelperTest
 *
 * Test methods in Helper class.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Tests
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests;

use WP_UnitTestCase;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

class WordPressHelper extends WP_UnitTestCase {

	/**
	 * Get admin url.
	 */
	public function test_get_admin_url() {
		$this->assertEquals( admin_url( 'admin.php?page=rank-math' ), Helper::get_admin_url() );
		$this->assertEquals( admin_url( 'admin.php?page=rank-math-somepage' ), Helper::get_admin_url( 'somepage' ) );
		$this->assertEquals(
			admin_url( 'admin.php?page=rank-math-somepage&param1=1&param2=2' ),
			Helper::get_admin_url(
				'somepage',
				array(
					'param1' => 1,
					'param2' => 2,
				)
			)
		);
	}

	/**
	 * Get connect url.
	 */
	public function test_get_connect_url() {
		if ( is_multisite() ) {
			$this->assertEquals( admin_url( 'admin.php?page=rank-math&view=help' ), Helper::get_connect_url() );
		} else {
			$this->assertEquals( admin_url( 'admin.php?page=rank-math&view=help' ), Helper::get_connect_url() );
		}
	}
}
