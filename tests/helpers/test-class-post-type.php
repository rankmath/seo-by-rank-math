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

class PostTypeHelper extends WP_UnitTestCase {

	/**
	 * Is post indexable.
	 */
	public function test_is_post_indexable() {
		rank_math()->settings->set( 'sitemap', 'exclude_posts', '3,4' );
		$this->assertFalse( Helper::is_post_indexable( 3 ) );

		$post_id = $this->factory()->post->create(array(
			'post_type'   => 'post',
			'post_title'  => 'Post Title',
			'post_status' => 'publish',
		));
		$this->assertTrue( Helper::is_post_indexable( $post_id ) );

		// By Settings.
		rank_math()->settings->set( 'titles', 'pt_post_custom_robots', true );
		rank_math()->settings->set( 'titles', 'pt_post_robots', array( 'noindex' ) );
		$this->assertFalse( Helper::is_post_indexable( $post_id ) );

		// Noindex by Meta.
		update_post_meta( $post_id, 'rank_math_robots', array( 'noindex' ) );
		$this->assertFalse( Helper::is_post_indexable( $post_id ) );
	}

	/**
	 * Check if post type is indexable.
	 */
	public function test_is_post_type_indexable() {

		// If noindex is set.
		rank_math()->settings->set( 'titles', 'pt_post_custom_robots', true );
		rank_math()->settings->set( 'titles', 'pt_post_robots', array( 'noindex' ) );
		$this->assertFalse( Helper::is_post_type_indexable( 'post' ) );

		// If robots is set to defaults.
		$this->assertTrue( Helper::is_post_type_indexable( 'page' ) );

		// If disabled sitemap.
		rank_math()->settings->set( 'sitemap', 'pt_page_sitemap', false );
		$this->assertFalse( Helper::is_post_type_indexable( 'page' ) );
	}

	/**
	 * Is post explicitly excluded.
	 */
	public function test_is_post_excluded() {
		$this->assertFalse( Helper::is_post_excluded( 1 ) );
		$this->assertFalse( Helper::is_post_excluded( 2 ) );

		$this->assertTrue( Helper::is_post_excluded( 3 ) );
		$this->assertTrue( Helper::is_post_excluded( 4 ) );
	}

	/**
	 * Gets post type label.
	 */
	public function test_get_post_type_label() {
		$this->assertFalse( Helper::get_post_type_label( 'new-post' ) );
		$this->assertEquals( 'Posts', Helper::get_post_type_label( 'post' ) );
		$this->assertEquals( 'Post', Helper::get_post_type_label( 'post', true ) );
	}
}
