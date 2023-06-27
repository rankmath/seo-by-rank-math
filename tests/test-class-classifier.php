<?php
/**
 * The Link Classifier.
 *
 * Determines of a link is an outbound or internal one.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests;

use RankMath\Sitemap\Classifier;
use RankMath\Tests\UnitTestCase;

defined( 'ABSPATH' ) || exit;

/**
 * TestClassifier class.
 */
class TestClassifier extends UnitTestCase {

	/**
	 * Test url
	 *
	 * @dataProvider provider_urls
	 *
	 * @param string $base_url        URL.
	 * @param string $url_to_classify URL to classify.
	 * @param string $expected        Expected output.
	 */
	public function test_classify( $base_url, $url_to_classify, $expected ) {
		$classifier = new Classifier( $base_url );

		$this->assertEquals( $expected, $classifier->classify( $url_to_classify ) );
	}

	/**
	 * @return array
	 */
	public function provider_urls() {
		return [
			[ 'http://example.com', 'page', 'internal' ],
			[ 'http://example.com', 'http://example.com/page', 'internal' ],
			[ 'https://example.com', 'http://example.com/page', 'internal' ],
			[ 'http://example.com', 'http://test.com/page', 'external' ],
			[ 'http://example.com', 'http://dev.example.com', 'external' ],
			[ 'http://example.com/subdirectory', 'http://example.com/subdirectory2/', 'external' ],
			[ 'http://example.com/subdirectory', 'http://example.com/subdirectory/hi?query=set', 'internal' ],
			[ 'http://example.com', 'mailto:johndoe@example.com', 'external' ],
			[ 'http://example.com', 'mailto:example.com', 'external' ],
		];
	}
}
