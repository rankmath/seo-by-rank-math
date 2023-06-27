<?php
/**
 * The Redirector
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests\Redirections;

use RankMath\Tests\UnitTestCase;
use MyThemeShop\Database\Database;
use RankMath\Redirections\DB;
use RankMath\Redirections\Redirector;
use RankMath\Redirections\Redirection;

defined( 'ABSPATH' ) || exit;

/**
 * TestRedirector class.
 */
class TestRedirector extends UnitTestCase {

	/**
	 * Clear logs completely.
	 */
	public function tearDown() {
		parent::tearDown();
		Database::table( 'rank_math_redirections' )->truncate();
	}

	public function test_for_no_redirection() {
		$this->go_to( home_url() );
		$redirector = $this->get_redirector();

		// Assert.
		$this->assertFalse( $this->getPrivate( $redirector, 'matched' ) );
	}

	/**
	 * Test for publish post redirection.
	 */
	public function test_for_cache() {
		$this->set_permalink_structure( '%postname%' );

		// Post.
		$post_id = $this->factory->post->create([
			'post_title' => 'Testing Pre Redirection'
		]);

		// Create Redirection.
		$redirection  = Redirection::create();
		$redirection->add_source( '/testing-pre-redirection', 'exact' );
		$redirection->add_destination( 'http://destination.test' );
		$redirection->save();

		// Assert.
		$this->go_to( home_url( '/testing-pre-redirection' ) );
		$redirector = $this->get_redirector();
		$this->assertTrue( $this->getPrivate( $redirector, 'cache' ) );
	}

	/**
	 * Test for publish post redirection.
	 */
	public function test_for_cache_by_url() {
		$this->set_permalink_structure( '%postname%' );

		// Create Redirection.
		$redirection  = Redirection::create();
		$redirection->add_source( '^category/(.*)', 'regex' );
		$redirection->add_destination( 'http://yahoo.test/$1' );
		$redirection->save();

		// Assert.
		$this->go_to( home_url( 'category/new-record' ) );

		$redirector = $this->get_redirector();
		$this->assertFalse( $this->getPrivate( $redirector, 'cache' ) );

		$redirector = $this->get_redirector();
		$this->assertTrue( $this->getPrivate( $redirector, 'cache' ) );
	}

	/**
	 * Tests for 410 header.
	 */
	public function test_redirector_410() {

		$redirection = Redirection::from([
			'sources'     => [
				[
					'pattern'    => 'checking-410-error',
					'comparison' => 'exact',
				]
			],
			'url_to'      => '',
			'header_code' => '410',
		]);
		$redirection_id = $redirection->save();

		$this->go_to( home_url( 'checking-410-error' ) );
		$redirector = $this->get_redirector();

		// Assert.
		$this->assertEquals( 410, $this->invokeMethod( $redirector, 'get_header_code' ) );
		$this->assertEquals( $redirection_id, $this->getPrivate( $redirector, 'matched' )['id'] );
	}

	/**
	 * Tests for 451 header.
	 */
	public function test_redirector_451() {

		$redirection = Redirection::from([
			'sources'     => [
				[
					'pattern'    => 'checking-451-error',
					'comparison' => 'exact',
				]
			],
			'url_to'      => '',
			'header_code' => '451',
		]);
		$redirection_id = $redirection->save();

		$this->go_to( home_url( 'checking-451-error' ) );
		$redirector = $this->get_redirector();

		// Assert.
		$this->assertEquals( 451, $this->invokeMethod( $redirector, 'get_header_code' ) );
		$this->assertEquals( $redirection_id, $this->getPrivate( $redirector, 'matched' )['id'] );
	}

	/**
	 * Tests whether the current request should be redirected to sitemap_index.xml.
	 *
	 * @dataProvider data_for_redirector
	 *
	 * @param string $case     The case to test.
	 * @param string $go_to    The URL to test for.
	 * @param string $expected The expected redirected URL.
	 */
	public function test_redirector( $case, $go_to, $expected ) {
		// Create Rule.
		if ( isset( $case['sources'] ) ) {
			$redirection = Redirection::create();
			$redirection->add_destination( $case['destination'] );
			$redirection->add_sources( $case['sources'] );
			$redirection_id = $redirection->save();
		}

		$this->go_to( home_url( $go_to ) );
		$redirector = $this->get_redirector();

		// Assert.
		$this->assertEquals( $expected, $this->getPrivate( $redirector, 'redirect_to' ) );
	}

