<?php
/**
 * Unit tests for post type sitemap provider.
 *
 * @since      1.0.28.2
 * @package    RankMath
 * @subpackage RankMath\Tests\Sitemap\Providers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap\Providers;

use RankMath\Tests\UnitTestCase;
use RankMath\Sitemap\Providers\Post_Type;

defined( 'ABSPATH' ) || exit;

class TestPost_Type extends UnitTestCase {

	/**
	 * @var Post_Type
	 */
	private static $instance;

	/**
	 * Set up our double class
	 */
	public function setUp() {
		parent::setUp();
		self::$instance = new Post_Type;
	}

	/**
	 * @covers RankMath\Sitemap\Providers\Post_Type::get_index_links
	 */
	public function test_get_index_links() {
		// Add post.
		$this->factory->post->create();

		// Set Sitemap off.
		rank_math()->settings->set( 'sitemap', 'pt_post_sitemap', false );
		$index_links = self::$instance->get_index_links( 1000 );
		$this->assertEmpty( $index_links );

		// Set sitemap on.
		rank_math()->settings->set( 'sitemap', 'pt_post_sitemap', true );
		$index_links = self::$instance->get_index_links( 1000 );
		$this->assertNotEmpty( $index_links );
		$this->assertContains( 'http://example.org/post-sitemap.xml', $index_links[0] );

		// Add post
		$this->factory->post->create();

		// Check for pagination.
		$index_links = self::$instance->get_index_links( 1 );
		$this->assertContains( 'http://example.org/post-sitemap1.xml', $index_links[0] );
		$this->assertContains( 'http://example.org/post-sitemap2.xml', $index_links[1] );
	}

	/**
	 * @covers RankMath\Sitemap\Providers\Post_Type::get_sitemap_links
	 */
	public function test_get_sitemap_links() {
		rank_math()->settings->set( 'titles', 'pt_post_custom_robots', false );
		rank_math()->settings->set( 'sitemap', 'pt_post_sitemap', true );

		$post_id = $this->factory->post->create();

		$sitemap_links = self::$instance->get_sitemap_links( 'post', 1000, 0 );
		$this->assertContains( get_permalink( $post_id ), $sitemap_links[0] );

		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'rank_math_robots', [ 'noindex' ] );
		$sitemap_links = self::$instance->get_sitemap_links( 'post', 1000, 0 );
		$this->assertNotContains( get_permalink( $post_id ), $sitemap_links[0] );
	}
}
