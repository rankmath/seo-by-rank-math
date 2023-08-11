<?php
/**
 * The Redirections Cache
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests\Redirections;

use RankMath\Tests\UnitTestCase;
use RankMath\Redirections\Cache;
use MyThemeShop\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * TestCache class.
 */
class TestCache extends UnitTestCase {

	/**
	 * Add a new record.
	 */
	public function test_add() {
		Database::table( 'rank_math_redirections_cache' )->truncate();

		// Add.
		$this->assertFalse( Cache::add() );
		$this->assertEquals( Cache::add([
			'from_url' => 'new-record',
			'redirection_id' => '1',
			'object_id'      => '1',
		]), 1 );

		// Get redirection by object id.
		$cache = Cache::get_by_object_id( 1, 'post' );
		$this->assertEquals( $cache->from_url, 'new-record' );

		// Get redirection by url.
		$cache = Cache::get_by_url( 'new-record' );
		$this->assertEquals( $cache->redirection_id, 1 );

		// Purge by object id.
		$this->assertEquals( Cache::purge_by_object_id( 1, 'post' ), 1 );
	}
}
