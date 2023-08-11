<?php
/**
 * Unit tests for taxonomy sitemap provider.
 *
 * @since      1.0.21
 * @package    RankMath
 * @subpackage RankMath\Tests\Sitemap\Providers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap\Providers;

use RankMath\Tests\UnitTestCase;
use RankMath\Sitemap\Providers\Taxonomy;

defined( 'ABSPATH' ) || exit;

class TestTaxonomy extends UnitTestCase {

	/**
	 * @var Taxonomy
	 */
	private static $instance;

	/**
	 * Set up our double class
	 */
	public function setUp() {
		parent::setUp();
		self::$instance = new Taxonomy;
	}

	/**
	 * @covers RankMath\Sitemap\Providers\Taxonomy::get_sitemap_links
	 */
	public function test_get_sitemap_links() {
		rank_math()->settings->set( 'titles', 'tax_category_custom_robots', false );
		rank_math()->settings->set( 'sitemap', 'tax_category_sitemap', true );
		rank_math()->settings->set( 'sitemap', 'tax_category_include_empty', true );

		$term_id = $this->factory->category->create();
		$post_id = $this->factory->post->create();
		wp_set_post_categories( $post_id, $term_id );

		$sitemap_links = self::$instance->get_sitemap_links( 'category', 1000, 0 );
		$sitemap_links = end( $sitemap_links );
		$this->assertContains( get_category_link( $term_id ), $sitemap_links['loc'] );
	}

	/**
	 * @covers RankMath\Sitemap\Providers\Taxonomy::get_index_links
	 */
	public function test_get_index_links() {

		// Check for emptiness.
		$index_links = self::$instance->get_index_links( 100 );
		$this->assertEmpty( $index_links );

		// Add post and taxonomy.
		$term_id = $this->factory->category->create();
		$post_id = $this->factory->post->create();
		wp_set_post_categories( $post_id, $term_id );

		// Set Sitemap off.
		rank_math()->settings->set( 'sitemap', 'tax_category_sitemap', false );
		$index_links = self::$instance->get_index_links( 100 );
		$this->assertEmpty( $index_links );

		// Set sitemap on.
		rank_math()->settings->set( 'sitemap', 'tax_category_sitemap', true );
		$index_links = self::$instance->get_index_links( 100 );
		$this->assertNotEmpty( $index_links );
		$this->assertContains( 'http://example.org/category-sitemap.xml', $index_links[0] );

		// Add post and taxonomy.
		$term_id = $this->factory->category->create();
		$post_id = $this->factory->post->create();
		wp_set_post_categories( $post_id, $term_id );

		// Check for pagination.
		$index_links = self::$instance->get_index_links( 1 );
		$this->assertContains( 'http://example.org/category-sitemap1.xml', $index_links[0] );
		$this->assertContains( 'http://example.org/category-sitemap2.xml', $index_links[1] );
	}
}
