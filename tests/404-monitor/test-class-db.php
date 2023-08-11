<?php
/**
 * The 404 module database operations
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Monitor
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests\Monitor;

use RankMath\Monitor\DB;
use RankMath\Tests\UnitTestCase;

defined( 'ABSPATH' ) || exit;

/**
 * TestDB class.
 */
class TestDB extends UnitTestCase {

	/**
	 * The query builder.
	 *
	 * @var Query_Builder
	 */
	protected $table;

	public function setUp() {
		parent::setUp();

		DB::add([
			'uri'            => 'http://rankmath.com',
			'accessed'       => current_time( 'mysql' ),
			'times_accessed' => '1',
			'ip'             => '127.0.0.0',
			'referer'        => '',
			'user_agent'     => '',
		]);

		DB::add([
			'uri'            => 'http://rankmath.com/awesome',
			'accessed'       => current_time( 'mysql' ),
			'times_accessed' => '1',
			'ip'             => '127.0.0.0',
			'referer'        => '',
			'user_agent'     => '',
		]);
	}

	public function tearDown() {
		parent::tearDown();

		DB::clear_logs();
	}

	/**
	 * Clear logs completely.
	 */
	public function test_clear_logs() {
		$this->assertTrue( DB::clear_logs() );
	}

	/**
	 * Add a record.
	 */
	public function test_add() {
		$this->assertEquals( DB::add([
			'uri'            => 'http://rankmath.com',
			'accessed'       => current_time( 'mysql' ),
			'times_accessed' => '1',
			'ip'             => '127.0.0.0',
			'referer'        => '',
			'user_agent'     => '',
		]), 3 );

		$this->assertEquals( DB::add([
			'uri'            => 'http://rankmath.com/awesome',
			'accessed'       => current_time( 'mysql' ),
			'times_accessed' => '1',
			'ip'             => '127.0.0.0',
			'referer'        => '',
			'user_agent'     => '',
		]), 4 );
	}

	/**
	 * Get error logs.
	 */
	public function test_get_logs() {
		$log = DB::get_logs( [] );
		$this->assertEquals( $log['count'], 2 );

		// Search.
		$log = DB::get_logs( [
			'search' => 'awesome'
		] );
		$this->assertEquals( $log['count'], 2 );

		// Ids.
		$log = DB::get_logs( [
			'ids' => 1
		] );
		$this->assertEquals( $log['count'], 1 );

		$log = DB::get_logs( [
			'ids' => [ 1, 2 ]
		] );
		$this->assertEquals( $log['count'], 2 );

		$log = DB::get_logs( [
			'ids' => [ 5, 6 ]
		] );
		$this->assertEquals( $log['count'], 0 );
	}

	/**
	 * Update a record.
	 *
	 * @param array $args Values to update.
	 */
	public function test_update() {
		$row = DB::update( [ 'uri' => 'http://rankmath.com/awesome' ] );

		$stats = DB::get_stats();
		$this->assertEquals( $stats->total, 2 );
		$this->assertEquals( $stats->hits, 3 );

		$this->assertInternalType( 'int', DB::update( [ 'uri' => 'http://yahoo.com' ] ) );
	}

	/**
	 * Get total logs count.
	 */
	public function test_get_count() {
		$this->assertEquals( DB::get_count(), 2 );
	}

	/**
	 * Get stats for dashboard widget.
	 */
	public function test_get_stats() {
		$stats = DB::get_stats();
		$this->assertEquals( $stats->total, 2 );
		$this->assertEquals( $stats->hits, 2 );

		DB::add([
			'uri'            => 'http://rankmath.com/yahaaa',
			'accessed'       => current_time( 'mysql' ),
			'times_accessed' => '10',
			'ip'             => '127.0.0.0',
			'referer'        => '',
			'user_agent'     => '',
		]);

		$stats = DB::get_stats();
		$this->assertEquals( $stats->total, 3 );
		$this->assertEquals( $stats->hits, 12 );
	}

	/**
	 * Update if url is a matched and hit.
	 *
	 * @param  object $row Record to update.
	 * @return int|false The number of rows updated, or false on error.
	 */
	private static function update_counter( $row ) {
		$id = absint( $row['id'] );
		if ( 0 === $id ) {
			return false;
		}

		$update_data = array(
			'accessed'       => current_time( 'mysql' ),
			'times_accessed' => absint( $row['times_accessed'] ) + 1,
		);
		return self::table()->set( $update_data )->where( 'id', $id )->update();
	}

	/**
	 * Delete a record.
	 */
	public function test_delete_log() {
		$log = DB::get_logs( [] );
		$log = wp_list_pluck( $log['logs'], 'id' );
		$this->assertEquals( DB::delete_log( $log ), 2 );
	}

	/**
	 * Add a record before clear the log as limit over.
	 */
	public function test_add_clear_log_limit_over() {
		rank_math()->settings->set( 'general', '404_monitor_limit', 2 );
		DB::add([
			'uri'            => 'http://rankmath.com',
			'accessed'       => current_time( 'mysql' ),
			'times_accessed' => '1',
			'ip'             => '127.0.0.0',
			'referer'        => '',
			'user_agent'     => '',
		]);

		$log = DB::get_logs( [] );
		$this->assertEquals( $log['count'], 1 );
	}
}
