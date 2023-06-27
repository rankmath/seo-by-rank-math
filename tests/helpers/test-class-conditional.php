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

class ConditionalHelper extends WP_UnitTestCase {

	/**
	 * The Conditional helpers.
	 */
	public function test_conditional_functions() {
		$this->assertFalse( Helper::is_site_connected() );

		// Whitelabel.
		$this->assertFalse( Helper::is_whitelabel() );

		add_filter( 'rank_math/whitelabel', '__return_true' );
		$this->assertTrue( Helper::is_whitelabel() );
	}

	/**
	 * Checks if the plugin is configured.
	 */
	public function test_is_configured() {
		$this->assertFalse( Helper::is_configured() );

		Helper::is_configured( true );
		$this->assertTrue( Helper::is_configured() );
		delete_option( 'rank_math_is_configured' );
	}

	/**
	 * Check if author archive are indexable
	 */
	public function test_is_author_archive_indexable() {

		// 1. False
		rank_math()->settings->set( 'titles', 'disable_author_archives', true );
		$this->assertFalse( Helper::is_author_archive_indexable() );

		// 2. False
		rank_math()->settings->set( 'titles', 'disable_author_archives', false );
		rank_math()->settings->set( 'titles', 'noindex_author_archive', true );
		$this->assertFalse( Helper::is_author_archive_indexable() );

		// 3. True
		rank_math()->settings->set( 'titles', 'disable_author_archives', false );
		rank_math()->settings->set( 'titles', 'author_custom_robots', false );
		$this->assertTrue( Helper::is_author_archive_indexable() );
	}
}
