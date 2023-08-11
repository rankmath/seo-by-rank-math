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
use RankMath\Settings;
use RankMath\Module\Manager;

defined( 'ABSPATH' ) || exit;

class Helpers extends WP_UnitTestCase {

	/**
	 * Get Setting.
	 */
	public function test_get_settings() {
		$settings = new Settings;

		// Default value.
		$this->assertEquals( $settings->get( 'general.fb_app_id', '123456789' ), '123456789' );

		// From DB.
		$this->assertEquals( $settings->get( 'general.breadcrumbs_home_label' ), 'Home' );
		$this->assertEquals( $settings->get( 'titles.twitter_card_type' ), 'summary_large_image' );
	}

	/**
	 * Get midnight time for date.
	 */
	public function test_get_midnight() {
		$midnight = Helper::get_midnight( strtotime( '23 September 2018' ) );
		$this->assertEquals( $midnight, '1537660800' );
	}

	/**
	 * Returns the value that is part of the given url.
	 */
	public function test_get_url_part() {
		$this->assertEmpty( Helper::get_url_part( '', 'host' ) );
		$this->assertEmpty( Helper::get_url_part( 'http://yahoo.com', 'port' ) );
		$this->assertEquals( 'yahoo.com', Helper::get_url_part( 'http://yahoo.com', 'host' ) );
		$this->assertEquals( 'http', Helper::get_url_part( 'http://yahoo.com', 'scheme' ) );
	}

	/**
	 * Get current page full url.
	 */
	public function test_get_current_page_url() {
		$this->assertEquals( 'http://example.org', Helper::get_current_page_url() );
		$this->assertEquals( 'http://example.org', Helper::get_current_page_url( true ) );
	}

	/**
	 * Get/Update search console data.
	 */
	public function test_search_console_data() {
		$key = 'rank_math_search_console_data';

		$this->assertFalse( Helper::search_console_data( false ) );
		$this->assertFalse( get_option( $key ) );

		$this->assertEquals(
			array(
				'authorized' => false,
				'profiles'   => array(),
			),
			Helper::search_console_data()
		);
		$this->assertFalse( get_option( $key ) );

		$this->assertEquals( array( 'test' ), Helper::search_console_data( array( 'test' ) ) );
		$this->assertEquals( array( 'test' ), get_option( $key ) );
	}

	/**
	 * Get module by id.
	 */
	public function test_get_module() {
		// Setup Module Manager.
		rank_math()->manager = new Manager;
		rank_math()->manager->setup_modules();
		delete_option( 'rank_math_modules' );

		// Check if modules setuped.
		$this->assertArrayHasKey( 'link-counter', rank_math()->manager->modules );
		$this->assertArrayHasKey( 'search-console', rank_math()->manager->modules );

		// If not active.
		$this->assertFalse( Helper::is_module_active( 'link-counter' ) );
		$this->assertFalse( Helper::is_module_active( 'search-console' ) );

		// Make modules activated.
		update_option( 'rank_math_modules', array( 'link-counter', 'search-console' ) );

		// If active.
		$this->assertTrue( Helper::is_module_active( 'link-counter' ) );
		$this->assertTrue( Helper::is_module_active( 'search-console' ) );

		// Load modules.
		rank_math()->manager->load_modules();
		$this->assertInstanceOf( 'RankMath\Links\Links', Helper::get_module( 'link-counter' ) );
		$this->assertFalse( Helper::search_console() );
	}

	/**
	 * Modify module status.
	 */
	public function test_update_modules() {
		// On.
		Helper::update_modules( array( 'awesome' => 'on' ) );
		$this->assertContains( 'awesome', get_option( 'rank_math_modules' ) );

		// Off.
		Helper::update_modules( array( 'awesome' => 'off' ) );
		$this->assertNotContains( 'awesome', get_option( 'rank_math_modules' ) );
	}
}
