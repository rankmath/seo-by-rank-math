<?php
/**
 * The Redirection Item.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests\Redirections;

use RankMath\Tests\UnitTestCase;
use RankMath\Redirections\Cache;
use RankMath\Redirections\Redirection;

defined( 'ABSPATH' ) || exit;

/**
 * TestRedirection class.
 */
class TestRedirection extends UnitTestCase {

	/**
	 * Retrieve Redirection instance.
	 */
	public function test_create() {
		// Check Empty.
		$redirection = Redirection::create();
		$this->assertEquals(
			$this->getPrivate( $redirection, 'data' ),
			[
				'id'          => 0,
				'sources'     => [],
				'url_to'      => '',
				'header_code' => '301',
				'hits'        => '0',
				'status'      => 'active',
				'created'     => '',
				'updated'     => '',
			]
		);
		$this->assertFalse( $redirection->save() );

		// Check source.
		$redirection->add_destination( 'http://destination.test' );
		$redirection->add_source( 'category/destination', 'exact' );
		$this->assertEquals(
			$this->getPrivate( $redirection, 'data' ),
			[
				'id'          => 0,
				'sources'     => [
					[
						'ignore'     => '',
						'pattern'    => 'category/destination',
						'comparison' => 'exact',
					],
				],
				'url_to'      => 'http://destination.test',
				'header_code' => '301',
				'hits'        => '0',
				'status'      => 'active',
				'created'     => '',
				'updated'     => '',
			]
		);

		$this->assertEquals( $redirection->save(), 1 );

		// Test with ID.
		$redirection = Redirection::create( 1 );
		$this->assertEquals(
			$this->getPrivate( $redirection, 'data' ),
			[
				'id'          => 1,
				'sources'     => [
					[
						'ignore'     => '',
						'pattern'    => 'category/destination',
						'comparison' => 'exact',
					],
				],
				'url_to'      => 'http://destination.test',
				'header_code' => '301',
				'hits'        => '0',
				'status'      => 'active',
				'created'     => '0000-00-00 00:00:00',
				'updated'     => '0000-00-00 00:00:00',
			]
		);
	}

	/**
	 * Create instance from array.
	 */
	public function test_from() {
		$redirection  = Redirection::from(
			[
				'id'          => 1,
				'sources'     => [
					[
						'pattern'    => 'category/destination',
						'comparison' => 'exact',
					],
				],
				'url_to'      => 'http://destination.test',
				'header_code' => '410',
				'hits'        => '100',
				'status'      => 'inactive',
			]
		);

		$this->assertEquals(
			$this->getPrivate( $redirection, 'data' ),
			[
				'id'          => 1,
				'sources'     => [
					[
						'ignore'     => '',
						'pattern'    => 'category/destination',
						'comparison' => 'exact',
					],
				],
				'url_to'      => 'http://destination.test',
				'header_code' => '410',
				'hits'        => '100',
				'status'      => 'inactive',
			]
		);
	}

	/**
	 * Test sources.
	 */
	public function test_sources() {
		$redirection = Redirection::create();
		$this->assertNull( $redirection->add_source( '', 'exact' ) );

		// Sanitize Source Url.
		$this->assertNull( $redirection->add_source( '#', 'exact' ) );
		$this->assertNull( $redirection->add_source( '/', 'exact' ) );

		// Sanitize Source Regex.
		$this->assertNull( $redirection->add_source( '#', 'regex' ) );
		$this->assertNull( $redirection->add_source( '/', 'regex' ) );

		$this->assertFalse( $this->invokeMethod( $redirection, 'sanitize_source_regex', [ '[\\' ] ) );
	}

	/**
	 * Test destination.
	 */
	public function test_destination() {
		$redirection = Redirection::create();

		$redirection->add_destination( 'destination/test' );
		$this->assertEquals( $redirection->url_to, 'http://example.org/destination/test' );

		$redirection->add_destination( '/destination/test' );
		$this->assertEquals( $redirection->url_to, 'http://example.org/destination/test' );

		$redirection->add_destination( '/destination/$1' );
		$this->assertEquals( $redirection->url_to, 'http://example.org/destination/$1' );

		$redirection->add_destination( 'https://destination.test' );
		$this->assertEquals( $redirection->url_to, 'https://destination.test' );

		$redirection->add_destination( '//destination.test' );
		$this->assertEquals( $redirection->url_to, '//destination.test' );

		$redirection->add_destination( 'http://destination.test' );
		$this->assertEquals( $redirection->url_to, 'http://destination.test' );
	}

