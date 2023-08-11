<?php
/**
 * Unit tests for admin Helper
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests\Admin;

use WP_UnitTestCase;
use RankMath\Admin\Admin_Helper;
use MyThemeShop\Helpers\WordPress;
use RankMath\Robots_Txt;

defined( 'ABSPATH' ) || exit;

class AdminHelper extends WP_UnitTestCase {

	/**
	 * Get robots.txt related data.
	 */
	public function test_get_robots_data() {
		$robots_data = Robots_Txt::get_robots_data();

		// Array has key.
		$this->assertArrayHasKey( 'exists', $robots_data );
		$this->assertArrayHasKey( 'default', $robots_data );

		// Check internal type.
		$this->assertInternalType( 'bool', $robots_data['exists'] );
		$this->assertInternalType( 'string', $robots_data['default'] );

		// Check if file not exists.
		$this->assertFalse( $robots_data['exists'] );
		$this->assertContains( 'Disallow:', $robots_data['default'] );

		// Check if file exists.
		$wp_filesystem = WordPress::get_filesystem();
		$wp_filesystem->put_contents( ABSPATH . 'robots.txt', 'Disallow:' );

		$robots_data = Robots_Txt::get_robots_data();
		$this->assertTrue( $robots_data['exists'] );
		$this->assertContains( 'Disallow:', $robots_data['default'] );

		$wp_filesystem->delete( ABSPATH . 'robots.txt' );
	}

	/**
	 * Get htaccess related data.
	 */
	public function test_get_htaccess_data() {
		$wp_filesystem = WordPress::get_filesystem();
		$htaccess_file = get_home_path() . '.htaccess';

		// File not exists.
		$data = Admin_Helper::get_htaccess_data();
		$this->assertFalse( $data );

		// File exists.
		$wp_filesystem->put_contents( $htaccess_file, '# Begin WordPress' );
		$data = Admin_Helper::get_htaccess_data();

		// Array has key.
		$this->assertArrayHasKey( 'content', $data );
		$this->assertArrayHasKey( 'writable', $data );

		// Check internal type.
		$this->assertInternalType( 'bool', $data['writable'] );
		$this->assertInternalType( 'string', $data['content'] );

		// Check for data.
		$this->assertTrue( $data['writable'] );
		$this->assertContains( '# Begin WordPress', $data['content'] );

		$wp_filesystem->delete( $htaccess_file );
	}

	/**
	 * Get taxonomies as choices.
	 */
	public function test_get_taxonomies_options() {
		$taxonomies = Admin_Helper::get_taxonomies_options();
		$this->assertArrayHasKey( 'off', $taxonomies );
		$this->assertArrayHasKey( 'category', $taxonomies );
	}

	/**
	 * Get tooltip html.
	 */
	public function test_get_tooltip() {
		$message       = 'Test tooltip';
		$assert_output = '<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span>' . $message . '</span></span>';
		$this->assertEquals( $assert_output, Admin_Helper::get_tooltip( $message ) );
	}

	/**
	 * Get admin view file.
	 */
	public function test_get_view() {
		$this->assertEquals( rank_math()->admin_dir() . 'views/test.php', Admin_Helper::get_view( 'test' ) );
	}

	/**
	 * Registration data get/update.
	 */
	public function test_registration_data() {
		$key = 'rank_math_connect_data';

		Admin_Helper::get_registration_data( false );
		$this->assertFalse( get_option( $key ) );

		Admin_Helper::get_registration_data( 'testdata' );
		$this->assertEquals( 'testdata', get_option( $key ) );

		$this->assertEquals( 'testdata', Admin_Helper::get_registration_data() );
	}

	/**
	 * Compare values.
	 */
	public function test_compare_values() {
		$this->assertSame( -2.0, Admin_Helper::compare_values( 4, 2 ) );
		$this->assertSame( 3.0, Admin_Helper::compare_values( 1, 4 ) );
		$this->assertSame( -50.0, Admin_Helper::compare_values( 4, 2, true ) );
		$this->assertSame( 300.0, Admin_Helper::compare_values( 1, 4, true ) );
	}

	/**
	 * Check if current page is post create/edit screen.
	 */
	public function test_is_post_edit() {
		global $pagenow;
		$old_pagenow = $pagenow;

		$pagenow = 'post.php';
		$this->assertTrue( Admin_Helper::is_post_edit() );

		$pagenow = 'post-new.php';
		$this->assertTrue( Admin_Helper::is_post_edit() );

		$pagenow = 'plugins.php';
		$this->assertFalse( Admin_Helper::is_post_edit() );

		$pagenow = $old_pagenow;
	}

	/**
	 * Check if current page is term create/edit screen.
	 */
	public function test_is_term_edit() {
		global $pagenow;
		$old_pagenow = $pagenow;

		$pagenow = 'term.php';
		$this->assertTrue( Admin_Helper::is_term_edit() );

		$pagenow = 'post-new.php';
		$this->assertFalse( Admin_Helper::is_term_edit() );

		$pagenow = $old_pagenow;
	}

	/**
	 * Check if current page is user create/edit screen.
	 */
	public function test_is_user_edit() {
		global $pagenow;
		$old_pagenow = $pagenow;

		$pagenow = 'profile.php';
		$this->assertTrue( Admin_Helper::is_user_edit() );

		$pagenow = 'user-edit.php';
		$this->assertTrue( Admin_Helper::is_user_edit() );

		$pagenow = 'post.php';
		$this->assertFalse( Admin_Helper::is_term_profile_page() );

		$pagenow = $old_pagenow;
	}

	/**
	 * Check if current page is user or term create/edit screen.
	 */
	public function test_is_term_profile_page() {
		global $pagenow;
		$old_pagenow = $pagenow;

		$pagenow = 'term.php';
		$this->assertTrue( Admin_Helper::is_term_profile_page() );

		$pagenow = 'profile.php';
		$this->assertTrue( Admin_Helper::is_term_profile_page() );

		$pagenow = 'user-edit.php';
		$this->assertTrue( Admin_Helper::is_term_profile_page() );

		$pagenow = 'post.php';
		$this->assertFalse( Admin_Helper::is_term_profile_page() );

		$pagenow = $old_pagenow;
	}
}
