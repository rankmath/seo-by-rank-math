<?php
/**
 * The Redirection
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests\Redirections;

use RankMath\Tests\UnitTestCase;
use RankMath\Redirections\DB;
use RankMath\Redirections\Admin;
use RankMath\Redirections\Table;
use MyThemeShop\Database\Database;
use RankMath\Redirections\Redirection;

defined( 'ABSPATH' ) || exit;

/**
 * TestMisc class.
 */
class TestMisc extends UnitTestCase {

	public function test_admin() {
		$_GET['page'] = 'rank-math-redirections';
		$admin = new Admin;
		$this->assertTrue( isset( $admin->directory ) );

		// Add Settings.
		$this->assertArrayHasKey( 'redirections', $admin->add_settings( [] ) );
	}

	public function test_table() {
		$GLOBALS['hook_suffix'] = 'rank-math_page_rank-math-redirections';

		$this->create_redirection();

		$table = new Table;
		$table->prepare_items();

		ob_start();
		$table->views();
		$table->search_box( esc_html__( 'Search', 'rank-math' ), 's' );
		$table->display();
		ob_get_clean();

		$this->assertEquals( 1, $table->get_pagination_arg( 'total_items' ) );

		// Trash page.
		$_GET['status'] = 'trashed';
		DB::change_status( 1, 'trashed' );

		$table = new Table;
		$table->prepare_items();

		ob_start();
		$table->views();
		$table->search_box( esc_html__( 'Search', 'rank-math' ), 's' );
		$table->display();
		ob_get_clean();

		Database::table( 'rank_math_redirections' )->truncate();
	}

	private function create_redirection() {
		$redirection = Redirection::create();
		$redirection->add_destination( 'http://destination.test' );
		$redirection->add_source( 'category/destination', 'exact' );
		$redirection->save();
	}
}