	/**
	 * Provides test data for redirector tests.
	 *
	 * @return array
	 */
	public function data_for_redirector() {
		$cases = [
			[
				'go_to'       => [
					'checking-for-no-match'
				],
				'expected'    => [ null ],
			],
			[
				'sources'     => [
					[
						'pattern'    => 'pt/fashion-tag/casual-pt-pt',
						'comparison' => 'exact',
					]
				],
				'go_to'       => [
					'pt/fashion-tag/casual-pt-pt?products-per-page=9'
				],
				'expected'    => [ 'http://destination-with-querystring.test/?products-per-page=9' ],
				'destination' => 'http://destination-with-querystring.test/',
			],
			[
				'sources'         => [
					[
						'pattern'    => 'plugins/wordpress-seo',
						'comparison' => 'exact',
					],
				],
				'go_to'       => [
					'plugins/wordpress-seo/?utm_campaign=Rank Math&utm_source=RM Plugin Ready Step Tweet'
				],
				'expected'    => [ 'http://destination-with-querystring.test/?utm_campaign=Rank Math&utm_source=RM Plugin Ready Step Tweet' ],
				'destination' => 'http://destination-with-querystring.test/',
			],
			[
				'sources'     => [
					[
						'pattern' => '10',
						'comparison' => 'start',
					],
					[
						'pattern' => 'awesome',
						'comparison' => 'start',
					]
				],
				'go_to'       => [
					'10-perfect-plugins',
					'awesome-seo-plugins',
				],
				'expected'    => [
					'http://yahoo.test/',
					'http://yahoo.test/',
				],
				'destination' => 'http://yahoo.test/',
			],
			[
				'sources' => [
					[
						'pattern' => 'Vestido%20traditional%20longo%20de%20tecido%20africano%20Ndebele%20-%20Marisela%20Veludo%20-%20Passion4Fashion',
						'comparison' => 'exact',
					],
				],
				'go_to'       => [ 'Vestido traditional longo de tecido africano Ndebele - Marisela Veludo - Passion4Fashion' ],
				'expected'    => [ 'http://space.test/' ],
				'destination' => 'http://space.test/',
			],
			[
				'sources'     => [
					[
						'pattern' => '^category/(.*)',
						'comparison' => 'regex',
					],
				],
				'go_to'       => [ 'category/new-record' ],
				'expected'    => [ 'http://yahoo.test/new-record' ],
				'destination' => 'http://yahoo.test/$1',
			],
			[
				'sources'     => [
					[
						'pattern' => "([0-9]{4})\/([0-9]{2})\/(?!page\/)(.+)$",
						'comparison' => 'regex',
					],
				],
				'go_to'       => [ '2019/09/perfect-plugins' ],
				'expected'    => [ 'http://yahoo.test/perfect-plugins' ],
				'destination' => 'http://yahoo.test/$3',
			],
			[
				'sources'     => [
					[
						'pattern' => "(.*)/amp$",
						'comparison' => 'regex',
					],
				],
				'go_to'       => [ 'perfect-plugins/amp' ],
				'expected'    => [ 'http://yahoo.test/perfect-plugins' ],
				'destination' => 'http://yahoo.test/$1',
			],
		];

		$testdata = [];
		foreach ( $cases as $case ) {
			$go_tos   = $case['go_to'];
			$expected = $case['expected'];
			unset( $case['go_to'], $case['expected'] );
			foreach ( $go_tos as $index => $go_to ) {
				$testdata[] = [ $case, $go_to, $expected[ $index ] ];
			}
		}

		return $testdata;
	}

	private function get_redirector() {
		// Dsiable Headers.
		add_filter( 'rank_math/redirection/add_redirect_header', '__return_false' );
		add_filter( 'wp_redirect', '__return_false' );

		$redirector = new Redirector;

		// Enable Headers.
		remove_filter( 'rank_math/redirection/add_redirect_header', '__return_false' );
		remove_filter( 'wp_redirect', '__return_false' );

		return $redirector;
	}
}
