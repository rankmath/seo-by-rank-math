<?php
/**
 * The Redirection module database operations
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests\Redirections;

use RankMath\Redirections\DB;
use RankMath\Tests\UnitTestCase;
use MyThemeShop\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * TestDB class.
 */
class TestDB extends UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->class_name = '\\RankMath\\Redirections\\DB';

		DB::add([
			'sources' => [
				[
					'pattern' => 'new-record',
					'comparison' => 'exact',
				]
			],
			'url_to'  => 'http://mts.test/new-record-1/',
			'status'  => 'active',
		]);

		DB::add([
			'sources' => [
				[
					'pattern' => 'yawar-ahmed',
					'comparison' => 'exact',
				]
			],
			'hits'    => 100,
			'url_to'  => 'http://mts.test/new-record-1/',
			'status'  => 'inactive',
		]);
	}

	/**
	 * Clear logs completely.
	 */
	public function tearDown() {
		parent::tearDown();
		Database::table( 'rank_math_redirections' )->truncate();
	}

	/**
	 * Get counts of record group by active and inactive.
	 */
	public function test_get_counts() {
		$counts = DB::get_counts();
		$this->assertEquals( '2', $counts['all'] );
		$this->assertEquals( '1', $counts['active'] );
		$this->assertEquals( '1', $counts['inactive'] );
		$this->assertEquals( '0', $counts['trashed'] );

		// Not Null.
		$counts = DB::get_counts();
		$this->assertEquals( '2', $counts['all'] );
	}

	/**
	 * Add a new record.
	 *
	 * @param array $args Values to insert.
	 *
	 * @return int
	 */
	public function test_add() {
		$this->assertFalse( DB::add( [] ) );

		$this->assertEquals(DB::add([
			'sources' => [
				[
					'pattern' => 'new-record',
					'comparison' => 'exact',
				]
			],
			'url_to'  => 'http://mts.test/new-record-1/',
			'status'  => 'active',
		]), 3 );
	}

	/**
	 * Get redirections.
	 */
	public function test_get_redirections() {
		$log = DB::get_redirections( [] );
		$this->assertEquals( $log['count'], 2 );

		// Search.
		$log = DB::get_redirections( [
			'search' => 'awesome'
		] );
		$this->assertEquals( $log['count'], 2 );

		// Status.
		$log = DB::get_redirections( [
			'status' => 'active'
		] );
		$this->assertEquals( $log['count'], 1 );

		$log = DB::get_redirections( [
			'status' => 'inactive'
		] );
		$this->assertEquals( $log['count'], 1 );
	}

	/**
	 * Match redirections for uri
	 */
	public function test_match_redirections() {
		$this->assertFalse( DB::match_redirections( '' ) );

		// Exact.
		$redirection = DB::match_redirections( 'new-record' );
		$this->assertEquals( $redirection['id'], 1 );

		// Exact but inactive.
		$redirection = DB::match_redirections( 'yawar-ahmed' );
		$this->assertFalse( $redirection );
	}

	/**
	 * Match redirections for source
	 */
	public function test_match_redirections_source() {
		$this->assertFalse( DB::match_redirections_source( '' ) );

		$redirection = DB::match_redirections_source( 'awesome' );
		$this->assertEquals( $redirection[0]['url_to'], 'http://mts.test/new-record-1/' );
	}

	/**
	 *  Get source by id.
	 */
	public function test_get_redirection_by_id() {
		$redirection = DB::get_redirection_by_id( 1 );
		$this->assertEquals( $redirection['url_to'], 'http://mts.test/new-record-1/' );

		$redirection = DB::get_redirection_by_id( 1, 'active' );
		$this->assertEquals( $redirection['url_to'], 'http://mts.test/new-record-1/' );

		$this->assertFalse( DB::get_redirection_by_id( 1, 'inactive' ) );
	}

	/**
	 * Get stats for dashboard widget.
	 *
	 * @return int
	 */
	public function test_get_stats() {
		$stats = DB::get_stats();
		$this->assertEquals( $stats->hits, 100 );
		$this->assertEquals( $stats->total, 2 );
	}

	/**
	 * Update a record.
	 */
	public function test_update() {
		$this->assertFalse( DB::update( [] ) );

		$this->assertFalse(DB::update([
			'url_to' => 'http://yahoo.test',
		]));

		$this->assertEquals(DB::update([
			'id'     => 2,
			'url_to' => 'http://yahoo.test',
		]), 1 );

		$this->assertEquals(DB::update_iff([
			'id'     => 2,
			'url_to' => 'http://yahoo.test',
		]), 2 );

		$this->assertEquals(DB::update_iff([
			'sources' => [
				[
					'pattern' => 'new-record',
					'comparison' => 'exact',
				]
			],
			'url_to'  => 'http://mts.test/new-record-1/',
			'status'  => 'active',
		]), 3 );
	}

	/**
	 * Update counter for redirection.
	 */
	public function test_update_access() {
		$this->assertFalse( DB::update_access() );

		$redirection = DB::get_redirection_by_id( 1 );
		DB::update_access( $redirection );
		$redirection = DB::get_redirection_by_id( 1 );
		$this->assertEquals( $redirection['hits'], 1 );
	}

	/**
	 * Delete multiple record.
	 */
	public function test_delete() {
		$this->assertEquals( DB::delete( [ 1, 2 ] ), 2 );
		$this->assertEquals( DB::delete( [ 3, 4 ] ), 0 );
	}

	/**
	 * Change record status to active or inactive.
	 */
	public function test_change_status() {
		$this->assertFalse( DB::change_status( 2, 'any' ) );

		DB::change_status( 2, 'active' );

		$redirection = DB::get_redirection_by_id( 2 );
		$this->assertEquals( $redirection['status'], 'active' );

		DB::change_status( 2, 'trashed' );

		$redirection = DB::get_redirection_by_id( 2 );
		$this->assertEquals( $redirection['status'], 'trashed' );

		// Clear Trashed.
		DB::change_status( 1, 'trashed' );
		$this->assertEquals( DB::clear_trashed(), 2 );
		$this->assertEquals( DB::clear_trashed(), 0 );

		// Periodic Clean Trashed.
		$this->assertEquals( DB::periodic_clean_trash(), 0 );
		DB::add([
			'sources' => [
				[
					'pattern' => 'new-record',
					'comparison' => 'exact',
				]
			],
			'url_to'  => 'http://mts.test/new-record-1/',
			'status'  => 'trashed',
			'updated' => gmdate( 'Y-m-d H:i:s', strtotime( '35 days ago' ) ),
		]);

		$this->assertEquals( DB::periodic_clean_trash(), 1 );
	}

	/**
	 * Check if status is valid.
	 */
	public function test_is_valid_status() {
		$this->assertTrue( $this->invokeMethod( $this->class_name, 'is_valid_status', [ 'active' ] ) );
		$this->assertFalse( $this->invokeMethod( $this->class_name, 'is_valid_status', [ 'rank-math' ] ) );
	}
}