	/**
	 * Sanitize redirection source URL.
	 *
	 * Following urls converted to URI:

	 */
	public function test_sanitize_source_url() {
		$redirection = Redirection::create();

		// '' => false
		$this->assertFalse( $this->invokeMethod( $redirection, 'sanitize_source_url', [ '' ] ) );

		// '/' => false
		$this->assertFalse( $this->invokeMethod( $redirection, 'sanitize_source_url', [ '/' ] ) );

		// website.com => false
		$this->assertFalse( $this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://example.org/' ] ) );

		// www.website.com => false
		$this->assertFalse( $this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://www.example.org/' ] ) );

		// https://website.com => false
		$this->assertFalse( $this->invokeMethod( $redirection, 'sanitize_source_url', [ 'https://example.org/' ] ) );

		// http://sub.website.com/URI => false
		$this->assertFalse( $this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://subdomain.example.org/' ] ) );

		// http://external.com/URI => false
		$this->assertFalse( $this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://yahoo.com/#test-hash' ] ) );

		// URI => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'test-uri' ] )
		);

		// /URI => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ '/test-uri' ] )
		);

		// #URI => #URI
		$this->assertEquals(
			'#test-hash',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ '#test-hash' ] )
		);

		// https://website.com#URI/ => #URI
		$this->assertEquals(
			'#test-hash',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://example.org#test-hash/' ] )
		);

		// https://website.com/#URI/ => #URI
		$this->assertEquals(
			'#test-hash',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://example.org/#test-hash/' ] )
		);

		// website.com/URI/ => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'example.org/test-uri/' ] )
		);

		// website.com/URI => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'example.org/test-uri' ] )
		);

		// http://website.com/URI/ => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://example.org/test-uri/' ] )
		);

		// http://website.com/URI => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://example.org/test-uri' ] )
		);

		// http://website.com/themes/portfolio/website.com => URI
		$this->assertEquals(
			'themes/portfolio/example.org',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://example.org/themes/portfolio/example.org' ] )
		);

		// https://website.com/URI/ => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'https://example.org/test-uri/' ] )
		);

		// https://website.com/URI => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'https://example.org/test-uri' ] )
		);

		// www.website.com/URI/ => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'www.example.org/test-uri/' ] )
		);

		// www.website.com/URI => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'www.example.org/test-uri' ] )
		);

		// http://www.website.com/URI/ => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://www.example.org/test-uri/' ] )
		);

		// http://www.website.com/URI => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'http://www.example.org/test-uri' ] )
		);

		// https://www.website.com/URI/ => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'https://www.example.org/test-uri/' ] )
		);

		// https://www.website.com/URI => URI
		$this->assertEquals(
			'test-uri',
			$this->invokeMethod( $redirection, 'sanitize_source_url', [ 'https://www.example.org/test-uri' ] )
		);
	}

	/**
	 * Collect WordPress Entity if any to add redirection cache.
	 */
	public function test_pre_redirection_cache() {
		$this->set_permalink_structure('%postname%');

		// Post.
		$post_id = $this->factory->post->create([
			'post_title' => 'Testing Pre Redirection'
		]);

		$redirection  = Redirection::create();
		$redirection->add_source( '/testing-pre-redirection', 'exact' );
		$redirection->add_destination( 'http://destination.test' );
		$redirection->save();

		$cache = Cache::get_by_object_id( $post_id, 'post' );
		$this->assertEquals( $cache->from_url, 'testing-pre-redirection' );
		$this->assertEquals( $cache->redirection_id, $redirection->get_id() );

		// Term.
		$term_id = $this->factory->term->create([
			'taxonomy' => 'category',
			'name'     => 'Testing Taxonomy'
		]);

		$redirection  = Redirection::create();
		$redirection->add_source( 'testing-taxonomy', 'exact' );
		$redirection->add_destination( 'http://destination.test' );
		$redirection->save();

		$cache = Cache::get_by_object_id( $term_id, 'term' );
		$this->assertEquals( $cache->from_url, 'testing-taxonomy' );
		$this->assertEquals( $cache->redirection_id, $redirection->get_id() );

		// User.
		$user_id = $this->factory->user->create([
			'user_login'    => 'testing-user',
			'role'          => 'administrator',
			'user_nicename' => 'Testing User'
		]);

		$redirection  = Redirection::create();
		$redirection->add_source( 'testing-user', 'exact' );
		$redirection->add_destination( 'http://destination.test' );
		$redirection->save();

		$cache = Cache::get_by_object_id( $user_id, 'user' );
		$this->assertEquals( $cache->from_url, 'testing-user' );
		$this->assertEquals( $cache->redirection_id, $redirection->get_id() );
	}

	/**
	 * Get the domain, without www. and protocol.
	 *
	 * @return string
	 */
	public function test_get_home_domain() {
		$redirection = Redirection::create();

		$this->assertEquals(
			'example.org',
			$this->invokeMethod( $redirection, 'get_home_domain' )
		);

		$this->assertEquals(
			'example.org',
			$this->invokeMethod( $redirection, 'get_home_domain' )
		);
	}
}
